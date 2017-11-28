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
	public $parent_object = 0;

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
		if ( ! empty( $this->parent_object ) ) {
			parent::run();
		}
	}

	/**
	 * make sure we know the parent object of this migratable media.
	 */
	public function set_parent_object( $object ) {
		$this->parent_object = $object;
	}

	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", '', $this->parent_object, $this );
	}

	/**
	 * Get the related WP terms of a given TMS Media.
	 * @param int $object_id. TMS raw Media data.
	 * @return array. An associate array of taxonmies and it's term ids. array( 'taxonomy-slug' => array( 1, 2... ) ).
	 */
	public function get_related_terms( $object_id ) {
		$query_key = $this->object_query_key . '_terms';
		$stmt = apply_filters( "tmsc_{$this->processor_type}_related_terms_stmt_query", '', $object_id );
		if ( ! empty( $stmt ) ) {
			return $this->fetch_results( $stmt, $query_key );
		}
		return array();
	}
}
