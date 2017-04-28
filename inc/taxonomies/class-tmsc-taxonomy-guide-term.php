<?php

/**
 * Taxonomy for Guide Terms.
 */
class Tmsc_Taxonomy_Guide_Term extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'guide-term';

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
	 * Creates the taxonomy.
	 */
	public function create_taxonomy() {
		register_taxonomy( $this->name, $this->object_types, array(
			'labels' => array(
				'name'                  => __( 'Guide Terms', 'tmsc' ),
				'singular_name'         => __( 'Guide Term', 'tmsc' ),
				'search_items'          => __( 'Search Guide Terms', 'tmsc' ),
				'popular_items'         => __( 'Popular Guide Terms', 'tmsc' ),
				'all_items'             => __( 'All Guide Terms', 'tmsc' ),
				'parent_item'           => __( 'Parent Guide Term', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Guide Term', 'tmsc' ),
				'edit_item'             => __( 'Edit Guide Term', 'tmsc' ),
				'view_item'             => __( 'View Guide Term', 'tmsc' ),
				'update_item'           => __( 'Update Guide Term', 'tmsc' ),
				'add_new_item'          => __( 'Add New Guide Term', 'tmsc' ),
				'new_item_name'         => __( 'New Guide Term Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Guide Terms', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Guide Terms', 'tmsc' ),
				'menu_name'             => __( 'Guide Terms', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		) );
	}
}

$taxonomy_guide_term = new Tmsc_Taxonomy_Guide_Term();
