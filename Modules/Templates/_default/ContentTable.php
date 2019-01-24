<?php
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\DivButton;

    $colsno = $templatePage->getProperty('colsno',1,'is_not0_integer');
    $rowsno = $templatePage->getProperty('rowsno',1,'is_not0_integer');
    $renderType = $templatePage->getProperty('render_type',1,'is_integer');
    $cseparator = '';
    if($renderType>1) {
?>
            <div class="dft-actions clearfix">
                <div class="col-md-2">
                    <h4 class="df-page-title"><?=Translate::GetLabel('page').': '.($pindex+1)?></h4>
                </div>
                <div class="col-md-5 form-horizontal">
<?php
    $txtName = new \NETopes\Core\Controls\TextBox(['tagid'=>'df_page_name_'.$pindex,'label'=>Translate::GetLabel('page_title'),'label_cols'=>4,'value'=>$templatePage->getProperty('title','','is_string')]);
    echo $txtName->Show();
?>
                </div>
                <div class="col-md-3">
<?php
    $btn_save_title = new Button(['value'=>Translate::GetButton('save_title'),'class'=>NApp::$theme->GetBtnInfoClass('btn-sm ml20'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','SetPageTitle','id_template'|{$idTemplate}~'pindex'|'$pindex'~'title'|'df_page_name_{$pindex}:value)->errors")]);
    echo $btn_save_title->Show();
?>
                </div>
                <div class="col-md-2">
<?php
    $btn_delete_page = new Button(['value'=>Translate::GetButton('add_page'),'class'=>NApp::$theme->GetBtnDangerClass('btn-sm pull-right'),'icon'=>'fa fa-times-circle','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','UpdatePagesList','id_template'|'{$idTemplate}'~'type'|'-1'~'pindex'|'{$pindex}','{$ctarget}')->errors")]);
	echo $btn_delete_page->Show();
?>
                </div>
            </div>
<?php
    }//if($renderType>1)
?>
            <div class="dft-actions mt10 clearfix">
<?php
    $btn_add_col = new Button(['value'=>Translate::GetButton('add_column'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs ml20 pull-left'),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','ShowAddTableElementForm','id_template'|{$idTemplate}~'pindex'|'{$pindex}'~'type'|'col'~'last_pos'|'{$colsno}','{$target}')->modal")]);
    echo $btn_add_col->Show();
    $btn_add_row = new Button(['value'=>Translate::GetButton('add_row'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs ml20 pull-left'),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','ShowAddTableElementForm','id_template'|{$idTemplate}~'pindex'|'{$pindex}'~'type'|'row'~'last_pos'|'{$rowsno}','{$target}')->modal")]);
	echo $btn_add_row->Show();
?>
            </div>
            <div class="dft-sc">
                <table class="dft-table">
                    <thead>
                        <tr>
                            <th class="auto">&nbsp;</th>
                            <th class="label">&nbsp;</th>
<?php
	for($i=1;$i<=$colsno;$i++) {
		$del_col_act = '';
		if($colsno>1) {
			$btn_del_col = new DivButton(array('tooltip'=>Translate::GetButton('delete_column'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs pull-right'),'icon'=>'fa fa-times-circle','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','UpdateContentTable','id_template'|{$idTemplate}~'pindex'|'{$pindex}'~'type'|'-1'~'colsno'|'{$i}','{$target}')->errors"),'confirm_text'=>Translate::GetMessage('confirm_delete')));
			$del_col_act = $btn_del_col->Show();
		}//if($colsno>1)
?>
						    <th class="ccolumn"><?=Translate::GetLabel('column').' '.$i.$del_col_act?></th>
						    <?=($i<$colsno ? $cseparator : '')?>
<?php
	}//END for
?>
                            <th class="auto">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
	for($i=1;$i<=$rowsno;$i++) {
		$del_row_act = '';
		if($rowsno>1) {
			$btn_del_row = new DivButton(array('tagid'=>'dft_del_col','tooltip'=>Translate::GetButton('delete_row'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs mr10'),'icon'=>'fa fa-times-circle','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','UpdateContentTable','id_template'|{$idTemplate}~'pindex'|'{$pindex}'~'type'|'-1'~'rowsno'|'{$i}','{$target}')->errors"),'confirm_text'=>Translate::GetMessage('confirm_delete')));
			$del_row_act = $btn_del_row->Show();
		}//if($rowsno>1)
?>
                        <tr>
                            <td class="auto">&nbsp;</td>
                            <td class="label"><?=$del_row_act.Translate::GetLabel('row').' '.$i?></td>
<?php
		for($j=1;$j<=$colsno;$j++) {
			$f = $fields->safeGet($i.'-'.$j,NULL,'is_object');
			if($f) {
				$btn_edit_item = new DivButton(array('tagid'=>'dfti_edit','tooltip'=>Translate::GetButton('edit'),'class'=>NApp::$theme->GetBtnSpecialLightClass('btn-xxs'),'icon'=>'fa fa-pencil-square-o','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','AddEditContentElement','id_template'|{$idTemplate}~'pindex'|'{$pindex}'~'id_item'|{$f->getProperty('id')}~'id_control'|{$f->getProperty('id_control')},'{$target}')->modal")));
				$edit_item_act = $btn_edit_item->Show();
				$btn_del_item = new DivButton(array('tagid'=>'dfti_del','tooltip'=>Translate::GetButton('delete'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs'),'icon'=>'fa fa-times','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','DeleteContentElementRecord','id_template'|{$idTemplate}~'pindex'|'{$pindex}'~'id'|{$f->getProperty('id')},'{$target}')->errors"),'confirm_text'=>Translate::GetMessage('confirm_delete')));
				$del_item_act = $btn_del_item->Show();
?>
                            <td class="ccolumn droppable" id="cell-<?=$pindex.'-'.$i.'-'.$j?>" data-cell="<?=$pindex.'-'.$i.'-'.$j?>" data-full="1">
                                <span class="blank" style="display: none;"><?=$i.'-'.$j?></span>
                                <div class="dft-item draggable move" data-id="<?=$f->getProperty('id')?>" data-cell="<?=$pindex.'-'.$i.'-'.$j?>">
                                    <span class="name"><?=$this->module->GetItemTitle($f)?></span>
                                    <?=$edit_item_act?>
                                    <span class="desc"><span class="title">[<?=$f->getProperty('class')?>]</span>&nbsp;-&nbsp;<?=$f->getProperty('control_name')?></span>
                                    <?=$del_item_act?>
                                </div>
                            </td>
<?php
			} else {
?>
						    <td class="ccolumn droppable" id="cell-<?=$pindex.'-'.$i.'-'.$j?>" data-cell="<?=$pindex.'-'.$i.'-'.$j?>" data-full="0"><span class="blank"><?=$i.'-'.$j?></span></td>
<?php
			}//if($f)
			echo ($j<$colsno ? $cseparator : '');
		}//END for
?>
                            <td class="auto">&nbsp;</td>
                        </tr>
<?php
	}//END for
?>
                    </tbody>
                </table>
            </div>
<?php
    $this->AddJsScript("
        $('.draggable.move').draggable({
            revert: 'invalid',
            cursor: 'move',
            containment: '#df_template_fields',
            helper: 'clone',
            snap: true
        });
        
        $('.droppable').droppable({
            accept: function(ui) { return($(this).attr('data-full')!='1'); },
            drop: function(event,ui) {
                var cell = $(this).attr('data-cell');
                var cellid = $(ui.draggable).attr('data-id');
                var ths = $(this); 
                if($(ui.draggable).hasClass('clone')) {
                    console.log('is add!');
                    var acb = function() {
                        $(ths).find('span.blank').hide();
                        $(ths).append($(ui.draggable).clone());
                        $(ths).children('.draggable').each(function(){
                            $(this).removeClass('clone');
                            $(this).attr('data-cell',cell);
                            $(this).draggable({
                                revert: 'invalid',
                                cursor: 'move',
                                containment: '#df_template_fields',
                                helper: 'clone',
                                snap: true
                            });
                        });
                        $(ths).attr('data-full','1');
                    };
                    ".NApp::Ajax()->PrepareWithCallback("AjaxRequest('{$this->class}','AddEditContentElement','id_template'|{$idTemplate}~'pindex'|'{$pindex}'~'id_control'|cellid~'cell'|cell,'{$target}')->modal-<cellid-<cell",'acb')."
                } else {
                    console.log('is move!');
                    var acb = function() {
                        var ocell = $(ui.draggable).attr('data-cell');
                        console.log('ocell: '+ocell);
                        $(ths).find('span.blank').hide();
                        $(ths).append($(ui.draggable).clone());
                        $(ths).children('.draggable').each(function(){
                            $(this).attr('data-cell',cell);
                            $(this).draggable({
                                revert: 'invalid',
                                cursor: 'move',
                                containment: '#df_template_fields',
                                helper: 'clone',
                                snap: true
                            });
                        });						
                        $('#cell-'+ocell).find('.draggable').remove();
                        $('#cell-'+ocell).attr('data-full','0');
                        $('#cell-'+ocell).find('span.blank').show();
                        $(ths).attr('data-full','1');
                    };
                    ".NApp::Ajax()->PrepareWithCallback("AjaxRequest('{$this->class}','MoveContentElement','id_template'|{$idTemplate}~'pindex'|'{$pindex}'~'id_item'|cellid~'cell'|cell,'{$target}')->errors-<cellid-<cell",'acb')."
                }
            }
        });
    ");