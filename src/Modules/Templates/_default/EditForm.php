<?php
use NETopes\Core\Data\DataProvider;

$ctrl_params=[
    'tag_id'=>'df_template_edit_tabs',
    'tabs'=>[
        [
            'type'=>'fixed',
            'uid'=>'def',
            'name'=>Translate::GetLabel('general'),
            'content_type'=>'control',
            'content'=>[
                'control_type'=>'BasicForm',
                'control_params'=>[
                    'tag_id'=>'df_template_edit_form',
                    'response_target'=>'df_template_edit_errors',
                    'cols_no'=>1,
                    'content'=>[
                        [
                            [
                                'control_type'=>'TextBox',
                                'control_params'=>['tag_id'=>'df_template_edit_version','value'=>$version.' - '.($version + 1),'label'=>Translate::GetLabel('version').' ('.Translate::GetLabel('validated').' - '.Translate::GetLabel('in_edit').')','align'=>'center','disabled'=>TRUE],
                            ],
                        ],
                        [
                            [
                                'control_type'=>'NumericTextBox',
                                'control_params'=>['tag_id'=>'df_template_edit_code','value'=>$item->getProperty('code',0,'is_numeric'),'label'=>Translate::GetLabel('code').' ('.Translate::GetLabel('numeric').')','align'=>'center','number_format'=>'0|||','disabled'=>TRUE,'required'=>TRUE],
                            ],
                        ],
                        [
                            [
                                'control_type'=>'TextBox',
                                'control_params'=>['tag_id'=>'df_template_edit_name','value'=>$item->getProperty('name','','is_string'),'label'=>Translate::GetLabel('name'),'onenter_button'=>'df_template_edit_save','required'=>TRUE,'disabled'=>($item->getProperty('version',0,'is_numeric')>0)],
                            ],
                        ],
                        [
                            [
                                'control_type'=>'ComboBox',
                                'control_params'=>['tag_id'=>'df_template_edit_ftype','value'=>DataProvider::GetKeyValue('_Custom\DFormsOffline','GetDynamicFormsTemplatesFTypes'),'label'=>Translate::GetLabel('type'),'value_field'=>'id','display_field'=>'name','selected_value'=>$item->getProperty('ftype',0,'is_numeric'),'required'=>TRUE,'disabled'=>TRUE],
                            ],
                        ],
                        ['separator'=>'line'],
                        // array(
                        // 	array(
                        //
                        // 		'control_type'=>'CheckBox',
                        // 		'control_params'=>array('tag_id'=>'df_template_edit_state','value'=>$item->getProperty('state',0,'is_numeric'),'label'=>Translate::GetLabel('active'),'class'=>'pull-left'),
                        // 	),
                        // ),
                        [
                            [
                                'control_type'=>'CheckBox',
                                'control_params'=>['tag_id'=>'df_template_edit_dmode','value'=>$item->getProperty('t_delete_mode',0,'is_numeric'),'label'=>Translate::GetLabel('hard_delete'),'class'=>'pull-left'],
                            ],
                        ],
                    ],
                    'actions'=>[
                        [
                            'params'=>['tag_id'=>'df_template_edit_save1','value'=>Translate::GetButton('save_and_close'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','AddEditRecord',
                                'id'|'{$id}'
                                ~'close'|1
                                ~'code'|df_template_edit_code:value
                                ~'name'|df_template_edit_name:value
                                ~'ftype'|df_template_edit_ftype:value
                                ~'dmode'|df_template_edit_dmode:value
                            ,'df_template_edit_form')->df_template_edit_errors")],
                        ],
                        //~'state'|df_template_edit_state:value
                        [
                            'params'=>['tag_id'=>'df_template_edit_save0','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','AddEditRecord',
                                'id'|'{$id}'
                                ~'close'|0
                                ~'code'|df_template_edit_code:value
                                ~'name'|df_template_edit_name:value
                                ~'ftype'|df_template_edit_ftype:value
                                ~'dmode'|df_template_edit_dmode:value
                            ,'df_template_edit_form')->df_template_edit_errors")],
                        ],
                    ],
                ],
            ],
        ],
        [
            'type'=>'fixed',
            'uid'=>'design',
            'name'=>Translate::GetLabel('design'),
            'content_type'=>'ajax',
            'content'=>"AjaxRequest('{$this->class}','ShowDesignEditForm','id_template'|{$id},'{{t_target}}')->{{t_target}}",
            'reload_onchange'=>TRUE,
            'autoload'=>FALSE,
        ],
        [
            'type'=>'fixed',
            'uid'=>'relations',
            'name'=>Translate::GetLabel('relations'),
            'content_type'=>'ajax',
            'content'=>"AjaxRequest('{$this->class}','ShowRelationsEditForm','id_template'|{$id},'{{t_target}}')->{{t_target}}",
            'reload_onchange'=>TRUE,
            'autoload'=>FALSE,
        ],
        [
            'type'=>'fixed',
            'uid'=>'content',
            'name'=>Translate::GetLabel('content'),
            'content_type'=>'ajax',
            'content'=>"AjaxRequest('\NETopes\Plugins\DForms\Modules\TemplatesContent\TemplatesContent','ShowContentEditForm','id_template'|{$id},'{{t_target}}')->{{t_target}}",
            'reload_onchange'=>TRUE,
            'autoload'=>FALSE,
        ],
        [
            'type'=>'fixed',
            'uid'=>'print_template',
            'name'=>Translate::GetLabel('print_template'),
            'content_type'=>'control',
            'content'=>[
                'control_type'=>'BasicForm',
                'control_params'=>[
                    'tag_id'=>'df_template_edit_print_template_form',
                    'response_target'=>'df_template_edit_print_template_errors',
                    'cols_no'=>1,
                    'content'=>[
                        [
                            [
                                'control_type'=>'CkEditor',
                                'control_params'=>['tag_id'=>'df_template_edit_print_template_value','value'=>$item->getProperty('t_print_template','','is_string'),'no_label'=>TRUE,'width'=>'100%','height'=>600,'extra_config'=>'toolbarStartupExpanded: true'],
                            ],
                        ],
                    ],
                    'actions'=>[
                        [
                            'params'=>['value'=>Translate::GetButton('save_and_close'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','SetPrintTemplate',
                                'id'|'{$id}'
                                ~'close'|1
                                ~'print_template'|GetCkEditorData('df_template_edit_print_template_value')
                            ,'df_template_edit_form')->df_template_edit_errors")],
                        ],
                        [
                            'params'=>['value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','SetPrintTemplate',
                                'id'|'{$id}'
                                ~'close'|0
                                ~'print_template'|GetCkEditorData('df_template_edit_print_template_value')
                            ,'df_template_edit_print_template_form')->df_template_edit_print_template_errors")],
                        ],
                    ],
                ],
            ],
        ],
    ],
];