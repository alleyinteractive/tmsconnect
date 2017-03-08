<?php
namespace TMSC\Database\Systems\Freer_Sackler;
class Freer_Sackler_Taxonomy_Processor extends \TMSC\Database\TMSC_Processor {

	/**
	 * The type of processor.
	 */
	public $processor_type = 'Taxonomy';

	/**
	 * Holds the URL of the current site being migrated
	 * @var string
	 */
	public $url;

	/**
	 * Constructor
	 * @param string $type
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
	}

	/**
	 * Generate our batch query.
	 */
	public function get_batch_query_stmt() {
		$guide_terms = $this->data_map->get_mapping();
		$cns = wp_list_pluck( $guide_terms['term']['data'], 'CN' );

		$guide_terms_stmt = "'" . implode( "','", $cns ) . "'";

		$stmt = "SELECT DISTINCT
    		Terms.TermID,
    		Terms.Term,
    		TermMaster.CN,
   			TermMaster.GuideTerm,
   			TermMaster.Children,
   			TermMaster.NodeDepth,
   			Terms.TermTypeID,
   			TermTypes.TermType
			FROM Terms
    		INNER JOIN TermMaster on Terms.TermMasterID = TermMaster.TermMasterID
   			INNER JOIN TermTypes on Terms.TermTypeID = TermTypes.TermTypeID
			WHERE TermMaster.CN IN ( $guide_terms_stmt )
			AND Children = 1
			AND (TermMaster.GuideTerm = 1)
			ORDER BY TermMaster.CN";
		return $stmt;
	}

	/**
	 * Add in additional queries for the migration.
	 */
	public function prepare_additional_queries() {

	}
}
