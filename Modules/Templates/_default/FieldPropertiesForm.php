<?php
use NETopes\Core\Controls\CheckBox;
use NETopes\Core\Controls\EditBox;
use NETopes\Core\Controls\HiddenInput;
use NETopes\Core\Controls\SmartComboBox;
use NETopes\Core\Controls\TextBox;

/**
 * @var int $idTemplate
 * @var int $idControl
 * @var int $fRow
 * @var int $fCol
 * @var string $cClass
 * @var string $cDataType
 * @var \NETopes\Core\Data\VirtualEntity $fieldType
 * @var \NETopes\Core\Data\VirtualEntity $item
 */
$vFields = [];
$hFields = [];
switch($cClass) {
    case 'FormTitle':
    case 'FormSeparator':
    case 'FormSubTitle':
    case 'Message':
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_itype','tag_name'=>'itype','value'=>$item->getProperty('itype',1,'is_integer')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_name','tag_name'=>'name','value'=>$item->getProperty('name',\NETopes\Core\AppSession::GetNewUID($idTemplate.$cClass),'is_string')]);
        $hFields[] = $ctrl->Show();
        if($cClass=='FormTitle') {
            $ctrl = new EditBox(['label'=>Translate::GetLabel('title'),'required'=>TRUE,'tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label','','is_string'),'height'=>50,'placeholder'=>'Title']);
            $vFields[] = $ctrl->Show();
        } elseif($cClass=='FormSubTitle') {
            $ctrl = new EditBox(['label'=>Translate::GetLabel('sub_title'),'required'=>TRUE,'tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label','','is_string'),'height'=>50,'placeholder'=>'Sub-title']);
            $vFields[] = $ctrl->Show();
        } elseif($cClass=='Message') {
            $ctrl = new TextBox(['label'=>Translate::GetLabel('short_text').' (max: 255)','tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label','','is_string'),'placeholder'=>'Field displayed label']);
            $vFields[] = $ctrl->Show();
            $ctrl = new EditBox(['label'=>Translate::GetLabel('long_text'),'tag_id'=>'dft_fp_description','tag_name'=>'description','value'=>$item->getProperty('description','','is_string'),'height'=>50]);
            $vFields[] = $ctrl->Show();
        } else {
            $ctrl = new HiddenInput(['tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label',$cClass,'is_string')]);
            $hFields[] = $ctrl->Show();
        }//if($cClass=='FormTitle')
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_required','tag_name'=>'required','value'=>$item->getProperty('required',0,'is_integer')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_listing','tag_name'=>'listing','value'=>$item->getProperty('listing',0,'is_integer')]);
        $hFields[] = $ctrl->Show();
        break;
    case 'BasicForm':
        $ctrl = new SmartComboBox(['label'=>Translate::GetLabel('field_type'),'tag_id'=>'dft_fp_itype','tag_name'=>'itype',
            'value_field'=>'id',
            'display_field'=>'name',
            'selected_value'=>$item->getProperty('itype',1,'is_integer'),
            'allow_clear'=>FALSE,
            'load_type'=>'database',
            'data_source'=>[
                'ds_class'=>'_Custom\DFormsOffline',
                'ds_method'=>'GetDynamicFormsFieldsITypes',
            ],
        ]);
        $vFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_name','tag_name'=>'name','value'=>$item->getProperty('name',NULL,'is_string')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label',$cClass,'is_string')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_required','tag_name'=>'required','value'=>$item->getProperty('required',0,'is_integer')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new HiddenInput(['tag_id'=>'dft_fp_listing','tag_name'=>'listing','value'=>$item->getProperty('listing',0,'is_integer')]);
        $hFields[] = $ctrl->Show();
        $ctrl = new SmartComboBox(['label'=>Translate::GetLabel('form'),'tag_id'=>'dft_fp_id_sub_form','tag_name'=>'id_sub_form','required'=>TRUE,'disabled'=>(is_numeric($id) && $id>0),
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
        ]);
        $vFields[] = $ctrl->Show();
        break;
    default:
        $ctrl = new SmartComboBox(['label'=>Translate::GetLabel('field_type'),'tag_id'=>'dft_fp_itype','tag_name'=>'itype',
            'value_field'=>'id',
            'display_field'=>'name',
            'selected_value'=>$item->getProperty('itype',1,'is_numeric'),
            'allow_clear'=>FALSE,
            'load_type'=>'database',
            'data_source'=>[
                'ds_class'=>'_Custom\DFormsOffline',
                'ds_method'=>'GetDynamicFormsFieldsITypes',
            ],
        ]);
        $vFields[] = $ctrl->Show();
        $ctrl = new TextBox(['label'=>Translate::GetLabel('field_name'),'required'=>TRUE,'tag_id'=>'dft_fp_name','tag_name'=>'name','value'=>$item->getProperty('name','','is_string'),'placeholder'=>'Field name (no spaces)']);
        $vFields[] = $ctrl->Show();
        $ctrl = new TextBox(['label'=>Translate::GetLabel('field_label'),'required'=>TRUE,'tag_id'=>'dft_fp_label','tag_name'=>'label','value'=>$item->getProperty('label','','is_string'),'placeholder'=>'Field displayed label']);
        $vFields[] = $ctrl->Show();
        $ctrl = new CheckBox(['label'=>Translate::GetLabel('required_field'),'tag_id'=>'dft_fp_required','tag_name'=>'required','value'=>$item->getProperty('required',0,'is_integer'),'class'=>'pull-left']);
        $vFields[] = $ctrl->Show();
        $ctrl = new CheckBox(['label'=>Translate::GetLabel('show_in_listing'),'tag_id'=>'dft_fp_listing','tag_name'=>'listing','value'=>$item->getProperty('listing',0,'is_integer'),'class'=>'pull-left']);
        $vFields[] = $ctrl->Show();
        if(in_array($cClass,['SmartComboBox','GroupCheckBox'])) {
            $ctrl = new SmartComboBox(['label'=>Translate::GetLabel('values_list'),'tag_id'=>'dft_fp_values_list','tag_name'=>'id_values_list',
            'placeholder'=>Translate::GetLabel('none'),
            'value_field'=>'id',
            'display_field'=>'name',
            'selected_value'=>$item->getProperty('id_values_list',NULL,'is_string'),
            'selected_text'=>$item->getProperty('values_list','','is_string'),
            'allow_clear'=>TRUE,
            'load_type'=>'database',
            'data_source'=>[
                'ds_class'=>'Plugins\DForms\ValuesLists',
                'ds_method'=>'GetItems',
                'ds_params'=>['for_id'=>NULL,'for_state'=>1,'for_text'=>NULL],
            ]]);
            $vFields[] = $ctrl->Show();
        }//if(in_array($cClass,['SmartComboBox']))
        break;
}//END switch

$ctrl_params = [
    'tag_id'=>'dft_fp_form',
    'cols_no'=>1,
    'label_cols'=>4,
    'content'=>[],
];
foreach($vFields as $f) {
    $ctrl_params['content'][] = [
        [
            'control_type'=>'CustomControl',
            'control_params'=>['value'=>$f],
        ],
    ];
}//END foreach
if(count($hFields)) {
    $ctrl_params['content'][] = [
        [
            'control_type'=>'CustomControl',
            'control_params'=>['value'=>implode("\n",$hFields)],
        ],
    ];
}//if(count($hFields))