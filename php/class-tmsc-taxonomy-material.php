<?php

/**
 * Custom taxonomy for Materials
 */
class TMSC_Taxonomy_Material extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'material';

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
			'name' => _x( 'Materials', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Material', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Materials', 'tmsc' ),
			'popular_items' => __( 'Popular Materials', 'tmsc' ),
			'all_items' => __( 'All Materials', 'tmsc' ),
			'parent_item' => __( 'Parent Material', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Material:', 'tmsc' ),
			'edit_item' => __( 'Edit Material', 'tmsc' ),
			'update_item' => __( 'Update Material', 'tmsc' ),
			'add_new_item' => __( 'Add New Material', 'tmsc' ),
			'new_item_name' => __( 'New Material Name', 'tmsc' ),
			'menu_name' => __( 'Materials', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used materials', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate materials with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove materials', 'tmsc' ),
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
new TMSC_Taxonomy_Material();
