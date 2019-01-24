<?php
/**
 * Controls data source file
 * Contains all database calls for Controls.
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
 * Controls data source class
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
abstract class BaseControls extends DataSource {
    /**
     * GetItems default parameters
     */
    const GET_ITEMS = ['for_id'=>NULL,'for_class'=>NULL,'for_state'=>NULL];
    /**
     * Gets controls types list
     * @param array $params ['for_id'=>NULL,'for_class'=>NULL,'for_state'=>NULL]
     * @param array $extra_params
     * @return array|bool
     */
	abstract public function GetItems($params = [],$extra_params = []);
	/**
     * GetItems default parameters
     */
    const GET_ITEM = ['for_id'=>NULL,'for_class'=>NULL,'for_state'=>NULL];
    /**
     * Gets a control type
     * @param array $params ['for_id'=>NULL,'for_class'=>NULL,'for_state'=>NULL]
     * @param array $extra_params
     * @return array|bool
     */
	abstract public function GetItem($params = [],$extra_params = []);
	/**
     * GetProperties default parameters
     */
    const GET_PROPERTIES = ['control_id'=>NULL,'for_state'=>NULL,'parent_id'=>NULL,'for_group'=>NULL,'for_key'=>NULL];
    /**
     * Gets control properties list
     * @param array $params ['control_id'=>NULL,'for_state'=>NULL,'parent_id'=>NULL,'for_group'=>NULL,'for_key'=>NULL]
     * @param array $extra_params
     * @return array|bool
     */
	abstract public function GetProperties($params = [],$extra_params = []);
}//END abstract class BaseControls extends DataSource