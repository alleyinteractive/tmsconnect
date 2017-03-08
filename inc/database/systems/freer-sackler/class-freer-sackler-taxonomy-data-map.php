<?php

namespace TMSC\Database\Systems\Freer_Sackler;

/**
 * Freer and Sacklers Data mapping for migratable objects.
 * Define system taxonomy & object meta data mappings here.
 */
class Freer_Sackler_Taxonomy_Data_Map extends \TMSC\Database\TMSC_Data_Map {

	/**
	 * Constructor
	 * @param string $url
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
	}

	/**
	 * Get taxonomy mappings
	 * @return associative array of taxonomy slugs to system IDs, like array( 'classifcation' => 'AABBCCCXXX', 'period' => 'AABBDDDXXY' )
	 */
	public function get_mapping() {
		// We can simply return an array of mappings here, but F&S mappings are set in the WP-Admin UI.
		$guide_terms = get_option( 'tmsc_guide_terms', array() );
		return $guide_terms;
	}
}
