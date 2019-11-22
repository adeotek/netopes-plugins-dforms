<?php
$positions=[];
for($i=1; $i<$maxPos; $i++) {
    $positions[]=['val'=>$i,'name'=>Translate::GetLabel(''.$type).' '.$i];
}
$positions[]=['val'=>$maxPos,'name'=>Translate::GetLabel('end')];
$ctrl_params=[
    'tag_id'=>'dft_add_element_form',
    'response_target'=>'dft_add_element_errors',
    'cols_no'=>1,
    'content'=>[
        [
            [
                'control_type'=>'ComboBox',
                'control_params'=>['tag_id'=>'dft_add_element_position','value'=>$positions,'label'=>Translate::GetLabel('position'),'label_cols'=>4,'required'=>TRUE,'value_field'=>'val','display_field'=>'name','selected_value'=>$maxPos],
            ],
        ],
    ],
    'actions'=>[
        [
            'params'=>['value'=>Translate::GetButton('add_'.$type),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','UpdateContentTable','id_template'|{$idTemplate}~'pindex'|'{$pIndex}'~'type'|0~'close'|1~'{$type}sno'|dft_add_element_position:value~'c_target'|'{$cTarget}','{$target}')->dft_add_element_errors")],
        ],
    ],
];
