<?php
$ctrl_params=[
    'tag_id'=>'df_list_edit_tabs',
    'tabs'=>[
        [
            'type'=>'fixed',
            'uid'=>'def',
            'name'=>Translate::GetLabel('general'),
            'content_type'=>'control',
            'content'=>[
                'control_type'=>'BasicForm',
                'control_params'=>[
                    'tag_id'=>'df_list_edit_form',
                    'response_target'=>'df_list_edit_errors',
                    'cols_no'=>1,
                    'content'=>[
                        [
                            [
                                'control_type'=>'TextBox',
                                'control_params'=>['tag_id'=>'df_list_edit_ltype','tag_name'=>'ltype','value'=>$item->getProperty('ltype','','is_string'),'label'=>Translate::GetLabel('code'),'required'=>TRUE,'readonly'=>TRUE],
                            ],
                        ],
                        [
                            [
                                'control_type'=>'TextBox',
                                'control_params'=>['tag_id'=>'df_list_edit_name','tag_name'=>'name','value'=>$item->getProperty('name','','is_string'),'label'=>Translate::GetLabel('name'),'onenter_button'=>'df_list_edit_save'],
                            ],
                        ],
                        [
                            [
                                'control_type'=>'CheckBox',
                                'control_params'=>['tag_id'=>'df_list_edit_state','tag_name'=>'state','value'=>$item->getProperty('state',1,'is_numeric'),'label'=>Translate::GetLabel('active'),'class'=>'pull-left'],
                            ],
                        ],
                    ],
                    'actions'=>[
                        [
                            'params'=>['tag_id'=>'df_list_edit_save','value'=>Translate::GetButton('save_and_close'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','AddEditRecord','id'|'{$id}'~'close'|0~df_list_edit_form:form,'df_list_edit_form')->df_list_edit_errors")],
                        ],
                        [
                            'params'=>['tag_id'=>'df_list_edit_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','AddEditRecord',,'df_list_edit_form')->df_list_edit_errors")],
                        ],
                    ],
                ],
            ],
        ],
        [
            'type'=>'fixed',
            'uid'=>'values',
            'name'=>Translate::GetLabel('values'),
            'content_type'=>'ajax',
            'content_ajax_command'=>"{ 'module': '{$this->class}', 'method': 'ValuesListing', 'params': { 'id_list': {$id}, 'edit': 1, 'target': '{!t_target!}' } }",
            'reload_onchange'=>TRUE,
            'autoload'=>FALSE,
        ],
    ],
];