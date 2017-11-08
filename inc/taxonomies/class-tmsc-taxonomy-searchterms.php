<?php

/**
 * Taxonomy for Search Terms Used for TMSConnect Specific Searching.
 * Allows for keyword aliases.
 */
class Tmsc_Taxonomy_SearchTerms extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'searchterms';

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
				'name'                  => __( 'Search Terms', 'tmsc' ),
				'singular_name'         => __( 'Search Term', 'tmsc' ),
				'search_items'          => __( 'Search Search Terms', 'tmsc' ),
				'popular_items'         => __( 'Popular Search Terms', 'tmsc' ),
				'all_items'             => __( 'All Search Terms', 'tmsc' ),
				'parent_item'           => __( 'Parent Search Terms', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Search Terms', 'tmsc' ),
				'edit_item'             => __( 'Edit Search Terms', 'tmsc' ),
				'view_item'             => __( 'View Search Terms', 'tmsc' ),
				'update_item'           => __( 'Update Search Terms', 'tmsc' ),
				'add_new_item'          => __( 'Add New Search Terms', 'tmsc' ),
				'new_item_name'         => __( 'New Search Terms Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Search Terms', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Search Terms', 'tmsc' ),
				'menu_name'             => __( 'Search Terms', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
			'public' => false,
		);
	}
}

$taxonomy_searchterms = new Tmsc_Taxonomy_SearchTerms();
