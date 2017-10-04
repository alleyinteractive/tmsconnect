<?php
/**
 * This file contains the singleton class for Search.
 *
 * @package TMSConnect
 */

namespace TMSC;

/**
 * Search
 */
class Search {
	use Singleton;

	/**
	 * Setup the singleton and kick off our search modifications.
	 */
	public function setup() {
		$this->setup_auth();
		$this->delay_sync_actions();

		add_filter( 'query_vars', [ $this, 'query_vars' ] );

		// SearchPress customizations.
		// TODO: Add customizations.

		// ES Admin customizations.
		add_filter( 'es_admin_searchable_fields', [ $this, 'es_admin_searchable_fields' ] );
		add_filter( 'es_admin_query_attachments_searchable_fields', [ $this, 'es_admin_query_attachments_searchable_fields' ], 10, 2 );
		add_action( 'es_admin_integration_pre_get_posts', [ $this, 'es_admin_integration_pre_get_posts' ] );
	}

	/**
	 * Handle authentication for the ES server.
	 */
	protected function setup_auth() {
		// If ES hosted by Found and protected by Shield, it requires http basic
		// authentication.
		$username = apply_filters( 'tmsc_sp_username', '' );
		$password = apply_filters( 'tmsc_sp_pass', '' );
		if ( function_exists( 'SP_API' ) && false !== strpos( SP_Config()->get_setting( 'host' ), 'aws.found.io' ) ) {
			SP_API()->request_defaults['headers']['Authorization'] = 'Basic ' . base64_encode( "$username:$password" );
		}
	}

	/**
	 * Delay sync action to ensure that all post meta is saved before SP syncs.
	 */
	protected function delay_sync_actions() {
		if ( class_exists( 'SP_Sync_Manager' ) && has_action( 'save_post', [ SP_Sync_Manager(), 'sync_post' ] ) ) {
			remove_action( 'save_post' ,[ SP_Sync_Manager(), 'sync_post' ] );
			add_action( 'save_post', [ SP_Sync_Manager(), 'sync_post' ], 1000 );
		}
	}

	/**
	 * Add custom fields that we want to be searchable within the admin.
	 *
	 * @param  array $fields Fields to be searched.
	 * @return array
	 */
	public function es_admin_searchable_fields( $fields ) {
		$es = \ES_Admin\ES::instance();
		$fields[] = $es->map_meta_field( 'title' );
		return $fields;
	}

	/**
	 * Filter the fields ES Admin searches when using ES_WP_Query in the
	 * `query_attachments` integration.
	 *
	 * @param  array $fields Mapped ES fields. {@see \ES_WP_Query::$es_map}.
	 * @param  \ES_WP_Query $query ES_WP_Query object for field mapping.
	 * @return array
	 */
	public function es_admin_query_attachments_searchable_fields( $fields, $query ) {
		$fields[] = $query->meta_map( 'title' );
		return $fields;
	}

	/**
	 * Hook into the filter to set searchable fields when the es admin
	 * integration is active.
	 *
	 * @param  \WP_Query $query WP_Query object, passed by reference.
	 */
	public function es_admin_integration_pre_get_posts( &$query ) {
		$excluded_object_types = [];
		if ( in_array( $query->get( 'post_type' ), $excluded_object_types, true ) ) {
			$query->set( 'es', false );
		} else {
			add_filter( 'es_searchable_fields', [ $this, 'es_admin_integration_searchable_fields' ], 10, 2 );
		}
	}

	/**
	 * Filter the fields ES Admin searches when using ES_WP_Query in the posts
	 * lists.
	 *
	 * @param  array $fields Mapped ES fields. {@see \ES_WP_Query::$es_map}.
	 * @param  \ES_WP_Query $query ES_WP_Query object for field mapping.
	 * @return array
	 */
	public function es_admin_integration_searchable_fields( $fields, $query ) {
		$fields[] = $query->meta_map( 'title' );
		return $fields;
	}

	/**
	 * Get the facets for the current global search, somewhat tweaked.
	 *
	 * @return array {@see \SP_WP_Search::get_facet_data()}.
	 */
	public static function get_facets() {
		return SP_Integration()->search_obj->get_facet_data( [
			'exclude_current' => false,
			'join_existing_terms' => false,
		] );
	}
}
Search::get_instance();