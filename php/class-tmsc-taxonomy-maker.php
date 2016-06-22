<?php

/**
 * Custom taxonomy for Makers
 */
class TMSC_Taxonomy_Maker extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'maker';

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
			'name' => _x( 'Maker', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Maker', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Makers', 'tmsc' ),
			'popular_items' => __( 'Popular Makers', 'tmsc' ),
			'all_items' => __( 'All Makers', 'tmsc' ),
			'parent_item' => __( 'Parent Maker', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Maker:', 'tmsc' ),
			'edit_item' => __( 'Edit Maker', 'tmsc' ),
			'update_item' => __( 'Update Maker', 'tmsc' ),
			'add_new_item' => __( 'Add New Maker', 'tmsc' ),
			'new_item_name' => __( 'New Maker Name', 'tmsc' ),
			'menu_name' => __( 'Makers', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used makers', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate makers with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove makers', 'tmsc' ),
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
new TMSC_Taxonomy_Maker();
