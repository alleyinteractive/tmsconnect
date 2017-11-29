<?php

namespace TMSC\Database;

/**
 * Base class for any generically imported taxonomy
 */
class TMSC_Zone extends \TMSC\Database\Migrateable {

	/**
	 * The type of migrateable object. Must be set by all implementing classes.
	 * @var string
	 */
	public $type = 'term';

	/**
	 * Constructor. Set this as a term migrateable.
	 */
	public function __construct() {
		parent::__construct( $this->type );
	}

	/**
	 * Get legacy ID
	 * @return int
	 *
	 */
	public function get_legacy_id() {
		if ( ! empty( $this->raw->ID ) ) {
			return $this->raw->ID;
		}
		return false;
	}

	public function load_existing() {
		$this->object = null;
		if ( ! empty( $this->get_legacy_id() ) ) {
			$this->object = tmsc_get_object_by_legacy_id( $this->get_legacy_id(), $this->processor->zone_post_type );
		}
	}

	/**
	 * Save this term
	 * @return boolean true if successfully saved
	 */
	public function save() {
		$this->load_existing();

		if ( ! empty( $this->object->ID ) ) {
			if ( z_get_zoninator()->zone_exists( $this->processor->current_zone_slug ) ) {
				$zone_id = z_get_zoninator()->get_zone_id( $this->processor->current_zone_slug );
				if ( ! empty( $zone_id ) && ! is_wp_error( $zone_id ) ) {
					z_get_zoninator()->add_zone_posts( $zone_id, array( $this->object->ID ), true );
					$zone = z_get_zone( $zone_id );
					// If the Zone description is incorrect, update it. This should only happen on the first object added to a zone.
					if ( $this->raw->Description !== $zone->description ) {
						z_get_zoninator()->update_zone( $zone_id, array(
							'description' => $this->raw->Description,
						) );
					}
					// We don't really have an object here. So wipe it.
					$this->object = null;
					return true;
				}
			}
		}
		return false;
	}
}
