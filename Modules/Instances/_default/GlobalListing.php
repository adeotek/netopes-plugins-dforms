<?php
$ctrl_params = array(
    'module'=>$this->class,
    'method'=>$this->GetCurrentMethod(),
    'persistent_state'=>TRUE,
    'target'=>$listingTarget,
    'alternate_row_collor'=>TRUE,
    'scrollable'=>FALSE,
    'with_filter'=>TRUE,
    'with_pagination'=>TRUE,
    'sortby'=>array('column'=>'CREATE_DATE','direction'=>'ASC'),
    'qsearch'=>'for_text',
    'data_source'=>'Plugins\DForms\Instances',
    'ds_method'=>'GetInstancesList',
    'ds_params'=>array('for_id'=>NULL,'template_id'=>$idTemplate,'for_template_code'=>$template_code,'for_state'=>NULL,'for_text'=>NULL),
    'auto_load_data'=>TRUE,
    'columns'=>array(
        'actions'=>array(
            'type'=>'actions',
            'visual_count'=>2,
            'actions'=>array(
                array(
                    'type'=>'DivButton',
                    'command_string'=>"AjaxRequest('{$this->class}','ShowEditForm','id'|{{id}}~'id_template'|'{{id_template}}'~'cmodule'|'{$this->class}'~'cmethod'|'GlobalListing')->main-content",
                    'params'=>array('tag_id'=>'df_instance_edit_btn','tooltip'=>Translate::GetButton('edit'),'class'=>'btn btn-primary btn-xxs','icon'=>'fa fa-pencil-square-o'),
                ),
                array(
                    'type'=>'DivButton',
                    'command_string'=>"AjaxRequest('{$this->class}','ShowViewForm','id'|{{id}}~'id_template'|'{{id_template}}'~'is_modal'|1)->modal",
                    'params'=>array('tag_id'=>'df_template_view_btn','tooltip'=>Translate::GetButton('view'),'class'=>'btn btn-primary btn-xxs pull-right','icon'=>'fa fa-eye'),
                ),
            ),
        ),
        'template_code'=>array(
            'db_field'=>'template_code',
            'data_type'=>'numeric',
            'type'=>'value',
            'format'=>'integer',
            'halign'=>'center',
            'label'=>Translate::GetLabel('template_code'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ),
        'template_name'=>array(
            'db_field'=>'template_name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'left',
            'label'=>Translate::GetLabel('template_name'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
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
        'ftype'=>array(
            'db_field'=>'ftype',
            'data_type'=>'numeric',
            'type'=>'indexof',
            'values_collection'=>$ftypes,
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
        'state'=>array(
            'width'=>'60',
            'db_field'=>'state',
            'data_type'=>'numeric',
            'type'=>'control',
            'control_type'=>'JqCheckBox',
            'control_params'=>array('container'=>FALSE,'no_label'=>TRUE,'tag_id'=>'df_instance_update_state','jqparams'=>'{ type: 5 }','onchange'=>"AjaxRequest('{$this->class}','EditRecordState','id'|'{{id}}'~'state'|df_instance_update_state_{{id}}:value)->errors"),
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
            'label'=>Translate::GetLabel('created_at'),
            'sortable'=>TRUE,
            'filterable'=>FALSE,
        ),
        'user_full_name'=>array(
            'db_field'=>'user_full_name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'center',
            'label'=>Translate::GetLabel('created_by'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ),
        'last_modified'=>array(
            'width'=>'120',
            'db_field'=>'last_modified',
            'data_type'=>'datetime',
            'type'=>'value',
            'halign'=>'center',
            'default_value'=>'-',
            'label'=>Translate::GetLabel('last_modified'),
            'sortable'=>TRUE,
            'filterable'=>FALSE,
        ),
        'last_user_full_name'=>array(
            'db_field'=>'last_user_full_name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'center',
            'default_value'=>'-',
            'label'=>Translate::GetLabel('modified_by'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ),
        'end_actions'=>array(
            'type'=>'actions',
            'visual_count'=>1,
            'actions'=>array(
                array(
                    'type'=>'DivButton',
                    'command_string'=>"AjaxRequest('{$this->class}','DeleteRecord','id'|{{id}}~'id_template'|'{{id_template}}'~'cmodule'|'{$this->class}'~'cmethod'|'GlobalListing')->errors",
                    'params'=>array('tag_id'=>'df_instance_delete_btn','tooltip'=>Translate::GetButton('delete'),'class'=>'btn btn-danger btn-xxs','icon'=>'fa fa-times','confirm_text'=>Translate::GetMessage('confirm_delete'),'conditions'=>array(array('field'=>'ftype','type'=>'!=','value'=>2))),
                ),
            ),
        ),
    ),
);