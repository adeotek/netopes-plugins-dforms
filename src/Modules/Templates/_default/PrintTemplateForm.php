<?php
/** @var int $templateId */
/** @var \NETopes\Core\Data\IEntity $item */
$ctrl_params=[
    'tag_id'=>'df_template_edit_print_template_form',
    'response_target'=>'df_template_edit_print_template_errors',
    'cols_no'=>1,
    'content'=>[
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_template_edit_print_template_page_orientation','value'=>$item->getProperty('print_page_orientation','L','is_string'),'label'=>Translate::GetLabel('page_orientation'),'align'=>'center','cols'=>1],
            ],
        ],
        [
            [
                'control_type'=>'CkEditor',
                'control_params'=>['tag_id'=>'df_template_edit_print_template_value','value'=>$item->getProperty('print_template','','is_string'),'label'=>Translate::GetLabel('print_template'),'label_position'=>'top','width'=>'100%','height'=>600,'extra_config'=>'toolbarStartupExpanded: true'],
            ],
        ],
    ],
    'actions'=>[
        [
            'params'=>['value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','SetPrintTemplate',
                'id_template'|'{$templateId}'
                ~'print_page_orientation'|df_template_edit_print_template_page_orientation:value
                ~'print_template'|GetCkEditorData('df_template_edit_print_template_value')
            ,'df_template_edit_print_template_form')->df_template_edit_print_template_errors")],
        ],
    ],
];