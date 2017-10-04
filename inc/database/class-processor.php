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
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = '';

	/**
	 * The current batch of results for migration
	 * @var array
	 */
	protected $data = array();

	/**
	 * The current migratable
	 * @var array
	 */
	public $migratable;


	private $stop_processing = false;

	/**
	 * Number of objects to process at a time.
	 */
	public $batch_size = 200;

	/**
	 * Number of objects processed.
	 */
	public $processed_count = 0;

	/**
	 * Holds the URL of the current site being migrated
	 * @var string
	 */
	public $url;

	/**
	 * Constructor
	 */
	public function __construct( $type ) {
		$this->processor_type = strtolower( $type );
	}

	/**
	 * Import an object (post, comment, or user) as part of a batch
	 */
	public function migrate_object() {
		if ( $this->migrateable->save() ) {
			$this->mark_object_as_processed( $this->migrateable );
		}
	}

	/**
	 * Add some metadata with legacy information
	 */
	public function mark_object_as_processed( $saved_migrateable ) {
		$object = $saved_migrateable->get_object();

		if ( ! empty( $object ) ) {
			if ( ! empty( $saved_migrateable->get_legacy_cn() ) ) {
				$saved_migrateable->update_meta( 'tmsc_legacy_CN', $saved_migrateable->get_legacy_cn() );
			}
			$saved_migrateable->update_meta( 'tmsc_legacy_id', $saved_migrateable->get_legacy_id() );
			$saved_migrateable->update_meta( 'tmsc_migration_time', time() );
		}
	}

	/**
	 * Get next Migrateable object
	 */
	abstract public function load_migrateable();

	/**
	 * Hook that runs before migrating each post; can use to reload state
	 * @param boolean $dry
	 */
	abstract protected function before_migrate_object();


	/**
	 * Run the import
	 */
	public function run() {
		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );

		$this->before_run();
		$this->load_migrateable();
		if ( ! empty( $this->migrateable ) ) {
			$this->migrateable->set_processor( $this );

			foreach ( $this->data as $object ) {
				$this->migrateable->set_data( $object );
				$this->before_migrate_object();
				$this->migrate_object();
				$this->after_migrate_object();
				$this->disable_autocommit();
				$this->migrateable->flush_stmt_queue();
				$this->commit();
				$this->finish();
			}
		}
		$this->after_run();

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );
		wp_cache_flush();
	}

	/**
	 * Hook that runs after migrating each post; can use to save state or increment the cursor
	 */
	abstract protected function after_migrate_object();

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
	}

	/**
	 * Is the migration complete?
	 */
	public function is_finished() {
		$done = $this->get_option( 'done' );
		return ! empty( $done );
	}

	/**
	 * Allow for bulk stmt execution.
	 */
	abstract protected function disable_autocommit();

	/**
	 * Commit all items in stmt queue.
	 */
	abstract protected function commit();
}
