<?php
use NETopes\Core\Controls\HiddenInput;
/**
 * @var int $idTemplate
 * @var int $idControl
 * @var int $fRow
 * @var int $fCol
 * @var int $colsNo
 * @var string $cClass
 * @var string $cDataType
 * @var \NETopes\Core\Data\VirtualEntity $fieldType
 * @var \NETopes\Core\Data\VirtualEntity $item
 */
$hFields = [];
switch($cClass) {
    case 'Message':
    case 'FormTitle':
    case 'FormSeparator':
    case 'FormSubTitle':
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_itype','tag_name'=>'itype','value'=>$item->getProperty('itype',1,'is_integer')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_name','tag_name'=>'name','value'=>$item->getProperty('name',\NETopes\Core\AppSession::GetNewUID($idTemplate.$cClass),'is_string')]);
        $hFields[] = $ctrl->Show();
        if($cClass=='FormSeparator') {
            $ctrl = new HiddenInput(['tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label',$cClass,'is_string')]);
            $hFields[] = $ctrl->Show();
        }//if($cClass=='FormTitle')
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_required','tag_name'=>'required','value'=>$item->getProperty('required',0,'is_integer')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_listing','tag_name'=>'listing','value'=>$item->getProperty('listing',0,'is_integer')]);
        $hFields[] = $ctrl->Show();
        break;
    case 'BasicForm':
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_name','tag_name'=>'name','value'=>$item->getProperty('name',NULL,'is_string')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label',$cClass,'is_string')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_required','tag_name'=>'required','value'=>$item->getProperty('required',0,'is_integer')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_listing','tag_name'=>'listing','value'=>$item->getProperty('listing',0,'is_integer')]);
        $hFields[] = $ctrl->Show();
        break;
}//END switch

$ctrl_params = [
    'tag_id'=>'dft_fp_form',
    'cols_no'=>1,
    'label_cols'=>4,
    'content'=>[
        (in_array($cClass,['Message','FormTitle','FormSubTitle','FormSeparator']) ? [] : [
            [
                'control_type'=>'SmartComboBox',
                'control_params'=>['label'=>Translate::GetLabel('field_type'),'tag_id'=>'dft_fp_itype','tag_name'=>'itype',
            'value_field'=>'id',
            'display_field'=>'name',
            'selected_value'=>$item->getProperty('itype',1,'is_numeric'),
            'allow_clear'=>FALSE,
            'load_type'=>'database',
            'data_source'=>[
                'ds_class'=>'_Custom\DFormsOffline',
                'ds_method'=>'GetDynamicFormsFieldsITypes',
            ],
                ],
            ],
        ]),
        (in_array($cClass,['Message','FormTitle','FormSubTitle','FormSeparator','BasicForm']) ? [] : [
            [
                'control_type'=>'TextBox',
                'control_params'=>['label'=>Translate::GetLabel('field_name'),'required'=>TRUE,'tag_id'=>'dft_fp_name','tag_name'=>'name','value'=>$item->getProperty('name','','is_string'),'placeholder'=>'Field name (no spaces)'],
            ],
        ]),
        (in_array($cClass,['Message','FormTitle','FormSubTitle','FormSeparator','BasicForm']) ? [] : [
            [
                'control_type'=>'TextBox',
                'control_params'=>['label'=>Translate::GetLabel('field_label'),'required'=>TRUE,'tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label','','is_string'),'placeholder'=>'Field displayed label'],
            ],
        ]),
        (in_array($cClass,['Message','FormTitle','FormSubTitle','FormSeparator','BasicForm']) ? [] : [
            [
                'control_type'=>'CheckBox',
                'control_params'=>['label'=>Translate::GetLabel('required_field'),'tag_id'=>'dft_fp_required','tag_name'=>'required','value'=>$item->getProperty('required',0,'is_integer'),'class'=>'pull-left'],
            ],
        ]),
        (in_array($cClass,['Message','FormTitle','FormSubTitle','FormSeparator','BasicForm']) ? [] : [
            [
                'control_type'=>'CheckBox',
                'control_params'=>['label'=>Translate::GetLabel('show_in_listing'),'tag_id'=>'dft_fp_listing','tag_name'=>'listing','value'=>$item->getProperty('listing',0,'is_integer'),'class'=>'pull-left'],
            ],
        ]),
        (in_array($cClass,['Message','FormTitle','FormSubTitle','FormSeparator','BasicForm']) || $colsNo<=1 ? [] : [
            [
                'control_type'=>'NumericTextBox',
                'control_params'=>['label'=>Translate::GetLabel('column_span'),'tag_id'=>'dft_fp_colspan','tag_name'=>'colspan','value'=>$item->getProperty('colspan',1,'is_integer'),'align'=>'center','number_format'=>'0|||'],
            ],
        ]),
        (!in_array($cClass,['SmartComboBox','GroupCheckBox']) ? [] : [
            [
                'control_type'=>'SmartComboBox',
                'control_params'=>['label'=>Translate::GetLabel('form'),'tag_id'=>'dft_fp_id_sub_form','tag_name'=>'id_sub_form','required'=>TRUE,'disabled'=>(is_numeric($id) && $id>0),
            'value_field'=>'id',
                    'display_field'=>'full_name',
                    'selected_value'=>$item->getProperty('id_sub_form',1,'is_integer'),
            'allow_clear'=>TRUE,
                    'placeholder'=>Translate::GetLabel('please_select_template'),
            'load_type'=>'database',
            'data_source'=>[
                        'ds_class'=>'Plugins\DForms\Templates',
                'ds_method'=>'GetItems',
                        'ds_params'=>['for_id'=>NULL,'for_validated'=>1,'for_state'=>1,'for_text'=>NULL,'for_ftype'=>1,'exclude_id'=>$idTemplate],
                    ],
                ],
            ],
        ]),
        ($cClass!='FormTitle' ? [] : [
        [
                'control_type'=>'EditBox',
                'control_params'=>['label'=>Translate::GetLabel('title'),'required'=>TRUE,'tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label','','is_string'),'height'=>50,'placeholder'=>'Title'],
            ],
        ]),
        ($cClass!='FormSubTitle' ? [] : [
            [
                'control_type'=>'EditBox',
                'control_params'=>['label'=>Translate::GetLabel('sub_title'),'required'=>TRUE,'tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label','','is_string'),'height'=>50,'placeholder'=>'Sub-title'],
            ],
        ]),
        ($cClass!='Message' ? [] : [
            [
                'control_type'=>'TextBox',
                'control_params'=>['label'=>Translate::GetLabel('short_text').' (max: 255)','tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label','','is_string'),'placeholder'=>'Field displayed label'],
            ],
        ]),
        ($cClass!='Message' ? [] : [
            [
                'control_type'=>'EditBox',
                'control_params'=>['label'=>Translate::GetLabel('long_text'),'tag_id'=>'dft_fp_description','tag_name'=>'description','value'=>$item->getProperty('description','','is_string'),'height'=>50],
        ],
        ]),
        [
        [
            'control_type'=>'CustomControl',
            'control_params'=>['value'=>implode("\n",$hFields)],
        ],
        ],
    ],
];