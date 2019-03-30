<?php
$ctrl_params = array(
    'tag_id'=>'df_lv_ae_form',
    'response_target'=>'df_lv_ae_errors',
    'cols_no'=>1,
    'content'=>array(
        array(
            array(
                'control_type'=>'TextBox',
                'control_params'=>array('tag_id'=>'df_lv_ae_value','tag_name'=>'value','value'=>$item->getProperty('value','','is_string'),'label'=>Translate::GetLabel('value'),'required'=>TRUE,'onenter_button'=>'df_lv_ae_save'),
            ),
        ),
        array(
            array(
                'control_type'=>'TextBox',
                'control_params'=>array('tag_id'=>'df_lv_ae_name','tag_name'=>'name','value'=>$item->getProperty('name','','is_string'),'label'=>Translate::GetLabel('name'),'onenter_button'=>'df_lv_ae_save'),
            ),
        ),
        array(
            array(
                'control_type'=>'CheckBox',
                'control_params'=>array('tag_id'=>'df_lv_ae_state','tag_name'=>'state','value'=>$item->getProperty('state',1,'is_numeric'),'label'=>Translate::GetLabel('active'),'class'=>'pull-left'),
            ),
        ),
        array(
            array(
                'control_type'=>'CheckBox',
                'control_params'=>array('tag_id'=>'df_lv_ae_implicit','tag_name'=>'implicit','value'=>$item->getProperty('implicit',0,'is_numeric'),'label'=>Translate::GetLabel('implicit'),'class'=>'pull-left'),
            ),
        ),
    ),
    'actions'=>array(
        array(
            'params'=>array('value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','AddEditValueRecord','id_list'|{$idList}~'id'|'{$id}'~'ctarget'|'{$target}'~df_lv_ae_form:form,'df_lv_ae_form')->df_lv_ae_errors")),
        ),
        array(
            'type'=>'CloseModal',
            'params'=>array('value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'),
        ),
    ),
);