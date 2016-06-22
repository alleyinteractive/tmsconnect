<?php

/**
 * Custom taxonomy for Historical Periods
 */
class TMSC_Taxonomy_Historical_Period extends TMSC_Taxonomy {

	/**
	 * Name of the custom taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'historical_period';

	/**
	 * Constructmsc. Always call the parent for global actions.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Creates the taxonomy
	 *
	 * @access public
	 * @return void
	 */
	public function create_taxonomy() {
		$labels = array(
			'name' => _x( 'Historical Period', 'taxonomy general name', 'tmsc' ),
			'singular_name' => _x( 'Historical Period', 'taxonomy singular name', 'tmsc' ),
			'search_items' => __( 'Search Historical Periods', 'tmsc' ),
			'popular_items' => __( 'Popular Historical Periods', 'tmsc' ),
			'all_items' => __( 'All Historical Periods', 'tmsc' ),
			'parent_item' => __( 'Parent Period', 'tmsc' ),
			'parent_item_colon' => __( 'Parent Period:', 'tmsc' ),
			'edit_item' => __( 'Edit Period', 'tmsc' ),
			'update_item' => __( 'Update Period', 'tmsc' ),
			'add_new_item' => __( 'Add New Period', 'tmsc' ),
			'new_item_name' => __( 'New Historical Period Name', 'tmsc' ),
			'menu_name' => __( 'Historical Periods', 'tmsc' ),
			'choose_from_most_used' => __( 'Choose from the most used periods', 'tmsc' ),
			'separate_items_with_commas' => __( 'Separate periods with commas', 'tmsc' ),
			'add_or_remove_items' => __( 'Add or remove periods', 'tmsc' ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'show_admin_column' => true,
			'rewrite' => false,
		);

		register_taxonomy( $this->name, array( 'tms_object' ), $args );
	}

	/**
	 * Adds the custom term fields
	 *
	 * @access public
	 * @return void
	 */
	public function custom_term_fields() {

	}
}
new TMSC_Taxonomy_Historical_Period();
