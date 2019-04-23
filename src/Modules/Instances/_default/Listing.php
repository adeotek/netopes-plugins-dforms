<?php
$ctrl_params=[
    'module'=>$cModule,
    'method'=>$cMethod,
    'persistent_state'=>TRUE,
    'target'=>$listingTarget,
    'alternate_row_collor'=>TRUE,
    'scrollable'=>FALSE,
    'with_filter'=>TRUE,
    'with_pagination'=>TRUE,
    'sortby'=>['column'=>'CREATE_DATE','direction'=>'ASC'],
    'qsearch'=>'for_text',
    'ds_class'=>'Plugins\DForms\Instances',
    'ds_method'=>'GetInstances',
    'ds_params'=>['for_id'=>NULL,'template_id'=>($idTemplate ? $idTemplate : NULL),'for_template_code'=>($template_code ? $template_code : NULL),'for_state'=>NULL,'for_text'=>NULL],
    'auto_load_data'=>TRUE,
    'columns'=>[
        'actions'=>[
            'type'=>'actions',
            'visual_count'=>3,
            'actions'=>[
                [
                    'dright'=>'print',
                    'type'=>'Link',
                    'params'=>['tooltip'=>Translate::GetButton('pdf'),'class'=>NApp::$theme->GetBtnSuccessClass('btn-xxs'),'icon'=>'fa fa-file-pdf-o',
                        'href'=>NApp::app_web_link().'/pipe/cdn.php',
                        'target'=>'_blank',
                        'url_params'=>[
                            'namespace'=>NApp::current_namespace(),
                            'language'=>NApp::_GetLanguageCode(),
                            'rtype'=>'shash',
                            'mrt'=>'1',
                            'dbg'=>1,
                        ],
                        'session_params'=>[
                            'module'=>$this->class,
                            'method'=>'GetInstancePdf',
                            'params'=>['id'=>'{!id!}','result_type'=>1,'cache'=>TRUE],
                        ],
                    ],
                ],
                [
                    'dright'=>'edit',
                    'type'=>'DivButton',
                    'command_string'=>"AjaxRequest('{$this->class}','ShowEditForm','id'|{!id!}~'id_template'|'{!id_template!}'~'cmodule'|'{$cModule}'~'cmethod'|'{$cMethod}'~'ctarget'|'{$cTarget}')->main-content",
                    'params'=>['tag_id'=>'df_instance_edit_btn','tooltip'=>Translate::GetButton('edit'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs'),'icon'=>'fa fa-pencil-square-o'],
                ],
                [
                    'dright'=>'view',
                    'type'=>'DivButton',
                    'command_string'=>"AjaxRequest('{$this->class}','ShowViewForm','id'|{!id!}~'id_template'|'{!id_template!}'~'is_modal'|1)->modal",
                    'params'=>['tag_id'=>'df_template_view_btn','tooltip'=>Translate::GetButton('view'),'class'=>NApp::$theme->GetBtnInfoClass('btn-xxs pull-right'),'icon'=>'fa fa-eye'],
                ],
            ],
        ],
    ],
];

if(is_array($fields)) {
    foreach($fields as $field) {
        if(get_array_value($field,'listing',0,'is_numeric')!=1) {
            continue;
        }
        $fname=get_array_value($field,'name','','is_string');
        switch(get_array_value($field,'class','','is_string')) {
            case 'CheckBox':
                $fdatatype='numeric';
                $ftype='checkbox';
                $flabel=get_array_value($field,'label','','is_string');
                break;
            case 'SmartComboBox':
                $fdatatype='string';
                $ftype='value';
                $flabel=get_array_value($field,'label','','is_string');
                break;
            default:
                $fdatatype='string';
                $ftype='value';
                $flabel=get_array_value($field,'label','','is_string');
                break;
        }//END switch
        $ctrl_params['columns']['item-'.$fname]=[
            'db_field'=>'item-'.$fname,
            'data_type'=>$fdatatype,
            'type'=>$ftype,
            'halign'=>'left',
            'default_value'=>'-',
            'label'=>$flabel,
        ];
    }//END foreach
}//if(is_array($fields))

if(is_array($this->show_in_listing)) {
    foreach($this->show_in_listing as $fname) {
        switch($fname) {
            case 'template_code':
                $ctrl_params['columns']['template_code']=[
                    'db_field'=>'template_code',
                    'data_type'=>'numeric',
                    'type'=>'value',
                    'format'=>'integer',
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('template_code'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ];
                break;
            case 'template_name':
                $ctrl_params['columns']['template_name']=[
                    'db_field'=>'template_name',
                    'data_type'=>'string',
                    'type'=>'value',
                    'halign'=>'left',
                    'label'=>Translate::GetLabel('template_name'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ];
                break;
            case 'version':
                $ctrl_params['columns']['version']=[
                    'db_field'=>'version',
                    'data_type'=>'numeric',
                    'type'=>'value',
                    'format'=>'integer',
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('version'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ];
                break;
            case 'ftype':
                $ctrl_params['columns']['ftype']=[
                    'db_field'=>'ftype',
                    'data_type'=>'numeric',
                    'type'=>'indexof',
                    'values_collection'=>$fTypes,
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('type'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                    'filter_type'=>'combobox',
                    'show_filter_cond_type'=>FALSE,
                    'filter_params'=>['value_field'=>'id','display_field'=>'name','selected_value'=>NULL],
                    'filter_data_source'=>[
                        'ds_class'=>'_Custom\DFormsOffline',
                        'ds_method'=>'GetDynamicFormsTemplatesFTypes',
                    ],
                ];
                break;
            case 'iso_code':
                $ctrl_params['columns']['iso_code']=[
                    'db_field'=>'iso_code',
                    'data_type'=>'string',
                    'type'=>'value',
                    'halign'=>'center',
                    'default_value'=>'-',
                    'label'=>Translate::GetLabel('iso_code'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ];
                break;
            case 'state':
                $ctrl_params['columns']['state']=[
                    'width'=>'60',
                    'db_field'=>'state',
                    'data_type'=>'numeric',
                    'type'=>'control',
                    'control_type'=>'JqCheckBox',
                    'control_params'=>['container'=>FALSE,'no_label'=>TRUE,'tag_id'=>'df_instance_update_state','jqparams'=>'{ type: 5 }','onchange'=>"AjaxRequest('{$cModule}','EditRecordState','id'|'{!id!}'~'state'|df_instance_update_state_{!id!}:value)->errors"],
                    'control_pafreq'=>['onchange'],
                    'label'=>Translate::GetLabel('active'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                    'filter_type'=>'combobox',
                    'show_filter_cond_type'=>FALSE,
                    'filter_params'=>['value_field'=>'id','display_field'=>'name','selected_value'=>NULL],
                    'filter_data_source'=>[
                        'ds_class'=>'_Custom\DFormsOffline',
                        'ds_method'=>'GetGenericArrays',
                        'ds_params'=>['type'=>'active'],
                    ],
                ];
                break;
            case 'create_date':
                $ctrl_params['columns']['create_date']=[
                    'width'=>'120',
                    'db_field'=>'create_date',
                    'data_type'=>'datetime',
                    'type'=>'value',
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('created_at'),
                    'sortable'=>TRUE,
                    'filterable'=>FALSE,
                ];
                break;
            case 'user_full_name':
                $ctrl_params['columns']['user_full_name']=[
                    'db_field'=>'user_full_name',
                    'data_type'=>'string',
                    'type'=>'value',
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('created_by'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ];
                break;
            case 'last_modified':
                $ctrl_params['columns']['last_modified']=[
                    'width'=>'120',
                    'db_field'=>'last_modified',
                    'data_type'=>'datetime',
                    'type'=>'value',
                    'halign'=>'center',
                    'default_value'=>'-',
                    'label'=>Translate::GetLabel('last_modified'),
                    'sortable'=>TRUE,
                    'filterable'=>FALSE,
                ];
                break;
            case 'last_user_full_name':
                $ctrl_params['columns']['last_user_full_name']=[
                    'db_field'=>'last_user_full_name',
                    'data_type'=>'string',
                    'type'=>'value',
                    'halign'=>'center',
                    'default_value'=>'-',
                    'label'=>Translate::GetLabel('modified_by'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ];
                break;
        }//END switch
    }//END foreach
}//if(is_array($this->show_in_listing))

$ctrl_params['columns']['end_actions']=[
    'type'=>'actions',
    'visual_count'=>1,
    'actions'=>[
        [
            'dright'=>'delete',
            'type'=>'DivButton',
            'command_string'=>"AjaxRequest('{$this->class}','DeleteRecord','id'|{!id!}~'id_template'|'{!id_template!}'~'cmodule'|'{$cModule}'~'cmethod'|'{$cMethod}'~'ctarget'|'{$cTarget}')->errors",
            'params'=>['tooltip'=>Translate::GetButton('delete'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs'),'icon'=>'fa fa-times','confirm_text'=>Translate::GetMessage('confirm_delete'),'conditions'=>[['field'=>'ftype','type'=>'!=','value'=>2]]],
        ],
    ],
];