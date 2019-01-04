<?php
$pagesNo = $item->getProperty('pagesno',1,'is_integer');
$ctrl_params = array(
    'tag_id'=>'df_template_design_edit_form',
    'response_target'=>'df_template_design_edit_errors',
    'colsno'=>1,
    'content'=>array(
        array(
            array(
                'control_type'=>'SmartComboBox',
                'control_params'=>array('tag_id'=>'df_template_design_edit_render_type','label'=>Translate::GetLabel('render_type'),
                    'tag_name'=>'render_type',
                    'required'=>TRUE,
                    'value_field'=>'id',
                    'display_field'=>'name',
                    'selectedvalue'=>$item->getProperty('render_type',1,'is_integer'),
                    'state_field'=>'state',
                    'load_type'=>'database',
                    'data_source'=>array(
                        'ds_class'=>'_Custom\Offline',
                        'ds_method'=>'GetDynamicFormsDesignRenderTypes',
                        'ds_params'=>['for_type'=>$pagesNo>1 ? 2 : 1],
                    ),
                ),
            ),
        ),
        array(
            array(
                'control_type'=>'TextBox',
                'control_params'=>array('tag_id'=>'df_template_design_edit_theme_type','tag_name'=>'theme_type','value'=>$item->getProperty('theme_type','','is_string'),'label'=>Translate::GetLabel('theme_type'),'onenter_button'=>'df_template_design_edit_save'),
            ),
        ),
        array(
            array(
                'control_type'=>'TextBox',
                'control_params'=>array('tag_id'=>'df_template_design_edit_controls_size','tag_name'=>'controls_size','value'=>$item->getProperty('controls_size','','is_string'),'label'=>Translate::GetLabel('controls_size'),'onenter_button'=>'df_template_design_edit_save'),
            ),
        ),
        array(
            array(
                'control_type'=>'NumericTextBox',
                'control_params'=>array('tag_id'=>'df_template_design_edit_label_cols','tag_name'=>'label_cols','value'=>$item->getProperty('label_cols','','is_integer'),'label'=>Translate::GetLabel('label_cols'),'allow_null'=>TRUE,'number_format'=>'0|||','onenter_button'=>'df_template_design_edit_save'),
            ),
        ),
        array(
            array(
                'control_type'=>'TextBox',
                'control_params'=>array('tag_id'=>'df_template_design_edit_separator_width','tag_name'=>'separator_width','value'=>$item->getProperty('separator_width','','is_string'),'label'=>Translate::GetLabel('separator_width'),'onenter_button'=>'df_template_design_edit_save'),
            ),
        ),
    ),
    'actions'=>array(
        array(
            'params'=>array('tag_id'=>'df_template_design_edit_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','EditDesignRecord','id_template'|{$idTemplate}~df_template_design_edit_form:form,'df_template_design_edit_form')->df_template_design_edit_errors")),
        ),
    ),
);