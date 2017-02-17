<?php

namespace TMSC;

class TMSC {

	private static $instance;

	// The default zones we load objects into
	public $default_zones = array();

	public $processors = array();

	/**
	 * Constructor
	 *
	 * @params string $name
	 * @params url $name optional
	 * @return void
	 */
	public function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new \TMSC\TMSC();
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		/**
		 * Our Default Zones
		 */
		$this->default_zones = array(
			array(
				'slug' => 'on-view',
				'name' => __( 'On View', 'tmsc' ),
				'description' => __( 'Manages the collection objects that are currently on view', 'tmsc' ),
			),
			array(
				'slug' => 'collection-highlights',
				'name' => __( 'Collection Highlights', 'tmsc' ),
				'description' => __( 'Manages the collection objects that are currently highlighted', 'tmsc' ),
			),
			array(
				'slug' => 'new-aquisitions',
				'name' => __( 'New Aquisitions', 'tmsc' ),
				'description' => __( 'Manages the collection objects that are new acquisitions, through both gift and purchase', 'tmsc' ),
			),
			array(
				'slug' => 'on-loan',
				'name' => __( 'On Loan', 'tmsc' ),
				'description' => __( 'Manages the collection objects that are currently on loan to other museums for temporary exhibitions or long-term display', 'tmsc' ),
			),
		);

		// Add zoninator post type support
		add_action( 'zoninator_post_init', array( $this, 'zoninator_post_type_support' ), 99 );
		add_filter( 'zoninator_recent_posts_args', array( $this, 'filter_zone_post_types' ) );
		add_filter( 'zoninator_search_args', array( $this, 'filter_zone_post_types' ) );

		// Add in our sync options page
		if ( function_exists( 'fm_register_submenu_page' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				fm_register_submenu_page( 'tmsc_guide_terms', 'edit.php?post_type=tms_object', __( 'Sync Guide Terms', 'tmsc' ) );
			}
		}
		add_action( 'fm_submenu_tmsc_guide_terms', array( $this, 'sync_init' ) );
	}

	/**
	 * Keep track of our custom DB system processors.
	 * @param string. $name. Namespaced class name.
	 * @return void.
	 */
	public function register_processor( $name = '' ) {
		if ( ! empty( $name ) ) {
			self::$instance->processors[ $name ] = array( $name );
		}
		return self::$instance->processors;
	}

	/**
	 * Add the Zoninator taxonomy to the custom post types that require it.
	 * The function calls below just return false if either the taxonomy or post type doesn't exist.
	 *
	 * @access public
	 * @return void
	 */
	public function zoninator_post_type_support() {

		$post_types = array( 'tms_object' );

		// Add the Zoninator taxonomy for the defined post types
		foreach ( $post_types as $post_type ) {
			add_post_type_support( $post_type, 'zoninator_zones' );
			register_taxonomy_for_object_type( 'zoninator_zones', $post_type );
		}

		// The Zoninator internal post_types array has already been written and cached by this point.
		// We must override it to enabled these post types for the recent posts dropdown and AJAX searches.
		z_get_zoninator()->post_types = array_merge( z_get_zoninator()->post_types, $post_types );
	}

	/**
	 * Only display tmsc objects in our TMSConnect Zones.
	 */
	public function filter_zone_post_types( $args ) {
		$active_zone_id = z_get_zoninator()->_get_request_var( 'zone_id', 0, 'absint' );
		$tmsc_zones = wp_list_pluck( $this->default_zones, 'slug' );
		if ( ! empty( $active_zone_id ) ) {
			$zone = z_get_zone( $active_zone_id );
			if ( in_array( $zone->slug, $tmsc_zones ) ) {
				$args['post_type'] = array( 'tms_object' );
			}
		}

		return $args;
	}

	/**
	 * Generate our sync options admin area.
	 *
	 */
	public function sync_init() {
		$fm = new \Fieldmanager_Group( array(
			'name' => 'tmsc_guide_terms',
			'children' => array(
				'term' => new \Fieldmanager_Group( array(
					'description' => __( 'Use the CN number of the guide terms that should serve as parent taxonomies', 'tmsc' ),
					'collapsible' => true,
					'label' => __( 'Guide Terms', 'tmsc' ),
					'children' => array(
						'data' => new \Fieldmanager_Group( array(
							'limit' => 0,
							'add_more_label' => __( 'Add another guide term', 'tmsc' ),
							'children' => array(
								'taxonomy_map' => new \Fieldmanager_Select( __( 'Map to Taxonomy', 'tmsc' ), array(
									'datasource' => new \Fieldmanager_Datasource( array(
										'options' => get_taxonomies(),
									) ),
								) ),
								'CN' => new \Fieldmanager_Textfield( __( 'Guide Term CN', 'tmsc' ) ),
							),
						) ),
					),
				) ),
			),
		) );
		$fm->activate_submenu_page();
	}

	/**
	 * Migrate a batch of objects.
	 *
	 */
	public function migrate( $args = array(), $assc_args = array() ) {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$dry = ( ! empty( $assc_args['dry'] ) );
		$cursor = ( ! empty( $assc_args['start'] ) ) ? $assc_args['start'] : false;
		$batch_size = ( ! empty( $assc_args['batch'] ) ) ? $assc_args['batch'] : false;

		$short_name = array_shift( $args );
		$processor = self::$instance->get_processor( $short_name );
		$processor->set_dry_run( $dry );

		/*
		self::$instance->set_processor_opts( $processor, $assc_args );

		if ( $batch_size ) {
			$processor->set_batch_size( $batch_size );
		}
		*/
		/*
		if ( $cursor ) {
			$old_cursor = $processor->get_cursor();
			$cursor = array_merge( $processor->get_starting_cursor(), $processor->parse_cursor( $cursor ) );
			$processor->set_cursor( $cursor );
		}

		if ( ! $processor->is_finished() ) {
			$processor->run();
		}

		if ( $cursor ) {
			$processor->set_cursor( $old_cursor );
		}*/
	}

	/**
	 * Export information for one item. This subcommand is incomplete.
	 */
	public function describe( $args = array(), $assc_args = array() ) {
		$short_name = array_shift( $args );
		$processor = self::$instance->get_processor( $short_name );
	}

	/**
	 * Migrate all objects.
	 */
	public function migrate_all( $args = array(), $assc_args = array() ) {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$dry = ! empty( $assc_args['dry'] );
		foreach ( self::$instance->processors( $args ) as $processor ) {
			self::$instance->set_processor_opts( $processor, $assc_args );
			while ( ! $processor->is_finished() ) {
				$processor->run();
				tmsc_stop_the_insanity();
			}
		}
	}

	/**
	 * Apply a function to all post_meta matching the given key and update the
	 * meta value with the return value of the function.
	 */
	public function filter_post_meta( $args = array() ) {
		global $wpdb;
		$meta_key = array_shift( $args );
		$function = array_shift( $args );
		if ( is_callable( $function ) ) {
			$ids = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_id, meta_value from {$wpdb->postmeta} WHERE meta_key = %s",
					$meta_key
				)
			);
			foreach ( $ids as $meta ) {
				$new_value = call_user_func_array( $function, array( $meta->post_id, $meta_key, $meta->meta_value ) );
				update_post_meta( $meta->post_id, $meta_key, $new_value, $meta->meta_value );
			}
		}
	}

	/**
	 * Reset the processor's or processors' cursor(s) to the starting point.
	 *
	 */
	public function rewind( $args = array() ) {
		foreach ( self::$instance->processors( $args ) as $processor ) {
			$processor->rewind();
		}
	}

	/**
	 * Clean this site of all TMSC-migrated content and rewind the
	 * processor's/processors' cursor(s). Sometimes you just need to start
	 * fresh.
	 */
	public function clean( $args = array() ) {
		global $_wp_using_ext_object_cache;

		foreach ( self::$instance->processors( $args ) as $processor ) {
			$processor->clean();
		}

		// Flush all transients
		if ( $_wp_using_ext_object_cache ) {
			// WP_CLI::run_command( array( 'cache', 'flush' ) );
		} else {
			// WP_CLI::run_command( array( 'transient', 'delete' ), array( 'all' => true ) );
		}
	}

	/**
	 * Empty TMSC's cache. This is primarily used for caching attachments.
	 *
	 */
	public function cache_clean() {
		tnsc_cache_clean();
	}

	/**
	 * Show a list of processors.
	 *
	 */
	public function list_processors( $args = array(), $assoc_args = array() ) {
		$processors = self::$instance->register_processor();
		$keys = array( 'Name', 'Class', 'Description' );
		foreach ( $processors as &$processor ) {
			$processor = array_combine( $keys, $processor );
		}

		$formatter = new \WP_CLI\Formatter( $assoc_args, $keys );
		$formatter->display_items( $processors );
	}

	/**
	 * Update term counts for the site.
	 *
	 * TMSC disables term counts throughout the migration process to improve
	 * performance. This command must be run after a migration to ensure that
	 * all term functions work properly.
	 *
	 */
	public function update_term_count() {
		tmsc_update_term_count();
	}

	/**
	 * Helper function to load the referenced processor
	 */
	private function get_processor( $processor ) {
		$message = __( 'Error: Cannot get DB Processor', 'tmsc' );
		if ( empty( $processor ) ) {
			self::$instance->set_sync_status( $message );
			exit;
		}

		$processors = self::$instance->register_processor( $processor );

		if ( empty( $processors[ $processor ] ) ) {
			self::$instance->set_sync_status( $message );
			exit;
		}

		$class = $processors[ $processor ][0];

		if ( ! class_exists( $class ) ) {
			self::$instance->set_sync_status( $message );
			exit;
		}

		return new $class;
	}

	/**
	 * Helper function to get an array of processors from passed arguments
	 */
	private function processors( $args ) {
		if ( count( $args ) !== 0 && count( $args ) === 1 && 'all' === $args[0] ) {
			$args = array_keys( self::$instance->register_processor() );
		}

		$ret = array();
		foreach ( $args as $arg ) {
			$ret[] = self::$instance->get_processor( $arg );
		}
		return $ret;
	}

	private function set_processor_opts( $processor, $assc_args ) {
		// Let the processor decide what to do with each arg
		foreach ( $assc_args as $condition => $value ) {
			$processor->add_condition( $condition, $value );
		}
	}
}
