<?php
/**
 * @var string      $tName
 * @var string      $fTagId
 * @var int         $idInstance
 * @var string|null $cModule
 * @var string|null $cMethod
 * @var string|null $cTarget
 * @var string      $aeSaveInstanceMethod
 * @var bool|null   $noRedirect
 */

if($this->actionsLocation=='form' && isset($ctrl_params) && is_array($ctrl_params)) {
    $fResponseTarget=get_array_value($ctrl_params,'response_target','','is_string');
    if(strlen($fTagId) && strlen($fResponseTarget)) {
        $ctrl_params['actions']=[
            [
                'params'=>['tag_id'=>'df_'.$tName.'_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','class'=>NApp::$theme->GetBtnPrimaryClass(),'onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': '{$aeSaveInstanceMethod}', 'params': { 'id_template': '{$this->templateId}', 'id': '{$idInstance}', 'relations': '{nGet|df_{$tName}_relations:form}', 'data': '{nGet|{$fTagId}:form}', 'no_redirect': '".(int)$noRedirect."', 'is_modal': '{$this->formsAsModal}', 'c_module': '{$cModule}', 'c_method': '{$cMethod}', 'c_target': '{$cTarget}', 'form_id': '{$fTagId}' } }",$fResponseTarget)],
            ],
        ];
        if($params->safeGet('back_action',TRUE,'bool')) {
            if($this->formsAsModal) {
                $ctrl_params['actions'][]=[
                    'type'=>'CloseModal',
                    'params'=>['value'=>Translate::GetButton('cancel'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-ban'],
                ];
            } else {
                $ctrl_params['actions'][]=[
                    'params'=>['value'=>Translate::GetButton('back'),'icon'=>'fa fa-chevron-left','class'=>NApp::$theme->GetBtnDefaultClass(),'onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$cModule}', 'method': '{$cMethod}', 'params': { 'id_template': {$this->templateId}, 'id': {$idInstance}, 'target': '{$cTarget}' } }",$cTarget)],
                ];
            }//if($this->formsAsModal)
        }//if($params->safeGet('back_action',TRUE,'bool'))
    }//if(strlen($fTagId) && strlen($fResponseTarget))
}//if($this->actionsLocation=='form' && isset($ctrl_params) && is_array($ctrl_params))