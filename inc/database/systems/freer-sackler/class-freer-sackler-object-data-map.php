<?php

namespace TMSC\Database\Systems\Freer_Sackler;

/**
 * Freer and Sacklers Data mapping for migratable objects.
 * Define system taxonomy & object meta data mappings here.
 */
class Freer_Sackler_Object_Data_Map extends \TMSC\Database\TMSC_Data_Map {

	/**
	 * Constructor
	 * @param string $url
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get taxonomy mappings
	 * @return associative array of taxonomy slugs to system IDs, like array( 'classifcation' => 'AABBCCCXXX', 'period' => 'AABBDDDXXY' )
	 */
	public function get_mapping() {

		return array();
	}
}
