<?php
namespace TMSC\Database\Systems\Freer_Sackler;
class Freer_Sackler_Object_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * The type of processor.
	 */
	public $processor_type = 'Object';

	/**
	 * Holds the URL of the current site being migrated
	 * @var string
	 */
	public $url;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
	}

	/**
	 * Generate our batch query.
	 */
	public function get_batch_query_stmt() {
		$stmt = 'SELECT *
			FROM AllWebmedia
			WHERE CuratorApproved = 0
			AND PrimaryDisplay=1';

		return $stmt;
	}

	/**
	 * Add in additional queries for the migration.
	 */
	public function prepare_additional_queries() {

	}

}
