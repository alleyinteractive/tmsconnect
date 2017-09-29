<?php
/**
 * The class used to process TMS Constituents Modules
 */
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Constituent_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Constituent';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_constituents';

	public $types;

	public $roles;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
		$this->types = $this->get_constituent_types();
		$this->roles = $this->get_constituent_roles();
	}

	/**
	 * Get the constituent types that we will be migrating.
	 */
	public function get_constituent_types() {
		$query_key = $this->object_query_key . '_all';
		$stmt = apply_filters( "tmsc_{$this->processor_type}_batch_stmt_query", '', $this );
		if ( ! empty( $stmt ) ) {
			if ( ! empty( $stmt ) ) {
			return $this->fetch_results( $stmt, $query_key );
		}
	}

	/**
	 * Get the constituent roles that we will be migrating.
	 */
	public function get_constituent_roles() {
		$query_key = $this->object_query_key . '_all';
		$stmt = apply_filters( "tmsc_{$this->processor_type}_roles_stmt_query", '', $this );
		if ( ! empty( $stmt ) ) {
			if ( ! empty( $stmt ) ) {
			return $this->fetch_results( $stmt, $query_key );
		}
	}

}
