<?php
namespace TMSC\Database;
abstract class TMSC_Processor extends \TMSC\Database\System_Processor {

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
	}

	/**
	 * Generate our objects we are migrating.
	 */
	abstract public function get_object_query_stmt();

	/**
	 * Set the main query used to obtain each listing of migrated items
	 * @param string $query
	 */
	public function set_object_query( $query, $key = null ) {
		$key = ( empty( $key ) ) ? $this->object_query_key : $key;

		// If the batch query was previously set, remove it.
		// Subclasses should be able to override this.
		if ( isset( $this->queries[ $key ] ) ) {
			unset( $this->queries[ $key ] );
		}

		// Prepare and store this query
		$this->prepare( $key, $query );
	}

	/**
	 * Get the next batch of migratables.
	 */
	protected function before_run( $params = array() ) {
		$stmt = $this->get_object_query_stmt();
		$stmt = $this->set_offset_sql( $stmt );
		// Set the object query to get the next result set, required by System_Processor
		$this->set_object_query( $stmt );
		$cursor = $this->get_cursor( $this->processor_type );

		$offset_params = array(
			':offset' => $cursor['offset'],
			':size' => $this->batch_size,
		);
		$full_params = array_merge( $params, $offset_params );

		parent::before_run( $full_params );

		// If no data was found, we're finished
		if ( empty( $this->data ) ) {
			$this->update_cursor( $this->processor_type, true );
		}
	}

	/**
	 * Get the offset of the current processor batch
	 */
	public function get_cursor( $processor ) {
		return get_option( "tmsc-cursor-{$processor}", array( 'offset' => 0, 'completed' => false ) );
	}

	/**
	 * Keep track of where our last run terminated.
	 */
	public function update_cursor( $processor, $completed = false ) {
		if ( ! empty( $processor ) ) {
			$cursor = $this->get_cursor( $this->processor_type );
			if ( empty( $completed ) ) {
				$cursor['offset'] = $cursor['offset'] + $this->batch_size + 1;
			} else {
				$cursor['completed'] = true;
			}

			update_option( "tmsc-cursor-{$processor}", $cursor, false );
			wp_cache_delete( "tmsc-cursor-{$processor}", 'options' );
		}
		return;
	}

	/**
	 * Increment our batch counts.
	 */
	protected function after_run( $params = array() ) {
		parent::after_run( $params );
		if ( ! empty( $this->data ) ) {
			$this->update_cursor( $this->processor_type );
		}
	}

	/**
	 * Load the next migrateable object
	 * @param array $params
	 */
	public function load_migrateable() {
		if ( ! empty( $this->data ) ) {
			$class = '\\TMSC\\Database\\TMSC_' . $this->migrateable_type;
			$this->migrateable = new $class();
		} else {
			$this->migrateable = null;
		}
	}
}
