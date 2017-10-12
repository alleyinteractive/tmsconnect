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
				fm_register_submenu_page( 'tmsc_guide_terms', 'edit.php?post_type=tms_object', __( 'Set Guide Terms', 'tmsc' ) );
			}
		}
		add_action( 'fm_submenu_tmsc_guide_terms', array( $this, 'sync_init' ) );

		// Handle External Image Processing as featured images.
		add_filter( 'wp_get_attachment_image_src', array( $this, 'get_img_src' ), 20, 4 );

		// Setup our search class
		require_once( TMSCONNECT_PATH . '/inc/class-search.php' );
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
	 * Override images if we are using an external image
	 *
	 */
	public function get_img_src( $image, $attachment_id, $size, $icon ) {

		return $image;
	}
}
