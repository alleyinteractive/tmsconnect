<?php
namespace TMSC\Database\Systems\Freer_Sackler;
class Freer_Sackler_Taxonomy_Processor extends \TMSC\Database\MySQL_Processor {
	/**
	 * Holds the URL of the current site being migrated
	 * @var string
	 */
	public $url;

	/**
	 * Constructor
	 * @param string $url
	 */
	public function __construct() {
		parent::__construct();

		// Set the batch query to get the next result set, required by MySQLProcessor
		$this->set_batch_query( 'SELECT * FROM XXX' );

		// Set additional queries to use to get taxonomy terms
		$this->prepare( 'content_categories', 'SELECT * FROM XXX where id=:id' );
	}

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
			$this->migrateable = new \TMSC\Database\Systems\Freer_Sackler\Freer_Sackler_Object( array_shift( $this->data ), $this );
		} else {
			$this->migrateable = null;
		}
	}
}
