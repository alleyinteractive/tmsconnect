<?php

/**
 * Taxonomy for Constituent Roles.
 */
class Tmsc_Taxonomy_Constituent_Roles extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'constituent-roles';

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
	 * Creates the taxonomy.
	 */
	public function create_taxonomy() {
		register_taxonomy( $this->name, $this->object_types, array(
			'labels' => array(
				'name'                  => __( 'Constituent Roles', 'tmsc' ),
				'singular_name'         => __( 'Constituent Roles', 'tmsc' ),
				'search_items'          => __( 'Search Constituent Roles', 'tmsc' ),
				'popular_items'         => __( 'Popular Constituent Roles', 'tmsc' ),
				'all_items'             => __( 'All Constituent Roles', 'tmsc' ),
				'parent_item'           => __( 'Parent Constituent Roles', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Constituent Roles', 'tmsc' ),
				'edit_item'             => __( 'Edit Constituent Roles', 'tmsc' ),
				'view_item'             => __( 'View Constituent Roles', 'tmsc' ),
				'update_item'           => __( 'Update Constituent Roles', 'tmsc' ),
				'add_new_item'          => __( 'Add New Constituent Roles', 'tmsc' ),
				'new_item_name'         => __( 'New Constituent Roles Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Constituent Roles', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Constituent Roles', 'tmsc' ),
				'menu_name'             => __( 'Constituent Roles', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		) );
	}
}

$taxonomy_constituent_roles = new Tmsc_Taxonomy_Constituent_Roles();
