<?php

/**
 * Custom taxonomy for Geographies
 */
class TMSC_Taxonomy_Geography extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'geography';

	/**
	 * Constructmsc. Always call the parent for global actions.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Creates the taxonomy
	 *
	 * @access public
	 * @return void
	 */
	public function create_taxonomy() {
		$labels = array(
			'name' => _x( 'Geography', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Geography', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Geographies', 'tmsc' ),
			'popular_items' => __( 'Popular Geographies', 'tmsc' ),
			'all_items' => __( 'All Geographies', 'tmsc' ),
			'parent_item' => __( 'Parent Geography', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Geography:', 'tmsc' ),
			'edit_item' => __( 'Edit Geography', 'tmsc' ),
			'update_item' => __( 'Update Geography', 'tmsc' ),
			'add_new_item' => __( 'Add New Geography', 'tmsc' ),
			'new_item_name' => __( 'New Geography Name', 'tmsc' ),
			'menu_name' => __( 'Geographies', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used geographies', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate geographies with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove geographies', 'tmsc' ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'show_admin_column' => true,
			'rewrite' => false,
		);

		register_taxonomy( $this->name, array( 'tms_object' ), $args );
	}

	/**
	 * Adds the custom term fields
	 *
	 * @access public
	 * @return void
	 */
	public function custom_term_fields() {

	}
}
new TMSC_Taxonomy_Geography();
