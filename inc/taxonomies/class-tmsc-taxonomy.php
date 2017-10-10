<?php

/**
 * Abstract class for taxonomy classes
 */
abstract class Tmsc_Taxonomy {

	/**
	 * Name of the taxonomy
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * Object types for this taxonomy
	 *
	 * @var array
	 */
	public $object_types = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Create the taxonomy
		add_action( 'init', array( $this, 'create_taxonomy' ) );
		$this->object_types = apply_filters( 'tmsc_register_taxonomy_object_types', $this->object_types, $this->name );
	}

	/**
	 * Create the taxonomy.
	 */
	public function create_taxonomy() {
		$args = apply_filters( 'tmsc_register_taxonomy_args', $this->register_taxonomy_args(), $this->name, $this->object_types );
		register_taxonomy( $this->name, $this->object_types, $args );
	}

	/**
	 * Args passed to register taxonomy.
	 * Allows for a filter.
	 * @return array.
	 */
	abstract function register_taxonomy_args();

}
