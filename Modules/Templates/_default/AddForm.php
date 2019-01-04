<?php
	$ctrl_params = array(
		'tag_id'=>'df_template_add_form',
		'response_target'=>'df_template_add_errors',
		'colsno'=>1,
		'content'=>array(
			array(
				array(
					'width'=>'600',
					'control_type'=>'NumericTextBox',
					'control_params'=>array('container'=>'simpletable','tag_id'=>'df_template_add_code','value'=>0,'label'=>Translate::GetLabel('code').' ('.Translate::GetLabel('numeric').')','labelwidth'=>200,'width'=>200,'align'=>'center','number_format'=>'0|||','onenter_button'=>'df_template_add_save','required'=>TRUE),
				),
			),
			array(
				array(
					'width'=>'600',
					'control_type'=>'TextBox',
					'control_params'=>array('container'=>'simpletable','tag_id'=>'df_template_add_name','value'=>'','label'=>Translate::GetLabel('name'),'labelwidth'=>200,'width'=>400,'onenter_button'=>'df_template_add_save','required'=>TRUE),
				),
			),
			array(
				array(
					'width'=>'600',
					'control_type'=>'ComboBox',
					'control_params'=>array('container'=>'simpletable','tag_id'=>'df_template_add_ftype','value'=>$ftypes,'label'=>Translate::GetLabel('type'),'labelwidth'=>200,'width'=>400,'value_field'=>'id','display_field'=>'name','selectedvalue'=>NULL,'required'=>TRUE),
				),
			),
			array(
				array(
					'width'=>'600',
					'control_type'=>'CheckBox',
					'control_params'=>array('container'=>'simpletable','tag_id'=>'df_template_add_state','value'=>1,'label'=>Translate::GetLabel('active'),'labelwidth'=>200,'class'=>'pull-left'),
				),
			),
			array('separator'=>'line'),
			array(
				array(
					'width'=>'600',
					'control_type'=>'NumericTextBox',
					'control_params'=>array('container'=>'simpletable','tag_id'=>'df_template_add_colsno','value'=>1,'label'=>Translate::GetLabel('columns_no'),'labelwidth'=>200,'width'=>100,'align'=>'center','number_format'=>'0|||','required'=>TRUE),
				),
			),
			array(
				array(
					'width'=>'600',
					'control_type'=>'CheckBox',
					'control_params'=>array('container'=>'simpletable','tag_id'=>'df_template_add_dmode','value'=>0,'label'=>Translate::GetLabel('hard_delete'),'labelwidth'=>200,'class'=>'pull-left'),
				),
			),
			array(
				array(
					'width'=>'600',
					'control_type'=>'TextBox',
					'control_params'=>array('container'=>'simpletable','tag_id'=>'df_template_add_iso_code','value'=>'','label'=>Translate::GetLabel('iso_code'),'labelwidth'=>200,'width'=>400,'onenter_button'=>'df_template_add_save'),
				),
			),
			// array('separator'=>'line'),
			// array(
			// 	array(
			// 		'width'=>'600',
			// 		'control_type'=>'CkEditor',
			// 		'control_params'=>array('container'=>'simpletable','tag_id'=>'df_template_add_print_template','value'=>'','label'=>Translate::GetLabel('print_template'),'labelwidth'=>200,'labelposition'=>'top','width'=>600,'height'=>100,'extra_config'=>'toolbarStartupExpanded: false'),
			// 	),
			// ),
		),
		'actions'=>array(
			array(
				'params'=>array('tag_id'=>'df_template_add_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','AddEditRecord',
					'id'|''
					~'code'|df_template_add_code:value
					~'name'|df_template_add_name:value
					~'ftype'|df_template_add_ftype:value
					~'state'|df_template_add_state:value
					~'colsno'|df_template_add_colsno:value
					~'dmode'|df_template_add_dmode:value
					~'iso_code'|df_template_add_iso_code:value
				,'df_template_add_form')->df_template_add_errors")),
			),//~'print_template'|GetCkEditorData('df_template_add_print_template')
			array(
				'type'=>'CloseModal',
				'params'=>array('tag_id'=>'df_template_add_cancel','value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'),
			),
		),
	);
?>