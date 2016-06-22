<?php

/**
 * Custom taxonomy for Collections
 */
class TMSC_Taxonomy_Collection extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'collection';

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
			'name' => _x( 'Collections', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Collection', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Collections', 'tmsc' ),
			'popular_items' => __( 'Popular Collections', 'tmsc' ),
			'all_items' => __( 'All Collections', 'tmsc' ),
			'parent_item' => __( 'Parent Collection', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Collection:', 'tmsc' ),
			'edit_item' => __( 'Edit Collection', 'tmsc' ),
			'update_item' => __( 'Update Collection', 'tmsc' ),
			'add_new_item' => __( 'Add New Collection', 'tmsc' ),
			'new_item_name' => __( 'New Collection Name', 'tmsc' ),
			'menu_name' => __( 'Collections', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used collections', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate collections with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove collections', 'tmsc' ),
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
new TMSC_Taxonomy_Collection();
