<?php
/**
 * BaseRelations data source file
 * Contains all database calls for forms forms relations.
 *
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
 * BaseRelations data source class
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
 */
abstract class BaseRelations extends DataSource {
    /**
     * GetTypeItems default parameters
     */
    const GET_TYPE_ITEMS=['for_id'=>NULL,'for_state'=>NULL,'for_text'=>NULL];

    /**
     * Gets forms relations types lists
     *
     * @param array $params ['for_id'=>NULL,'for_state'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     * @return mixed
     * @throws \NETopes\Core\AppException
     */
    abstract public function GetTypeItems($params=[],$extra_params=[]);

    /**
     * Gets forms relations type item
     *
     * @param array $params ['for_id'=>NULL,'for_state'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetTypeItem($params=[],$extra_params=[]);

    /**
     * SetNewTypeItem default parameters
     */
    const SET_NEW_TYPE_ITEM=['in_dtype'=>NULL,'in_name'=>NULL,'in_table_name'=>NULL,'in_column_name'=>NULL,'in_display_fields'=>NULL,'in_state'=>NULL];

    /**
     * Sets a new relations type item
     *
     * @param array $params ['in_dtype'=>NULL,'in_name'=>NULL,'in_table_name'=>NULL,'in_column_name'=>NULL,'in_display_fields'=>NULL,'in_state'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetNewTypeItem($params=[],$extra_params=[]);

    /**
     * SetTypeItem default parameters
     */
    const SET_TYPE_ITEM=['for_id'=>NULL,'in_name'=>NULL,'in_state'=>NULL];

    /**
     * Sets a relations type item
     *
     * @param array $params ['for_id'=>NULL,'in_dtype'=>NULL,'in_name'=>NULL,'in_table_name'=>NULL,'in_column_name'=>NULL,'in_display_fields'=>NULL,'in_state'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetTypeItem($params=[],$extra_params=[]);

    /**
     * UnsetTypeItem default parameters
     */
    const UNSET_TYPE_ITEM=['for_id'=>NULL];

    /**
     * Unsets a relations type item
     *
     * @param array $params ['for_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function UnsetTypeItem($params=[],$extra_params=[]);
}//END class BaseRelations extends DataSource