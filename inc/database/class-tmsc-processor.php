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

		// Child processors are inherently batched. So skip.
		if ( ! in_array( $this->processor_type, \TMSC\TMSC::instance()->get_child_processors(), true ) ) {
			$stmt = $this->set_offset_sql( $stmt );
			$cursor = tmsc_get_cursor( $this->processor_type );
			$offset_params = array(
				':offset' => $cursor['offset'],
				':size' => $this->batch_size,
			);
			$params = array_merge( $params, $offset_params );
		}

		// Set the object query to get the next result set, required by System_Processor
		$this->set_object_query( $stmt );
		parent::before_run( $params );

		// If no data was found, we're finished
		if ( empty( $this->data ) && ! in_array( $this->processor_type, \TMSC\TMSC::instance()->get_child_processors(), true ) ) {
			tmsc_update_cursor( $this->processor_type, $this->batch_size, true );
		}
	}

	/**
	 * Increment our batch counts.
	 */
	protected function after_run( $params = array() ) {
		parent::after_run( $params );
		if ( ! empty( $this->data ) && ! in_array( $this->processor_type, \TMSC\TMSC::instance()->get_child_processors(), true ) ) {
			tmsc_update_cursor( $this->processor_type, $this->batch_size );
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
