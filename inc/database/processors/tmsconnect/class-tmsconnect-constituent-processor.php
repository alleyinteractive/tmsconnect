<?php
/**
 * The class used to process TMS Exhibition Modules
 */
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Constituent_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * Which migrateable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Object';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_constituents';

	/**
	 * The post type used with this processor if applicable.
	 */
	public $post_type = 'constituent';

	public $constituent_roles = array();

	public $constituent_types = array();

	/**
	 * Number of objects to process at a time.
	 */
	public $batch_size = 100;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
		$this->constituent_types = $this->get_constituent_types();
		$this->constituent_roles = $this->get_constituent_roles();
	}

	/**
	 * Run our import in batches by taxonomy.
	 * @return void
	 */
	public function run() {
		add_filter( "tmsc_set_{$this->processor_type}_post_type", array( $this, 'get_post_type' ) );
		parent::run();
		remove_filter( "tmsc_set_{$this->processor_type}_post_type", array( $this, 'get_post_type' ) );
	}

	/**
	 * Get the current post type associated with this processor if applicable.
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Generate our exhibitions we are migrating.
	 */
	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", '', $this );
	}

	/**
	 * Ensure we have our type taxonomies populated.
	 * @return array. An array taxonomy terms.
	 */
	public function get_constituent_types() {
		$types = array();
		$query_key = "{$this->object_query_key}_types";
		$stmt = apply_filters( "tmsc_{$this->processor_type}_types_stmt_query", '', $this );
		if ( ! empty( $stmt ) ) {
			$results = $this->fetch_results( $stmt, $query_key );
			foreach ( $results as $constituent_type ) {
				$existing_term = tmsc_get_term_by_legacy_id( $constituent_type->ID, 'constituent_type' );
				if ( empty( $existing_term ) ) {
					$new_term = wp_insert_term( $constituent_type->Name, 'constituent_type' );
					$term_id = $new_term['term_id'];
					add_term_meta( $term_id, 'tmsc_legacy_id', $constituent_type->ID );
				} else{
					$term_id = $existing_term->term_id;
				}
				$types[ $constituent_type->ID ] = $term_id;
			}
		}
		return $types;
	}

	/**
	 * Ensure we have our type taxonomies populated.
	 * @return array. An array taxonomy terms.
	 */
	public function get_constituent_roles() {
		$roles = apply_filters( 'tmsc_set_constituent_roles', array() );
		if ( empty( $roles ) ) {
			$query_key = "{$this->object_query_key}_roles";
			$stmt = apply_filters( "tmsc_{$this->processor_type}_roles_stmt_query", '', $this );
			if ( ! empty( $stmt ) ) {
				$results = $this->fetch_results( $stmt, $query_key );
				foreach ( $results as $role ) {
					$legacy_id = "{$this->constituent_types[ $role->ID ]}-{$role->RoleID}";
					$existing_term = tmsc_get_term_by_legacy_id( $legacy_id, 'constituent_type' );
					if ( empty( $existing_term ) ) {
						$parent_term = tmsc_get_term_by_legacy_id( $role->ID, 'constituent_type' );
						$new_term = wp_insert_term( $role->Role, 'constituent_type', array( 'parent' => $parent_term->term_id ) );
						if ( ! empty( $new_term ) && ! is_wp_error( $new_term ) ) {
							$term_id = $new_term['term_id'];
							add_term_meta( $term_id, 'tmsc_legacy_id', $legacy_id );
							add_term_meta( $term_id, 'constituent_role_id', $role->RoleID );
							add_term_meta( $term_id, 'constituent_type_id', $role->ID );
						}
					} else{
						$term_id = $existing_term->term_id;
					}
					if ( ! empty( $term_id ) ) {
						$roles[ $legacy_id ] = $term_id;
					}
				}
			}
		}
		return $roles;
	}

	/**
	 * Get the related WP terms of a given TMS Object ID.
	 * @param int $object_id. TMS raw Object ID.
	 * @return array. An associate array of taxonmies and it's term ids. array( 'taxonomy-slug' => array( 1, 2... ) ).
	 */
	public function get_related_terms( $object_id ) {
		$terms = array();
		$query_key = $this->object_query_key . '_terms';
		$stmt = apply_filters( "tmsc_{$this->processor_type}_related_terms_stmt_query", '', $object_id );
		if ( ! empty( $stmt ) ) {
			$results = $this->fetch_results( $stmt, $query_key );

			if ( ! empty( $results ) ) {
				foreach ( $results as $row ) {
					$existing_term = tmsc_get_term_by_legacy_id( $row->TermID );

					if ( ! empty( $existing_term ) && ! is_wp_error( $existing_term ) ) {
						if ( $existing_term instanceof \WP_Term ) {
							$terms[ $existing_term->taxonomy ][] = $existing_term->term_id;
						} elseif ( is_array( $existing_term ) ) {
							// This will get triggered with search terms. So let's index both.
							foreach ( $existing_term as $multi_term ) {
								$terms[ $multi_term->taxonomy ][] = $multi_term->term_id;
							}
						}
					}
				}
			}
		}
		return $terms;
	}

	/**
	 * Get the related WP terms of a given TMS Object ID.
	 * @param int $object_id. TMS raw Object ID.
	 * @param string $key. The slug to be used in the meta table.
	 * @param array $config.
	 * @return array. An array of legacy ids to be post processed.
	 */
	public function get_related( $object_id, $key, $config = array() ) {
		$query_key = $this->object_query_key . '_' . $key;
		$stmt = apply_filters( "tmsc_{$this->processor_type}_relationship_{$key}_stmt_query", '', $object_id, $config );
		$relationship_data = array();
		if ( ! empty( $stmt ) ) {
			$results = $this->fetch_results( $stmt, $query_key );
			$relationship_data = wp_list_pluck( $results, 'ID' );
		}
		return $relationship_data;
	}
}
