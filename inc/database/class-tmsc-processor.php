<?php
namespace TMSC\Database;
abstract class TMSC_Processor extends \TMSC\Database\MySQL_Processor {

	/**
	 * The type of processor.
	 */
	public $processor_type = '';

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

		$stmt = $this->get_batch_query_stmt();
		// Set the batch query to get the next result set, required by MySQLProcessor
		$this->set_batch_query( $stmt );

		// Set additional queries to use to get taxonomy terms
		$this->prepare_additional_queries();
	}

	/**
	 * Generate our batch query.
	 */
	abstract public function get_batch_query_stmt();

	/**
	 * Generate our batch query.
	 */
	abstract public function prepare_additional_queries();

	/**
	 * Clean crashed posts
	 */
	protected function before_run( $params = array() ) {
		tmsc_clean_crashed_posts();
		parent::before_run( $params );
	}

	/**
	 * Load the next migrateable object
	 * @param array $params
	 */
	public function load_migrateable() {
		if ( ! empty( $this->data ) ) {
			$class = '\\TMSC\\Database\\Systems\\' . TMSC_SYSTEM_BUILD_CLASS_PREFIX . '\\' . TMSC_SYSTEM_BUILD_CLASS_PREFIX . '_' . $this->processor_type;
			$this->migrateable = new $class( array_shift( $this->data ), $this );
		} else {
			$this->migrateable = null;
		}
	}
}
