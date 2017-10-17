<?php
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Zone_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Zone';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_zones';

	/**
	 * An array of all zones to be populated.
	 * @var array
	 */
	public $zones = array();

	/**
	 * Current taxonomy being migrated.
	 * @var object
	 */
	public $current_zone;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
		$this->zones = apply_filters( 'tmsc_curated_zones', array() );
	}

	/**
	 * Run our import in batches by taxonomy.
	 * @return void
	 */
	public function run() {
		$cursor = $this->get_zone_cursor();
		foreach ( $this->zones as $zone_slug => $config ) {
			if ( ! in_array( $zone_slug, $cursor['migrated'], true ) ) {
				$this->current_zone = $zone_slug;
				break;
			}
		}

		// If we have migrated all zones, set it as completed.
		if ( ! empty( $this->current_zone ) ) {
			// Wipe our zone posts before we update.
			if ( ! empty( $this->get_object_query_stmt() ) && ! in_array( $this->current_zone, $cursor['migrated'], true ) && 0 === $cursor['offset'] ) {
				global $zoninator;
				$zoninator->remove_zone_posts( $this->current_zone, null );
			}
			parent::run();
		} else {
			tmsc_update_cursor( $this->processor_type, $this->batch_size, true );
		}
	}

	/**
	 * Get our zone cursor.
	 */
	public function get_zone_cursor() {
		$cursor = tmsc_get_cursor( $this->processor_type );
		// If it is our first run through, then set the appropriate migrated cursor value.
		if ( empty( $cursor['migrated'] ) ) {
			$cursor['migrated'] = array();
			update_option( "tmsc-cursor-{$this->processor_type}", $cursor, false );
			wp_cache_delete( "tmsc-cursor-{$this->processor_type}", 'options' );
		}
		return $cursor;
	}

	/**
	 * Generate our objects we are migrating.
	 * Ensure that these objects are ordered by CN and return the columns.
	 */
	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_{$this->current_zone}_stmt_query", '', $this->current_zone, $this );
	}

	/**
	 * Prepare our statement with the current taxonomy.
	 */
	protected function before_run( $params = array() ) {
		parent::before_run( $params );
		$cursor = $this->get_zone_cursor();

		if ( $cursor['completed'] && ! empty( $this->current_zone ) ) {
			$cursor['migrated'][] = $this->current_zone;
			$cursor['offset'] = 0;
			// Set the global completed to false and allow it to get set in the run function.
			$cursor['completed'] = false;
			update_option( "tmsc-cursor-{$this->processor_type}", $cursor, true );
			wp_cache_delete( "tmsc-cursor-{$this->processor_type}", 'options' );
		}
	}
}
