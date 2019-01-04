<?php
	$ctrl_params = array(
		'tag_id'=>'df_template_rel_ae_form',
		'response_target'=>'df_template_rel_ae_errors',
		'colsno'=>1,
		'content'=>array(
			array(
				array(
					'control_type'=>'SmartComboBox',
					'control_params'=>array('tag_id'=>'df_template_rel_ae_type','label'=>Translate::GetLabel('type'),'required'=>TRUE,'disabled'=>(is_numeric($id) && $id>0),
						'value_field'=>'id',
						'display_field'=>'name',
						'selectedvalue'=>get_array_param($item,'id_relation_type',NULL,'is_numeric'),
						'allow_clear'=>TRUE,
						'placeholder'=>Translate::GetLabel('please_select'),
						'load_type'=>'database',
						'data_source'=>array(
							'ds_class'=>'Components\DForms\Templates',
							'ds_method'=>'GetRelationsTypes',
							'ds_extra_params'=>array('sort'=>array('NAME'=>'ASC')),
						),
					),
				),
			),
			array(
				array(
					'control_type'=>'TextBox',
					'control_params'=>array('tag_id'=>'df_template_rel_ae_name','value'=>get_array_param($item,'name','','is_string'),'label'=>Translate::GetLabel('name'),'required'=>TRUE,'onenter_button'=>'df_template_rel_ae_save'),
				),
			),
			array(
				array(
					'control_type'=>'TextBox',
					'control_params'=>array('tag_id'=>'df_template_rel_ae_key','value'=>get_array_param($item,'key','','is_string'),'label'=>Translate::GetLabel('key'),'required'=>TRUE,'onenter_button'=>'df_template_rel_ae_save'),
				),
			),
			array(
				array(
					'control_type'=>'SmartComboBox',
					'control_params'=>array('tag_id'=>'df_template_rel_ae_rtype','label'=>Translate::GetLabel('value_type'),'required'=>TRUE,
						'value_field'=>'id',
						'display_field'=>'name',
						'selectedvalue'=>get_array_param($item,'rtype',NULL,'is_numeric'),
						'load_type'=>'database',
						'data_source'=>array(
							'ds_class'=>'_Custom\Offline',
							'ds_method'=>'GetDynamicFormsRelationsRTypes',
						),
					),
				),
			),
			array(
				array(
					'control_type'=>'SmartComboBox',
					'control_params'=>array('tag_id'=>'df_template_rel_ae_utype','label'=>Translate::GetLabel('usage_type'),'required'=>TRUE,
						'value_field'=>'id',
						'display_field'=>'name',
						'selectedvalue'=>get_array_param($item,'utype',NULL,'is_numeric'),
						'load_type'=>'database',
						'data_source'=>array(
							'ds_class'=>'_Custom\Offline',
							'ds_method'=>'GetDynamicFormsRelationsUTypes',
						),
					),
				),
			),
			array(
				array(
					'control_type'=>'CheckBox',
					'control_params'=>array('tag_id'=>'df_template_rel_ae_required','value'=>get_array_param($item,'required',0,'is_numeric'),'label'=>Translate::GetLabel('required'),'class'=>'pull-left'),
				),
			),
		),
		'actions'=>array(
			array(
				'params'=>array('tag_id'=>'df_template_rel_ae_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','AddEditRelationRecord',
					'id_template'|{$id_template}
					~'id'|'{$id}'
					~'type'|df_template_rel_ae_type:value
					~'rtype'|df_template_rel_ae_rtype:value
					~'utype'|df_template_rel_ae_utype:value
					~'name'|df_template_rel_ae_name:value
					~'key'|df_template_rel_ae_key:value
					~'required'|df_template_rel_ae_required:value
					~'ctarget'|'{$target}'
				,'df_template_rel_ae_form')->df_template_rel_ae_errors")),
			),
			array(
				'type'=>'CloseModal',
				'params'=>array('tag_id'=>'df_template_rel_ae_cancel','value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'),
			),
		),
	);
?>