<?php

/**
 * Custom taxonomy for References
 */
class TMSC_Taxonomy_Reference extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'reference';

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
			'name' => _x( 'References', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Reference', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search References', 'tmsc' ),
			'popular_items' => __( 'Popular References', 'tmsc' ),
			'all_items' => __( 'All References', 'tmsc' ),
			'parent_item' => __( 'Parent Reference', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Reference:', 'tmsc' ),
			'edit_item' => __( 'Edit Reference', 'tmsc' ),
			'update_item' => __( 'Update Reference', 'tmsc' ),
			'add_new_item' => __( 'Add New Reference', 'tmsc' ),
			'new_item_name' => __( 'New Reference Name', 'tmsc' ),
			'menu_name' => __( 'References', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used references', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate references with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove references', 'tmsc' ),
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
new TMSC_Taxonomy_Reference();
