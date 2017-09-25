<?php
/**
 * Manages the plugin's singleton instances.
 *
 * @package TMSC
 */

namespace TMSC;

/**
 * Triat for creating a singleton.
 */
trait Singleton {

	/**
	 * Instances of child classes.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Initialization typically happens via get_instance() method.
	 */
	public function __construct() {}

	/**
	 * Return an instance of a child class.
	 *
	 * @return Object.
	 */
	public static function get_instance() {
		$class = get_called_class();
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new static();
			self::$instances[ $class ]->setup();
		}
		return self::$instances[ $class ];
	}

	/**
	 * Set up the class.
	 */
	protected function setup() {}
}
