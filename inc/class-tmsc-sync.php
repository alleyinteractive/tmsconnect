<?php

namespace TMSC;

/**
 * Use the event scheduler to set up our object sync.
 * Can also be fired manually via the admin ui.
 *
 */
class TMSC_Sync {
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
		wp_die( "Please don't __clone \TMSC\TMSC_Sync" );
	}

	public function __wakeup() {
		wp_die( "Please don't __wakeup \TMSC\TMSC_Sync" );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new \TMSC\TMSC_Sync;
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
			add_action( 'wp_ajax_get_option_value', array( self::$instance, 'ajax_get_option_value' ) );
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
		load_template( TMSCONNECT_PATH . '/templates/tmsc-sync-admin.php' );
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
			/**
			 * Uncomment the schedule event function and comment the object sync function to enable asynchronous sync.
			 */
			self::$instance->object_sync();
			// wp_schedule_single_event( time(), 'tmsc_cron_events', array() );
			echo 1;
		} else {
			echo 0;
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'tmsc' ) );
		}
		exit();
	}

	/**
	 * A generic ajax responder that spits back a option value or returns a boolean if a comparison value is passed.
	 */
	public function ajax_get_option_value() {
		check_ajax_referer( 'wp_admin_js_script', 'nonce' );
		if ( ! empty( $_POST['option_key'] ) ) {
			// If an old value is passed, return success only if it is different from the current value.
			// This can be used for asynchrounous checks.
			$current_value = get_option( sanitize_key( $_POST['option_key'] ) );
			if ( ! empty( $_POST['old_value'] ) ) {
				if ( $_POST['old_value'] !== $current_value && __( 'Syncing TMS Objects', 'tmsc' ) !== $current_value ) {
					wp_send_json( array( 'success' => true, 'data' => esc_js( $current_value ) ) );
				} else {
					wp_send_json_error( $current_value );
				}
			} else {
				wp_send_json_error( $current_value );
			}
		} else {
			wp_send_json_error();
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
	 * @return void.
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
		// If we are syncing disable post revisions.

		if ( ! defined( 'WP_POST_REVISIONS' ) || WP_POST_REVISIONS ) {
			define( 'WP_POST_REVISIONS', FALSE );
		}

		/**
		 * @TODO
		 *
		 * Remove the max_execution_time update.
		 */
		ini_set( 'max_execution_time', 300 );

		$message = __( 'Syncing TMS Objects', 'tmsc' );
		tmsc_set_sync_status( $message );
		// Register an instantiate processors
		foreach ( tmsc_get_system_processors() as $processor_slug => $processor_class_slug ) {
			\TMSC\TMSC::instance()->get_processor( $processor_class_slug );
		}
		// Migrate our objects and taxonomies.
		\TMSC\TMSC::instance()->migrate( array( 'all' ), array( 'start' => 0 ) );

		$message = date( 'Y-m-d H:i:s' );

		// Set sync status and clear our message cache.
		tmsc_set_sync_status( $message );

		/**
		 * @TODO
		 *
		 * Remove the max_execution_time update.
		 */
		ini_set( 'max_execution_time', 30 );
	}
}

function tmsc_sync() {
	return \TMSC\TMSC_Sync::instance();
}
tmsc_sync();
