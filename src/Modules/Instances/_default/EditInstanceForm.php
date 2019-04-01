<?php
use NETopes\Core\Controls\BasicForm;

$basicForm=NULL;
if(isset($ctrl_params) && is_array($ctrl_params)) {
    $tName=get_array_value($ctrl_params,'tname',microtime(),'is_string');
    $fTagId=get_array_value($ctrl_params,'tag_id','','is_string');
    $fResponseTarget=get_array_value($ctrl_params,'response_target','','is_string');
    if(strlen($fTagId) && strlen($fResponseTarget)) {
        $ctrl_params['actions']=[
            [
                'params'=>['tag_id'=>'df_'.$tName.'_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','class'=>'btn btn-primary','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','SaveRecord','id_template'|{$idTemplate}~'id'|{$idInstance}~'data'|df_{$tName}_form:form~'is_modal'|'{$is_modal}'~'cmodule'|'{$cModule}'~'cmethod'|'{$cMethod}'~'ctarget'|'{$cTarget}','{$fTagId}')->{$fResponseTarget}")],
            ],
        ];
        if($is_modal) {
            $ctrl_params['actions'][]=[
                'type'=>'CloseModal',
                'params'=>['tag_id'=>'df_'.$tName.'_cancel','value'=>Translate::GetButton('cancel'),'class'=>'btn btn-default','icon'=>'fa fa-ban'],
            ];
        } else {
            $ctrl_params['actions'][]=[
                'params'=>['tag_id'=>'df_'.$tName.'_back','value'=>Translate::GetButton('back'),'icon'=>'fa fa-chevron-left','class'=>'btn btn-default','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$cModule}','{$cMethod}','id_template'|{$idTemplate}~'id'|{$idInstance},'{$cTarget}')->{$cTarget}")],
            ];
        }//if($is_modal)
    }//if(strlen($fTagId) && strlen($fResponseTarget))
    // NApp::Dlog($ctrl_params,'BasicForm');
    $basicForm=new BasicForm($ctrl_params);
}//if(isset($ctrl_params) && is_array($ctrl_params))
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-flat mt10">
			<div class="container-fluid mt10">
                <?php echo(is_object($basicForm) ? $basicForm->Show() : 'UNKNOWN ERROR!'); ?>
			</div>
		</div>
	</div>
</div>
