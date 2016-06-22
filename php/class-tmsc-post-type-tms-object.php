<?php

/**
 * Custom post type for TMS Objects.
 */
class TMSC_Post_Type_TMS_Object {

	/**
	 * Name of the custom post type.
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'tms_object';


	/**
	 * Constructmsc. Always call the parent for global actions.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		// Create the post type
		add_action( 'init', array( $this, 'create_post_type' ), 20 );

		// Add custom meta boxes
		add_action( 'fm_post_' . $this->name, array( $this, 'add_meta_boxes' ) );

		// Additional save logic
		add_action( 'save_post_' . $this->name, array( $this, 'save_post_hook' ) );
	}

	/**
	 * Creates the post type.
	 *
	 * @access public
	 * @return void
	 */
	public function create_post_type() {
		$labels = array(
			'name' => _x( 'TMS Object', 'post type general name', 'tmsc' ),
			'singular_name' => _x( 'TMS Object', 'post type singular name', 'tmsc' ),
			'plural_name' => _x( 'All TMS Objects', 'post type plural name', 'tmsc' ),
			'menu_name' => _x( 'TMS Objects', 'tmsc' ),
			'add_new' => esc_html__( 'Add New', 'tmsc' ),
			'add_new_item' => esc_html__( 'Add New', 'tmsc' ),
			'edit_item' => esc_html__( 'Edit', 'tmsc' ),
			'new_item' => esc_html__( 'New', 'tmsc' ),
			'view_item' => esc_html__( 'View', 'tmsc' ),
			'search_items' => esc_html__( 'Search', 'tmsc' ),
			'not_found' => esc_html__( 'No TMS objects found', 'tmsc' ),
			'not_found_in_trash' => esc_html__( 'No TMS objects found found in Trash', 'tmsc' ),
		);

		$args = array(
			'labels' => $labels,
			'publicly_queryable' => true,
			'public' => true,
			'show_ui' => true,
			'query_var' => true,
			'taxonomies' => array( 'post_tag' ),
			'has_archive' => true,
			'rewrite' => true,
			'hierarchical' => false,
			'supports' => array( 'title', 'revisions', 'excerpt' ),
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'menu_icon' => 'dashicons-book-alt',
		);

		register_post_type( $this->name, $args );
	}

	/**
	 * Adds custom meta boxes for this post type.
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes() {

	}


	/**
	 * Save post hook
	 */
	public function save_post_hook( $post_id ) {
		// Associate post with publication.
	}
}

new TMSC_Post_Type_TMS_Object();
