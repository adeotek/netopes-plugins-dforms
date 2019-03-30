<?php
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\DivButton;
use NETopes\Core\Controls\TextBox;

/**
 * @var \NETopes\Core\Data\VirtualEntity $templatePage
 * @var int $idTemplate
 * @var string $cTarget
 */
$colsNo = $templatePage->getProperty('colsno',1,'is_not0_integer');
$rowsNo = $templatePage->getProperty('rowsno',1,'is_not0_integer');
$renderType = $templatePage->getProperty('render_type',1,'is_integer');
$cSeparator = '';
if($renderType>1) {
?>
            <div class="dft-actions clearfix">
                <div class="col-md-2">
                    <h4 class="df-page-title"><?=Translate::GetLabel('page').': '.($pIndex+1)?></h4>
                </div>
                <div class="col-md-5 form-horizontal">
<?php
$txtName=new TextBox(['tag_id'=>'df_page_name_'.$pIndex,'label'=>Translate::GetLabel('page_title'),'label_cols'=>4,'value'=>$templatePage->getProperty('title','','is_string')]);
    echo $txtName->Show();
?>
                </div>
                <div class="col-md-3">
<?php
$btnSaveTitle=new Button(['value'=>Translate::GetButton('save_title'),'class'=>NApp::$theme->GetBtnInfoClass('btn-sm ml20'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','SetPageTitle','id_template'|'{$idTemplate}'~'pindex'|'{$pIndex}'~'title'|df_page_name_{$pIndex}:value)->errors")]);
    echo $btnSaveTitle->Show();
?>
                </div>
                <div class="col-md-2">
<?php
$btnDeletePage=new Button(['value'=>Translate::GetButton('delete_page'),'class'=>NApp::$theme->GetBtnDangerClass('btn-sm pull-right'),'icon'=>'fa fa-times-circle','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','UpdatePagesList','id_template'|'{$idTemplate}'~'type'|'-1'~'pindex'|'{$pIndex}','{$cTarget}')->errors")]);
	echo $btnDeletePage->Show();
?>
                </div>
            </div>
<?php
}//if($renderType>1)
?>
            <div class="dft-actions mt10 clearfix">
<?php
$btnAddCol=new Button(['value'=>Translate::GetButton('add_column'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs ml20 pull-left'),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','ShowAddTableElementForm','id_template'|{$idTemplate}~'pindex'|'{$pIndex}'~'type'|'col'~'last_pos'|'{$colsNo}'~'ctarget'|'{$cTarget}','{$target}')->modal")]);
echo $btnAddCol->Show();
$btnAddRow=new Button(['value'=>Translate::GetButton('add_row'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs ml20 pull-left'),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','ShowAddTableElementForm','id_template'|{$idTemplate}~'pindex'|'{$pIndex}'~'type'|'row'~'last_pos'|'{$rowsNo}'~'ctarget'|'{$cTarget}','{$target}')->modal")]);
echo $btnAddRow->Show();
?>
            </div>
            <div class="dft-sc">
                <table class="dft-table">
                    <thead>
                        <tr>
                            <th class="auto">&nbsp;</th>
                            <th class="label">&nbsp;</th>
<?php
for($i=1;$i<=$colsNo;$i++) {
    $delColAct = '';
    if($colsNo>1) {
        $btnDelCol=new DivButton(['tooltip'=>Translate::GetButton('delete_column'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs pull-right'),'icon'=>'fa fa-times-circle','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','UpdateContentTable','id_template'|{$idTemplate}~'pindex'|'{$pIndex}'~'type'|'-1'~'colsno'|'{$i}'~'ctarget'|'{$cTarget}','{$target}')->errors"),'confirm_text'=>Translate::GetMessage('confirm_delete')]);
        $delColAct = $btnDelCol->Show();
    }//if($colsNo>1)
?>
						    <th class="ccolumn"><?=Translate::GetLabel('column').' '.$i.$delColAct?></th>
						    <?=($i<$colsNo ? $cSeparator : '')?>
<?php
}//END for
?>
                            <th class="auto">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
for($i=1;$i<=$rowsNo;$i++) {
    $delRowAct = '';
    if($rowsNo>1) {
        $btnDelRow=new DivButton(['tag_id'=>'dft_del_col','tooltip'=>Translate::GetButton('delete_row'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs mr10'),'icon'=>'fa fa-times-circle','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','UpdateContentTable','id_template'|{$idTemplate}~'pindex'|'{$pIndex}'~'type'|'-1'~'rowsno'|'{$i}'~'ctarget'|'{$cTarget}','{$target}')->errors"),'confirm_text'=>Translate::GetMessage('confirm_delete')]);
        $delRowAct = $btnDelRow->Show();
    }//if($rowsNo>1)
?>
                        <tr>
                            <td class="auto">&nbsp;</td>
                            <td class="label"><?=$delRowAct.Translate::GetLabel('row').' '.$i?></td>
<?php
    for($j=1;$j<=$colsNo;$j++) {
        $f = $fields->safeGet($i.'-'.$j,NULL,'is_object');
        if($f) {
            $btnEditItem=new DivButton(['tag_id'=>'dfti_edit','tooltip'=>Translate::GetButton('edit'),'class'=>NApp::$theme->GetBtnSpecialLightClass('btn-xxs'),'icon'=>'fa fa-pencil-square-o','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','AddEditContentElement','id_template'|{$idTemplate}~'pindex'|'{$pIndex}'~'cols_no'|'{$colsNo}'~'id_item'|{$f->getProperty('id')}~'id_control'|{$f->getProperty('id_control')}~'ctarget'|'{$cTarget}','{$target}')->modal")]);
            $editItemAct = $btnEditItem->Show();
            $btnDelItem=new DivButton(['tag_id'=>'dfti_del','tooltip'=>Translate::GetButton('delete'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs'),'icon'=>'fa fa-times','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','DeleteContentElementRecord','id_template'|{$idTemplate}~'pindex'|'{$pIndex}'~'id'|{$f->getProperty('id')}~'ctarget'|'{$cTarget}','{$target}')->errors"),'confirm_text'=>Translate::GetMessage('confirm_delete')]);
            $delItemAct = $btnDelItem->Show();
?>
                            <td class="ccolumn droppable" id="cell-<?=$pIndex.'-'.$i.'-'.$j?>" data-cell="<?=$pIndex.'-'.$i.'-'.$j?>" data-full="1">
                                <span class="blank" style="display: none;"><?=$i.'-'.$j?></span>
                                <div class="dft-item draggable move" data-id="<?=$f->getProperty('id')?>" data-cell="<?=$pIndex.'-'.$i.'-'.$j?>">
                                    <span class="name"><?=$this->module->GetItemTitle($f)?></span>
                                    <?=$editItemAct?>
                                    <span class="desc"><span class="title">[<?=$f->getProperty('class')?>]</span>&nbsp;-&nbsp;<?=$f->getProperty('control_name')?></span>
                                    <?=$delItemAct?>
                                </div>
                            </td>
<?php
        } else {
?>
						    <td class="ccolumn droppable" id="cell-<?=$pIndex.'-'.$i.'-'.$j?>" data-cell="<?=$pIndex.'-'.$i.'-'.$j?>" data-full="0"><span class="blank"><?=$i.'-'.$j?></span></td>
<?php
        }//if($f)
        echo ($j<$colsNo ? $cSeparator : '');
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
    $('#{$target} .draggable.move').draggable({
        revert: 'invalid',
        cursor: 'move',
        containment: '#df_template_fields',
        helper: 'clone',
        snap: true
    });
    
    $('#{$target} .droppable').droppable({
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
                ".NApp::Ajax()->LegacyPrepareWithCallback("AjaxRequest('{$this->class}','AddEditContentElement','id_template'|{$idTemplate}~'pindex'|'{$pIndex}'~'cols_no'|'{$colsNo}'~'id_control'|cellid~'cell'|cell,'{$target}')->modal-<cellid-<cell",'acb')."
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
                ".NApp::Ajax()->LegacyPrepareWithCallback("AjaxRequest('{$this->class}','MoveContentElement','id_template'|{$idTemplate}~'pindex'|'{$pIndex}'~'id_item'|cellid~'cell'|cell,'{$target}')->errors-<cellid-<cell",'acb')."
            }
        }
    });
");