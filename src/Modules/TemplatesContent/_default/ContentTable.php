<?php
use NETopes\Core\App\AppHelpers;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\DivButton;
use NETopes\Core\Controls\TextBox;

/**
 * @var \NETopes\Core\Data\VirtualEntity $templatePage
 * @var int                              $template
 * @var string                           $cTarget
 * @var string                           $target
 * @var array                            $fields
 * @var int                              $pIndex
 */
$colsNo=$templatePage->getProperty('colsno',1,'is_not0_integer');
$rowsNo=$templatePage->getProperty('rowsno',1,'is_not0_integer');
$renderType=$templatePage->getProperty('render_type',1,'is_integer');
$cSeparator='';
if($renderType>1) {
    ?>
	<div class="dft-actions clearfix">
		<div class="col-md-2">
			<h4 class="df-page-title"><?= Translate::GetLabel('page').': '.($pIndex + 1) ?></h4>
		</div>
		<div class="col-md-5 form-horizontal">
            <?php
            $txtName=new TextBox(['tag_id'=>'df_page_name_'.$pIndex,'label'=>Translate::GetLabel('page_title'),'label_cols'=>4,'value'=>$templatePage->getProperty('title','','is_string')]);
            echo $txtName->Show();
            ?>
		</div>
		<div class="col-md-3">
            <?php
            $btnSaveTitle=new Button(['value'=>Translate::GetButton('save_title'),'class'=>NApp::$theme->GetBtnInfoClass('btn-sm ml20'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'SetPageTitle', 'params': { 'id_template': '{$template}', 'pindex': '{$pIndex}', 'title': '{nGet|df_page_name_{$pIndex}:value}' } }",'errors')]);
            echo $btnSaveTitle->Show();
            ?>
		</div>
		<div class="col-md-2">
            <?php
            $btnDeletePage=new Button(['value'=>Translate::GetButton('delete_page'),'class'=>NApp::$theme->GetBtnDangerClass('btn-sm pull-right'),'icon'=>'fa fa-times-circle','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'UpdatePagesList', 'params': { 'id_template': '{$template}', 'type': '-1', 'pindex': '{$pIndex}', 'target': '{$cTarget}' } }",'errors')]);
            echo $btnDeletePage->Show();
            ?>
		</div>
	</div>
    <?php
}//if($renderType>1)
?>
	<div class="dft-actions mt10 clearfix">
        <?php
        $btnAddCol=new Button(['value'=>Translate::GetButton('add_column'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs ml20 pull-left'),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'ShowAddTableElementForm', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'type': 'col', 'last_pos': '{$colsNo}', 'c_target': '{$cTarget}', 'target': '{$target}' } }",'modal')]);
        echo $btnAddCol->Show();
        $btnAddRow=new Button(['value'=>Translate::GetButton('add_row'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs ml20 pull-left'),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'ShowAddTableElementForm', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'type': 'row', 'last_pos': '{$rowsNo}', 'c_target': '{$cTarget}', 'target': '{$target}' } }",'modal')]);
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
                for($i=1; $i<=$colsNo; $i++) {
                    $delColAct='';
                    if($colsNo>1) {
	                    $btnDelCol=new DivButton(['tooltip'=>Translate::GetButton('delete_column'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs pull-right'),'icon'=>'fa fa-times-circle','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'UpdateContentTable', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'type': '-1', 'colsno': '{$i}', 'c_target': '{$cTarget}', 'target': '{$target}' } }",'errors')]);
	                    $delColAct=$btnDelCol->Show();
                    }//if($colsNo>1)
                    ?>
					<th class="ccolumn"><?= Translate::GetLabel('column').' '.$i.$delColAct ?></th>
                    <?= ($i<$colsNo ? $cSeparator : '') ?>
                    <?php
                }//END for
                ?>
				<th class="auto">&nbsp;</th>
			</tr>
			</thead>
			<tbody>
            <?php
            for($i=1; $i<=$rowsNo; $i++) {
                $delRowAct='';
                if($rowsNo>1) {
	                $btnDelRow=new DivButton(['tag_id'=>'dft_del_col','tooltip'=>Translate::GetButton('delete_row'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs mr10'),'icon'=>'fa fa-times-circle','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'UpdateContentTable', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'type': '-1', 'rowsno': '{$i}', 'c_target': '{$cTarget}', 'target': '{$target}' } }",'errors'),'confirm_text'=>Translate::GetMessage('confirm_delete')]);
	                $delRowAct=$btnDelRow->Show();
                }//if($rowsNo>1)
                ?>
				<tr>
					<td class="auto">&nbsp;</td>
					<td class="label"><?= $delRowAct.Translate::GetLabel('row').' '.$i ?></td>
                    <?php
                    for($j=1; $j<=$colsNo; $j++) {
                        $f=$fields->safeGet($i.'-'.$j,NULL,'is_object');
                        if($f) {
	                        $btnEditItem=new DivButton(['tag_id'=>'dfti_edit','tooltip'=>Translate::GetButton('edit'),'class'=>NApp::$theme->GetBtnSpecialLightClass('btn-xxs'),'icon'=>'fa fa-pencil-square-o','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'AddEditContentElement', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'cols_no': '{$colsNo}', 'id_item': {$f->getProperty('id')}, 'id_control': {$f->getProperty('id_control')}, 'c_target': '{$cTarget}', 'target': '{$target}' } }",'modal')]);
	                        $editItemAct=$btnEditItem->Show();
	                        $btnDelItem=new DivButton(['tag_id'=>'dfti_del','tooltip'=>Translate::GetButton('delete'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs'),'icon'=>'fa fa-times','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'DeleteContentElementRecord', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'id': {$f->getProperty('id')}, 'c_target': '{$cTarget}', 'target': '{$target}' } }",'errors'),'confirm_text'=>Translate::GetMessage('confirm_delete')]);
	                        $delItemAct=$btnDelItem->Show();
	                        ?>
	                        <td class="ccolumn droppable" id="cell-<?= $pIndex.'-'.$i.'-'.$j ?>" data-cell="<?= $pIndex.'-'.$i.'-'.$j ?>"
	                            data-full="1">
		                        <span class="blank" style="display: none;"><?= $i.'-'.$j ?></span>
		                        <div class="dft-item draggable move" data-id="<?= $f->getProperty('id') ?>" data-cell="<?= $pIndex.'-'.$i.'-'.$j ?>">
			                        <span class="name"><?= $this->module->GetItemTitle($f) ?></span>
			                        <?= $editItemAct ?>
			                        <span class="desc"><span
					                        class="title">[<?= $f->getProperty('class') ?>]</span>&nbsp;-&nbsp;<?= $f->getProperty('control_name') ?></span>
			                        <?= $delItemAct ?>
		                        </div>
	                        </td>
	                        <?php
                        } else {
                            ?>
							<td class="ccolumn droppable" id="cell-<?= $pIndex.'-'.$i.'-'.$j ?>" data-cell="<?= $pIndex.'-'.$i.'-'.$j ?>"
								data-full="0"><span class="blank"><?= $i.'-'.$j ?></span></td>
                            <?php
                        }//if($f)
                        echo($j<$colsNo ? $cSeparator : '');
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
$this->AddJsScript($this->module->GetResourceFile('ContentTable','.js'),TRUE,TRUE,[
	'htmlTarget'=>$target,
	'addEditContentAction'=>[
		'value'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'AddEditContentElement', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'cols_no': '{$colsNo}', 'id_control': cellId, 'cell': cell, 'target': '{$target}' } }",'modal',['cellId','cell'],TRUE,NULL,TRUE,'acb'),
		'type'=>AppHelpers::JS_SCRIPT_INJECTION_TYPE_FUNCTION,
		'arguments'=>['cellId','cell','acb'],
		'js_var_type'=>'const',
	],
	'moveContentElementAction'=>[
		'value'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'MoveContentElement', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'id_item': cellId, 'cell': cell, 'target': '{$target}' } }",'errors',['cellId','cell'],TRUE,NULL,TRUE,'acb'),
		'type'=>AppHelpers::JS_SCRIPT_INJECTION_TYPE_FUNCTION,
		'arguments'=>['cellId','cell','acb'],
		'js_var_type'=>'const',
	],
]);