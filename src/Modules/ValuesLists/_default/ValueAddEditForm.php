<?php
/** @var int $listId */
$ctrl_params=[
    'drights_uid'=>$this->module->dRightsUid ?? $this->module::DRIGHTS_UID,
    'tag_id'=>'df_lv_ae_form',
    'response_target'=>'df_lv_ae_errors',
    'cols_no'=>1,
    'content'=>[
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_lv_ae_value','tag_name'=>'value','value'=>$item->getProperty('value','','is_string'),'label'=>Translate::GetLabel('value'),'required'=>TRUE,'onenter_button'=>'df_lv_ae_save'],
            ],
        ],
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_lv_ae_name','tag_name'=>'name','value'=>$item->getProperty('name','','is_string'),'label'=>Translate::GetLabel('name'),'onenter_button'=>'df_lv_ae_save'],
            ],
        ],
        [
            [
                'control_type'=>'CheckBox',
                'control_params'=>['tag_id'=>'df_lv_ae_state','tag_name'=>'state','value'=>$item->getProperty('state',1,'is_numeric'),'label'=>Translate::GetLabel('active'),'class'=>'pull-left'],
            ],
        ],
        [
            [
                'control_type'=>'CheckBox',
                'control_params'=>['tag_id'=>'df_lv_ae_implicit','tag_name'=>'implicit','value'=>$item->getProperty('implicit',0,'is_numeric'),'label'=>Translate::GetLabel('implicit'),'class'=>'pull-left'],
            ],
        ],
    ],
    'actions'=>[
        [
            'params'=>['value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->Prepare("{ module: '{$this->name}', 'method': 'AddEditValueRecord', 'params': { 'id_list': '{$listId}', 'id': '{$id}', 'c_target': '{$target}', 'form_id': 'df_lv_ae_form' }, 'arrayParams': [ '{nGet|df_lv_ae_form:form}' ] }",'df_lv_ae_errors')],
        ],
        [
            'type'=>'CloseModal',
            'params'=>['value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'],
        ],
    ],
];