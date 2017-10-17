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
			$this->object = tmsc_get_object_by_legacy_id( $this->get_legacy_id(), 'tms_object' );
		}
	}

	/**
	 * Save this term
	 * @return boolean true if successfully saved
	 */
	public function save() {
		if ( $this->before_save() ) {
			return;
		}
		$this->load_existing();
		if ( ! empty( $this->object->ID ) ) {
			global $zoninator;
			$zoninator->add_zone_posts( $this->processor->current_zone, $this->object->ID, true );

			$this->after_save();

			return true;
		}
		return false;
	}
}
