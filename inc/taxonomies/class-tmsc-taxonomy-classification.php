<?php

/**
 * Taxonomy for Classifications.
 */
class Tmsc_Taxonomy_Classification extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'classification';

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
				'name'                  => __( 'Classifications', 'tmsc' ),
				'singular_name'         => __( 'Classification', 'tmsc' ),
				'search_items'          => __( 'Search Classifications', 'tmsc' ),
				'popular_items'         => __( 'Popular Classifications', 'tmsc' ),
				'all_items'             => __( 'All Classifications', 'tmsc' ),
				'parent_item'           => __( 'Parent Classification', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Classification', 'tmsc' ),
				'edit_item'             => __( 'Edit Classification', 'tmsc' ),
				'view_item'             => __( 'View Classification', 'tmsc' ),
				'update_item'           => __( 'Update Classification', 'tmsc' ),
				'add_new_item'          => __( 'Add New Classification', 'tmsc' ),
				'new_item_name'         => __( 'New Classification Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Classifications', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Classifications', 'tmsc' ),
				'menu_name'             => __( 'Classifications', 'tmsc' ),
			),
			'hierarchical' => true,
			'rewrite' => array(
				'with_front' => false,
			),
		);
	}
}

$taxonomy_classification = new Tmsc_Taxonomy_Classification();
