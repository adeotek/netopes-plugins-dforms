<?php
/**
 * @var string $controlClass
 * @var string $tName
 * @var string $fTagId
 */

if($controlClass=='BasicForm' && isset($ctrl_params) && is_array($ctrl_params)) {
    $fResponseTarget=get_array_value($ctrl_params,'response_target','','is_string');
    if(strlen($fTagId) && strlen($fResponseTarget)) {
        $ctrl_params['actions']=[
            [
                'params'=>['tag_id'=>'df_'.$tName.'_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','class'=>NApp::$theme->GetBtnPrimaryClass(),'onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','SaveRecord','id_template'|{$idTemplate}~'id'|{$idInstance}~'data'|df_{$tName}_form:form~'is_modal'|'{$isModal}'~'cmodule'|'{$cModule}'~'cmethod'|'{$cMethod}'~'ctarget'|'{$cTarget}','{$fTagId}')->{$fResponseTarget}")],
            ],
        ];
        if($isModal) {
            $ctrl_params['actions'][]=[
                'type'=>'CloseModal',
                'params'=>['tag_id'=>'df_'.$tName.'_cancel','value'=>Translate::GetButton('cancel'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-ban'],
            ];
        } else {
            $ctrl_params['actions'][]=[
                'params'=>['tag_id'=>'df_'.$tName.'_back','value'=>Translate::GetButton('back'),'icon'=>'fa fa-chevron-left','class'=>NApp::$theme->GetBtnDefaultClass(),'onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$cModule}','{$cMethod}','id_template'|{$idTemplate}~'id'|{$idInstance},'{$cTarget}')->{$cTarget}")],
            ];
        }//if($isModal)
    }//if(strlen($fTagId) && strlen($fResponseTarget))
}//if($controlClass=='BasicForm' && isset($ctrl_params) && is_array($ctrl_params))