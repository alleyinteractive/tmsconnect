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
	 * The current migrateable
	 * @var array
	 */
	public $migrateable;


	private $stop_processing = false;

	/**
	 * Number of objects to process at a time.
	 */
	public $batch_size = 200;

	/**
	 * Constructor
	 */
	public function __construct( $type ) {
		$this->processor_type = strtolower( $type );
		$this->batch_size = apply_filters( 'tmsc_sync_batch_size', $this->batch_size );
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
	public function load_migrateable() {

	}

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
			}
		}
		$this->after_run();

		// Only enable term and comment counting if this object does
		// not have a parent (i.e. if this object IS a parent).
		if ( empty( $this->parent_object ) ) {
			wp_defer_term_counting( false );
			wp_defer_comment_counting( false );
		}
		wp_cache_flush();
	}

	/**
	 * Hook that runs after migrating each post; can use to save state or increment the cursor
	 */
	abstract protected function after_migrate_object();

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
	 * Allow for bulk stmt execution.
	 */
	abstract protected function disable_autocommit();

	/**
	 * Commit all items in stmt queue.
	 */
	abstract protected function commit();
}
