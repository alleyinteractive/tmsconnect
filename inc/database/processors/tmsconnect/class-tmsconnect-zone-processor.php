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
	 * Current zone slug to populate.
	 * @var object
	 */
	public $current_zone_slug = '';

	/**
	 * Current zone type. Will default to current zone slug.
	 */
	public $current_zone_type = '';

	/**
	 * Current taxonomy being migrated.
	 * @var array|string.
	 */
	public $zone_post_type =  array();

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
	}

	/**
	 * Run our import in batches by taxonomy.
	 * @return void
	 */
	public function run() {
		$this->zones = apply_filters( 'tmsc_curated_zones', array() );

		$cursor = $this->get_zone_cursor();

		$this->current_zone_slug = '';
		$this->current_zone_type = '';

		foreach ( $this->zones as $zone_slug => $config ) {
			if ( ! in_array( $zone_slug, $cursor['migrated'], true ) ) {
				$this->current_zone_slug = $zone_slug;
				$this->current_zone_type = ( ! empty( $config['zone_type'] ) ) ? $config['zone_type'] : $zone_slug;

				$this->zone_post_type = ( ! empty( $config['post_type'] ) ) ? $config['post_type'] : 'tms_object';
				break;
			}
		}

		// If we have migrated all zones, set it as completed.
		if ( ! empty( $this->current_zone_slug ) ) {
			// Wipe our zone posts before we update.
			if ( ! in_array( $this->current_zone_slug, $cursor['migrated'], true ) && 0 === $cursor['offset'] ) {
				global $zoninator;
				$zoninator->remove_zone_posts( $this->current_zone_slug, null );
			}

			if ( ! empty( $this->get_object_query_stmt() ) ) {
				parent::run();
			} else {
				if ( empty( $cursor['migrated'] ) ) {
					$cursor['migrated'] = array();
				}
				$cursor['migrated'][] = $this->current_zone_slug;
				$cursor['complete'] = false;
				$cursor['offset'] = 0;
				update_option( "tmsc-cursor-{$this->processor_type}", $cursor, false );
				wp_cache_delete( "tmsc-cursor-{$this->processor_type}", 'options' );
			}
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
		return apply_filters( "tmsc_{$this->processor_type}_{$this->current_zone_type}_stmt_query", '', $this->current_zone_slug, $this );
	}

	/**
	 * Prepare our statement with the current taxonomy.
	 */
	protected function before_run( $params = array() ) {
		parent::before_run( $params );
		$cursor = $this->get_zone_cursor();

		if ( $cursor['completed'] && ! empty( $this->current_zone_slug ) ) {
			$cursor['migrated'][] = $this->current_zone_slug;
			$cursor['offset'] = 0;
			// Set the global completed to false and allow it to get set in the run function.
			$cursor['completed'] = false;
			update_option( "tmsc-cursor-{$this->processor_type}", $cursor, true );
			wp_cache_delete( "tmsc-cursor-{$this->processor_type}", 'options' );
		}
	}
}
