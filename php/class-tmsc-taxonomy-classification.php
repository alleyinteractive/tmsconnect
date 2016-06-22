<?php

/**
 * Custom taxonomy for Classifications
 */
class TMSC_Taxonomy_Classification extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'classification';

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
			'name' => _x( 'Classification', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Classification', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Classifications', 'tmsc' ),
			'popular_items' => __( 'Popular Classifications', 'tmsc' ),
			'all_items' => __( 'All Classifications', 'tmsc' ),
			'parent_item' => __( 'Parent Classification', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Classification:', 'tmsc' ),
			'edit_item' => __( 'Edit Classification', 'tmsc' ),
			'update_item' => __( 'Update Classification', 'tmsc' ),
			'add_new_item' => __( 'Add New Classification', 'tmsc' ),
			'new_item_name' => __( 'New Classification Name', 'tmsc' ),
			'menu_name' => __( 'Classifications', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used classifications', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate classifications with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove classifications', 'tmsc' ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
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
new TMSC_Taxonomy_Classification();
