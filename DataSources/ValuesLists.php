<?php
/**
 * ValuesLists data source file
 *
 * Contains all database calls for forms values lists.
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
 * ValuesLists data source class
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
abstract class ValuesLists extends DataSource {
	/**
	 * Gets forms values lists
	 *
	 * @access public
	 */
	abstract public function GetItems($params = [],$extra_params = []);
	/**
	 * Sets a new values list
	 *
	 * @access public
	 */
	abstract public function SetNewItem($params = [],$extra_params = []);
	/**
	 * Sets a values list
	 *
	 * @access public
	 */
	abstract public function SetItem($params = [],$extra_params = []);
	/**
	 * Unets a values list
	 *
	 * @access public
	 */
	abstract public function UnsetItem($params = [],$extra_params = []);
	/**
	 * Gets forms lists values
	 *
	 * @access public
	 */
	abstract public function GetValues($params = [],$extra_params = []);
	/**
	 * Gets forms lists values items
	 *
	 * @access public
	 */
	abstract public function GetValueItems($params = [],$extra_params = []);
	/**
	 * Sets a new list value
	 *
	 * @access public
	 */
	abstract public function SetNewValue($params = [],$extra_params = []);
	/**
	 * Sets a list value
	 *
	 * @access public
	 */
	abstract public function SetValue($params = [],$extra_params = []);
	/**
	 * Unets a list value
	 *
	 * @access public
	 */
	abstract public function UnsetValue($params = [],$extra_params = []);
}//END class ValuesLists extends DataSource