<?php
$ctrl_params = array(
    'module'=>$this->name,
    'method'=>$this->GetCurrentMethod(),
    'persistent_state'=>TRUE,
    'target'=>$dgtarget,
    'alternate_row_color'=>TRUE,
    'scrollable'=>FALSE,
    'with_filter'=>TRUE,
    'with_pagination'=>TRUE,
    'sortby'=>array('column'=>'name','direction'=>'asc'),
    'qsearch'=>'for_text',
    'data_source'=>$this->module::$dataSourceName,
    'ds_method'=>'GetValues',
    'ds_params'=>array('for_id'=>NULL,'list_id'=>$idList,'for_ltype'=>NULL,'for_state'=>NULL,'for_implicit'=>NULL,'for_text'=>NULL),
    'auto_load_data'=>TRUE,
    'columns'=>array(
        'name'=>array(
            'db_field'=>'name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'left',
            'label'=>Translate::GetLabel('name'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ),
        'value'=>array(
            'db_field'=>'value',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'center',
            'label'=>Translate::GetLabel('value'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ),
        'state'=>array(
            'width'=>'80',
            'db_field'=>'state',
            'data_type'=>'numeric',
            'type'=>'checkbox',
            'label'=>Translate::GetLabel('active'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
            'filter_type'=>'combobox',
            'show_filter_cond_type'=>FALSE,
            'filter_params'=>array('value_field'=>'id','display_field'=>'name','selectedvalue'=>NULL),
            'filter_data_call'=>array(
                'data_source'=>'_Custom\Offline',
                'ds_method'=>'GetGenericArrays',
                'ds_params'=>array('type'=>'active'),
            ),
        ),
        'implicit'=>array(
            'width'=>'80',
            'db_field'=>'implicit',
            'data_type'=>'numeric',
            'type'=>'checkbox',
            'label'=>Translate::GetLabel('implicit'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
            'filter_type'=>'combobox',
            'show_filter_cond_type'=>FALSE,
            'filter_params'=>array('value_field'=>'id','display_field'=>'name','selectedvalue'=>NULL),
            'filter_data_call'=>array(
                'data_source'=>'_Custom\Offline',
                'ds_method'=>'GetGenericArrays',
                'ds_params'=>array('type'=>'yes-no'),
            ),
        ),
        'create_date'=>array(
            'width'=>'130',
            'db_field'=>'create_date',
            'data_type'=>'datetime',
            'type'=>'value',
            'halign'=>'center',
            'format'=>'datetime',
            'label'=>Translate::GetLabel('create_date'),
            'sortable'=>TRUE,
            'filterable'=>FALSE,
        ),
        'end_actions'=>($edit ? array(
            'type'=>'actions',
            'visual_count'=>2,
            'actions'=>array(
                array(
                    'type'=>'DivButton',
                    'command_string'=>"AjaxRequest('{$this->name}','ShowValueAddEditForm','id'|{{id}}~'id_list'|{{id_list}},'{$target}')->modal",
                    'params'=>array('tag_id'=>'df_list_edit_btn','tooltip'=>Translate::GetButton('edit'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs'),'icon'=>'fa fa-pencil-square-o'),
                ),
                array(
                    'type'=>'DivButton',
                    'command_string'=>"AjaxRequest('{$this->name}','DeleteValueRecord','id'|{{id}}~'id_list'|{{id_list}},'{$target}')->errors",
                    'params'=>array('tag_id'=>'df_list_delete_btn','tooltip'=>Translate::GetButton('delete'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs'),'icon'=>'fa fa-times','confirm_text'=>Translate::GetMessage('confirm_delete')),
                ),
            ),
        ) : []),
    ),
);