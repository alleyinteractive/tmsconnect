<?php

/**
 * Taxonomy for Constituent Type.
 */
class Tmsc_Taxonomy_Constituent_Type extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'constituent_type';

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
		$this->object_types = array( 'constituent' );

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
				'name'                  => __( 'Constituent Types', 'tmsc' ),
				'singular_name'         => __( 'Constituent Type', 'tmsc' ),
				'search_items'          => __( 'Search Constituent Types', 'tmsc' ),
				'popular_items'         => __( 'Popular Constituent Types', 'tmsc' ),
				'all_items'             => __( 'All Constituent Types', 'tmsc' ),
				'parent_item'           => __( 'Parent Constituent Type', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Constituent Type', 'tmsc' ),
				'edit_item'             => __( 'Edit Constituent Type', 'tmsc' ),
				'view_item'             => __( 'View Constituent Type', 'tmsc' ),
				'update_item'           => __( 'Update Constituent Type', 'tmsc' ),
				'add_new_item'          => __( 'Add New Constituent Type', 'tmsc' ),
				'new_item_name'         => __( 'New Constituent Type Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Constituent Types', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Constituent Types', 'tmsc' ),
				'menu_name'             => __( 'Constituent Types', 'tmsc' ),
			),
			'hierarchical' => true,
			'rewrite' => array(
				'with_front' => false,
			),
		);
	}
}

$taxonomy_constituent_type = new Tmsc_Taxonomy_Constituent_Type();
