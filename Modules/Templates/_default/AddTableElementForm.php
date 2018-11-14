<?php
$positions = [];
for($i=1;$i<$maxPos;$i++) { $positions[] = ['val'=>$i,'name'=>Translate::GetLabel(''.$type).' '.$i]; }
$positions[] = ['val'=>$maxPos,'name'=>Translate::GetLabel('end')];
$ctrl_params = [
	'tagid'=>'dft_add_element_form',
	'response_target'=>'dft_add_element_errors',
	'colsno'=>1,
	'content'=>[
		[
			[
				'control_type'=>'ComboBox',
				'control_params'=>['tagid'=>'dft_add_element_position','value'=>$positions,'label'=>Translate::GetLabel('position'),'label_cols'=>4,'required'=>TRUE,'valfield'=>'val','displayfield'=>'name','selectedvalue'=>$maxPos],
			],
		],
	],
	'actions'=>[
		[
			'params'=>['tagid'=>'dft_add_element_save','value'=>Translate::GetButton('add_'.$type),'icon'=>'fa fa-plus-circle','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','UpdateContentTable','id_template'|{$idTemplate}~'pindex'|'{$pindex}'~'type'|0~'close'|1~'{$type}sno'|dft_add_element_position:value,'{$target}')->dft_add_element_errors")],
		],
	],
];
