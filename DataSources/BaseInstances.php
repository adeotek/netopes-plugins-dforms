<?php
/**
 * DynamicForms data source file
 * Contains all database calls for dynamic forms.
 * @package    NETopes\Plugins\DataSources\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\DataSources;
use NETopes\Core\Data\DataSource;
/**
 * DynamicForms data source class
 * For all database call methods the default parameters configuration is:
 * $params (array) An array of parameters
 * to be passed to the query/stored procedure
 * $extra_params (array) An array of parameters that may contain:
 * - 'transaction'= name of transaction in which the query will run
 * - 'type' = request type: select, count, execute (default 'select')
 * - 'firstrow' = integer to limit number of returned rows
 * (if used with 'lastrow' represents the offset of the returned rows)
 * - 'lastrow' = integer to limit number of returned rows
 * (to be used only with 'firstrow')
 * - 'sort' = an array of fields to compose ORDER BY clause
 * - 'filters' = an array of condition to be applied in WHERE clause
 * return (array|bool) Returns database request result
 * @package  NETopes\Plugins\DataSources\DForms
 */
abstract class BaseInstances extends DataSource {
	/**
	 * Gets a DynamicForm template
	 */
	abstract public function GetTemplate($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm relations
	 */
	abstract public function GetRelations($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm fields
	 */
	abstract public function GetFields($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm structure with fields
	 */
	abstract public function GetStructure($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm instances
	 */
	abstract public function GetInstancesList($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm instances
	 */
	abstract public function GetInstances($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm instances
	 */
	abstract public function GetInstanceItem($params = [],$extra_params = []);
	/**
	 * Sets a new DynamicForm instance
	 */
	abstract public function SetNewInstance($params = [],$extra_params = []);
	/**
	 * Unsets a DynamicForm instance
	 */
	abstract public function UnsetInstance($params = [],$extra_params = []);
	/**
	 * Sets a DynamicForm instance state
	 */
	abstract public function SetInstanceState($params = [],$extra_params = []);
	/**
	 * Sets a new DynamicForm instance field value
	 */
	abstract public function SetNewInstanceValue($params = [],$extra_params = []);
	/**
	 * Unsets all DynamicForm instance values
	 */
	abstract public function UnsetInstanceValues($params = [],$extra_params = []);
	/**
	 * Sets a new DynamicForm instance relation value
	 */
	abstract public function SetNewInstanceRelation($params = [],$extra_params = []);
}//END abstract class BaseInstances extends DataSource