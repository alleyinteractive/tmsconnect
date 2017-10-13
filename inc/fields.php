<?php
/**
 * Auto generate our FM fields based on our meta data mappings for our defined post types.
 */
function tmsc_add_fm_meta_boxes() {
	if ( is_admin() ) {
		$processors = tmsc_get_system_processors();
		// We will add meta fields for any post types that aren't objects. By default constituents and exhibitions.
		foreach ( array_keys( $processors ) as $type ) {
			if ( post_type_exists( $type ) ) {
				add_action( "fm_post_{$type}", 'tmsc_add_post_type_meta_boxes' );
			}
		}
	}
}
add_action( 'init', 'tmsc_add_fm_meta_boxes' );

/**
 * Generic function to auto generate Fieldmanager text fields for imported data.
 */
function tmsc_add_post_type_meta_boxes( $type ) {
	if ( ! empty( $type ) && in_array( $type, array_keys( tmsc_get_system_processors() ) ) && ! ( TMSC_Custom_Landing_Page_Types()->on_custom_landing_admin_page() && 'single' === TMSC_Custom_Landing_Page_Types()->current_landing_type ) && 'taxonomy' !== $type ) {
		$post_type = ( 'object' === $type ) ? 'tms_object' : $type;

		$mapping = apply_filters( "tmsc_{$type}_meta_keys", array() );
		if ( ! empty( $mapping ) ) {
			foreach ( $mapping as $key => $field ) {
				$fm = new Fieldmanager_Textfield( array(
					'name' => $key,
					'label' => __( 'Imported DB Field', 'tmsc' ),
				) );
				$fm->add_meta_box( $field, array( $post_type ), 'normal' );
			}
		}
		$post_processed_fields = apply_filters( "tmsc_{$type}_relationship_map", array() );
		if ( ! empty( $post_processed_fields ) ) {
			foreach ( $post_processed_fields as $slug => $config ) {
				if ( 'post' === $config['type'] || 'page' === $config['type'] ) {
					if ( 'attachment' === $config['slug'] ) {
						$fm = new Fieldmanager_Group( array(
							'name' => $slug,
							'limit' => 0,
							'add_more_label' => __( 'Add', 'tmsc' ),
							'sortable' => true,
							'children' => array(
								'ids' => new Fieldmanager_Media(),
							),
						) );
						$fm->add_meta_box( $config['label'], $post_type );
					} else {
						$fm = new Fieldmanager_Zone_Field(
							array(
								'name' => $slug,
								'query_args' => array( 'post_type' => $config['slug'] ),
							)
						);
						$fm->add_meta_box( $config['label'], $post_type );
					}
				} elseif ( 'link' === $config['type'] ) {
					$fm = new Fieldmanager_Group( array (
						'name' => $slug,
						'limit' => 0,
						'add_more_label' => __( 'Add', 'tmsc' ),
						'sortable' => true,
						'children' => array(
							'links' => new Fieldmanager_Group( array(
								'children' => array(
									'url' => new Fieldmanager_Textfield( array(
										'label' => __( 'Url', 'tmsc' ),
									) ),
									'label' => new Fieldmanager_Textfield( array(
										'label' => __( 'Label', 'tmsc' ),
									) ),
									'image' => new Fieldmanager_Textfield( array(
										'label' => __( 'Image Url', 'tmsc' ),
									) ),
								),
							) ),
						),
					) );
					$fm->add_meta_box( $config['label'], $post_type );
				}
			}
		}
	}
}

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

/* begin fm:medium */
/**
 * `medium` Fieldmanager fields.
 */
function tmsc_fm_medium() {
	$fm = new Fieldmanager_Textfield( array(
		'name' => 'medium',
		'label' => __( 'Medium', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'Medium', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_medium' );
/* end fm:medium */

/* begin fm:object_name */
/**
 * `object_name` Fieldmanager fields.
 */
function tmsc_fm_object_name() {
	$fm = new Fieldmanager_Textfield( array(
		'name' => 'object_name',
		'label' => __( 'Object Name', 'tmsc' ),
	) );
	$fm->add_meta_box( __( 'Object Name', 'tmsc' ), array( 'tms_object' ), 'normal' );
}
add_action( 'fm_post_tms_object', 'tmsc_fm_object_name' );
/* end fm:object_name */
