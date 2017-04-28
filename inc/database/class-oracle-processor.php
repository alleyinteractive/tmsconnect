<?php
namespace TMSC\Database;

abstract class Oracle_Processor extends \TMSC\Database\Database_Processor {
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
	protected function get_connection() {
		// Return the existing connection if set
		if ( ! empty( $this->pdo ) ) {
			return $this->pdo;
		}

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
}
class_alias( '\TMSC\Database\Oracle_Processor', '\TMSC\Database\System_Processor' );
