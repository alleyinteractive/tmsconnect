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

	// Our persistant DB connection.
	public static $tms_pdo_connection = null;

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
			add_action( 'tmsc_cron_events', array( self::$instance, 'cron_events' ), 10, 1 );
			add_action( 'tmsc_complete_sync', array( self::$instance, 'complete_sync' ), 10, 1 );
			add_action( 'tmsc_do_post_processing', array( self::$instance, 'do_post_processing' ), 10, 1 );
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
	 * Setup a persistant connection for our DB processors.
	 */
	public function get_connection() {
		if ( empty( self::$tms_pdo_connection ) ) {
			$system_processor = new \TMSC\Database\System_Processor();
			self::$tms_pdo_connection = $system_processor->get_connection();
		}
		return self::$tms_pdo_connection;
	}

	/**
	 * Terminate our persistant connection for our DB processors.
	 */
	public function terminate_connection() {
		self::$tms_pdo_connection = null;
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
			if ( ! empty( $_POST['tmsc-processors'] ) ) {
				$cursor = get_option( 'tmsc-processors-cursor', array() );
				if ( empty( $cursor ) ) {
					$system_processors = tmsc_get_system_processors();
					foreach( $_POST['tmsc-processors'] as $processor_slug ) {
						$cursor[ $processor_slug ] = $system_processors[ $processor_slug ];
					}
					update_option( 'tmsc-processors-cursor', $cursor, false );
				}
			}

			// If we pressed the button manually, process any post processing data.
			wp_schedule_single_event( time(), 'tmsc_cron_events', array() );

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

	public function cron_events_activation() {
		// Run our once a day.
		if ( ! wp_next_scheduled( 'tmsc_cron_events' ) ) {
			wp_schedule_event( time(), 'daily', 'tmsc_cron_events' );
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
			$message = __( 'Syncing TMS Objects', 'tmsc' );
			tmsc_set_sync_status( $message );
		}

		// Update our custom post type.
		self::$instance->object_sync();
	}

	// Connect to the feed and update our post types with the latest data.
	public function object_sync() {

		$current_processor = '';
		$current_processor_class_slug = '';

		// Set-up a persistant connection.
		self::$instance->get_connection();

		$system_processors = get_option( 'tmsc-processors-cursor', tmsc_get_system_processors() );
		// Register and instantiate processors
		foreach ( $system_processors as $processor_slug => $processor_class_slug ) {
			if ( empty( $current_processor ) ) {
				$processor = \TMSC\TMSC::instance()->get_processor( $processor_class_slug );
				$cursor = tmsc_get_cursor( $processor_slug );

				if ( empty( $cursor['completed'] ) ) {
					$current_processor = $processor_slug;
					$current_processor_class_slug = $processor_class_slug;
					break;
				}
			}
		}

		if ( ! empty( $current_processor_class_slug ) ) {
			// Migrate our objects and taxonomies.
			\TMSC\TMSC::instance()->migrate( $current_processor_class_slug );
			wp_schedule_single_event( time(), 'tmsc_cron_events', array() );
		} else {
			wp_schedule_single_event( time(), 'tmsc_complete_sync', array() );
		}
		self::$instance->terminate_connection();
	}

	/**
	 * Handle our post processing meta fields and clean up after import.
	 */
	public function complete_sync() {
		self::$instance->do_post_processing();

		foreach ( tmsc_get_system_processors() as $processor_slug => $processor_class_slug ) {
			delete_option( "tmsc-cursor-{$processor_slug}" );
		}
		delete_option( 'tmsc-processors-cursor' );

		$message = date( 'Y-m-d H:i:s' );

		// Set sync status and clear our message cache.
		tmsc_set_sync_status( $message );
		tmsc_stop_the_insanity();
	}

	/**
	 * Iterate through our post processing meta fields and update accordingly.
	 */
	public function do_post_processing() {
		global $wpdb;
		$post_processing_data = $wpdb->get_results(
			$wpdb->prepare( "SELECT post_id, meta_value from {$wpdb->postmeta} WHERE meta_key = %s", 'tmsc_post_processing' )
		);
		foreach ( $post_processing_data as $row ) {
			$post_id = $row->post_id;
			$processor_type = get_post_meta( $post_id, 'tmsc_processor_type' ,true );
			if ( ! empty( $processor_type ) ) {
				$relationship_map = apply_filters( "tmsc_{$processor_type}_relationship_map", array() );
				$data = maybe_unserialize( $row->meta_value );

				foreach ( $data as $key => $ids ) {
					if ( ! empty( $ids ) ) {
						if ( 'post' === $relationship_map[ $key ]['type'] ) {
							$related_posts = get_posts( array(
								'fields' => 'ids',
								'suppress_filters' => false,
								'ignore_sticky_posts' => true,
								'no_found_rows' => true,
								'post_type' => $relationship_map[ $key ]['slug'],
								'post_status' => 'publish',
								'meta_query' => array(
									array(
										'key'     => 'tmsc_legacy_id',
										'value'   => $ids[ $key ],
										'compare' => 'IN',
									),
								),
							) );

							if ( ! empty( $related_posts ) ) {
								update_post_meta( $post_id, $key, $related_posts );
							}
						}
					}
				}
			}
			delete_post_meta( $post_id, 'tmsc_post_processing' );
		}
		self::$instance->update_term_count();
	}

	/**
	 * Update term counts for the site.
	 *
	 * TMSConnect disables term counts throughout the migration process to improve
	 * performance. This command must be run after a migration to ensure that
	 * all term functions work properly.
	 * @return void
	 */
	public function update_term_count() {
		// Note the start time and keep track of how many fields have been converted for script output
		$timestamp_start = microtime( true );

		// Get all taxonomies
		$taxonomies = get_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			// Get all terms for the taxonomy
			// Use special handling for the 'author' taxonomy due to Co-Authors Plus custom post count function
			if ( 'author' === $taxonomy && function_exists( 'coauthors' ) ) {
				$args = array(
					'hide_empty' => 0,
				);
				$terms = get_terms( $taxonomy, $args );
				$tt_ids = array();
				foreach ( $terms as $term ) {
					$tt_ids[] = $term->term_taxonomy_id;
				}
				$terms = $tt_ids;
			} else {
				$args = array(
					'hide_empty' => 0,
					'fields' => 'ids',
				);
				$terms = get_terms( $taxonomy, $args );
			}
			if ( is_array( $terms ) && ! empty( $terms ) ) {
				wp_update_term_count_now( $terms, $taxonomy );
			}
		}
	}

}

function tmsc_sync() {
	return \TMSC\TMSC_Sync::instance();
}
// Initial call to setup instance
add_action( 'after_setup_theme', __NAMESPACE__ . '\\tmsc_sync', 100 );
