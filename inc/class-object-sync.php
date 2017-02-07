<?php

namespace TMSC;

/**
 * Use the event scheduler to set up our object sync.
 * Can also be fired manually via the admin ui.
 *
 */
class Object_Sync {
	private static $instance;

	/**
	 * The capability required to manage object sync. Defaults to 'manage_options'.
	 * @var string
	 */
	public static $capability = 'manage_options';

	// The url prefix we use to grab images from I.D.S.
	// Built using: http://ids.si.edu/ids/deliveryService
	public static $image_url = '';

	// TMS DB Server address.
	public static $tms_db_host = '';

	// TMS DB name.
	public static $tms_db_name = '';

	// TMS DB user name.
	public static $tms_db_user = '';

	// TMS DB user password.
	public static $tms_db_password = '';

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}

	public function __clone() {
		wp_die( "Please don't __clone TMSC_2016_Cards_Sync" );
	}

	public function __wakeup() {
		wp_die( "Please don't __wakeup TMSC_2016_Cards_Sync" );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new \TMSC\Object_Sync;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		// Our defaults
		self::$tms_db_host = get_option( 'tmsc-db-host', self::$tms_db_host );
		self::$tms_db_name = get_option( 'tmsc-db-name', self::$tms_db_name );
		self::$tms_db_user = get_option( 'tmsc-db-user', self::$tms_db_user );
		self::$tms_db_password = get_option( 'tmsc-db-password', self::$tms_db_password );
		self::$image_url = get_option( 'tmsc-ids-image-url', self::$image_url );

		if ( ! empty( self::$tms_db_host ) ) {
			// Our Cron Setup
			add_filter( 'cron_schedules', array( self::$instance, 'add_intervals' ) );
			add_action( 'tmsc_cron_events', array( self::$instance, 'cron_events' ), 10, 1 );
			add_action( 'wp', array( self::$instance, 'cron_events_activation' ) );
		}

		if ( current_user_can( self::$capability ) ) {
			// Our Admin Area Menu
			add_action( 'admin_menu', array( self::$instance, 'add_menu_pages' ) );
			add_action( 'wp_ajax_sync_objects', array( self::$instance, 'sync_objects' ) );
		}
	}

	/**
	 * Add CPT and related taxonomies to object menu
	 */
	public static function add_menu_pages() {
		add_submenu_page( 'edit.php?post_type=tms_object', __( 'TMSC Sync', 'tmsc' ), __( 'Sync TMS Objects', 'tmsc' ), 'manage_options', 'tmsc-sync', array( self::$instance, 'render_object_sync_submenu_page' ) );
	}

	/**
	 * Render our admin sync template part
	 */
	public function render_object_sync_submenu_page() {
		load_template( TMSCONNECT_PATH . '/templates/object-sync-admin.php' );
	}

	/**
	 * Our ajax handler for syncing manually from the wp-admin area submenu.
	 */
	public function sync_objects() {
		if ( current_user_can( self::$capability ) ) {
			check_ajax_referer( 'tmsc_object_sync', 'tmsc_nonce' );
			if ( ! empty( $_POST['tmsc-db-host'] ) ) {
				$host = sanitize_text_field( wp_unslash( $_POST['tmsc-db-host'] ) );
				update_option( 'tmsc-db-host', $host, false );
				self::$tms_db_host = $host;
			}
			if ( ! empty( $_POST['tmsc-db-name'] ) ) {
				$name = sanitize_text_field( wp_unslash( $_POST['tmsc-db-name'] ) );
				update_option( 'tmsc-db-name', $name, false );
				self::$tms_db_name = $name;
			}
			if ( ! empty( $_POST['tmsc-db-user'] ) ) {
				$user = sanitize_text_field( wp_unslash( $_POST['tmsc-db-user'] ) );
				update_option( 'tmsc-db-user', $user, false );
				self::$tms_db_user = $user;
			}
			if ( ! empty( $_POST['tmsc-db-password'] ) ) {
				$password = sanitize_text_field( wp_unslash( $_POST['tmsc-db-password'] ) );
				update_option( 'tmsc-db-password', $password, false );
				self::$tms_db_password = $password;
			}
			if ( ! empty( $_POST['tmsc-image-url'] ) ) {
				$url = esc_url_raw( wp_unslash( $_POST['tmsc-image-url'] ) );
				update_option( 'tmsc-image-url', $url, false );
				self::$image_url = $url;
			}

			wp_schedule_single_event( time(), 'tmsc_cron_events', array() );
			echo 1;
		} else {
			echo 0;
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'tmsc' ) );
		}
		exit();
	}

	/**
	 * Add custom intervals
	 */
	public function add_intervals( $schedules ) {
		$schedules['halfhour'] = array(
			'interval' => 1800,
			'display' => __( 'Every Half Hour', 'tmsc' ),
		);
		return $schedules;
	}

	public function cron_events_activation() {
		// Run our Cron every 30 mins.
		if ( ! wp_next_scheduled( 'tmsc_cron_events' ) ) {
			wp_schedule_event( time(), 'halfhour', 'tmsc_cron_events' );
		}
	}

	/**
	 * Add in any cron events that need to be fired every 30 mins here.
	 * @param array. $args. An array of args to pass to this event scheduler hook.
	 */
	public function cron_events( $args = array() ) {
		// Make sure sync is not currently running
		if ( 'Syncing TMS Objects' !== get_option( 'tmsc-last-sync-date' ) ) {
			// Update our custom post type.
			self::$instance->object_sync();
		}
	}

	// Connect to the feed and update our post types with the latest data.
	public function object_sync() {

		$message = __( 'Syncing TMS Objects', 'tmsc' );
		// update_option( 'tmsc-last-sync-date', $message, false );

		$this->migrate( array( 'Freer_Sackler_Processor' ), array( 'dry' => true, 'start' => 0, 'batch' => true ) );

		$message = date( 'Y-m-d H:i:s' );
		// Set sync status and clear our message cache.
		update_option( 'tmsc-last-sync-date', $message, false );
		wp_cache_delete( 'tmsc-last-sync-date', 'options' );
	}

	/**
	 * Migrate a batch of objects.
	 *
	 */
	public function migrate( $args = array(), $assc_args = array() ) {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$dry = !empty( $assc_args['dry'] );
		$cursor = !empty( $assc_args['start'] ) ? $assc_args['start'] : false;
		$batch_size = !empty( $assc_args['batch'] ) ? $assc_args['batch'] : false;

		$short_name = array_shift( $args );
		$processor = $this->get_processor( $short_name );
		$processor->set_dry_run( $dry );

		$this->set_processor_opts( $processor, $assc_args );

		if ( $batch_size ) {
			$processor->set_batch_size( $batch_size );
		}

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
		}
	}

	/**
	 * Export information for one item. This subcommand is incomplete.
	 */
	public function describe( $args = array(), $assc_args = array() ) {
		$short_name = array_shift( $args );
		$processor = $this->get_processor( $short_name );
	}

	/**
	 * Migrate all objects.
	 */
	public function migrate_all( $args = array(), $assc_args = array() ) {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$dry = !empty( $assc_args['dry'] );
		foreach ( $this->processors( $args ) as $processor ) {
			$this->set_processor_opts( $processor, $assc_args );
			while ( !$processor->is_finished() ) {
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
		foreach ( $this->processors( $args ) as $processor ) {
			$processor->rewind();
		}
	}

	/**
	 * Clean this site of all AMT-migrated content and rewind the
	 * processor's/processors' cursor(s). Sometimes you just need to start
	 * fresh.
	 */
	public function clean( $args = array() ) {
		global $_wp_using_ext_object_cache;

		foreach ( $this->processors( $args ) as $processor ) {
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
		$processors = tmsc_register_processor();
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
		if ( empty( $processor ) ) {
			exit;
		}

		$processors = tmsc_register_processor();

		if ( empty( $processors[ $processor ] ) ) {
			exit;
		}

		$class = $processors[ $processor ][1];

		if ( ! class_exists( $class ) ) {
			exit;
		}

		return new $class;
	}

	/**
	 * Helper function to get an array of processors from passed arguments
	 */
	private function processors( $args ) {
		if ( count( $args ) !== 0 && count( $args ) === 1 && 'all' === $args[0] ) {
			$args = array_keys( tmsc_register_processor() );
		}

		$ret = array();
		foreach ( $args as $arg ) {
			$ret[] = $this->get_processor( $arg );
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

function tmsc_object_sync() {
	return \TMSC\Object_Sync::instance();
}
tmsc_object_sync();
