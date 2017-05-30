<?php
namespace TMSC\Database;

abstract class MySQL_Processor extends \TMSC\Database\Database_Processor {

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
		$port = ( empty( $port ) ) ? '3306' : $port;
		$dsn = "mysql:host={$host};port={$port};dbname={$this->dbname}";

		$connection = new \PDO( $dsn, $this->username, $this->password, array(
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		) );

		// Since MySQL supports it, don't automatically quote parameters to allow control over data types.
		// This comes in useful for automatically manipulating the LIMIT clause.
		$connection->setAttribute( \PDO::ATTR_EMULATE_PREPARES, false );

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
		$wpdb->query( 'SET autocommit = 0;' );
	}

	/**
	 * Commit all items in stmt queue.
	 */
	public function commit() {
		global $wpdb;
		$wpdb->query( 'COMMIT;' );
		$wpdb->query( 'SET autocommit = 1;' );
	}
}
class_alias( '\TMSC\Database\MySQL_Processor', '\TMSC\Database\System_Processor' );
