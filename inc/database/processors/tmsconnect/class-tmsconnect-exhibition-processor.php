<?php
/**
 * The class used to process TMS Exhibition Modules
 */
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Exhibition_Processor extends \TMSC\Database\TMSC_Processor {
	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Object';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_exhibitions';
}
