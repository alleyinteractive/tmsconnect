<?php
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Taxonomy_Processor extends \TMSC\Database\TMSC_Processor {

	/**
	 * The type of processor.
	 */
	public $processor_type = 'Taxonomy';

	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Taxonomy';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_taxonomies';

	/**
	 * An array of all taxonomies that need to be migrated.
	 * @var array
	 */
	public $taxonomies = array();

	/**
	 * Current taxonomy being migrated.
	 * @var object
	 */
	public $current_tax;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
		$this->taxonomies = $this->get_migratable_taxonomies();
	}

	/**
	 * Run our import in batches by taxonomy.
	 * @return void
	 */
	public function run() {
		foreach ( $this->taxonomies as $taxonomy ) {
			$this->current_tax = $taxonomy;
			parent::run();
		}
	}

	/**
	 * Generate our objects we are migrating.
	 * Ensure that these objects are ordered by CN and return the columns.
	 */
	public function get_object_query_stmt() {
		return apply_filters( "tmsc_{$this->processor_type}_stmt_query", "SELECT DISTINCT
			Terms.TermID,
			Terms.Term,
			TermMaster.CN,
			TermMaster.Children,
			'{$this->current_tax->taxonomy}' as taxonomy
		FROM Terms
		INNER JOIN TermMaster on Terms.TermMasterID = TermMaster.TermMasterID
		INNER JOIN TermTypes on Terms.TermTypeID = TermTypes.TermTypeID
		WHERE Terms.TermTypeID = 1
		AND TermMaster.CN LIKE '{$this->current_tax->CN}.%'
		ORDER BY TermMaster.CN", $this );
	}

	/**
	 * Get the taxonomies that we will be migrating.
	 */
	public function get_migratable_taxonomies() {
		$guide_terms = get_option( 'tmsc_guide_terms', array() );
		$cns = array();
		if ( ! empty( $guide_terms ) && ! empty( $guide_terms['term']['data'] ) ) {
			foreach ( $guide_terms['term']['data'] as $taxonomies ) {
				if ( ! empty( $taxonomies['taxonomy_map'] ) && ! empty( $taxonomies['CN'] ) ) {
					// Make sure we don't have a custom processor for the guide term.
					if ( file_exists( trailingslashit( TMSC_SYSTEM_PATH ) . TMSC_SYSTEM_BUILD_FILE_PREFIX . '/class-' . TMSC_SYSTEM_BUILD_FILE_PREFIX . '-' . $taxonomies['taxonomy_map'] . '-processor.php' ) ) {

					} else {
						$cns[ $taxonomies['CN'] ] = $taxonomies['taxonomy_map'];
					}
				}
			}

			$guide_terms_stmt = "'" . implode( "','", array_keys( $cns ) ) . "'";

			$stmt = "SELECT DISTINCT
				Terms.TermID,
				Terms.Term,
				TermMaster.CN
				FROM Terms
				INNER JOIN TermMaster on Terms.TermMasterID = TermMaster.TermMasterID
				INNER JOIN TermTypes on Terms.TermTypeID = TermTypes.TermTypeID
				WHERE TermMaster.CN IN ( $guide_terms_stmt )
				AND Children = 1
				AND (TermMaster.GuideTerm = 1)
				ORDER BY TermMaster.CN";
			$this->prepare( $this->object_query_key, $stmt );
			$query = $this->query( $this->object_query_key );
			$results = $query->fetchAll();

			// Set the guide term as the top level taxonomy so that our results know the proper WP taxonomy.
			foreach ( $results as $index => $result ) {
				$results[ $index ]->taxonomy = $cns[ $result->CN ];
			}
			return $results;
		}
		return;
	}
}
