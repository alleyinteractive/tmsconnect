<?php
/**
 * Custom post type for Events.
 */
class Tmsc_Post_Type_Event extends Tmsc_Post_Type {

	/**
	 * Name of the custom post type.
	 *
	 * @var string
	 */
	public $name = 'event';

	/**
	 * Creates the post type.
	 */
	public function create_post_type() {
		register_post_type( $this->name, array(
			'labels' => array(
				'name'               => __( 'Events', 'tmsc' ),
				'singular_name'      => __( 'Event', 'tmsc' ),
				'add_new'            => __( 'Add New Event', 'tmsc' ),
				'add_new_item'       => __( 'Add New Event', 'tmsc' ),
				'edit_item'          => __( 'Edit Event', 'tmsc' ),
				'new_item'           => __( 'New Event', 'tmsc' ),
				'view_item'          => __( 'View Event', 'tmsc' ),
				'search_items'       => __( 'Search Events', 'tmsc' ),
				'not_found'          => __( 'No Events found', 'tmsc' ),
				'not_found_in_trash' => __( 'No Events found in Trash', 'tmsc' ),
				'parent_item_colon'  => __( 'Parent Event:', 'tmsc' ),
				'menu_name'          => __( 'Events', 'tmsc' ),
			),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-calendar-alt',
			'supports' => array( 'title', 'revisions', 'excerpt' ),
		) );
	}
}

$post_type_event = new Tmsc_Post_Type_Event();
