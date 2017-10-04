<?php
/**
 * The class used to process TMS Exhibition Modules
 */
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Exhibition_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Object';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_exhibitions';

	/**
	 * The post type used with this processor if applicable.
	 */
	public $post_type = 'exhibition';

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

	/**
	 * Generate our exhibitions we are migrating.
	 */
	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", '', $this );
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
					$term = tmsc_get_term_by_legacy_id( $row->TermID );
					if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
						$terms[ $term->taxonomy ][] = $term->term_id;
					}
				}
			}
		}
		return $terms;
	}

	/**
	 * Get the related WP terms of a given TMS Object ID.
	 * @param int $object_id. TMS raw Object ID.
	 * @return array. An associate array of taxonmies and it's term ids. array( 'taxonomy-slug' => array( 1, 2... ) ).
	 */
	public function get_related_objects( $object_id ) {
		$query_key = $this->object_query_key . '_related_objects';
		$stmt = apply_filters( "tmsc_{$this->processor_type}_related_objects_stmt_query", '', $object_id );
		if ( ! empty( $stmt ) ) {
			return $this->fetch_results( $stmt, $query_key );
		}
		return array();
	}
}
