<?php
/**
 * Auto generate our FM fields based on our meta data mappings for our defined post types.
 */
function tmsc_add_fm_meta_boxes() {
	if ( is_admin() ) {
		$processors = tmsc_get_system_processors();
		// We will add meta fields for any post types that aren't objects. By default constituents and exhibitions.
		foreach ( array_keys( $processors ) as $type ) {
			// Meta Data
			$post_type = ( 'object' === $type ) ? 'tms_object' : $type;
			if ( post_type_exists( $post_type ) ) {
				add_action( "fm_post_{$post_type}", 'tmsc_add_post_type_meta_boxes' );
			}
		}
	}
}
add_action( 'init', 'tmsc_add_fm_meta_boxes' );

/**
 * Generic function to auto generate Fieldmanager text fields for imported data.
 */
function tmsc_add_post_type_meta_boxes( $type ) {

	$active_types = array_keys( tmsc_get_system_processors() );
	$active_types[] = 'tms_object';
	if ( ! empty( $type ) && in_array( $type, $active_types, true ) && ! ( TMSC_Custom_Landing_Page_Types()->on_custom_landing_admin_page() && 'single' === TMSC_Custom_Landing_Page_Types()->current_landing_type ) && 'taxonomy' !== $type ) {

		// Meta Data
		$post_type = ( 'object' === $type ) ? 'tms_object' : $type;
		$type = ( 'tms_object' === $type ) ? 'object' : $type;

		$mapping = apply_filters( "tmsc_{$type}_meta_keys", array() );
		if ( ! empty( $mapping ) ) {
			foreach ( $mapping as $key => $field ) {
				$fm = new Fieldmanager_Textfield( array(
					'name' => $key,
				) );
				$fm->add_meta_box( $field, array( $post_type ), 'normal' );
			}
		}

		// Relationship data.
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
							'link' => new Fieldmanager_Group( array(
								'children' => array(
									'url' => new Fieldmanager_Textfield( array(
										'label' => __( 'Url', 'tmsc' ),
									) ),
									'label' => new Fieldmanager_Textfield( array(
										'label' => __( 'Label', 'tmsc' ),
									) ),
									'image' => new Fieldmanager_Textfield( array(
										'label' => __( 'Image Url', 'tmsc' ),
										'description' => __( 'Link to an external image', 'tmsc' ),
									) ),
								),
							) ),
						),
					) );
					$fm->add_meta_box( $config['label'], $post_type );
				}
			}
		}

		// Constituent Relationships.
		// Get all roles and constituent types and create metaboxes for them.
		$enabled_types = apply_filters( 'tmsc_constituent_enabled_types', array( 'tms_object' ) );
		if ( in_array( $post_type, $enabled_types, true ) ) {
			$constituent_types = get_terms( array(
				'taxonomy' => 'constituent_type',
				'hide_empty' => false,
				'parent' => 0,
			) );
			foreach ( $constituent_types as $type_term ) {
				$type_roles = get_terms( array(
					'taxonomy' => 'constituent_type',
					'hide_empty' => false,
					'parent' => $type_term->term_id,
				) );
				if ( ! empty( $type_roles ) ) {
					$roles = array();

					foreach ( $type_roles as $role_term ) {
						$roles[ $role_term->slug ] = new Fieldmanager_Zone_Field(
							array(
								'name' => $role_term->slug,
								'label' => $role_term->name,
								'query_args' => array(
									'post_type' => 'constituent',
									'tax_query' => array(
										'taxonomy' => 'constituent_type',
										'field' => 'term_id',
										'terms' => array( $role_term->term_id ),
									),
								),
							)
						);
					}

					$fm_constituent = new Fieldmanager_Group( array (
						'name' => $type_term->slug,
						'tabbed' => 'vertical',
						'children' => $roles,
					) );
					$fm_constituent->add_meta_box( __( 'Constituent Type : ', 'tmsc' ) . $type_term->name, $post_type );
				}
			}
		}
	}
}
