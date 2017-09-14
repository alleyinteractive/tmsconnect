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
		add_filter( 'tmsc_get_child_processors', array( $this, 'set_child_processors' ) );
	}

	/**
	 * This is a child processor so register it as such.
	 * @param array $child_processors.
	 * @return array.
	 */
	public function set_child_processors( $child_processors ) {
		$child_processors[] = $this->processor_type;
		return $child_processors;
	}

	/**
	 * Run our import in batches by taxonomy.
	 * @return void
	 */
	public function run() {
		parent::run();
	}

	/**
	 * make sure we know the parent object of this migratable media.
	 */
	public function set_parent_object( $object ) {
		$this->parent_object = $object;
	}

	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", '', $this->parent_object );
	}
}
