<?php

/**
 * Taxonomy for Periods.
 */
class Tmsc_Taxonomy_Period extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'period';

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
				'name'                  => __( 'Periods', 'tmsc' ),
				'singular_name'         => __( 'Period', 'tmsc' ),
				'search_items'          => __( 'Search Periods', 'tmsc' ),
				'popular_items'         => __( 'Popular Periods', 'tmsc' ),
				'all_items'             => __( 'All Periods', 'tmsc' ),
				'parent_item'           => __( 'Parent Period', 'tmsc' ),
				'parent_item_colon'     => __( 'Parent Period', 'tmsc' ),
				'edit_item'             => __( 'Edit Period', 'tmsc' ),
				'view_item'             => __( 'View Period', 'tmsc' ),
				'update_item'           => __( 'Update Period', 'tmsc' ),
				'add_new_item'          => __( 'Add New Period', 'tmsc' ),
				'new_item_name'         => __( 'New Period Name', 'tmsc' ),
				'add_or_remove_items'   => __( 'Add or remove Periods', 'tmsc' ),
				'choose_from_most_used' => __( 'Choose from most used Periods', 'tmsc' ),
				'menu_name'             => __( 'Periods', 'tmsc' ),
			),
			'rewrite' => array(
				'with_front' => false,
			),
		) );
	}
}

$taxonomy_period = new Tmsc_Taxonomy_Period();
