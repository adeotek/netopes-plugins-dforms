<?php
if(isset($ctrl_params) && is_array($ctrl_params)) {
    $tName = get_array_value($ctrl_params,'tname',microtime(),'is_string');
    $fTagId = get_array_value($ctrl_params,'tag_id','','is_string');
    $fResponseTarget = get_array_value($ctrl_params,'response_target','','is_string');
    if(strlen($fTagId) && strlen($fResponseTarget)) {
        $ctrl_params['actions'] = array(
            array(
                'params'=>array('tag_id'=>'df_'.$tName.'_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','class'=>NApp::$theme->GetBtnPrimaryClass(),'onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','SaveRecord','id_template'|{$idTemplate}~'id'|{$idInstance}~'data'|df_{$tName}_form:form~'is_modal'|'{$is_modal}'~'cmodule'|'{$cModule}'~'cmethod'|'{$cMethod}'~'ctarget'|'{$cTarget}','{$fTagId}')->{$fResponseTarget}")),
            ),
        );
        if($is_modal) {
            $ctrl_params['actions'][] = array(
                'type'=>'CloseModal',
                'params'=>array('tag_id'=>'df_'.$tName.'_cancel','value'=>Translate::GetButton('cancel'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-ban'),
            );
        } else {
            $ctrl_params['actions'][] = array(
                'params'=>array('tag_id'=>'df_'.$tName.'_back','value'=>Translate::GetButton('back'),'icon'=>'fa fa-chevron-left','class'=>NApp::$theme->GetBtnDefaultClass(),'onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$cModule}','{$cMethod}','id_template'|{$idTemplate}~'id'|{$idInstance},'{$cTarget}')->{$cTarget}")),
            );
        }//if($is_modal)
    }//if(strlen($fTagId) && strlen($fResponseTarget))
}//if(isset($ctrl_params) && is_array($ctrl_params))