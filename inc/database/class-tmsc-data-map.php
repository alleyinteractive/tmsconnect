<?php

namespace TMSC\Database;

/**
 * Base class for mapping system data to our migratables.
 */
abstract class TMSC_Data_Map {

	/**
	 * Object meta data mapping
	 * @var array
	 */
	public $meta_data = array();

	/**
	 * For mapping taxonomies to system specific values.
	 * @var array
	 */
	public $taxonomies = array();

	/**
	 * Constructor. Set the system data maps.
	 */
	public function __construct() {
		$this->taxonomies = $this->get_taxonomy_mapping();
		$this->meta_data = $this->get_meta_data_mapping();
	}

	/**
	 * Get taxonomy mappings
	 * @return associative array of taxonomy slugs to system IDs, like array( 'classifcation' => 'AABBCCCXXX', 'period' => 'AABBDDDXXY' )
	 */
	abstract public function get_taxonomy_mapping();

	/**
	 * Get object meta data mappings
	 * @return associative array of fieldmanager meta field slugs to system IDs, like array( 'references' => 'column1', 'web_resources' => 'column6' )
	 */
	abstract public function get_meta_data_mapping();
}
