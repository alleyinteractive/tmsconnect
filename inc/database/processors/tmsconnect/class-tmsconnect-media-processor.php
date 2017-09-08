<?php
/**
 * The class used to process TMS Media Attachments
 */
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Media_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Media';

	/**
	 * The key used for the current attachment query
	 * @var string
	 */
	public $object_query_key = 'tms_attachments';

	/**
	 * The parent object for this set of media attachments.
	 */
	public $parent_object;

	/**
	 * Current object raw data.
	 * @var object
	 */
	private $current_object = null;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
	}

	/**
	 * Run our import in batches by taxonomy.
	 * @return void
	 */
	public function run() {
		while ( $this->offset < $this->total_objects ) {
			foreach ( $this->current_batch as $object ) {
				$this->current_object = $object;
				parent::run();
			}
			$this->offset = $this->offset + $this->batch_size;
		}
	}

	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", '', $this->current_object );
	}
}
