<?php

/**
 * Taxonomy for Exhibition Types.
 */
class Tmsc_Taxonomy_Exhibition_Type extends Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'exhibition_type';

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
		$this->object_types = array( 'exhibition' );

		parent::__construct();
	}

	/**
	 * Args passed to register taxonomy.
	 * Allows for a filter.
	 * @return array.
	 */
	public function register_taxonomy_args() {
		$exhibition_type_labels = array(
			'name'                       => _x( 'Exhibition Type', 'Taxonomy General Name', 'tmsc' ),
			'singular_name'              => _x( 'Exhibition Type', 'Taxonomy Singular Name', 'tmsc' ),
			'menu_name'                  => __( 'Exhibition Types', 'tmsc' ),
			'all_items'                  => __( 'All Exhibition Types', 'tmsc' ),
			'new_item_name'              => __( 'New Exhibition Type', 'tmsc' ),
			'add_new_item'               => __( 'Add New Exhibition Type', 'tmsc' ),
			'edit_item'                  => __( 'Edit Exhibition Type', 'tmsc' ),
			'update_item'                => __( 'Update Exhibition Type', 'tmsc' ),
			'view_item'                  => __( 'View Exhibition Type', 'tmsc' ),
			'separate_items_with_commas' => __( '', 'tmsc' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'tmsc' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'tmsc' ),
			'popular_items'              => __( 'Popular Exhibition Types', 'tmsc' ),
			'search_items'               => __( 'Search Exhibition Types', 'tmsc' ),
			'not_found'                  => __( 'Not Found', 'tmsc' ),
			'no_terms'                   => __( 'No items', 'tmsc' ),
			'items_list'                 => __( 'Exhibition Types list', 'tmsc' ),
			'items_list_navigation'      => __( 'Exhibition Types list navigation', 'tmsc' ),
		);
		$rewrite_args = array(
			'slug' => 'exhibition-type',
			'with_front' => true,
			'hierarchical' => true,
			'ep_mask' => 0,
		);
		$exhibition_type_args = array(
			'labels'            => $exhibition_type_labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'show_tagcloud'     => false,
			'rewrite'           => $rewrite_args,
		);
		return $exhibition_type_args;
	}
}

$taxonomy_exhibition_type = new Tmsc_Taxonomy_Exhibition_Type();

