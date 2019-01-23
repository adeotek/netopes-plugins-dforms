<?php
/**
 * ValuesLists data source file
 * Contains all database calls for forms values lists.
 * @package    NETopes\Plugins\DataSources\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DataSources\DForms;
use NETopes\Core\Data\DataSource;
/**
 * ValuesLists data source class
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
abstract class BaseValuesLists extends DataSource {
    /**
     * GetItems default parameters
     */
    const GET_ITEMS = ['for_id'=>NULL,'for_state'=>NULL,'for_text'=>NULL];
    /**
     * Gets forms values lists
     * @param array $params ['for_id'=>NULL,'for_state'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     */
	abstract public function GetItems($params = [],$extra_params = []);
	/**
     * Gets forms values list item
     * @param array $params ['for_id'=>NULL,'for_state'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     */
	abstract public function GetItem($params = [],$extra_params = []);
    /**
     * SetNewItem default parameters
     */
    const SET_NEW_ITEM = ['in_ltype'=>NULL,'in_name'=>NULL,'in_state'=>NULL];
	/**
	 * Sets a new values list
     * @param array $params ['in_ltype'=>NULL,'in_name'=>NULL,'in_state'=>NULL]
     * @param array $extra_params
	 */
	abstract public function SetNewItem($params = [],$extra_params = []);
    /**
     * SetItem default parameters
     */
    const SET_ITEM = ['for_id'=>NULL,'in_name'=>NULL,'in_state'=>NULL];
	/**
	 * Sets a values list
     * @param array $params ['for_id'=>NULL,'in_name'=>NULL,'in_state'=>NULL]
     * @param array $extra_params
	 */
	abstract public function SetItem($params = [],$extra_params = []);
    /**
     * UnsetItem default parameters
     */
    const UNSET_ITEM = ['for_id'=>NULL];
	/**
	 * Unets a values list
     * @param array $params ['for_id'=>NULL]
     * @param array $extra_params
	 */
	abstract public function UnsetItem($params = [],$extra_params = []);
    /**
     * GetValues default parameters
     */
    const GET_VALUES = ['for_id'=>NULL,'list_id'=>NULL,'for_ltype'=>NULL,'for_state'=>NULL,'for_implicit'=>NULL,'for_text'=>NULL];
	/**
	 * Gets forms lists values
     * @param array $params ['for_id'=>NULL,'list_id'=>NULL,'for_ltype'=>NULL,'for_state'=>NULL,'for_implicit'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
	 */
	abstract public function GetValues($params = [],$extra_params = []);
    /**
	 * Gets forms lists values item
     * @param array $params ['for_id'=>NULL,'list_id'=>NULL,'for_ltype'=>NULL,'for_state'=>NULL,'for_implicit'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
	 */
	abstract public function GetValue($params = [],$extra_params = []);
    /**
     * GetValueItems default parameters
     */
    const GET_VALUE_ITEMS = ['for_id'=>NULL,'list_id'=>NULL,'for_ltype'=>NULL,'for_state'=>NULL];
	/**
	 * Gets forms lists values items
     * @param array $params ['for_id'=>NULL,'list_id'=>NULL,'for_ltype'=>NULL,'for_state'=>NULL]
     * @param array $extra_params
	 */
	abstract public function GetValueItems($params = [],$extra_params = []);
    /**
     * SetNewValue default parameters
     */
    const SET_NEW_VALUE = ['list_id'=>NULL,'in_value'=>NULL,'in_name'=>NULL,'in_state'=>NULL,'in_implicit'=>NULL];
	/**
	 * Sets a new list value
     * @param array $params ['list_id'=>NULL,'in_value'=>NULL,'in_name'=>NULL,'in_state'=>NULL,'in_implicit'=>NULL]
     * @param array $extra_params
	 */
	abstract public function SetNewValue($params = [],$extra_params = []);
    /**
     * SetValue default parameters
     */
    const SET_VALUE = ['for_id'=>NULL,'in_value'=>NULL,'in_name'=>NULL,'in_state'=>NULL,'in_implicit'=>NULL];
	/**
	 * Sets a list value
     * @param array $params ['for_id'=>NULL,'in_value'=>NULL,'in_name'=>NULL,'in_state'=>NULL,'in_implicit'=>NULL]
     * @param array $extra_params
	 */
	abstract public function SetValue($params = [],$extra_params = []);
    /**
     * UnsetValue default parameters
     */
    const UNSET_VALUE = ['for_id'=>NULL];
	/**
	 * Unets a list value
     * @param array $params ['for_id'=>NULL]
     * @param array $extra_params
	 */
	abstract public function UnsetValue($params = [],$extra_params = []);
}//END class BaseValuesLists extends DataSource