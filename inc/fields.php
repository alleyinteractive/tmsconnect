<?php

/* begin fm:related_objects */
/**
 * `related_objects` Fieldmanager fields.
 */
function tmsc_fm_related_objects() {
	$fm = new Fieldmanager_Autocomplete( array(
		'name' => 'related_objects',
		'limit' => 0,
		'label' => __( 'TMS Object Name', 'tmsc' ),
		'add_more_label' => __( 'Add another object', 'tmsc' ),
		'datasource' => new Fieldmanager_Datasource_Post( array(
			'query_args' => array(
				'post_type' => array( 'tms_object' ),
			),
		) ),
	) );
	$fm->add_meta_box( __( 'Related Records', 'tmsc' ), array( 'tms_object' ) );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_related_objects' );
/* end fm:related_objects */

/* begin fm:accession_number */
/**
 * `accession_number` Fieldmanager fields.
 */
function tmsc_fm_accession_number() {
	$fm = new Fieldmanager_Textfield( array(
		'name' => 'accession_number',
		'label' => __( 'Accession Number', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'Accession Number', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_accession_number' );
/* end fm:accession_number */

/* begin fm:movement */
/**
 * `movement` Fieldmanager fields.
 */
function tmsc_fm_movement() {
	$fm = new Fieldmanager_Textfield( array(
		'name' => 'movement',
		'label' => __( 'Movement', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'Movement', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_movement' );
/* end fm:movement */

/* begin fm:provenance */
/**
 * `provenance` Fieldmanager fields.
 */
function tmsc_fm_provenance() {
	$fm = new Fieldmanager_Richtextarea( array(
		'name' => 'provenance',
		'label' => __( 'Provenance', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'Provenance', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_provenance' );
/* end fm:provenance */

/* begin fm:dimensions */
/**
 * `dimensions` Fieldmanager fields.
 */
function tmsc_fm_dimensions() {
	$fm = new Fieldmanager_Textfield( array(
		'name' => 'dimensions',
		'label' => __( 'Dimensions', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'Dimension(s)', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_dimensions' );
/* end fm:dimensions */

/* begin fm:location */
/**
 * `location` Fieldmanager fields.
 */
function tmsc_fm_location() {
	$fm = new Fieldmanager_Textfield( array(
		'name' => 'location',
		'label' => __( 'Location', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'On View Location', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_location' );
/* end fm:location */

/* begin fm:rights */
/**
 * `rights` Fieldmanager fields.
 */
function tmsc_fm_rights() {
	$fm = new Fieldmanager_Textfield( array(
		'name' => 'rights',
		'label' => __( 'Rights', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'Rights Statement', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_rights' );
/* end fm:rights */

/* begin fm:credit */
/**
 * `credit` Fieldmanager fields.
 */
function tmsc_fm_credit() {
	$fm = new Fieldmanager_Textfield( array(
		'name' => 'credit',
		'label' => __( 'Credit', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'Credit Line', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_credit' );
/* end fm:credit */

/* begin fm:school */
/**
 * `school` Fieldmanager fields.
 */
function tmsc_fm_school() {
	$fm = new Fieldmanager_Textfield( array(
		'name' => 'school',
		'label' => __( 'School', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'School', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_school' );
/* end fm:school */

/* begin fm:references */
/**
 * `references` Fieldmanager fields.
 */
function tmsc_fm_references() {
	$fm = new Fieldmanager_Richtextarea( array(
		'name' => 'references',
		'label' => __( 'References', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'Published References', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_references' );
/* end fm:references */

/* begin fm:web_resources */
/**
 * `web_resources` Fieldmanager fields.
 */
function tmsc_fm_web_resources() {
	$fm = new Fieldmanager_Group( array(
		'name' => 'web_resources',
		'limit' => 0,
		'add_more_label' => __( 'Add another resource', 'tmsc' ),
		'children' => array(
			'title' => new Fieldmanager_Textfield( __( 'Title', 'tmsc' ) ),
			'url' => new Fieldmanager_Textfield( __( 'Web Resource', 'tmsc' ) ),
		),
	) );
	$fm->add_meta_box( __( 'Web Resources', 'tmsc' ), array( 'tms_object' ) );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_web_resources' );
/* end fm:web_resources */
