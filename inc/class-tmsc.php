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

	/**
	 * Instatiate our instance.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new \TMSC\TMSC();
			self::$instance->setup();
		}
		return self::$instance;
	}

	/**
	 * Setup TMSC.
	 */
	public function setup() {
		/**
		 * Our Default Zones
		 */
		$this->default_zones = apply_filters( 'tmsc_curated_zones', array() );

		// Add zoninator post type support
		add_action( 'zoninator_post_init', array( $this, 'zoninator_post_type_support' ), 99 );
		add_filter( 'zoninator_recent_posts_args', array( $this, 'filter_zone_post_types' ) );
		add_filter( 'zoninator_search_args', array( $this, 'filter_zone_post_types' ) );

		// Add in our sync options page
		if ( function_exists( 'fm_register_submenu_page' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				fm_register_submenu_page( 'tmsc_guide_terms', 'edit.php?post_type=tms_object', __( 'Set Guide Terms', 'tmsc' ) );
			}
		}
		add_action( 'fm_submenu_tmsc_guide_terms', array( $this, 'sync_init' ) );

		/**
		 * Allow manual edits of posts and prevent sync overwrites.
		 */
		add_action( 'post_submitbox_misc_actions', array( $this, 'add_sync_lock_meta_field' ) );

		// Sync locking is only handled with human interaction. So disable it for CLI.
		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			add_action( 'save_post', array( $this, 'set_sync_lock' ) );
		}

		// Setup our search class
		require_once( TMSCONNECT_PATH . '/inc/class-search.php' );

		// Enable Links
		if ( apply_filters( 'tmsc_enable_links', false ) ) {
			add_filter( 'pre_option_link_manager_enabled', '__return_true' );
		}
	}

	/**
	 * Add the Zoninator taxonomy to the custom post types that require it.
	 * The function calls below just return false if either the taxonomy or post type doesn't exist.
	 *
	 * @access public
	 * @return void
	 */
	public function zoninator_post_type_support() {
		global $zoninator;
		// Remove the standard menu and put one in that is more Museum friendly.
		remove_action( 'admin_menu', array( $zoninator, 'admin_page_init' ) );
		add_action( 'admin_menu', array( $this, 'setup_curated_area_menu' ) );

		$post_types = array( 'tms_object', 'exhibition' );

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
	 * Relabel our zone menu.
	 */
	public function setup_curated_area_menu() {
		global $zoninator;
		add_menu_page( __( 'Curated Zones', 'tmsc' ), __( 'Curated Zones', 'tmsc' ), $zoninator->_get_manage_zones_cap(), $zoninator->key, array( $zoninator, 'admin_page' ), 'dashicons-images-alt2', 11 );
	}

	/**
	 * Only display tmsc objects in our TMSConnect Zones.
	 */
	public function filter_zone_post_types( $args ) {
		$active_zone_id = z_get_zoninator()->_get_request_var( 'zone_id', 0, 'absint' );
		$tmsc_zones = array_keys( $this->default_zones );
		if ( ! empty( $active_zone_id ) ) {
			$zone = z_get_zone( $active_zone_id );
			if ( in_array( $zone->slug, $tmsc_zones ) ) {
				$args['post_type'] = ( empty( $this->default_zones[ $zone->slug ]['post_type'] ) ) ? array( 'tms_object' ) : $this->default_zones[ $zone->slug ]['post_type'];
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
					'description' => __( 'Use the CN number of the guide terms that should serve as parent taxonomies.', 'tmsc' ),
					'collapsible' => true,
					'label' => __( 'Guide Terms', 'tmsc' ),
					'children' => array(
						'data' => new \Fieldmanager_Group( array(
							'limit' => 0,
							'add_more_label' => __( 'Add another guide term', 'tmsc' ),
							'children' => array(
								'taxonomy_map' => new \Fieldmanager_Select( __( 'Map to Taxonomy', 'tmsc' ), array(
									'datasource' => new \Fieldmanager_Datasource( array(
										'options' => get_taxonomies( array(
											'_builtin' => false,
											'object_type' => array( 'tms_object' ),
										) ),
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
	 * Keep track of our custom DB system processors.
	 * @param string. $name. Namespaced class name.
	 * @return void.
	 */
	public function register_processor( $name = '' ) {
		if ( empty( $name ) || 'all' === $name ) {
			foreach ( tmsc_get_system_processors() as $processor_slug => $processor_class_slug ) {
				if ( empty( self::$instance->processors[ $processor_class_slug ] ) ) {
					self::$instance->processors[ $processor_class_slug ] = '\\TMSC\\Database\\Processors\\' . TMSC_SYSTEM_BUILD_CLASS_PREFIX . '\\' . TMSC_SYSTEM_BUILD_CLASS_PREFIX . '_' . $processor_class_slug . '_Processor';
				}
			}
		} else {
			if ( empty( self::$instance->processors[ $name ] ) ) {
				self::$instance->processors[ $name ] = '\\TMSC\\Database\\Processors\\' . TMSC_SYSTEM_BUILD_CLASS_PREFIX . '\\' . TMSC_SYSTEM_BUILD_CLASS_PREFIX . '_' . $name . '_Processor';
			}
		}
		return self::$instance->processors;
	}

	/**
	 * Migrate objects.
	 */
	public function migrate( $processor_class_slug, $assc_args = array() ) {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		foreach ( self::$instance->processors( $processor_class_slug ) as $processor ) {
			if ( ! in_array( $processor->processor_type, self::$instance->get_child_processors(), true ) ) {
				$processor->run();
				tmsc_stop_the_insanity();
			}
		}
	}

	/**
	 * Helper function to load the referenced processor
	 */
	public function get_processor( $processor ) {

		$message = __( 'Error: Cannot get DB Processor', 'tmsc' );
		if ( empty( $processor ) ) {
			tmsc_set_sync_status( $message );
			exit;
		}

		$processors = self::$instance->register_processor( $processor );

		if ( empty( $processors[ $processor ] ) ) {
			tmsc_set_sync_status( $message );
			exit;
		}

		$class = $processors[ $processor ];

		if ( ! class_exists( $class ) ) {
			tmsc_set_sync_status( $message );
			exit;
		}

		return new $class( $processor );
	}

	/**
	 * Helper function to get an array of processors from passed arguments
	 */
	private function processors( $processor ) {
		$ret = array();
		if ( ! empty( $processor ) ) {

			if ( 'all' === $processor )	{
				$processors = array_keys( self::$instance->register_processor() );
				foreach ( $processors as $p ) {
					$ret[] = self::$instance->get_processor( $p );
				}
			} else {
				$ret[] = self::$instance->get_processor( $processor );
			}
		}

		return $ret;
	}

	/**
	 * A list of all of our child processors.
	 */
	public function get_child_processors() {
		return apply_filters( 'tmsc_get_child_processors', array() );
	}

	/**
	 * Add a hidden sync lock meta field that set on click of the update button.
	 */
	public function add_sync_lock_meta_field( $post ) {

		// If the post has been created, we can grab the saved info.
		if ( $this->sync_lock_enabled( $post->ID ) ) {
			$status = ( empty( get_post_meta( $post->ID, 'tmsc_sync_lock', true ) ) ) ? 0 : 1;
			?>
			<div class="misc-pub-section misc-pub-synclock">
				<span id="synclock-pub-section">
					<span id="synclock-pub-section-icon" class="dashicons dashicons-<?php echo ( empty( $status ) ) ? esc_attr( 'unlock' ) : esc_attr( 'lock' ); ?>"></span>
				</span>
				&nbsp;<?php esc_html_e( 'Sync Lock Status: ', 'tmsc' ); ?>: <b><span class="ai-target-text"><?php echo ( $status ) ? __( 'Locked', 'tmsc' ) : __( 'Unlocked', 'tmsc' ); ?></span></b>
				<a id="clear-sync-lock-status" href="#clear-sync-lock-status" class="clear-lock-status hide-if-no-js" role="button">
					<span aria-hidden="true"><?php esc_html_e( 'Clear', 'tmsc' ); ?></span>
					<span class="screen-reader-text">
						<?php
						esc_html_e( 'Clear Lock Status ', 'tmsc' );
						?>
					</span>
				</a>
				<div id="sync-lock-input">
					<input id="sync_lock" name="sync_lock" type="hidden" value="<?php echo esc_attr( $status ); ?>">
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Set sync lock for our posts.
	 */
	public function set_sync_lock( $post_id ) {
		if ( $this->sync_lock_enabled( $post_id ) ) {
			if ( empty( $_POST['sync_lock'] ) ) {
				delete_post_meta( $post_id, 'tmsc_sync_lock' );
			} else {
				update_post_meta( $post_id, 'tmsc_sync_lock', true );
			}
		}
	}

	/**
	 * Enable Sync Lock
	 */
	public function sync_lock_enabled( $post_id ) {
		$post_type = get_post_type( $post_id );
		$sync_locked_types = apply_filters( 'tmsc_sync_lock_enabled', array( 'tms_object', 'exhibition', 'constituant' ) );
		return in_array( $post_type, $sync_locked_types, true );
	}
}
