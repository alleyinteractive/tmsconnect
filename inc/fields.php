<?php

/* begin fm:related_objects */
/**
 * `related_objects` Fieldmanager fields.
 */
function tmsc_fm_related_objects() {
	$fm = new Fieldmanager_Autocomplete( array(
		'name' => 'related_objects',
		'limit' => 0,
		'label' => 'TMS Object Name',
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
