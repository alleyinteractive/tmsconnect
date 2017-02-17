<?php

namespace TMSC\Database\Systems\Freer_Sackler;

/**
 * Freer and Sacklers Data mapping for migratable objects.
 * Define system taxonomy & object meta data mappings here.
 */
class Feer_Sackler_Data_Map extends \TMSC\Database\TMSC_Data_Map {

	/**
	 * Get taxonomy mappings
	 * @return associative array of taxonomy slugs to system IDs, like array( 'classifcation' => 'AABBCCCXXX', 'period' => 'AABBDDDXXY' )
	 */
	public function get_taxonomy_mapping(){
		// We can simply return an array of mappings here, but F&S mappings are set in the WP-Admin UI.
	}

	/**
	 * Get object meta data mappings
	 * @return associative array of fieldmanager meta field slugs to system IDs, like array( 'references' => 'column1', 'web_resources' => 'column6' )
	 */
	public function get_meta_data_mapping(){
		$mapping = array();
		return $mapping;
	}
}
