<?php
$ctrl_params = array(
    'tagid'=>'df_list_add_form',
    'response_target'=>'df_list_add_errors',
    'colsno'=>1,
    'content'=>array(
        array(
            array(
                'control_type'=>'TextBox',
                'control_params'=>array('tagid'=>'df_list_add_ltype','tagname'=>'ltype','value'=>'','label'=>Translate::GetLabel('code'),'required'=>TRUE,'onenterbutton'=>'df_list_add_save'),
            ),
        ),
        array(
            array(
                'control_type'=>'TextBox',
                'control_params'=>array('tagid'=>'df_list_add_name','tagname'=>'name','value'=>'','label'=>Translate::GetLabel('name'),'onenterbutton'=>'df_list_add_save'),
            ),
        ),
        array(
            array(
                'control_type'=>'CheckBox',
                'control_params'=>array('tagid'=>'df_list_add_state','tagname'=>'state','value'=>1,'label'=>Translate::GetLabel('active'),'class'=>'pull-left'),
            ),
        ),
    ),
    'actions'=>array(
        array(
            'params'=>array('value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','AddEditRecord',df_list_add_form:form,'df_list_add_form')->df_list_add_errors")),
        ),
        array(
            'type'=>'CloseModal',
            'params'=>array('value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'),
        ),
    ),
);