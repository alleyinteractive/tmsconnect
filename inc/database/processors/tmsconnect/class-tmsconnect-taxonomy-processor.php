<?php
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Taxonomy_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * Which migrateable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Taxonomy';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_taxonomies';

	/**
	 * An array of all taxonomies that need to be migrated.
	 * @var array
	 */
	public $taxonomies = array();

	/**
	 * Current taxonomy being migrated.
	 * @var object
	 */
	public $current_tax;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
		$this->taxonomies = $this->get_migrateable_taxonomies();
	}

	/**
	 * Run our import in batches by taxonomy.
	 * @return void
	 */
	public function run() {
		$cursor = $this->get_taxonomy_cursor();
		foreach ( $this->taxonomies as $index => $taxonomy ) {
			if ( ! in_array( $taxonomy->taxonomy, $cursor['migrated'], true ) ) {
				$this->current_tax = $taxonomy;
				break;
			}
		}

		// If we have migrated all the taxonomies, set it as completed.
		if ( ! empty( $this->current_tax ) ) {
			parent::run();
		} else {
			tmsc_update_cursor( $this->processor_type, $this->batch_size, true );
		}
	}

	/**
	 * Get our taxonomy cursor.
	 */
	public function get_taxonomy_cursor() {
		$cursor = tmsc_get_cursor( $this->processor_type );
		// If it is our first run through, then set the appropriate migrated cursor value.
		if ( empty( $cursor['migrated'] ) ) {
			$cursor['migrated'] = array();
			update_option( "tmsc-cursor-{$this->processor_type}", $cursor, false );
			wp_cache_delete( "tmsc-cursor-{$this->processor_type}", 'options' );
		}
		return $cursor;
	}

	/**
	 * Generate our objects we are migrating.
	 * Ensure that these objects are ordered by CN and return the columns.
	 */
	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", '', $this->current_tax->taxonomy, $this->current_tax->CN, $this );
	}

	/**
	 * Prepare our statement with the current taxonomy.
	 */
	protected function before_run( $params = array() ) {
		parent::before_run( $params );
		$cursor = $this->get_taxonomy_cursor();

		if ( $cursor['completed'] && ! empty( $this->current_tax ) ) {
			$cursor['migrated'][] = $this->current_tax->taxonomy;
			$cursor['offset'] = 0;
			// Set the global completed to false and allow it to get set in the run function.
			$cursor['completed'] = false;
			update_option( "tmsc-cursor-{$this->processor_type}", $cursor, true );
			wp_cache_delete( "tmsc-cursor-{$this->processor_type}", 'options' );
		}
	}

	/**
	 * Get the taxonomies that we will be migrating.
	 */
	public function get_migrateable_taxonomies() {
		$guide_terms = apply_filters( "tmsc_{$this->processor_type}_guide_terms", get_option( 'tmsc_guide_terms', array() ) );
		$cns = array();
		if ( ! empty( $guide_terms ) && ! empty( $guide_terms['term']['data'] ) ) {
			foreach ( $guide_terms['term']['data'] as $taxonomies ) {
				if ( ! empty( $taxonomies['taxonomy_map'] ) && ! empty( $taxonomies['CN'] ) ) {
					// Make sure we don't have a custom processor for the guide term.
					if ( file_exists( trailingslashit( TMSC_SYSTEM_PATH ) . TMSC_SYSTEM_BUILD_FILE_PREFIX . '/class-' . TMSC_SYSTEM_BUILD_FILE_PREFIX . '-' . $taxonomies['taxonomy_map'] . '-processor.php' ) ) {

					} else {
						$cns[ $taxonomies['CN'] ] = $taxonomies['taxonomy_map'];
					}
				}
			}

			$stmt = apply_filters( "tmsc_{$this->processor_type}_batch_stmt_query", '', $cns, $this );

			$this->prepare( $this->object_query_key, $stmt );
			$query = $this->query( $this->object_query_key );
			$results = $this->fetch_results( $stmt, $this->object_query_key );

			// Set the guide term as the top level taxonomy so that our results know the proper WP taxonomy.
			foreach ( $results as $index => $result ) {
				$results[ $index ]->taxonomy = $cns[ $result->CN ];
			}
			return $results;
		}
		return;
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
			return $this->fetch_results( $stmt, $query_key );
		}
		return $terms;
	}

}
