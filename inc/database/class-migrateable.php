<?php

namespace TMSC\Database;

/**
 * Base class for any imported content source
 */
abstract class Migrateable {

	/**
	 * The current WordPress object data being migrated
	 * @var stdClass
	 */
	protected $object = null;

	/**
	 * Holds the current raw data being processed from the migration source
	 * @var stdClass
	 */
	protected $raw = null;

	/**
	 * URL of the site being migrated. Useful for many functions, such as legacy URL.
	 * @var string
	 */
	protected $url;

	/**
	 * The processor used for this migrateable
	 * @var Processor
	 */
	protected $processor = null;

	/**
	 * Queue of meta key/value pairs to save for the current object
	 * @var array
	 */
	private $meta_queue = array();

	/**
	 * Array of attachment migrateables created during save process
	 * @var array
	 */
	protected $children = array();

	/**
	 * The name of this migrateable object
	 * @var string
	 */
	public $name;

	/**
	 * The type of migrateable object. Must be set by all implementing classes.
	 * @var string
	 */
	public $type;

	/**
	 * Constructor. Set the current object to an empty class and define the type.
	 */
	public function __construct( $type ) {
		$this->type = $type;
		$this->object = new \stdClass;
	}

	/**
	 * Get legacy URL
	 * @return string
	 */
	abstract public function get_legacy_url();

	/**
	 * Get legacy ID - must be GUID
	 * @return string
	 */
	abstract public function get_legacy_id();

	/**
	 * This is a hook that gets fired before the post is saved. Return true if the current post should be skipped.
	 * @return boolean true if this post should be skipped
	 */
	protected function before_save() {
		// nothing.
	}

	/**
	 * This is a hook that gets fired after the post is saved, if it's saved successfully.
	 * This is the place to set post_meta
	 * @return void
	 */
	protected function after_save() {
		// nothing.
	}

	/**
	 * Get the current object
	 */
	public function get_object() {
		return $this->object;
	}

	/**
	 * Get the current URL of the object
	 */
	abstract public function get_url();

	/**
	 * Update the object (used in after_save usually)
	 */
	abstract public function update();

	/**
	 * Delete the object
	 */
	abstract public function delete();

	/**
	 * Save this object
	 */
	abstract public function save();

	/**
	 * Get the save override
	 * @return boolean
	 */
	public function save_override( &$post ) {
		return false;
	}

	/**
	 * Save the final object status
	 * @return string
	 */
	public function save_final_object_status() {}

	/**
	 * Proxy for the appropriate update meta function for this object
	 */
	public function update_meta( $k, $v, $prev_value = null ) {
		$func = 'update_' . $this->type . '_meta';
		if ( function_exists( $func ) ) {
			$this->meta_queue[] = array( $func, $k, $v, $prev_value );
			$this->flush_meta_queue();
		}
	}

	/**
	 * Proxy for the appropriate add meta function for this object
	 */
	public function add_meta( $k, $v, $unique = false ) {
		$func = 'add_' . $this->type . '_meta';
		if ( function_exists( $func ) ) {
			$this->meta_queue[] = array( $func, $k, $v, $unique );
			$this->flush_meta_queue();
		}
	}

	/**
	 * Proxy for the appropriate get meta function for this object
	 */
	public function get_meta( $k, $single = true ) {
		return get_metadata( $this->type, $this->object->ID, $k, $single );
	}

	/**
	 * Process any queued meta actions. Allows us to set meta keys before the post is saved.
	 */
	protected function flush_meta_queue() {
		if ( empty( $this->object->ID ) ) {
			return;
		}
		while ( $meta_entry = array_pop( $this->meta_queue ) ) {
			$func = array_shift( $meta_entry );
			array_unshift( $meta_entry, $this->object->ID );
			call_user_func_array( $func, $meta_entry );
		}
	}

	/**
	 * Get the children of this object
	 * @return array
	 */
	public function get_children() {
		return $this->children;
	}

	/**
	 * Check if this object is new based on if the ID is set
	 * @return boolean
	 */
	public function is_new() {
		return empty( $this->object->ID ) || '1' == $this->get_meta( 'tmsc_stub' );
	}

	/**
	 * Set processor
	 * @param $processor
	 */
	public function set_processor( $processor ) {
		$this->processor = $processor;
	}

	/**
	 * Set our raw data.
	 * @param $raw
	 */
	public function set_data( $raw ) {
		$this->raw = $raw;
	}
}
