<?php
namespace TMSC\Database;
abstract class Database_Processor extends \TMSC\Database\Processor {
	protected $pdo;
	protected $queries = array();
	abstract protected function get_connection();
	public function __construct( $type ) {
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
}
