<?php
	use NETopes\Core\Controls\BasicForm;

	$basicform = NULL;
	if(isset($ctrl_params) && is_array($ctrl_params)) {
		$tname = get_array_param($ctrl_params,'tname',microtime(),'is_string');
		$f_tagid = get_array_param($ctrl_params,'tagid','','is_string');
		$f_rtarget = get_array_param($ctrl_params,'response_target','','is_string');
		if(strlen($f_tagid) && strlen($f_rtarget)) {
			$ctrl_params['actions'] = array(
				array(
					'params'=>array('tagid'=>'df_'.$tname.'_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','class'=>'btn btn-primary','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','SaveRecord','id_template'|{$id_template}~'id'|{$id_instance}~'data'|df_{$tname}_form:form~'is_modal'|'{$is_modal}'~'cmodule'|'{$cmodule}'~'cmethod'|'{$cmethod}'~'ctarget'|'{$ctarget}','{$f_tagid}')->{$f_rtarget}")),
				),
			);
			if($is_modal) {
				$ctrl_params['actions'][] = array(
					'type'=>'CloseModal',
					'params'=>array('tagid'=>'df_'.$tname.'_cancel','value'=>Translate::GetButton('cancel'),'class'=>'btn btn-default','icon'=>'fa fa-ban'),
				);
			} else {
				$ctrl_params['actions'][] = array(
					'params'=>array('tagid'=>'df_'.$tname.'_back','value'=>Translate::GetButton('back'),'icon'=>'fa fa-chevron-left','class'=>'btn btn-default','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$cmodule}','{$cmethod}','id_template'|{$id_template}~'id'|{$id_instance},'{$ctarget}')->{$ctarget}")),
				);
			}//if($is_modal)
		}//if(strlen($f_tagid) && strlen($f_rtarget))
		// NApp::_Dlog($ctrl_params,'BasicForm');
		$basicform = new BasicForm($ctrl_params);
	}//if(isset($ctrl_params) && is_array($ctrl_params))
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-flat mt10">
			<div class="container-fluid mt10">
				<?php echo (is_object($basicform) ? $basicform->Show() : 'UNKNOWN ERROR!'); ?>
			</div>
		</div>
	</div>
</div>
