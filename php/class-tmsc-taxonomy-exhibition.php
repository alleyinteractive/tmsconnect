<?php

/**
 * Custom taxonomy for Exhibitions
 */
class TMSC_Taxonomy_Exhibition extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'exhibition';

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
			'name' => _x( 'Exhibitions', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Exhibition', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Exhibitions', 'tmsc' ),
			'popular_items' => __( 'Popular Exhibitions', 'tmsc' ),
			'all_items' => __( 'All Exhibitions', 'tmsc' ),
			'parent_item' => __( 'Parent Exhibition', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Exhibition:', 'tmsc' ),
			'edit_item' => __( 'Edit Exhibition', 'tmsc' ),
			'update_item' => __( 'Update Exhibition', 'tmsc' ),
			'add_new_item' => __( 'Add New Exhibition', 'tmsc' ),
			'new_item_name' => __( 'New Exhibition Name', 'tmsc' ),
			'menu_name' => __( 'Exhibitions', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used exhibitions', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate exhibitions with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove exhibitions', 'tmsc' ),
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
new TMSC_Taxonomy_Exhibition();
