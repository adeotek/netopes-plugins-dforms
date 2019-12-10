<?php
/** @var \NETopes\Core\Data\IEntity $item */
/** @var int $templateId */
$pagesNo=$item->getProperty('pagesno',1,'is_integer');
$ctrl_params=[
    'tag_id'=>'df_template_design_edit_form',
    'response_target'=>'df_template_design_edit_errors',
    'cols_no'=>1,
    'content'=>[
        [
            [
                'control_type'=>'SmartComboBox',
                'control_params'=>['tag_id'=>'df_template_design_edit_render_type','label'=>Translate::GetLabel('render_type'),
                    'tag_name'=>'render_type',
                    'required'=>TRUE,
                    'value_field'=>'id',
                    'display_field'=>'name',
                    'selected_value'=>$item->getProperty('render_type',1,'is_integer'),
                    'state_field'=>'state',
                    'minimum_input_length'=>0,
                    'load_type'=>'database',
                    'data_source'=>[
                        'ds_class'=>'_Custom\DFormsOffline',
                        'ds_method'=>'GetDynamicFormsDesignRenderTypes',
                        'ds_params'=>['for_type'=>$pagesNo>1 ? 2 : 1],
                    ],
                ],
            ],
        ],
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_template_design_edit_theme_type','tag_name'=>'theme_type','value'=>$item->getProperty('theme_type','','is_string'),'label'=>Translate::GetLabel('theme_type'),'onenter_button'=>'df_template_design_edit_save'],
            ],
        ],
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_template_design_edit_controls_size','tag_name'=>'controls_size','value'=>$item->getProperty('controls_size','','is_string'),'label'=>Translate::GetLabel('controls_size'),'onenter_button'=>'df_template_design_edit_save'],
            ],
        ],
        [
            [
                'control_type'=>'NumericTextBox',
                'control_params'=>['tag_id'=>'df_template_design_edit_label_cols','tag_name'=>'label_cols','value'=>$item->getProperty('label_cols','','is_integer'),'label'=>Translate::GetLabel('label_cols'),'allownull'=>TRUE,'number_format'=>'0|||','onenter_button'=>'df_template_design_edit_save'],
            ],
        ],
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_template_design_edit_separator_width','tag_name'=>'separator_width','value'=>$item->getProperty('separator_width','','is_string'),'label'=>Translate::GetLabel('separator_width'),'onenter_button'=>'df_template_design_edit_save'],
            ],
        ],
        ['separator'=>'line'],
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_template_design_edit_iso_code','tag_name'=>'iso_code','value'=>$item->getProperty('iso_code','','is_string'),'label'=>Translate::GetLabel('iso_code'),'onenter_button'=>'df_template_design_edit_save'],
            ],
        ],
    ],
    'actions'=>[
        [
            'params'=>['tag_id'=>'df_template_design_edit_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->name}', 'method': 'EditDesignRecord', 'params': { 'id_template': '{$templateId}', 'form_id': 'df_template_design_edit_form' }, 'arrayParams': [ '{nGet|df_template_design_edit_form:form}' ] }",'df_template_design_edit_errors')],
        ],
    ],
];