<?php
use NETopes\Core\Data\DataProvider;

$ctrl_params=[
    'tag_id'=>'df_template_add_form',
    'response_target'=>'df_template_add_errors',
    'cols_no'=>1,
    'label_cols'=>4,
    'content'=>[
        [
            [
                'control_type'=>'NumericTextBox',
                'control_params'=>['tag_id'=>'df_template_add_code','tag_name'=>'code','value'=>0,'label'=>Translate::GetLabel('code').' ('.Translate::GetLabel('numeric').')','align'=>'center','number_format'=>'0|||','onenter_button'=>'df_template_add_save','required'=>TRUE],
            ],
        ],
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_template_add_name','tag_name'=>'name','value'=>'','label'=>Translate::GetLabel('name'),'onenter_button'=>'df_template_add_save','required'=>TRUE],
            ],
        ],
        [
            [
                'control_type'=>'ComboBox',
                'control_params'=>['tag_id'=>'df_template_add_ftype','tag_name'=>'ftype','value'=>DataProvider::GetKeyValue('_Custom\DFormsOffline','GetDynamicFormsTemplatesFTypes'),'label'=>Translate::GetLabel('type'),'value_field'=>'id','display_field'=>'name','selected_value'=>NULL,'required'=>TRUE],
            ],
        ],
        [
            [
                'control_type'=>'CheckBox',
                'control_params'=>['tag_id'=>'df_template_add_state','tag_name'=>'state','value'=>1,'label'=>Translate::GetLabel('active'),'class'=>'pull-left'],
            ],
        ],
        ['separator'=>'line'],
        [
            [
                'control_type'=>'NumericTextBox',
                'control_params'=>['tag_id'=>'df_template_add_colsno','tag_name'=>'colsno','value'=>1,'label'=>Translate::GetLabel('columns_no'),'align'=>'center','number_format'=>'0|||','required'=>TRUE],
            ],
        ],
        [
            [
                'control_type'=>'CheckBox',
                'control_params'=>['tag_id'=>'df_template_add_dmode','tag_name'=>'dmode','value'=>0,'label'=>Translate::GetLabel('hard_delete'),'class'=>'pull-left'],
            ],
        ],
    ],
    'actions'=>[
        [
            'params'=>['value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->name}', 'method': 'AddRecord', 'params': { 'form_id': 'df_template_add_form' }, 'arrayParams': [ '{nGet|df_template_add_form:form}' ] }",'df_template_add_errors')],
        ],
        [
            'type'=>'CloseModal',
            'params'=>['value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'],
        ],
    ],
];