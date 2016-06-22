<?php

/**
 * Custom taxonomy for Locations
 */
class TMSC_Taxonomy_Location extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'location';

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
			'name' => _x( 'Locations', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Location', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Locations', 'tmsc' ),
			'popular_items' => __( 'Popular Locations', 'tmsc' ),
			'all_items' => __( 'All Locations', 'tmsc' ),
			'parent_item' => __( 'Parent Location', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Location:', 'tmsc' ),
			'edit_item' => __( 'Edit Location', 'tmsc' ),
			'update_item' => __( 'Update Location', 'tmsc' ),
			'add_new_item' => __( 'Add New Location', 'tmsc' ),
			'new_item_name' => __( 'New Location Name', 'tmsc' ),
			'menu_name' => __( 'Locations', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used locations', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate locations with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove locations', 'tmsc' ),
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
new TMSC_Taxonomy_Location();
