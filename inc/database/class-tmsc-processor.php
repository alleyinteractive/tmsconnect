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
	public function set_object_query( $query ) {
		// If the batch query was previously set, remove it.
		// Subclasses should be able to override this.
		if ( isset( $this->queries[ $this->object_query_key ] ) ) {
			unset( $this->queries[ $this->object_query_key ] );
		}

		// Prepare and store this query
		$this->prepare( $this->object_query_key, $query );
	}

	/**
	 * Get the next batch of migratables.
	 */
	protected function before_run( $params = array() ) {
		$stmt = $this->get_object_query_stmt();
		// Set the object query to get the next result set, required by System_Processor
		$this->set_object_query( $stmt );

		parent::before_run( $params );
	}

	/**
	 * Increment our batch counts.
	 */
	protected function after_run( $params = array() ) {
		parent::after_run( $params );
	}

	/**
	 * Load the next migrateable object
	 * @param array $params
	 */
	public function load_migrateable() {
		if ( ! empty( $this->data ) ) {
			$class = '\\TMSC\\Database\\TMSC_' . $this->migrateable_type;
			$this->migrateable = new $class( array_shift( $this->data ), $this );
		} else {
			$this->migrateable = null;
		}
	}
}
