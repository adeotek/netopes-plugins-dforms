<?php
$positions = [];
for($i=0;$i<$maxPos;$i++) { $positions[] = ['val'=>$i,'name'=>Translate::GetLabel('page').' '.($i+1)]; }
$positions[] = ['val'=>$maxPos,'name'=>Translate::GetLabel('end')];
$ctrl_params = [
    'tagid'=>'dft_add_page_form',
    'response_target'=>'dft_add_page_errors',
    'colsno'=>1,
    'content'=>[
        [
            [
                'control_type'=>'ComboBox',
                'control_params'=>['tagid'=>'dft_add_page_position','value'=>$positions,'label'=>Translate::GetLabel('position'),'label_cols'=>4,'required'=>TRUE,'valfield'=>'val','displayfield'=>'name','selectedvalue'=>$maxPos],
            ],
        ],
    ],
    'actions'=>[
        [
            'params'=>['tagid'=>'dft_add_page_save','value'=>Translate::GetButton('add_page'),'icon'=>'fa fa-plus-circle','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','UpdatePagesList','id_template'|{$idTemplate}~'type'|0~'close'|1~'pindex'|dft_add_page_position:value,'{$target}')->dft_add_page_errors")],
        ],
    ],
];