<?php
/**
 * DynamicForms data source file
 * Contains all database calls for dynamic forms.
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
 *
 * @package  NETopes\Plugins\DataSources\DForms
 */
abstract class BaseInstances extends DataSource {
    /**
     * GetTemplate default parameters
     */
    public const GET_TEMPLATE=['for_id'=>NULL,'for_code'=>NULL,'instance_id'=>NULL,'item_id'=>NULL,'for_state'=>NULL];

    /**
     * Gets a DynamicForm template
     *
     * @param array $params ['for_id'=>NULL,'for_code'=>NULL,'instance_id'=>NULL,'item_id'=>NULL,'for_state'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetTemplate($params=[],$extra_params=[]);

    /**
     * GetRelations default parameters
     */
    public const GET_RELATIONS=['for_id'=>NULL,'instance_id'=>NULL];

    /**
     * Gets DynamicForm relations
     *
     * @param array $params ['for_id'=>NULL,'instance_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetRelations($params=[],$extra_params=[]);

    /**
     * GetFields default parameters
     */
    public const GET_FIELDS=['template_id'=>NULL,'for_template_code'=>NULL,'instance_id'=>NULL,'for_listing'=>NULL,'for_pindex'=>NULL];

    /**
     * Gets DynamicForm fields
     *
     * @param array $params ['template_id'=>NULL,'for_template_code'=>NULL,'instance_id'=>NULL,'for_listing'=>NULL,'for_pindex'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetFields($params=[],$extra_params=[]);

    /**
     * GetPages default parameters
     */
    public const GET_PAGES=['for_id'=>NULL,'instance_id'=>NULL,'template_id'=>NULL,'for_template_code'=>NULL,'for_pindex'=>NULL,'sub_form_id'=>NULL];

    /**
     * Gets DynamicForm instance pages
     *
     * @param array $params ['for_id'=>NULL,'instance_id'=>NULL,'template_id'=>NULL,'for_template_code'=>NULL,'for_pindex'=>NULL,'sub_form_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetPages($params=[],$extra_params=[]);

    /**
     * GetStructure default parameters
     */
    public const GET_STRUCTURE=['template_id'=>NULL,'for_template_code'=>NULL,'instance_id'=>NULL,'for_pindex'=>NULL,'item_id'=>NULL,'for_index'=>NULL];

    /**
     * Gets DynamicForm structure with fields
     *
     * @param array $params ['template_id'=>NULL,'for_template_code'=>NULL,'instance_id'=>NULL,'for_pindex'=>NULL,'item_id'=>NULL,'for_index'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetStructure($params=[],$extra_params=[]);

    /**
     * GetInstancesList default parameters
     */
    public const GET_INSTANCES_LIST=['for_id'=>NULL,'template_id'=>NULL,'for_template_code'=>NULL,'for_state'=>NULL,'for_text'=>NULL];

    /**
     * Gets DynamicForm instances
     *
     * @param array $params ['for_id'=>NULL,'template_id'=>NULL,'for_template_code'=>NULL,'for_state'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetInstancesList($params=[],$extra_params=[]);

    /**
     * GetInstances default parameters
     */
    public const GET_INSTANCES=['for_id'=>NULL,'template_id'=>NULL,'for_template_code'=>NULL,'for_state'=>NULL,'for_text'=>NULL];

    /**
     * Gets DynamicForm instances
     *
     * @param array $params ['for_id'=>NULL,'template_id'=>NULL,'for_template_code'=>NULL,'for_state'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetInstances($params=[],$extra_params=[]);

    /**
     * GetInstanceItem default parameters
     */
    public const GET_INSTANCE_ITEM=['for_id'=>NULL];

    /**
     * Gets DynamicForm instance
     *
     * @param array $params ['for_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetInstanceItem($params=[],$extra_params=[]);

    /**
     * GetSingletonInstance default parameters
     */
    public const GET_SINGLETON_INSTANCE_ITEM=['for_uid'=>NULL,'template_id'=>NULL,'for_template_code'=>NULL];

    /**
     * Gets DynamicForm instance for single instance templates
     *
     * @param array $params ['for_uid'=>NULL,'template_id'=>NULL,'for_template_code'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetSingletonInstance($params=[],$extra_params=[]);

    /**
     * SetNewInstance default parameters
     */
    public const SET_NEW_INSTANCE=['template_id'=>NULL,'in_state'=>NULL,'user_id'=>NULL];

    /**
     * Sets a new DynamicForm instance
     *
     * @param array $params ['template_id'=>NULL,'in_state'=>NULL,'user_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetNewInstance($params=[],$extra_params=[]);

    /**
     * UnsetInstance default parameters
     */
    public const UNSET_INSTANCE=['for_id'=>NULL];

    /**
     * Unsets a DynamicForm instance
     *
     * @param array $params ['for_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function UnsetInstance($params=[],$extra_params=[]);

    /**
     * SetInstanceState default parameters
     */
    public const SET_INSTANCE_STATE=['for_id'=>NULL,'in_state'=>NULL,'user_id'=>NULL];

    /**
     * Sets a DynamicForm instance state
     *
     * @param array $params ['for_id'=>NULL,'in_state'=>NULL,'user_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetInstanceState($params=[],$extra_params=[]);

    /**
     * SetNewInstanceValue default parameters
     */
    public const SET_NEW_INSTANCE_VALUE=['instance_id'=>NULL,'item_uid'=>NULL,'in_value'=>NULL,'in_name'=>NULL,'in_index'=>NULL];

    /**
     * Sets a new DynamicForm instance field value
     *
     * @param array $params ['instance_id'=>NULL,'item_uid'=>NULL,'in_value'=>NULL,'in_name'=>NULL,'in_index'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetNewInstanceValue($params=[],$extra_params=[]);

    /**
     * UnsetInstanceValues default parameters
     */
    public const UNSET_INSTANCE_VALUES=['for_id'=>NULL];

    /**
     * Unsets all DynamicForm instance values
     *
     * @param array $params ['for_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function UnsetInstanceValues($params=[],$extra_params=[]);

    /**
     * SetNewInstanceRelation default parameters
     */
    public const SET_NEW_INSTANCE_RELATION=['instance_id'=>NULL,'relation_id'=>NULL,'in_ivalue'=>NULL,'in_svalue'=>NULL];

    /**
     * Sets a new DynamicForm instance relation value
     *
     * @param array $params ['instance_id'=>NULL,'relation_id'=>NULL,'in_ivalue'=>NULL,'in_svalue'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetNewInstanceRelation($params=[],$extra_params=[]);
}//END abstract class BaseInstances extends DataSource