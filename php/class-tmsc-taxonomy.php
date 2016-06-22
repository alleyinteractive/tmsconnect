<?php
/**
 * Abstract class for all other taxonomy classes
 */

abstract class TMSC_Taxonomy {

	/**
	 * Name of the taxonomy
	 *
	 * @var string
	 * @access public
	 */
	public $name = null;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		// Create the taxonomy
		add_action( 'init', array( $this, 'create_taxonomy' ) );

		// Add custom meta boxes
		add_action( 'fm_term_' . $this->name, array( $this, 'custom_term_fields' ) );
	}

	/**
	 * Create the taxonomy.
	 *
	 * @access public
	 */
	abstract public function create_taxonomy();

	/**
	 * Add custom meta boxes.
	 * Each class should implement if it has meta box definitions.
	 *
	 * @access public
	 */
	public function custom_term_fields() {

	}
}
