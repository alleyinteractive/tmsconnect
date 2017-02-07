<?php

namespace TMSC\Database;

abstract class MySQL_Processor extends \TMSC\Database\Database_Processor {
	/**
	 * The current batch of results for migration
	 * @var array
	 */
	protected $data = array();

	/**
	 * The host name for the MySQL server
	 * @var string
	 */
	protected $host;

	/**
	 * The MySQL database name
	 * @var string
	 */
	protected $dbname;

	/**
	 * The MySQL username
	 * @var string
	 */
	protected $username;

	/**
	 * The MySQL password
	 * @var string
	 */
	protected $password;

	/**
	 * The key used for the batch query
	 * @var string
	 */
	protected $batch_query_key = 'batch_query';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get the database connection
	 * @return \PDO database connection instance
	 */
	protected function get_connection() {
		$this->host = get_option( 'tmsc-db-host' );
		$this->dbname = get_option( 'tmsc-db-name' );
		$this->username = get_option( 'tmsc-db-user' );
		$this->password = get_option( 'tmsc-db-password' );

		// Return the existing connection if set
		if ( ! empty( $this->pdo ) ) {
			return $this->pdo;
		}

		// Build the DSN string
		$dsn = "mysql:host={$this->host};dbname={$this->dbname}";

		tmsc_notice( "Getting MySQL database connection $dsn" );
		$connection = new \PDO( $dsn, $this->username, $this->password, array(
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		) );

		// Since MySQL supports it, don't automatically quote parameters to allow control over data types.
		// This comes in useful for automatically manipulating the LIMIT clause.
		$connection->setAttribute( \PDO::ATTR_EMULATE_PREPARES, false );
		return $connection;
	}

	/**
	 * Set the main query used to obtain each batch of results
	 * @param string $query
	 */
	public function set_batch_query( $query ) {
		if ( false === stripos( $query, 'LIMIT' )
			&& false === stripos( $query, 'PROCEDURE' )
			&& false === stripos( $query, 'FOR UPDATE' )
			&& false === stripos( $query, 'LOCK IN SHARE MODE' ) ) {
			// Automatically add a LIMIT clause to standard queries.
			$query .= ' LIMIT :offset, :size';
		} else if ( false === stripos( $query, 'LIMIT' )
			&& ( false !== stripos( $query, 'PROCEDURE' )
			&& false !== stripos( $query, 'FOR UPDATE' )
			&& false !== stripos( $query, 'LOCK IN SHARE MODE' ) ) ) {
			// A LIMIT clause can't be added automatically but is required.
			// Throw an exception here.
			throw new \Exception( "A LIMIT clause in the format 'LIMIT :offset, :size' must be specified manually for this custom query." );
		} else if ( false !== stripos( $query, 'LIMIT' )
			&& false === stripos( $query, 'LIMIT :offset, :size' ) ) {
			// A LIMIT clause was added but is incompatible.
			throw new \Exception( "A LIMIT clause must be specified in the format 'LIMIT :offset, :size'." );
		}

		// If the batch query was previously set, remove it.
		// Subclasses should be able to override this.
		if ( isset( $this->queries[ $this->batch_query_key ] ) ) {
			unset( $this->queries[ $this->batch_query_key ] );
		}

		// Prepare and store this query
		$this->prepare( $this->batch_query_key, $query );
	}

	/**
	 * Load the next set of results
	 * @param array $params
	 */
	protected function before_run( $params = array() ) {
		// Initialize the cursor if needed
		if ( empty( $this->cursor ) ) {
			$this->init_cursor();
		}

		// Add offset and size to the list of params unless manually defined.
		// Any implementing classes are expected to provide their own required params.
		if ( ! isset( $params[':offset'] ) ) {
			$params[':offset'] = intval( $this->cursor );
		}

		if ( ! isset( $params[':size'] ) ) {
			$params[':size'] = intval( $this->batch_size );
		}

		// Execute the query and store the results
		$query = $this->query( $this->batch_query_key, $params );
		$this->data = $query->fetchAll();
		tmsc_notice( "Data size: " . count( $this->data ) );
		$query->closeCursor();

		// If no data was found, we're finished
		if ( empty( $this->data ) ) {
			tmsc_notice( 'No rows were found' );
			$this->finish();
		} else {
			tmsc_notice( 'Processing rows ' . $this->cursor . ' to ' . $this->cursor + $this->batch_size );
		}

	}

	/**
	 * Reset the offset to the first row of data when starting a migration
	 * @return array
	 */
	public function get_starting_cursor() {
		return 0;
	}

	/**
	 * Hook that runs before migrating a post.
	 * End this batch if no rows remain to process.
	 * @param boolean $dry
	 */
	protected function before_migrate_object( $dry = false ) {
		// If no data is left, we're done with this batch
		if ( empty( $this->data ) ) {
			$this->halt();
		}
	}

	/**
	 * Hook that runs after migrating a post.
	 * Increment and save the cursor.
	 */
	protected function after_migrate_object( $dry = false ) {
		$this->cursor++;
		if ( !$dry ) {
			$this->save_cursor();
		}
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