<?php
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\TableView;
    use NETopes\Core\Data\DataProvider;
$ctrl_params = array(
    'module'=>$this->class,
    'method'=>$this->GetCurrentMethod(),
    'persistent_state'=>TRUE,
    'target'=>'listing_content',
    'alternate_row_collor'=>TRUE,
    'scrollable'=>FALSE,
    'with_filter'=>TRUE,
    'with_pagination'=>TRUE,
    'sortby'=>array('column'=>'name','direction'=>'asc'),
    'qsearch'=>'for_text',
    'data_source'=>'Plugins\DForms\Templates',
    'ds_method'=>'GetItems',
    'ds_params'=>array('for_id'=>NULL,'for_validated'=>NULL,'for_state'=>NULL,'for_text'=>NULL,'for_ftype'=>NULL,'exclude_id'=>NULL),
    'auto_load_data'=>TRUE,
    'columns'=>array(
        'actions'=>array(
            'type'=>'actions',
            'visual_count'=>4,
            'actions'=>array(
                array(
                    'type'=>'DivButton',
                    'dright'=>'add',
                    'command_string'=>"AjaxRequest('{$this->class}','CloneRecord','id'|{{id}})->errors",
                    'params'=>array('tooltip'=>Translate::GetButton('clone'),'class'=>NApp::$theme->GetBtnSpecialLightClass('btn-xxs'),'icon'=>'fa fa-copy'),
                ),
                array(
                    'type'=>'DivButton',
                    'dright'=>'edit',
                    'command_string'=>"AjaxRequest('{$this->class}','ShowEditForm','id'|{{id}})->main-content",
                    'params'=>array('tooltip'=>Translate::GetButton('edit'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs'),'icon'=>'fa fa-pencil-square-o','conditions'=>array(array('field'=>'validated','type'=>'==','value'=>0))),
                ),
                array(
                    'type'=>'DivButton',
                    'dright'=>'validate',
                    'command_string'=>"AjaxRequest('{$this->class}','ValidateRecord','id'|{{id}}~'new_value'|'-1')->errors",
                    'params'=>array('tooltip'=>Translate::GetButton('delete_unvalidated_version'),'class'=>NApp::$theme->GetBtnWarningClass('btn-xxs'),'icon'=>'fa fa-minus','conditions'=>array(array('field'=>'validated','type'=>'==','value'=>0),array('field'=>'version','type'=>'>','value'=>0))),
                ),
                array(
                    'type'=>'DivButton',
                    'dright'=>'edit',
                    'command_string'=>"AjaxRequest('{$this->class}','CreateNewVersion','id'|{{id}})->errors",
                    'params'=>array('tooltip'=>Translate::GetButton('new_version'),'class'=>NApp::$theme->GetBtnWarningClass('btn-xxs'),'icon'=>'fa fa-code-fork','conditions'=>array(array('field'=>'validated','type'=>'==','value'=>1))),
                ),
                array(
                    'type'=>'DivButton',
                    'dright'=>'view',
                    'command_string'=>"AjaxRequest('{$this->class}','ShowViewForm','id'|{{id}})->main-content",
                    'params'=>array('tooltip'=>Translate::GetButton('view'),'class'=>NApp::$theme->GetBtnInfoClass('btn-xxs'),'icon'=>'fa fa-eye','conditions'=>array(array('field'=>'version','type'=>'>','value'=>0))),
                ),
            ),
        ),
        'code'=>array(
            'db_field'=>'code',
            'data_type'=>'numeric',
            'type'=>'value',
            'format'=>'integer',
            'halign'=>'center',
            'label'=>Translate::GetLabel('code'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ),
        'name'=>array(
            'db_field'=>'name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'left',
            'label'=>Translate::GetLabel('name'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ),
        'ftype'=>array(
            'db_field'=>'ftype',
            'data_type'=>'numeric',
            'type'=>'indexof',
            'values_collection'=>DataProvider::GetKeyValue('_Custom\DFormsOffline','GetDynamicFormsTemplatesFTypes'),
            'halign'=>'center',
            'label'=>Translate::GetLabel('type'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
            'filter_type'=>'combobox',
            'show_filter_cond_type'=>FALSE,
            'filter_params'=>array('value_field'=>'id','display_field'=>'name','selected_value'=>NULL),
            'filter_data_call'=>array(
                'data_source'=>'_Custom\DFormsOffline',
                'ds_method'=>'GetDynamicFormsTemplatesFTypes',
            ),
        ),
        'version'=>array(
            'db_field'=>'version',
            'data_type'=>'numeric',
            'type'=>'value',
            'format'=>'integer',
            'halign'=>'center',
            'label'=>Translate::GetLabel('version'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ),
        'validated'=>array(
            'width'=>'70',
            'db_field'=>'validated',
            'data_type'=>'numeric',
            'type'=>'checkbox',
            'label'=>Translate::GetLabel('validated'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
            'filter_type'=>'combobox',
            'show_filter_cond_type'=>FALSE,
            'filter_params'=>array('value_field'=>'id','display_field'=>'name','selected_value'=>NULL),
            'filter_data_call'=>array(
                'data_source'=>'_Custom\DFormsOffline',
                'ds_method'=>'GetGenericArrays',
                'ds_params'=>array('type'=>'yes-no'),
            ),
        ),
        'state'=>array(
            'width'=>'60',
            'db_field'=>'state',
            'data_type'=>'numeric',
            'type'=>'control',
            'control_type'=>'JqCheckBox',
            'control_params'=>array('container'=>FALSE,'no_label'=>TRUE,'tag_id'=>'df_template_update_state','jqparams'=>'{ type: 5 }','onchange'=>"AjaxRequest('{$this->class}','EditRecordState','id'|'{{id}}'~'state'|df_template_update_state_{{id}}:value)->errors"),
            'control_pafreq'=>array('onchange'),
            'label'=>Translate::GetLabel('active'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
            'filter_type'=>'combobox',
            'show_filter_cond_type'=>FALSE,
            'filter_params'=>array('value_field'=>'id','display_field'=>'name','selected_value'=>NULL),
            'filter_data_call'=>array(
                'data_source'=>'_Custom\DFormsOffline',
                'ds_method'=>'GetGenericArrays',
                'ds_params'=>array('type'=>'active'),
            ),
        ),
        'create_date'=>array(
            'width'=>'120',
            'db_field'=>'create_date',
            'data_type'=>'datetime',
            'type'=>'value',
            'halign'=>'center',
            'format'=>'datetime',
            'label'=>Translate::GetLabel('create_date'),
            'sortable'=>TRUE,
            'filterable'=>FALSE,
        ),
        'last_modified'=>array(
            'width'=>'120',
            'db_field'=>'last_modified',
            'data_type'=>'datetime',
            'type'=>'value',
            'halign'=>'center',
            'format'=>'datetime',
            'label'=>Translate::GetLabel('last_modified'),
            'sortable'=>TRUE,
            'filterable'=>FALSE,
        ),
        'user_full_name'=>array(
            'db_field'=>'user_full_name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'left',
            'label'=>Translate::GetLabel('user'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ),
        'end_actions'=>array(
            'type'=>'actions',
            'visual_count'=>1,
            'actions'=>array(
                array(
                    'type'=>'DivButton',
                    'dright'=>'delete',
                    'command_string'=>"AjaxRequest('{$this->class}','DeleteRecord','id'|{{id}})->errors",
                    'params'=>array('tooltip'=>Translate::GetButton('delete'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs'),'icon'=>'fa fa-times','confirm_text'=>Translate::GetMessage('confirm_delete')),
                ),
            ),
        ),
    ),
);