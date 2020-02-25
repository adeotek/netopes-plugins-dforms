<?php
$ctrl_params=[
    'drights_uid'=>$this->module->dRightsUid ?? $this->module::DRIGHTS_UID,
    'tag_id'=>'df_list_add_form',
    'response_target'=>'df_list_add_errors',
    'cols_no'=>1,
    'content'=>[
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_list_add_ltype','tag_name'=>'ltype','value'=>'','label'=>Translate::GetLabel('code'),'required'=>TRUE,'onenter_button'=>'df_list_add_save'],
            ],
        ],
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_list_add_name','tag_name'=>'name','value'=>'','label'=>Translate::GetLabel('name'),'onenter_button'=>'df_list_add_save'],
            ],
        ],
        [
            [
                'control_type'=>'CheckBox',
                'control_params'=>['tag_id'=>'df_list_add_state','tag_name'=>'state','value'=>1,'label'=>Translate::GetLabel('active'),'class'=>'pull-left'],
            ],
        ],
    ],
    'actions'=>[
        [
            'params'=>['tag_id'=>'df_list_add_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'AddEditRecord', 'params': { 'target': 'df_list_add_form' }, 'arrayParams': [ '{nGet|df_list_add_form:form}' ] }",'df_list_add_errors')],
        ],
        [
            'type'=>'CloseModal',
            'params'=>['tag_id'=>'df_list_add_cancel','value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'],
        ],
    ],
];