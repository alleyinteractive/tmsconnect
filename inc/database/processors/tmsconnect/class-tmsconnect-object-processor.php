<?php
/**
 * The class used to process TMS Object Modules
 */
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Object_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * The type of processor.
	 */
	public $processor_type = 'Object';

	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Object';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_objects';

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
	public function get_object_query_stmt() {
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
