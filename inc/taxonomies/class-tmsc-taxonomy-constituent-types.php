<?php

/**
 * Taxonomy for Constituent Types.
 */
class Tmsc_Taxonomy_Constituent_Types extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'constituent-types';

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
				'singular_name'         => __( 'Constituent Types', 'tmsc' ),
				'search_items'          => __( 'Search Constituent Types', 'tmsc' ),
				'popular_items'         => __( 'Popular Constituent Types', 'tmsc' ),
				'all_items'             => __( 'All Constituent Types', 'tmsc' ),
				'parent_item'           => __( 'Parent Constituent Types', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Constituent Types', 'tmsc' ),
				'edit_item'             => __( 'Edit Constituent Types', 'tmsc' ),
				'view_item'             => __( 'View Constituent Types', 'tmsc' ),
				'update_item'           => __( 'Update Constituent Types', 'tmsc' ),
				'add_new_item'          => __( 'Add New Constituent Types', 'tmsc' ),
				'new_item_name'         => __( 'New Constituent Types Name', 'tmsc' ),
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

$taxonomy_constituent_types = new Tmsc_Taxonomy_Constituent_Types();
