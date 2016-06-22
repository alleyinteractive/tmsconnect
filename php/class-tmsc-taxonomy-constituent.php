<?php

/**
 * Custom taxonomy for Constituents
 */
class TMSC_Taxonomy_Constituent extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'constituent';

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
			'name' => _x( 'Constituents', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Constituent', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Constituents', 'tmsc' ),
			'popular_items' => __( 'Popular Constituents', 'tmsc' ),
			'all_items' => __( 'All Constituents', 'tmsc' ),
			'parent_item' => __( 'Parent Constituent', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Constituent:', 'tmsc' ),
			'edit_item' => __( 'Edit Constituent', 'tmsc' ),
			'update_item' => __( 'Update Constituent', 'tmsc' ),
			'add_new_item' => __( 'Add New Constituent', 'tmsc' ),
			'new_item_name' => __( 'New Constituent Name', 'tmsc' ),
			'menu_name' => __( 'Constituents', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used constituents', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate constituents with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove constituents', 'tmsc' ),
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
new TMSC_Taxonomy_Constituent();
