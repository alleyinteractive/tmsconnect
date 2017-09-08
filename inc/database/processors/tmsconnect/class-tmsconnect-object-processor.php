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
	 * The number of web visible TMS objects to migrate.
	 */
	public $total_objects = 0;

	/**
	 * The number of web visible TMS objects to migrate in a batch.
	 */
	public $batch_size = 0;

	/**
	 * The starting point of our batch.
	 */
	public $offset = 0;

	/**
	 * Current raw data of our batch objects.
	 * @var array
	 */
	private $current_batch = array();

	/**
	 * Current object raw data.
	 * @var object
	 */
	private $current_object = null;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
		$this->batch_size = apply_filters( 'tmsc_object_sync_batch_size', 500 );
		$this->total_objects = $this->get_num_objects();
		// TODO: DELETE THIS AFTER TESTING
		$this->total_objects = 200;
	}

	/**
	 * Run our import in batches by taxonomy.
	 * @return void
	 */
	public function run() {
		while ( $this->offset < $this->total_objects ) {
			$this->current_batch = $this->get_migratable_objects();
			foreach ( $this->current_batch as $object ) {
				$this->current_object = $object;
				parent::run();
			}
			$this->offset = $this->offset + $this->batch_size;
		}
	}

	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", '', $this->current_object );
	}

	/**
	 * Generate a batch of objects to migrate.
	 * Ensure that these objects are ordered by ObjectID and return the columns.
	 */
	public function get_migratable_objects() {
		$stmt = apply_filters( "tmsc_{$this->processor_type}_batch_stmt_query", '' );

		// DB systems use different syntax for offsets and limits.
		$stmt = $this->set_offset_sql( $stmt );
		$params = array(
			':offset' => $this->offset,
			':size' => $this->batch_size,
		);
		return $this->fetch_results( $stmt, $this->object_query_key, $params );
	}

	/**
	 * Get the total number of TMS objects that we will be migrating.
	 */
	public function get_num_objects() {
		$query_key = $this->object_query_key . '_count';
		$stmt = apply_filters( "tmsc_{$this->processor_type}_count_stmt_query", '' );
		$results = $this->fetch_results( $stmt, $query_key );
		return reset( $results )->total;
	}

	/**
	 * Get the related WP terms of a given TMS Object ID.
	 * @param int $object_id. TMS raw Object ID.
	 * @return array. An associate array of taxonmies and it's term ids. array( 'taxonomy-slug' => array( 1, 2... ) ).
	 */
	public function get_related_terms( $object_id ) {
		$query_key = $this->object_query_key . '_terms';
		$terms = array();
		$stmt = apply_filters( "tmsc_{$this->processor_type}_related_terms_stmt_query", '', $object_id );
		if ( ! empty( $stmt ) ) {
			$results = $this->fetch_results( $stmt, $query_key );

			$terms = array();
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

	/**
	 * Get the related WP terms of a given TMS Object ID.
	 * @param int $object_id. TMS raw Object ID.
	 * @return array. An associate array of taxonmies and it's term ids. array( 'taxonomy-slug' => array( 1, 2... ) ).
	 */
	public function get_object_attachments( $object_id ) {
		$query_key = $this->object_query_key . '_attachments';
		$stmt = apply_filters( "tmsc_{$this->processor_type}_attachments_stmt_query", '', $object_id );
		if ( ! empty( $stmt ) ) {
			return $this->fetch_results( $stmt, $query_key );
		}
		return array();
	}
}
