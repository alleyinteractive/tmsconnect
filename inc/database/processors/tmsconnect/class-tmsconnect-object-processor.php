<?php
/**
 * The class used to process TMS Object Modules
 */
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Object_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Object';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_objects';

	/**
	 * The post type used with this processor if applicable.
	 */
	public $post_type = 'tms_object';

	/**
	 * Number of objects to process at a time.
	 */
	public $batch_size = 40;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
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

	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", '' );
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
						if ( $existing_term instanceof WP_Term ) {
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
			// Check results and conditionally map data based on field vlaues of a result row. Used with constituents and any other complex meta mappings of legacy IDs.
			if ( ! empty( $config['keys'] ) ) {
				// Iterate through our results to map keys to data.
				foreach ( $results as $row ) {
					$relationship_keys = array();
					foreach ( $config['keys'] as $conditional_key => $data ) {
						if ( empty( $relationship_data[ $conditional_key ] ) ) {
							$relationship_data[ $conditional_key ] = array();
						}
						if ( ! empty( $data['conditions'] ) ) {
							$conditions = $data['conditions'];
						} else {
							$conditions = $data;
						}

						$meets_conditions = $this->meets_conditions( $row, $conditions );
						if ( $meets_conditions ) {
							if ( ! empty( $data['subkey'] ) ) {
								if ( empty( $relationship_data[ $conditional_key ][ $data['subkey'] ] ) ) {
									$relationship_data[ $conditional_key ][ $data['subkey'] ] = array();
								}
								$relationship_data[ $conditional_key ][ $data['subkey'] ][] = $row->ID;
							} else {
								$relationship_data[ $conditional_key ][] = $row->ID;
							}
						}
					}
				}
			} elseif ( 'link' === $config['type'] ) {
				$relationship_data = array_map( 'get_object_vars', $results );
			} else {
				$relationship_data = wp_list_pluck( $results, 'ID' );
			}
		}
		return $relationship_data;
	}

	public function meets_conditions( $data, $conditions ) {
		foreach ( $conditions as $field => $value ) {
			if ( $data->{$field} !== $value ) {
				return false;
			}
		}
		return true;
	}
}
