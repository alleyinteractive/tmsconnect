<?php

namespace TMSC\Database;

/**
 * Base class for mapping system data to our migratables.
 */
abstract class TMSC_Data_Map {
	/**
	 * The type of processor.
	 * @var string.
	 */
	public $processor_type = '';

	/**
	 * Object meta data mapping
	 * @var array
	 */
	public $data_map = array();

	/**
	 * Constructor. Set the system data maps.
	 */
	public function __construct( $type ) {
		$this->processor_type = $type;
		$this->data_map = $this->get_mapping();
	}

	/**
	 * Get data mapping.
	 * @param string. $type. Processor type.
	 * @return associative array of slugs to system IDs, like array( 'classifcation' => 'AABBCCCXXX', 'period' => 'AABBDDDXXY' )
	 */
	abstract public function get_mapping();
}
