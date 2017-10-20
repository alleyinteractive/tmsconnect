<?php

/**
 * Taxonomy for Keywords Used for TMSConnect Specific Searching.
 * Allows for keyword aliases.
 */
class Tmsc_Taxonomy_Keywords extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'keywords';

	/**
	 * Object types for this taxonomy
	 *
	 * @var array
	 */
	public $object_types;


	/**
	 * Build the taxonomy object.
	 */
	public function __construct() {
		$this->object_types = array( 'tms_object' );

		parent::__construct();
	}

	/**
	 * Args passed to register taxonomy.
	 * Allows for a filter.
	 * @return array.
	 */
	public function register_taxonomy_args() {
		return array(
			'labels' => array(
				'name'                  => __( 'Keywords', 'tmsc' ),
				'singular_name'         => __( 'Keywords', 'tmsc' ),
				'search_items'          => __( 'Search Keywords', 'tmsc' ),
				'popular_items'         => __( 'Popular Keywords', 'tmsc' ),
				'all_items'             => __( 'All Keywords', 'tmsc' ),
				'parent_item'           => __( 'Parent Keywords', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Keywords', 'tmsc' ),
				'edit_item'             => __( 'Edit Keywords', 'tmsc' ),
				'view_item'             => __( 'View Keywords', 'tmsc' ),
				'update_item'           => __( 'Update Keywords', 'tmsc' ),
				'add_new_item'          => __( 'Add New Keywords', 'tmsc' ),
				'new_item_name'         => __( 'New Keywords Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Keywords', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Keywords', 'tmsc' ),
				'menu_name'             => __( 'Keywords', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		);
	}
}

$taxonomy_keywords = new Tmsc_Taxonomy_Keywords();
