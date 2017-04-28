<?php
namespace TMSC\Database;
abstract class Database_Processor extends \TMSC\Database\Processor {
	/**
	 * The host name for the DB server
	 * @var string
	 */
	protected $host;

	/**
	 * The database name
	 * @var string
	 */
	protected $dbname;

	/**
	 * The DB username
	 * @var string
	 */
	protected $username;

	/**
	 * The DB password
	 * @var string
	 */
	protected $password;

	protected $pdo;

	protected $queries = array();


	abstract protected function get_connection();

	public function __construct( $type ) {

		$this->host = get_option( 'tmsc-db-host' );
		$this->dbname = get_option( 'tmsc-db-name' );
		$this->username = get_option( 'tmsc-db-user' );
		$this->password = get_option( 'tmsc-db-password' );

		parent::__construct( $type );
		$this->pdo = $this->get_connection();
	}
	public function prepare( $key, $query ) {
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

		// If no data was found, we're finished
		if ( empty( $this->data ) ) {
			$this->finish();
		}
	}

	/**
	 * Hook that runs before migrating a post.
	 * End this batch if no rows remain to process.
	 * @param boolean $dry
	 */
	protected function before_migrate_object() {
		// If no data is left, we're done with this batch
		if ( empty( $this->data ) ) {
			$this->halt();
		}
	}

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
