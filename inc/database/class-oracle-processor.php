<?php
namespace TMSC\Database;

class Oracle_Processor extends \TMSC\Database\Database_Processor {
	/**
	 * Constructor
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
	}

	/**
	 * Get the database connection
	 * @return \PDO database connection instance
	 */
	public function get_connection() {
		// Build the DSN string
		$host = $this->host;
		list( $host, $port ) = explode( ':', $this->host );
		$port = ( empty( $port ) ) ? '1433' : $port;
		$dsn = "oci:host={$host};port={$port};dbname={$this->dbname}";

		$connection = new \PDO( $dsn, $this->username, $this->password );

		// Uncomment to enable connection debugging.
		$connection->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		return $connection;
	}

	/**
	 * Allow for bulk stmt execution.
	 */
	public function disable_autocommit() {
		// Let's bulk insert.
		global $wpdb;
	}

	/**
	 * Commit all items in stmt queue.
	 */
	public function commit() {
		global $wpdb;
	}
}
class_alias( '\TMSC\Database\Oracle_Processor', '\TMSC\Database\System_Processor' );
