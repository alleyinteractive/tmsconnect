<?php

/**
 * Taxonomy for Materials.
 */
class Tmsc_Taxonomy_Material extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'material';

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
	 * @param array.
	 * @return array.
	 */
	public function register_taxonomy_args( $args = array() ) {
		return array(
			'labels' => array(
				'name'                  => __( 'Materials', 'tmsc' ),
				'singular_name'         => __( 'Material', 'tmsc' ),
				'search_items'          => __( 'Search Materials', 'tmsc' ),
				'popular_items'         => __( 'Popular Materials', 'tmsc' ),
				'all_items'             => __( 'All Materials', 'tmsc' ),
				'parent_item'           => __( 'Parent Material', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Material', 'tmsc' ),
				'edit_item'             => __( 'Edit Material', 'tmsc' ),
				'view_item'             => __( 'View Material', 'tmsc' ),
				'update_item'           => __( 'Update Material', 'tmsc' ),
				'add_new_item'          => __( 'Add New Material', 'tmsc' ),
				'new_item_name'         => __( 'New Material Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Materials', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Materials', 'tmsc' ),
				'menu_name'             => __( 'Materials', 'tmsc' ),
			),
			'hierarchical' => true,
			'rewrite' => array(
				'with_front' => false,
			),
		);
	}
}

$taxonomy_material = new Tmsc_Taxonomy_Material();
