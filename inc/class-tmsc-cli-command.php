<?php

WP_CLI::add_command( 'tmsconnect', 'TMSC_CLI_Command' );

/**
 * CLI Commands for SearchPress
 */
class TMSC_CLI_Command extends WP_CLI_Command {

	public $batch_size = 40;

	/**
	 * Sync objects from TMS.
	 *
	 * ## OPTIONS
	 *
	 * [--reset]
	 * : Clears out current sync cursors and status. If this is not used, sync will pick up where it last left off.
	 *
	 * [--batch-size=<num>]
	 * : Process this many objects as a time. Defaults to 200.
	 *
	 * [--force]
	 * : Always write new objects even if hash matches.
	 *
	 * [--post-processing]
	 * : Don't migrate and only run post processing of meta data.
	 *
	 * [<processor>...]
	 * : By default, this subcommand will run on all defined processors.
	 * Instead, you can specify one or more individual processors to run. Multiple
	 * processor slugs should be space-delimited (see examples)
	 *
	 * ## EXAMPLES
	 *
	 *      # Clear the current sync and resync the whole site.
	 *      wp tmsconnect sync --reset
	 *
	 *      # Run only the post processing.
	 *      wp tmsconnect sync --post-processing
	 *
	 *      # Sync all data using current cursors with a batch size of 200.
	 *      wp tmsconnect sync --batch-size=200
	 *
	 *      # Do only an object sync using current cursors.
	 *      wp tmsconnect sync object
	 *
	 *      # Sync using only taxonomy, exhbition and constituent processors using current cursors.
	 *      wp tmsconnect sync taxonomy exhibition constituent
	 *
	 * @synopsis [--reset] [--batch-size=<num>] [<processor>...] [--force] [--post-processing]
	 */
	public function sync( $args, $assoc_args ) {
		$timestamp_start = microtime( true );

		if ( ! empty( $assoc_args['reset'] ) ) {
			$this->reset();
		}

		if ( ! empty( $assoc_args['force'] ) ) {
			add_filter( 'tmsc_force_sync_update', '__return_true' );
		}

		if ( ! empty( $assoc_args['batch-size'] ) ) {
			$this->batch_size = (int) $assoc_args['batch-size'];
			add_filter( 'tmsc_sync_batch_size', array( $this, 'get_batch_size' ) );
		}

		if ( empty( $assoc_args['post-processing'] ) ) {
			$processors = tmsc_get_system_processors();
			if ( ! empty( $args ) ) {
				$processors_cursor = array();
				foreach ( $args as $processor ) {
					if ( in_array( $processor, array_keys( $processors ), true ) ) {
						$processors_cursor[ $processor ] = $processors[ $processor ];
					}
				}
				$processors = $processors_cursor;
			}
			WP_CLI::line( 'Setting up cursor for processors: ' . implode( ', ', array_values( $processors ) ) );
			update_option( 'tmsc-processors-cursor', $processors );
			wp_cache_delete( 'tmsc-processors-cursor', 'options' );

			$message = __( 'Syncing TMS Objects', 'tmsc' );
			tmsc_set_sync_status( $message );

			// Set-up a persistant connection.
			\TMSC\TMSC_Sync::instance()->get_connection();

			$system_processors = get_option( 'tmsc-processors-cursor', tmsc_get_system_processors() );
			// Register and instantiate processors
			foreach ( $system_processors as $processor_slug => $processor_class_slug ) {
				// Instantiate our processor.
				$current_processor = \TMSC\TMSC::instance()->get_processor( $processor_class_slug );
				$doing_migration = true;
				$cursor = tmsc_get_cursor( $processor_slug );
				do {

					if ( empty( $cursor['completed'] ) ) {
						WP_CLI::line( "Processing {$processor_slug} with offset {$cursor['offset']}" );

						$doing_migration = true;
						\TMSC\TMSC::instance()->migrate( $processor_class_slug );
						$cursor = tmsc_get_cursor( $processor_slug );
					} else {
						WP_CLI::success( sprintf(
							__( "Sync for %s Processor Complete!\n%d\tfinal offset", 'tmsc' ),
							$processor_class_slug,
							$cursor['offset']
						) );
						$doing_migration = false;
					}
					$this->contain_memory_leaks();
				} while ( $doing_migration );
			}

			\TMSC\TMSC_Sync::instance()->terminate_connection();
			WP_CLI::success( 'Processor Migrations Complete!' );
		}

		WP_CLI::line( "Post Processing Meta Data" );
		\TMSC\TMSC_Sync::instance()->complete_sync();
		WP_CLI::success( 'Post Processing Complete!' );

		$this->finish( $timestamp_start );
	}

	/**
	 * Wipe out our cursor data.
	 */
	private function reset() {
		WP_CLI::line( 'Resetting Cursors' );
		delete_option( 'tmsc-last-sync-date' );
		wp_cache_delete( 'tmsc-last-sync-date', 'options' );
		foreach ( tmsc_get_system_processors() as $type => $label ) {
			delete_option( "tmsc-cursor-{$type}" );
			wp_cache_delete( "tmsc-cursor-{$type}", 'options' );
		}
		delete_option( 'tmsc-processors-cursor' );
		wp_cache_delete( 'tmsc-processors-cursor', 'options' );
	}

	/**
	 * Get our batch size.
	 */
	public function get_batch_size() {
		return $this->batch_size;
	}

	private function finish( $timestamp_start ) {
		WP_CLI::line( "Process completed in " . $this->time_format( microtime( true ) - $timestamp_start ) );
		WP_CLI::line( "Max memory usage was " . round( memory_get_peak_usage() / 1024 / 1024, 2 ) . "M" );
	}

	private function time_format( $seconds ) {
		$ret = '';
		if ( $seconds > DAY_IN_SECONDS ) {
			$days = floor( $seconds / DAY_IN_SECONDS );
			$ret .= $days . 'd';
			$seconds -= $days * DAY_IN_SECONDS;
		}
		if ( $seconds > HOUR_IN_SECONDS ) {
			$hours = floor( $seconds / HOUR_IN_SECONDS );
			$ret .= $hours . 'h';
			$seconds -= $hours * HOUR_IN_SECONDS;
		}
		if ( $seconds > MINUTE_IN_SECONDS ) {
			$minutes = floor( $seconds / MINUTE_IN_SECONDS );
			$ret .= $minutes . 'm';
			$seconds -= $minutes * MINUTE_IN_SECONDS;
		}
		return $ret . absint( ceil( $seconds ) ) . 's';
	}

	/**
	 * Prevent memory leaks from growing out of control
	 */
	private function contain_memory_leaks() {
		global $wpdb, $wp_object_cache;
		$wpdb->queries = array();
		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}
		$wp_object_cache->group_ops = array();
		$wp_object_cache->stats = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache = array();
		if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset();
		}
	}

	/**
	 * Associate all linked terms posts with corresponding terms.
	 * ## OPTIONS
	 *
	 * [--post-type]
	 * : Specify the post type you want to set linked terms for.
	 */
	public function set_linked_terms( $args, $assoc_args ) {
		if ( ! empty( $assoc_args['post-type'] ) ) {
			$post_type = $assoc_args['post-type'];
			$offset = 0;
			$num_posts = 200;
			$batch_args = [
				'post_type' => $post_type,
				'offset' => $offset,
				'posts_per_page' => $num_posts,
			];
			$posts = get_posts( $batch_args );
			while ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$term = tmsc_get_linked_term( $post->ID );
					if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
						wp_set_object_terms( $post->ID, [ (int) $term->term_id ], $term->taxonomy, false );
					}
				}
				$offset = $offset + $num_posts;
				$batch_args['offset'] = $offset;
				$posts = get_posts( $batch_args );
			}
		} else {
			WP_CLI::error( 'Please specify a post type.', true );
		}
	}
}
