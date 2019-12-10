<?php
use NETopes\Core\App\Module;
use NETopes\Core\Data\DataProvider;

/** @var string $dgtarget */
/** @var int $template */
$ctrl_params=[
    'module'=>$this->class,
    'method'=>$this->GetCurrentMethod(),
    'persistent_state'=>FALSE,
    'target'=>$dgtarget,
    'alternate_row_color'=>TRUE,
    'scrollable'=>FALSE,
    'with_filter'=>TRUE,
    'with_pagination'=>TRUE,
    'custom_actions'=>[
        [
            'control_type'=>'Button',
            'dright'=>Module::DRIGHT_EDIT,
            'control_params'=>['value'=>Translate::GetButton('add'),'class'=>NApp::$theme->GetBtnPrimaryClass(),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->PrepareAjaxRequest(['module'=>$this->class,'method'=>'ShowRelationAddEditForm','params'=>['id_template'=>$template,'target'=>$target]],['target_id'=>'modal'])],
        ]
    ],
    'sortby'=>['column'=>'name','direction'=>'asc'],
    'qsearch'=>'for_text',
    'ds_class'=>'Plugins\DForms\Templates',
    'ds_method'=>'GetRelations',
    'ds_params'=>['for_id'=>NULL,'template_id'=>$template,'for_text'=>NULL],
    'auto_load_data'=>TRUE,
    'columns'=>[
        'actions'=>[
            'type'=>'actions',
            'visual_count'=>1,
            'actions'=>[
                [
                    'type'=>'DivButton',
                    'ajax_command'=>"{ 'module': '{$this->class}', 'method': 'ShowRelationAddEditForm', 'params': { 'id': {!id!}, 'id_template': {!id_template!}, 'target': '{$target}' } }",
                    'ajax_target_id'=>'modal',
                    'params'=>['tooltip'=>Translate::GetButton('edit'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs'),'icon'=>'fa fa-pencil-square-o'],
                ],
            ],
        ],
        'relation_type'=>[
            'db_field'=>'relation_type',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'center',
            'label'=>Translate::GetLabel('type'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
            // 'filter_type'=>'combobox',
            // 'show_filter_cond_type'=>FALSE,
            // 'filter_params'=>array('value_field'=>'id','display_field'=>'name','selected_value'=>NULL),
            // 'filter_data_source'=>array(
            // 	'ds_class'=>'_Custom\DFormsOffline',
            // 	'ds_method'=>'GetDynamicFormsTemplatesFTypes',
            // ),
        ],
        'name'=>[
            'db_field'=>'name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'left',
            'label'=>Translate::GetLabel('name'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ],
        'table_name'=>[
            'db_field'=>'table_name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'left',
            'label'=>Translate::GetLabel('table'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ],
        'column_name'=>[
            'db_field'=>'column_name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'left',
            'label'=>Translate::GetLabel('column'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ],
        'utype'=>[
            'db_field'=>'utype',
            'data_type'=>'numeric',
            'type'=>'indexof',
            'values_collection'=>DataProvider::GetKeyValue('_Custom\DFormsOffline','GetDynamicFormsRelationsUTypes'),
            'halign'=>'center',
            'label'=>Translate::GetLabel('usage_type'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
            'filter_type'=>'combobox',
            'show_filter_cond_type'=>FALSE,
            'filter_params'=>['value_field'=>'id','display_field'=>'name','selected_value'=>NULL],
            'filter_data_source'=>[
                'ds_class'=>'_Custom\DFormsOffline',
                'ds_method'=>'GetDynamicFormsRelationsUTypes',
            ],
        ],
        'required'=>[
            'width'=>'80',
            'db_field'=>'required',
            'data_type'=>'numeric',
            'type'=>'checkbox',
            'label'=>Translate::GetLabel('required'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
            'filter_type'=>'combobox',
            'show_filter_cond_type'=>FALSE,
            'filter_params'=>['value_field'=>'id','display_field'=>'name','selected_value'=>NULL],
            'filter_data_source'=>[
                'ds_class'=>'_Custom\DFormsOffline',
                'ds_method'=>'GetGenericArrays',
                'ds_params'=>['type'=>'yes-no'],
            ],
        ],
        'end_actions'=>[
            'type'=>'actions',
            'visual_count'=>1,
            'actions'=>[
                [
                    'type'=>'DivButton',
                    'ajax_command'=>"{ 'module': '{$this->class}', 'method': 'DeleteRelationRecord', 'params': { 'id': {!id!}, 'id_template': {!id_template!}, 'target': '{$target}' } }",
                    'ajax_target_id'=>'errors',
                    'params'=>['tooltip'=>Translate::GetButton('delete'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs'),'icon'=>'fa fa-times','confirm_text'=>Translate::GetMessage('confirm_delete')],
                ],
            ],
        ],
    ],
];
