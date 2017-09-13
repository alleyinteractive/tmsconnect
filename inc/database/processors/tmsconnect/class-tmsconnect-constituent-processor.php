<?php
/**
 * The class used to process TMS Constituents Modules
 */
namespace TMSC\Database\Processors\TMSConnect;
class TMSConnect_Constituent_Processor extends \TMSC\Database\Processors\TMSConnect\TMSC_Object_Processor {
	/**
	 * Which migratable type the objects of this processor will be.
	 */
	public $migrateable_type = 'Object';

	/**
	 * The key used for the current object query
	 * @var string
	 */
	public $object_query_key = 'tms_constituents';
}
