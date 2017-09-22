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

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
		$this->types = $this->get_constituent_types();
		$this->individual_types = $this->get_individual_types();
	}

	/**
	 * Get the constituent types that we will be migrating.
	 */
	public function get_constituent_types() {
		$types = array();

		$stmt = '';
		if ( ! empty( $stmt ) ) {
			$this->prepare( $this->object_query_key, $stmt );
			$query = $this->query( $this->object_query_key );
			$results = $query->fetchAll();

			// Set the guide term as the top level taxonomy so that our results know the proper WP taxonomy.
			foreach ( $results as $index => $result ) {
				$results[ $index ]->taxonomy = $cns[ $result->CN ];
			}
			return $results;
		}
	}

	/**
	 * Get the individual constituent types that we will be migrating.
	 */
	public function get_individual_types() {
		$types = array();

		$stmt = '';
		if ( ! empty( $stmt ) ) {
			$this->prepare( $this->object_query_key, $stmt );
			$query = $this->query( $this->object_query_key );
			$results = $query->fetchAll();

			// Set the guide term as the top level taxonomy so that our results know the proper WP taxonomy.
			foreach ( $results as $index => $result ) {
				$results[ $index ]->taxonomy = $cns[ $result->CN ];
			}
			return $results;
		}
	}

}
