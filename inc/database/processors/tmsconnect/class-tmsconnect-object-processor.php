<?php
/**
 * The class used to process TMS Object Modules
 */
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Object_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * The type of processor.
	 */
	public $processor_type = 'Object';

	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Object';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_objects';

	/**
	 * The number of web visible TMS objects to migrate.
	 */
	public $total_objects = 0;

	/**
	 * The number of web visible TMS objects to migrate in a batch.
	 */
	public $batch_size = 0;

	/**
	 * The starting point of our batch.
	 */
	public $offset = 0;

	/**
	 * Current raw data of our batch objects.
	 * @var array
	 */
	private $current_batch = array();

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
		$this->batch_size = apply_filters( 'tmsc_object_sync_batch_size', 500 );
		$this->total_objects = $this->get_num_objects();
	}

	/**
	 * Run our import in batches by taxonomy.
	 * @return void
	 */
	public function run() {
		while ( $this->offset < $this->total_objects ) {
			$this->current_batch = $this->get_migratable_objects();
			foreach ( $this->current_batch as $object ) {
				$this->current_object = $object;
				parent::run();
			}
			$this->offset = $this->offset + $this->batch_size;
		}
	}

	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", "SELECT DISTINCT Objects.ObjectID,
				Objects.SortNumber,
				Objects.ObjectName,
				Objects.ObjectNumber,
				Objects.CuratorApproved,
				Objects.Dated,
				Objects.Dimensions,
				Objects.Medium,
				Objects.CreditLine
			FROM Objects
			WHERE Objects.ObjectID = {$this->current_object->ObjectID}", $this );
	}

	/**
	 * Generate a batch of objects to migrate.
	 * Ensure that these objects are ordered by ObjectID and return the columns.
	 */
	public function get_migratable_objects() {
		$stmt = 'SELECT ObjectID, Title, Rank, MediaMasterID, RenditionNumber, FileName
			FROM AllWebMedia
			WHERE CuratorApproved = 0
			AND PrimaryDisplay=1
			ORDER BY ObjectID';

		// DB systems use different syntax for offsets and limits.
		$stmt = $this->set_offset_sql( $stmt );
		$this->set_object_query( $stmt );
		$this->prepare( $this->object_query_key, $stmt );
		$params = array(
			':offset' => $this->offset,
			':size' => $this->batch_size,
		);

		$query = $this->query( $this->object_query_key, $params );
		return $query->fetchAll();
	}

	/**
	 * Get the total number of TMS objects that we will be migrating.
	 */
	public function get_num_objects() {
		$stmt = 'SELECT count(ObjectID) as total
			FROM AllWebMedia
			WHERE CuratorApproved = 0
			AND PrimaryDisplay=1';
		$this->prepare( $this->object_query_key, $stmt );
		$query = $this->query( $this->object_query_key );
		$results = $query->fetchAll();

		return reset( $results )->total;
	}
}
