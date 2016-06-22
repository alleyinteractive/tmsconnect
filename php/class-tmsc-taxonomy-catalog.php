<?php

/**
 * Custom taxonomy for Catalogs
 */
class TMSC_Taxonomy_Catalog extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'catalog';

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
			'name' => _x( 'Catalogs', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Catalog', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Catalogs', 'tmsc' ),
			'popular_items' => __( 'Popular Catalogs', 'tmsc' ),
			'all_items' => __( 'All Catalogs', 'tmsc' ),
			'parent_item' => __( 'Parent Catalog', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Catalog:', 'tmsc' ),
			'edit_item' => __( 'Edit Catalog', 'tmsc' ),
			'update_item' => __( 'Update Catalog', 'tmsc' ),
			'add_new_item' => __( 'Add New Catalog', 'tmsc' ),
			'new_item_name' => __( 'New Catalog Name', 'tmsc' ),
			'menu_name' => __( 'Catalogs', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used catalogs', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate catalogs with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove catalogs', 'tmsc' ),
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
new TMSC_Taxonomy_Catalog();
