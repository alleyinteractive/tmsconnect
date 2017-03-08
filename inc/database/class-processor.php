<?php

namespace TMSC\Database;

/**
 * Base class for the tool which gets the next piece of content.
 */
abstract class Processor {
	/**
	 * The type of processor.
	 * @var string.
	 */
	public $processor_type = '';

	/**
	 * The data map of this processor.
	 * @var data map object.
	 */
	public $data_map = null;

	private $stop_processing = false;

	protected $migrateable = null;

	protected $batch_size = 100;

	protected $skip_migrateable = false;

	protected $cursor = array();

	protected $conditions = array();

	protected $mapping_only = false;

	protected $dry_run = false;

	/**
	 * Constructor
	 */
	public function __construct( $type ) {
		$this->processor_type = $type;
		$this->get_data_map();
	}

	public function __toString() {
		return static::NAME;
	}

	public function skip() {
		return $this->skip_migrateable = true;
	}

	/**
	 * Import an object (post, comment, or user) as part of a batch
	 */
	public function migrate_object() {
		$this->load_migrateable();
		if ( ! $this->matches_conditions() ) {
			return $this->skip();
		} else if ( empty( $this->migrateable ) ) {
			return;
		} else if ( $this->skip_migrateable ) {
			return $this->skip();
		} else { // Do the migration
			$this->migrateable->set_processor( $this );
			$this->check_legacy_url();

			// The main event
			if ( $this->mapping_only ) {
				$this->migrateable->mapping_only_save();
			} elseif ( $this->dry_run ) {
				$this->migrateable->dry_run_save();
			} elseif ( $this->migrateable->save() ) {
				$this->mark_object_as_processed( $this->migrateable );
			}
		}
	}

	/**
	 * Validate the legacy URL but allow for some migrateable types to not have legacy URLs
	 */
	protected function check_legacy_url() {
		$url = $this->migrateable->get_legacy_url();
		if ( ! empty( $url ) && ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new \Exception( 'Legacy URL ' . $url . ' is not valid' );
		}
	}

	/**
	 * Add some metadata with legacy information
	 */
	public function mark_object_as_processed( $saved_migrateable ) {
		$object = $saved_migrateable->get_object();
		if ( ! empty( $object->ID ) ) {
			$saved_migrateable->update_meta( 'tmsc_source', static::NAME );
			$saved_migrateable->update_meta( 'tmsc_legacy_url', $saved_migrateable->get_legacy_url() );
			$saved_migrateable->update_meta( 'tmsc_legacy_id', $saved_migrateable->get_legacy_id() );
			$saved_migrateable->update_meta( 'tmsc_migration_time', time() );
			$saved_migrateable->update_meta( 'tmsc_cursor_position', $this->cursor );
			$saved_migrateable->save_final_object_status();
		}

		// same for children
		foreach ( $saved_migrateable->get_children() as $child ) {
			$this->mark_object_as_processed( $child );
		}
	}

	public function get_migrated_object_ids() {
		global $wpdb;
		$name = static::NAME;
		return $wpdb->get_col(
			"SELECT post_id FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE meta_key = 'tmsc_source'
				AND meta_value = '$name'
				AND p.post_type != 'attachment'"
		);
	}

	/**
	 * Get the cursor state representing the beginning of a migration
	 * @return mixed
	 */
	abstract public function get_starting_cursor();

	/**
	 * Set all pointers back to the start.
	 */
	public function rewind() {
		$this->set_option( 'done', false );
		$this->cursor = $this->get_starting_cursor();
		$this->save_cursor();
	}

	/**
	 * Get next Migrateable object
	 */
	abstract public function load_migrateable();

	/**
	 * Hook that runs before migrating each post; can use to reload state
	 * @param boolean $dry
	 */
	abstract protected function before_migrate_object( $dry = false );

	/**
	 * Get the current processor data map.
	 */
	public function get_data_map() {
		$class = '\\TMSC\\Database\\Systems\\' . TMSC_SYSTEM_BUILD_CLASS_PREFIX . '\\' . TMSC_SYSTEM_BUILD_CLASS_PREFIX . '_' . $this->processor_type . '_Data_Map';
		$this->data_map = new $class( $this->processor_type );
	}

	/**
	 * Run the import
	 */
	public function run() {

		wp_defer_term_counting( true );

		if ( empty( $this->processor_type ) ) {
			throw new \Exception( 'NAME must be defined as a const in the top-level processor class.' );
		}

		$this->before_run();

		for ( $i = 0; false === $this->batch_size || $i < $this->batch_size; $i++ ) {
			$this->skip_migrateable = false;
			$this->before_migrate_object();
			if ( $this->stop_processing ) {
				break;
			}
			if ( ! $this->skip_migrateable ) {
				$this->migrate_object();
				if ( $this->stop_processing ) {
					break;
				}
				$this->after_migrate_object();
				if ( $this->stop_processing ) {
					break;
				}
			}
		}

		$this->after_run();
	}

	/**
	 * Hook that runs after migrating each post; can use to save state or increment the cursor
	 */
	protected function after_migrate_object( $dry = false ) {
		if ( ! $dry ) {
			$this->save_cursor();
		}
	}

	/**
	 * Remove all content migrated by this processor. Override this if you don't like how awesomely fast it is and
	 * would prefer to use a lame method like wp_delete_post().
	 */
	public function clean() {
		global $wpdb;
		$name = static::NAME;
		$ids = $wpdb->get_results(
			"SELECT post_id from {$wpdb->postmeta} WHERE meta_key = 'tmsc_source' AND meta_value = '$name'"
		);
		foreach ( $ids as $idobj ) {
			$id = $idobj->post_id;
			// blunt but fast.
			$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE ID = $id OR post_parent = $id" );
			$wpdb->query( "DELETE FROM {$wpdb->term_relationships} WHERE object_id = $id" );
			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id = $id" );
			$this->after_delete( $id );
		}
		$this->rewind();
	}

	/**
	 * Save cursor state
	 */
	protected function save_cursor() {
		$this->set_option( 'cursor', $this->cursor );
	}

	/**
	 * Start up the cursor.
	 */
	public function init_cursor() {
		$this->cursor = $this->get_option( 'cursor' );
		if ( ! $this->cursor ) {
			$this->rewind();
		}
		return $this->cursor;
	}

	public function set_cursor( $cursor ) {
		$this->cursor = $cursor;
		$this->save_cursor();
	}

	public function get_cursor() {
		return $this->get_option( 'cursor' );
	}

	public function parse_cursor( $cursor_str ) {
		return json_decode( $cursor_str, true );
	}

	public function set_batch_size( $batch_size ) {
		$this->batch_size = $batch_size;
	}

	/**
	 * Get migration option
	 */
	protected function set_option( $k, $v ) {
		update_option( 'tmsc_' . $this->processor_type . '_' . $k, $v );
	}

	/**
	 * Set migration option
	 */
	protected function get_option( $k ) {
		return get_option( 'tmsc_' . $this->processor_type . '_' . $k );
	}

	/**
	 * Hook that runs before the batch starts; could use to reload state
	 */
	protected function before_run() {

	}

	/**
	 * Hook that runs when the batch ends; can could to save state
	 */
	protected function after_run() {

	}

	/**
	 * Hook that runs after a post is deleted.
	 */
	protected function after_delete( $post_id ) {

	}

	/**
	 * Stop the migration
	 */
	protected function halt() {
		$this->stop_processing = true;
	}

	/**
	 * Complete the migration
	 */
	protected function finish() {
		$this->halt();
		$this->set_option( 'done', true );
	}

	/**
	 * Is the migration complete?
	 */
	public function is_finished() {
		$done = $this->get_option( 'done' );
		return ! empty( $done );
	}

	public function add_condition( $condition, $value ) {
		if ( method_exists( $this, 'condition_set_' . $condition ) ) {
			$value = call_user_func( array( $this, 'condition_set_' . $condition ), $value );
		}

		if ( null !== $value ) {
			$this->conditions[ $condition ] = $value;
		}
	}

	public function set_mapping_only( $mapping_only ) {
		$this->mapping_only = $mapping_only;
	}

	public function matches_conditions() {
		foreach ( $this->conditions as $condition => $value ) {
			$matches = call_user_func( array( $this, 'condition_match_' . $condition ), $value );
			if ( ! $matches ) {
				return false;
			}
		}
		return true;
	}

	public function condition_set_since( $value ) {
		$since = strtotime( $value );
		if ( empty( $since ) ) {
			return;
		} else {
			return $since;
		}
	}

	public function condition_set_before( $value ) {
		$before = strtotime( $value );
		if ( empty( $before ) ) {
			return;
		} else {
			return $before;
		}
	}

	public function condition_set_mapping_only( $value ) {
		if ( ! empty( $value ) ) {
			$this->set_mapping_only( true );
		}
		return null;
	}

	public function condition_match_before( $value ) {
		return $this->migrateable->get_pubdate() <= $value;
	}

	public function condition_match_since( $value ) {
		return $this->migrateable->get_pubdate() >= $value;
	}

	public function get_condition( $index ) {
		if ( isset( $this->conditions[ $index ] ) ) {
			return $this->conditions[ $index ];
		} else {
			return null;
		}
	}

	public function set_dry_run( $dry_run ) {
		$this->dry_run = $dry_run;
	}

}

function tmsc_flatten( $arr, $buffer = array() ) {
	foreach ( $arr as $val ) {
		if ( is_array( $val ) ) {
			$buffer = tmsc_flatten( $val, $buffer );
		} else {
			$buffer[] = $val;
		}
	}
	return $buffer;
};
