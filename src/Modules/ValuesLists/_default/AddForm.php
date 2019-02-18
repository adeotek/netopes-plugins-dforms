<?php
$ctrl_params = array(
    'tag_id'=>'df_list_add_form',
    'response_target'=>'df_list_add_errors',
    'cols_no'=>1,
    'content'=>array(
        array(
            array(
                'control_type'=>'TextBox',
                'control_params'=>array('tag_id'=>'df_list_add_ltype','tag_name'=>'ltype','value'=>'','label'=>Translate::GetLabel('code'),'required'=>TRUE,'onenter_button'=>'df_list_add_save'),
            ),
        ),
        array(
            array(
                'control_type'=>'TextBox',
                'control_params'=>array('tag_id'=>'df_list_add_name','tag_name'=>'name','value'=>'','label'=>Translate::GetLabel('name'),'onenter_button'=>'df_list_add_save'),
            ),
        ),
        array(
            array(
                'control_type'=>'CheckBox',
                'control_params'=>array('tag_id'=>'df_list_add_state','tag_name'=>'state','value'=>1,'label'=>Translate::GetLabel('active'),'class'=>'pull-left'),
            ),
        ),
    ),
    'actions'=>array(
        array(
            'params'=>array('tag_id'=>'df_list_add_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','AddEditRecord',df_list_add_form:form,'df_list_add_form')->df_list_add_errors")),
        ),
        array(
            'type'=>'CloseModal',
            'params'=>array('tag_id'=>'df_list_add_cancel','value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'),
        ),
    ),
);