<?php
/**
 * @var string      $actionsLocation
 * @var string      $tName
 * @var string      $fTagId
 * @var int         $idTemplate
 * @var int         $idInstance
 * @var string|null $cModule
 * @var string|null $cMethod
 * @var string|null $cTarget
 * @var string      $aeSaveInstanceMethod
 * @var bool|null   $noRedirect
 */

if($actionsLocation=='form' && isset($ctrl_params) && is_array($ctrl_params)) {
    $fResponseTarget=get_array_value($ctrl_params,'response_target','','is_string');
    if(strlen($fTagId) && strlen($fResponseTarget)) {
        $ctrl_params['actions']=[
            [
                'params'=>['tag_id'=>'df_'.$tName.'_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','class'=>NApp::$theme->GetBtnPrimaryClass(),'onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': '{$aeSaveInstanceMethod}', 'params': { 'id_template': '{$idTemplate}', 'id': '{$idInstance}', 'relations': '{nGet|df_{$tName}_relations:form}', 'data': '{nGet|df_{$tName}_form:form}', 'no_redirect': '".(int)$noRedirect."', 'is_modal': '{$isModal}', 'cmodule': '{$cModule}', 'cmethod': '{$cMethod}', 'ctarget': '{$cTarget}', 'target': '{$fTagId}' } }",$fResponseTarget)],
            ],
        ];
        if($params->safeGet('back_action',TRUE,'bool')) {
            if($isModal) {
                $ctrl_params['actions'][]=[
                    'type'=>'CloseModal',
                    'params'=>['tag_id'=>'df_'.$tName.'_cancel','value'=>Translate::GetButton('cancel'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-ban'],
                ];
            } else {
                $ctrl_params['actions'][]=[
                    'params'=>['tag_id'=>'df_'.$tName.'_back','value'=>Translate::GetButton('back'),'icon'=>'fa fa-chevron-left','class'=>NApp::$theme->GetBtnDefaultClass(),'onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$cModule}', 'method': '{$cMethod}', 'params': { 'id_template': {$idTemplate}, 'id': {$idInstance}, 'target': '{$cTarget}' } }",$cTarget)],
                ];
            }//if($isModal)
        }//if($params->safeGet('back_action',TRUE,'bool'))
    }//if(strlen($fTagId) && strlen($fResponseTarget))
}//if($actionsLocation=='form' && isset($ctrl_params) && is_array($ctrl_params))