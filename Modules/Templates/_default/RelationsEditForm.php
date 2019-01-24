		<div class="col-md-12 actions-group">
<?php
	use NETopes\Core\Controls\Button;
	use NETopes\Core\Controls\TableView;

	$dgtarget = 'dg-'.$target;
	$btn_add = new Button(array('tagid'=>'df_trelations_add','value'=>Translate::GetButton('add'),'class'=>'btn btn-primary btn-xs pull-left','icon'=>'fa fa-plus-circle','onclick'=>NApp::arequest()->PrepareAjaxRequest(['module'=>'Plugins\DForms\Templates\Templates','method'=>'ShowRelationAddEditForm','target'=>'modal','params'=>['id_template'=>$id_template,'target'=>$target]])));
	echo $btn_add->Show();
?>
		</div>
		<div id="<?php echo $dgtarget; ?>">
<?php
	$ctrl_params = array(
		'module'=>$this->name,
		'method'=>$this->GetCurrentMethod(),
		'persistent_state'=>TRUE,
		'target'=>$dgtarget,
		'alternate_row_collor'=>TRUE,
		'scrollable'=>FALSE,
		'with_filter'=>TRUE,
		'with_pagination'=>TRUE,
		'sortby'=>array('column'=>'name','direction'=>'asc'),
		'qsearch'=>'for_text',
		'data_source'=>'Plugins\DForms\Templates',
		'ds_method'=>'GetRelations',
		'ds_params'=>array('for_id'=>NULL,'template_id'=>$id_template,'for_text'=>NULL),
		'auto_load_data'=>TRUE,
		'columns'=>array(
			'actions'=>array(
				'type'=>'actions',
				'visual_count'=>2,
				'actions'=>array(
					array(
						'type'=>'DivButton',
						'command_string'=>"AjaxRequest('{$this->class}','DeleteRelationRecord','id'|{{id}}~'id_template'|{{id_template}},'{$target}')->errors",
						'params'=>array('tagid'=>'df_list_delete_btn','tooltip'=>Translate::GetButton('delete'),'class'=>'btn btn-danger btn-xxs','icon'=>'fa fa-times','confirm_text'=>Translate::GetMessage('confirm_delete')),
					),
					array(
						'type'=>'DivButton',
						'command_string'=>"AjaxRequest('{$this->class}','ShowRelationAddEditForm','id'|{{id}}~'id_template'|{{id_template}},'{$target}')->modal",
						'params'=>array('tagid'=>'df_list_edit_btn','tooltip'=>Translate::GetButton('edit'),'class'=>'btn btn-primary btn-xxs','icon'=>'fa fa-pencil-square-o'),
					),
				),
			),
			'relation_type'=>array(
				'db_field'=>'relation_type',
				'data_type'=>'string',
				'type'=>'value',
				'halign'=>'center',
				'label'=>Translate::GetLabel('type'),
				'sortable'=>TRUE,
				'filterable'=>TRUE,
				// 'filter_type'=>'combobox',
				// 'show_filter_cond_type'=>FALSE,
				// 'filter_params'=>array('valfield'=>'id','displayfield'=>'name','selectedvalue'=>NULL),
				// 'filter_data_call'=>array(
				// 	'data_source'=>'_Custom\Offline',
				// 	'ds_method'=>'GetDynamicFormsTemplatesFTypes',
				// ),
			),
			'name'=>array(
				'db_field'=>'name',
				'data_type'=>'string',
				'type'=>'value',
				'halign'=>'left',
				'label'=>Translate::GetLabel('name'),
				'sortable'=>TRUE,
				'filterable'=>TRUE,
			),
			'table_name'=>array(
				'db_field'=>'table_name',
				'data_type'=>'string',
				'type'=>'value',
				'halign'=>'left',
				'label'=>Translate::GetLabel('table'),
				'sortable'=>TRUE,
				'filterable'=>TRUE,
			),
			'column_name'=>array(
				'db_field'=>'column_name',
				'data_type'=>'string',
				'type'=>'value',
				'halign'=>'left',
				'label'=>Translate::GetLabel('column'),
				'sortable'=>TRUE,
				'filterable'=>TRUE,
			),
			'required'=>array(
				'width'=>'80',
				'db_field'=>'required',
				'data_type'=>'numeric',
				'type'=>'checkbox',
				'label'=>Translate::GetLabel('required'),
				'sortable'=>TRUE,
				'filterable'=>TRUE,
				'filter_type'=>'combobox',
				'show_filter_cond_type'=>FALSE,
				'filter_params'=>array('valfield'=>'id','displayfield'=>'name','selectedvalue'=>NULL),
				'filter_data_call'=>array(
					'data_source'=>'_Custom\Offline',
					'ds_method'=>'GetGenericArrays',
					'ds_params'=>array('type'=>'yes-no'),
				),
			),
		),
	);
	$datagrid = new TableView($ctrl_params);
	echo $datagrid->Show();
?>
		</div>
