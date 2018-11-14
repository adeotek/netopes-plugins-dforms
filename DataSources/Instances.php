<?php
/**
 * DynamicForms data source file
 *
 * Contains all database calls for dynamic forms.
 *
 * @package    NETopes\Plugins\DataSources\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2019 AdeoTEK Software
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DataSources\DForms;
use NETopes\Core\Data\DataSource;
/**
 * DynamicForms data source class
 *
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
 *
 * @package  NETopes\Plugins\DataSources\DForms
 * @access   public
 */
abstract class Instances extends DataSource {
	/**
	 * Gets a DynamicForm template
	 *
	 * @access public
	 */
	abstract public function GetTemplate($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm relations
	 *
	 * @access public
	 */
	abstract public function GetRelations($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm fields
	 *
	 * @access public
	 */
	abstract public function GetFields($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm structure with fields
	 *
	 * @access public
	 */
	abstract public function GetStructure($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm instances
	 *
	 * @access public
	 */
	abstract public function GetInstancesList($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm instances
	 *
	 * @access public
	 */
	abstract public function GetInstances($params = [],$extra_params = []);
	/**
	 * Gets DynamicForm instances
	 *
	 * @access public
	 */
	abstract public function GetInstanceItem($params = [],$extra_params = []);
	/**
	 * Sets a new DynamicForm instance
	 *
	 * @access public
	 */
	abstract public function SetNewInstance($params = [],$extra_params = []);
	/**
	 * Unsets a DynamicForm instance
	 *
	 * @access public
	 */
	abstract public function UnsetInstance($params = [],$extra_params = []);
	/**
	 * Sets a DynamicForm instance state
	 *
	 * @access public
	 */
	abstract public function SetInstanceState($params = [],$extra_params = []);
	/**
	 * Sets a new DynamicForm instance field value
	 *
	 * @access public
	 */
	abstract public function SetNewInstanceValue($params = [],$extra_params = []);
	/**
	 * Unsets all DynamicForm instance values
	 *
	 * @access public
	 */
	abstract public function UnsetInstanceValues($params = [],$extra_params = []);
	/**
	 * Sets a new DynamicForm instance relation value
	 *
	 * @access public
	 */
	abstract public function SetNewInstanceRelation($params = [],$extra_params = []);
}//END abstract class Instances extends DataSource