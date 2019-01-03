<?php
$version = $item->getProperty('version',0,'is_numeric');
$ctrl_params = array(
    'tag_id'=>'df_template_edit_tabs',
    'tabs'=>array(
        array(
            'type'=>'fixed',
            'uid'=>'def',
            'name'=>Translate::GetLabel('general'),
            'content_type'=>'control',
            'content'=>array(
                'control_type'=>'BasicForm',
                'control_params'=>array(
                    'tag_id'=>'df_template_edit_form',
                    'response_target'=>'df_template_edit_errors',
                    'colsno'=>1,
                    'content'=>array(
                        array(
                            array(
                                'width'=>'900',
                                'control_type'=>'TextBox',
                                'control_params'=>array('tag_id'=>'df_template_edit_version','value'=>$version.' - '.($version+1),'label'=>Translate::GetLabel('version').' ('.Translate::GetLabel('validated').' - '.Translate::GetLabel('in_edit').')','width'=>200,'align'=>'center','disabled'=>TRUE),
                            ),
                        ),
                        array(
                            array(
                                'width'=>'900',
                                'control_type'=>'NumericTextBox',
                                'control_params'=>array('tag_id'=>'df_template_edit_code','value'=>$item->getProperty('code',0,'is_numeric'),'label'=>Translate::GetLabel('code').' ('.Translate::GetLabel('numeric').')','align'=>'center','number_format'=>'0|||','disabled'=>TRUE,'required'=>TRUE),
                            ),
                        ),
                        array(
                            array(
                                'width'=>'900',
                                'control_type'=>'TextBox',
                                'control_params'=>array('tag_id'=>'df_template_edit_name','value'=>$item->getProperty('name','','is_string'),'label'=>Translate::GetLabel('name'),'onenter_button'=>'df_template_edit_save','required'=>TRUE,'disabled'=>($item->getProperty('version',0,'is_numeric')>0)),
                            ),
                        ),
                        array(
                            array(
                                'width'=>'900',
                                'control_type'=>'ComboBox',
                                'control_params'=>array('tag_id'=>'df_template_edit_ftype','value'=>$ftypes,'label'=>Translate::GetLabel('type'),'valfield'=>'id','displayfield'=>'name','selectedvalue'=>$item->getProperty('ftype',0,'is_numeric'),'required'=>TRUE,'disabled'=>TRUE),
                            ),
                        ),
                        // array(
                        // 	array(
                        // 		'width'=>'900',
                        // 		'control_type'=>'CheckBox',
                        // 		'control_params'=>array('tag_id'=>'df_template_edit_state','value'=>$item->getProperty('state',0,'is_numeric'),'label'=>Translate::GetLabel('active'),'class'=>'pull-left'),
                        // 	),
                        // ),
                        array('separator'=>'line'),
                        array(
                            array(
                                'width'=>'900',
                                'control_type'=>'CheckBox',
                                'control_params'=>array('tag_id'=>'df_template_edit_dmode','value'=>$item->getProperty('t_delete_mode',0,'is_numeric'),'label'=>Translate::GetLabel('hard_delete'),'class'=>'pull-left'),
                            ),
                        ),
                        array(
                            array(
                                'width'=>'900',
                                'control_type'=>'TextBox',
                                'control_params'=>array('tag_id'=>'df_template_edit_iso_code','value'=>$item->getProperty('t_iso_code','','is_string'),'label'=>Translate::GetLabel('iso_code'),'onenter_button'=>'df_template_edit_save'),
                            ),
                        ),
                    ),
                    'actions'=>array(
                        array(
                            'params'=>array('tag_id'=>'df_template_edit_save1','value'=>Translate::GetButton('save_and_close'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','AddEditRecord',
                                'id'|'{$id}'
                                ~'close'|1
                                ~'code'|df_template_edit_code:value
                                ~'name'|df_template_edit_name:value
                                ~'ftype'|df_template_edit_ftype:value
                                ~'dmode'|df_template_edit_dmode:value
                                ~'iso_code'|df_template_edit_iso_code:value
                            ,'df_template_edit_form')->df_template_edit_errors")),
                        ),
                        //~'state'|df_template_edit_state:value
                        array(
                            'params'=>array('tag_id'=>'df_template_edit_save0','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','AddEditRecord',
                                'id'|'{$id}'
                                ~'close'|0
                                ~'code'|df_template_edit_code:value
                                ~'name'|df_template_edit_name:value
                                ~'ftype'|df_template_edit_ftype:value
                                ~'dmode'|df_template_edit_dmode:value
                                ~'iso_code'|df_template_edit_iso_code:value
                            ,'df_template_edit_form')->df_template_edit_errors")),
                        ),
                    ),
                ),
            ),
        ),
        array(
            'type'=>'fixed',
            'uid'=>'design',
            'name'=>Translate::GetLabel('design'),
            'content_type'=>'ajax',
            'content'=>"AjaxRequest('{$this->name}','ShowDesignEditForm','id_template'|{$id},'{{t_target}}')->{{t_target}}",
            'reload_onchange'=>TRUE,
            'autoload'=>FALSE,
        ),
        array(
            'type'=>'fixed',
            'uid'=>'relations',
            'name'=>Translate::GetLabel('relations'),
            'content_type'=>'ajax',
            'content'=>"AjaxRequest('{$this->name}','ShowRelationsEditForm','id_template'|{$id},'{{t_target}}')->{{t_target}}",
            'reload_onchange'=>TRUE,
            'autoload'=>FALSE,
        ),
        array(
            'type'=>'fixed',
            'uid'=>'content',
            'name'=>Translate::GetLabel('content'),
            'content_type'=>'ajax',
            'content'=>"AjaxRequest('{$this->name}','ShowContentEditForm','id_template'|{$id},'{{t_target}}')->{{t_target}}",
            'reload_onchange'=>TRUE,
            'autoload'=>FALSE,
        ),
        array(
            'type'=>'fixed',
            'uid'=>'print_template',
            'name'=>Translate::GetLabel('print_template'),
            'content_type'=>'control',
            'content'=>array(
                'control_type'=>'BasicForm',
                'control_params'=>array(
                    'tag_id'=>'df_template_edit_print_template_form',
                    'response_target'=>'df_template_edit_print_template_errors',
                    'colsno'=>1,
                    'content'=>array(
                        array(
                            array(
                                'width'=>'900',
                                'control_type'=>'CkEditor',
                                'control_params'=>array('tag_id'=>'df_template_edit_print_template_value','value'=>$item->getProperty('t_print_template','','is_string'),'label'=>Translate::GetLabel('print_template'),'labelposition'=>'top','width'=>'100%','height'=>600,'extra_config'=>'toolbarStartupExpanded: true'),
                            ),
                        ),
                    ),
                    'actions'=>array(
                        array(
                            'params'=>array('tag_id'=>'df_template_edit_print_template_save1','value'=>Translate::GetButton('save_and_close'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','SetPrintTemplate',
                                'id'|'{$id}'
                                ~'close'|1
                                ~'print_template'|GetCkEditorData('df_template_edit_print_template_value')
                            ,'df_template_edit_form')->df_template_edit_errors")),
                        ),
                        //~'state'|df_template_edit_state:value
                        array(
                            'params'=>array('tag_id'=>'df_template_edit_print_template_save0','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','SetPrintTemplate',
                                'id'|'{$id}'
                                ~'close'|0
                                ~'print_template'|GetCkEditorData('df_template_edit_print_template_value')
                            ,'df_template_edit_print_template_form')->df_template_edit_print_template_errors")),
                        ),
                    ),
                ),
            ),
        ),
    ),
);