<?php
/**
 * Templates data source file
 * Contains all database calls for dynamic forms templates.
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
 * Templates data source class
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
abstract class BaseTemplates extends DataSource {
    /**
     * GetItems default parameters
     */
    const GET_ITEMS=['for_id'=>NULL,'for_validated'=>NULL,'for_state'=>NULL,'for_text'=>NULL,'for_ftype'=>NULL,'exclude_id'=>NULL];

    /**
     * Gets templates
     *
     * @param array $params ['for_id'=>NULL,'for_validated'=>NULL,'for_state'=>NULL,'for_text'=>NULL,'for_ftype'=>NULL,'exclude_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetItems($params=[],$extra_params=[]);

    /**
     * Gets templates
     *
     * @param array $params ['for_id'=>NULL,'for_validated'=>NULL,'for_state'=>NULL,'for_text'=>NULL,'for_ftype'=>NULL,'exclude_id'=>NULL]
     * @param array $extra_params
     * @return array|mixed
     */
    abstract public function GetItem($params=[],$extra_params=[]);

    /**
     * SetNewItem default parameters
     */
    const SET_NEW_ITEM=['in_code'=>NULL,'in_name'=>NULL,'in_ftype'=>NULL,'in_state'=>NULL,'in_colsno'=>NULL,'in_rowsno'=>NULL,'in_delete_mode'=>NULL,'user_id'=>NULL];

    /**
     * Sets a new template
     *
     * @param array $params ['in_code'=>NULL,'in_name'=>NULL,'in_ftype'=>NULL,'in_state'=>NULL,'in_colsno'=>NULL,'in_rowsno'=>NULL,'in_delete_mode'=>NULL,'user_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetNewItem($params=[],$extra_params=[]);

    /**
     * SetItem default parameters
     */
    const SET_ITEM=['for_id'=>NULL,'in_name'=>NULL,'in_ftype'=>NULL,'in_state'=>NULL,'in_delete_mode'=>NULL,'user_id'=>NULL];

    /**
     * Sets a template
     *
     * @param array $params ['for_id'=>NULL,'in_name'=>NULL,'in_ftype'=>NULL,'in_state'=>NULL,'in_delete_mode'=>NULL,'user_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetItem($params=[],$extra_params=[]);

    /**
     * SetItem default parameters
     */
    const UNSET_ITEM=['for_id'=>NULL];

    /**
     * Unsets a template
     *
     * @param array $params
     * @param array $extra_params
     * @return mixed
     */
    abstract public function UnsetItem($params=[],$extra_params=[]);

    /**
     * SetItemValidated default parameters
     */
    const SET_ITEM_STATE=['for_id'=>NULL,'in_state'=>NULL,'user_id'=>NULL];

    /**
     * Sets a template state
     *
     * @param array $params ['for_id'=>NULL,'in_state'=>NULL,'user_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetItemState($params=[],$extra_params=[]);

    /**
     * SetItemValidated default parameters
     */
    const SET_ITEM_VALIDATED=['for_id'=>NULL,'new_value'=>NULL,'user_id'=>NULL];

    /**
     * Sets a template validated field
     * new_value:
     *      -1=revert current un-validated version;
     *       0=create new (un-validated) version;
     *       1=validate current un-validated version;
     *
     * @param array $params ['for_id'=>NULL,'new_value'=>NULL,'user_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetItemValidated($params=[],$extra_params=[]);

    /**
     * GetItemPages default parameters
     */
    const GET_ITEM_PAGES=['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL,'for_pindex'=>NULL];

    /**
     * Gets template pages
     *
     * @param array $params ['for_id'=>NULL,'template_id'=>NULL,'for_pindex'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetItemPages($params=[],$extra_params=[]);

    /**
     * GetItemPage default parameters
     */
    const GET_ITEM_PAGE=['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL,'for_pindex'=>NULL];

    /**
     * Gets a template page
     *
     * @param array $params ['for_id'=>NULL,'template_id'=>NULL,'for_pindex'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetItemPage($params=[],$extra_params=[]);

    /**
     * SetNewTemplatePage default parameters
     */
    const SET_NEW_TEMPLATE_PAGE=['for_id'=>NULL,'in_pindex'=>NULL];

    /**
     * Sets a new template page (at the end)
     *
     * @param array $params ['for_id'=>NULL,'in_pindex'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetNewTemplatePage($params=[],$extra_params=[]);

    /**
     * SetTemplatePage default parameters
     */
    const SET_TEMPLATE_PAGE=['for_id'=>NULL,'in_pindex'=>NULL];

    /**
     * Sets a template page position
     *
     * @param array $params ['for_id'=>NULL,'in_pindex'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetTemplatePage($params=[],$extra_params=[]);

    /**
     * SetTemplatePageTitle default parameters
     */
    const SET_TEMPLATE_PAGE_TITLE=['template_id'=>NULL,'for_pindex'=>NULL,'in_title'=>NULL];

    /**
     * Sets template page title
     *
     * @param array $params ['template_id'=>NULL,'for_pindex'=>NULL,'in_title'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetTemplatePageTitle($params=[],$extra_params=[]);

    /**
     * UnsetTemplatePage default parameters
     */
    const UNSET_TEMPLATE_PAGE=['for_id'=>NULL,'in_pindex'=>NULL];

    /**
     * Unset a template page
     *
     * @param array $params ['for_id'=>NULL,'in_pindex'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function UnsetTemplatePage($params=[],$extra_params=[]);

    /**
     * SetNewTableCell default parameters
     */
    const SET_NEW_TABLE_CELL=['for_id'=>NULL,'in_col'=>NULL,'in_row'=>NULL,'in_pindex'=>NULL];

    /**
     * Sets a new template cell(s) (at the end)
     *
     * @param array $params ['for_id'=>NULL,'in_col'=>NULL,'in_row'=>NULL,'in_pindex'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetNewTableCell($params=[],$extra_params=[]);

    /**
     * SetTableCell default parameters
     */
    const SET_TABLE_CELL=['for_id'=>NULL,'in_col'=>NULL,'in_row'=>NULL,'in_pindex'=>NULL];

    /**
     * Sets a new template cell(s) (at any position)
     *
     * @param array $params ['for_id'=>NULL,'in_col'=>NULL,'in_row'=>NULL,'in_pindex'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetTableCell($params=[],$extra_params=[]);

    /**
     * UnsetTableCell default parameters
     */
    const UNSET_NEW_TABLE_CELL=['for_id'=>NULL,'in_col'=>NULL,'in_row'=>NULL,'in_pindex'=>NULL];

    /**
     * Unset a template cell
     *
     * @param array $params ['for_id'=>NULL,'in_col'=>NULL,'in_row'=>NULL,'in_pindex'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function UnsetTableCell($params=[],$extra_params=[]);

    /**
     * GetItemProperties default parameters
     */
    const GET_ITEM_PROPERTIES=['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL];

    /**
     * Gets template properties
     *
     * @param array $params ['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL]
     * @param array $extra_params
     * @return array|mixed
     */
    abstract public function GetItemProperties($params=[],$extra_params=[]);

    /**
     * SetPropertiesItem default parameters
     */
    const SET_PROPERTIES_ITEM=['template_id'=>NULL,'in_render_type'=>NULL,'in_theme_type'=>NULL,'in_controls_size'=>NULL,'in_label_cols'=>NULL,'in_separator_width'=>NULL,'in_iso_code'=>NULL,'in_print_template'=>NULL];

    /**
     * Sets a relation
     *
     * @param array $params ['template_id'=>NULL,'in_render_type'=>NULL,'in_theme_type'=>NULL,'in_controls_size'=>NULL,'in_label_cols'=>NULL,'in_separator_width'=>NULL,'in_iso_code'=>NULL,'in_print_template'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetPropertiesItem($params=[],$extra_params=[]);

    /**
     * GetRelations default parameters
     */
    const GET_RELATIONS_ITEMS=['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL,'validated'=>NULL,'for_utype'=>NULL,'for_text'=>NULL];

    /**
     * Gets templates relations
     *
     * @param array $params ['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL,'validated'=>NULL,'for_utype'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetRelations($params=[],$extra_params=[]);

    /**
     * Gets templates relations
     *
     * @param array $params ['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL,'validated'=>NULL,'for_utype'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     * @return array|mixed
     */
    abstract public function GetRelation($params=[],$extra_params=[]);

    /**
     * SetNewRelation default parameters
     */
    const SET_NEW_RELATION=['template_id'=>NULL,'relation_type_id'=>NULL,'in_name'=>NULL,'in_key'=>NULL,'in_required'=>NULL,'in_rtype'=>NULL,'in_utype'=>NULL,'in_category_column'=>NULL];

    /**
     * Sets a new template relation
     *
     * @param array $params ['template_id'=>NULL,'relation_type_id'=>NULL,'in_name'=>NULL,'in_key'=>NULL,'in_required'=>NULL,'in_rtype'=>NULL,'in_utype'=>NULL,'in_category_column'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetNewRelation($params=[],$extra_params=[]);

    /**
     * SetRelation default parameters
     */
    const SET_RELATION=['for_id'=>NULL,'in_name'=>NULL,'in_key'=>NULL,'in_required'=>NULL,'in_rtype'=>NULL,'in_utype'=>NULL,'in_category_column'=>NULL];

    /**
     * Sets a relation
     *
     * @param array $params ['for_id'=>NULL,'in_name'=>NULL,'in_key'=>NULL,'in_required'=>NULL,'in_rtype'=>NULL,'in_utype'=>NULL,'in_category_column'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetRelation($params=[],$extra_params=[]);

    /**
     * UnsetRelation default parameters
     */
    const UNSET_RELATION=['for_id'=>NULL];

    /**
     * Unets a relation
     *
     * @param array $params ['for_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function UnsetRelation($params=[],$extra_params=[]);

    /**
     * GetFields default parameters
     */
    const GET_FIELDS=['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL,'for_pindex'=>NULL,'sub_form_id'=>NULL,'for_text'=>NULL];

    /**
     * Gets templates page fields
     *
     * @param array $params ['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL,'for_pindex'=>NULL,'sub_form_id'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetFields($params=[],$extra_params=[]);

    /**
     * GetField default parameters
     */
    const GET_FIELD=['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL,'for_pindex'=>NULL,'sub_form_id'=>NULL,'for_text'=>NULL];

    /**
     * Gets template page field
     *
     * @param array $params ['for_id'=>NULL,'template_id'=>NULL,'for_version'=>NULL,'for_pindex'=>NULL,'sub_form_id'=>NULL,'for_text'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function GetField($params=[],$extra_params=[]);

    /**
     * SetNewField default parameters
     */
    const SET_NEW_FIELD=['template_id'=>NULL,'sub_form_id'=>NULL,'in_pindex'=>NULL,'in_itype'=>NULL,'in_frow'=>NULL,'in_fcol'=>NULL,'in_name'=>NULL,'in_label'=>NULL,'in_required'=>NULL,'in_listing'=>NULL,'values_list_id'=>NULL,'in_class'=>NULL,'in_data_type'=>NULL,'in_params'=>NULL,'in_width'=>NULL,'in_colspan'=>NULL,'in_description'=>NULL];

    /**
     * Sets a new template item
     *
     * @param array $params ['template_id'=>NULL,'sub_form_id'=>NULL,'in_pindex'=>NULL,'in_itype'=>NULL,'in_frow'=>NULL,'in_fcol'=>NULL,'in_name'=>NULL,'in_label'=>NULL,'in_required'=>NULL,'in_listing'=>NULL,'values_list_id'=>NULL,'in_class'=>NULL,'in_data_type'=>NULL,'in_params'=>NULL,'in_width'=>NULL,'in_colspan'=>NULL,'in_description'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetNewField($params=[],$extra_params=[]);

    /**
     * SetField default parameters
     */
    const SET_FIELD=['for_id'=>NULL,'in_pindex'=>NULL,'in_itype'=>NULL,'in_frow'=>NULL,'in_fcol'=>NULL,'in_name'=>NULL,'in_label'=>NULL,'in_required'=>NULL,'in_listing'=>NULL,'values_list_id'=>NULL,'in_class'=>NULL,'in_data_type'=>NULL,'in_params'=>NULL,'in_width'=>NULL,'in_colspan'=>NULL,'in_description'=>NULL];

    /**
     * Sets a template item
     *
     * @param array $params ['for_id'=>NULL,'in_pindex'=>NULL,'in_itype'=>NULL,'in_frow'=>NULL,'in_fcol'=>NULL,'in_name'=>NULL,'in_label'=>NULL,'in_required'=>NULL,'in_listing'=>NULL,'values_list_id'=>NULL,'in_class'=>NULL,'in_data_type'=>NULL,'in_params'=>NULL,'in_width'=>NULL,'in_colspan'=>NULL,'in_description'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function SetField($params=[],$extra_params=[]);

    /**
     * UnsetField default parameters
     */
    const UNSET_FIELD=['for_id'=>NULL];

    /**
     * Unset a template item
     *
     * @param array $params ['for_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function UnsetField($params=[],$extra_params=[]);

    /**
     * CloneItem default parameters
     */
    const CLONE_ITEM=['for_id'=>NULL,'user_id'=>NULL];

    /**
     * Clone a template
     *
     * @param array $params ['for_id'=>NULL,'user_id'=>NULL]
     * @param array $extra_params
     * @return mixed
     */
    abstract public function CloneItem($params=[],$extra_params=[]);
}//END abstract class BaseTemplates extends DataSource