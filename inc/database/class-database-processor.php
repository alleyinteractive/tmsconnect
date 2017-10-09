<?php
namespace TMSC\Database;
abstract class Database_Processor extends \TMSC\Database\Processor {
	protected $queries = array();


	abstract protected function get_connection();

	public function __construct( $type = null ) {
		// Only call a parent class if this has been instantiated properly.
		if ( ! empty( $type ) ) {
			parent::__construct( $type );
		}
	}

	public function prepare( $key, $query ) {
		$this->pdo = \TMSC\tmsc_sync()->get_connection();
		if ( empty( $this->queries[ $key ] ) ) {
			try {
				$this->queries[ $key ] = $this->pdo->prepare( $query );
			} catch ( \PDOException $e ) {
				error_log(
					strtr(
						print_r( $e->getMessage(), true ),
						array(
							"\r\n" => PHP_EOL,
							"\r" => PHP_EOL,
							"\n" => PHP_EOL,
						)
					)
				);
			}
		}

		return $this->queries[ $key ];
	}

	public function query( $key, $params = array(), $classname = 'stdClass' ) {
		if ( empty( $this->queries[ $key ] ) ) {
			throw new \Exception( "Tried to execute query '{$key}' which has not been prepared." );
		}
		$query = $this->queries[ $key ];
		$query->execute( $params );
		$query->setFetchMode( \PDO::FETCH_CLASS, $classname );

		return $query;
	}

	/**
	 * Load the next set of results
	 * @param array $params
	 */
	protected function before_run( $params = array() ) {
		// Execute the query and store the results
		$query = $this->query( $this->object_query_key, $params );
		$this->data = $query->fetchAll();
	}

	/**
	 * A helper function to fetch a custom set of results outside of the processors main migratables.
	 * @param string $stmt. An sql statement.
	 * @param string $query_key. The key uses in $this->queries
	 * @param array $params. Params to pass to query.
	 * @return array. An array of result row objects.
	 */
	public function fetch_results( $stmt, $query_key = null, $params = array() ) {
		$query_key = ( empty( $query_key ) ) ? $this->object_query_key : $query_key;
		$this->set_object_query( $stmt, $query_key );
		$query = $this->query( $query_key, $params );
		return $query->fetchAll();
	}

	/**
	 * Hook that runs before migrating a post.
	 * End this batch if no rows remain to process.
	 * @param boolean $dry
	 */
	protected function before_migrate_object() {}

	/**
	 * Hook that runs after migrating a post.
	 * Increment and save the cursor.
	 */
	protected function after_migrate_object() {

	}

	/**
	 * Hook that runs after the batch ends
	 * @param boolean $dry
	 */
	protected function after_run() {
		// Prevent memory issues
		tmsc_stop_the_insanity();
	}
}
