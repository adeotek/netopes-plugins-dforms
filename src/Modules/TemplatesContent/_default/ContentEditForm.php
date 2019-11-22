<?php
/**
 * @var \NETopes\Core\Data\DataSet       $fieldsTypes
 * @var \NETopes\Core\Data\VirtualEntity $templateProps
 * @var \NETopes\Core\Data\DataSet       $templatePages
 * @var int                              $idTemplate
 */
use NETopes\Core\App\ModulesProvider; ?>
	<div class="dft-container clearfix" id="dft-container">
		<div class="side-column">
			<span class="dft-items-list-title"><?= Translate::GetLabel('fields_types') ?></span>
			<div class="dft-sc">
				<ul class="dft-items-list">
                    <?php
                    if(is_iterable($fieldsTypes) && count($fieldsTypes)) {
                        /** @var \NETopes\Core\Data\VirtualEntity $ft */
                        foreach($fieldsTypes as $ft) {
                            ?>
							<li>
								<div class="dft-item draggable clone" data-id="<?= $ft->getProperty('id') ?>">
									<span class="name">[<?= $ft->getProperty('class') ?>]</span>
									<span class="desc"><?= $ft->getProperty('name') ?></span>
								</div>
							</li>
                            <?php
                        }//END foreach
                    }//if(is_iterable($fieldsTypes) && count($fieldsTypes))
                    ?>
				</ul>
			</div>
		</div>
		<div class="dft-pages-actions">
            <?php
            if($templateProps->getProperty('render_type',1,'is_integer')>1) {
                $pagesNo=$templateProps->getProperty('pagesno',1,'is_integer');
                $btn_add_page=new NETopes\Core\Controls\Button(['value'=>Translate::GetButton('add_page'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-sm pull-left'),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'ShowAddPageForm', 'params': { 'id_template': '{$idTemplate}', 'pagesno': '{$pagesNo}', 'target': '{$target}' } }",'modal')]);
                echo $btn_add_page->Show();
            }//if($templateProps->getProperty('render_type',1,'is_integer')>1)
            ?>
		</div>
		<div class="dft-content" id="df_template_fields">
			<div class="dft-pages" id="df_template_pages">
                <?php
                if(is_iterable($templatePages) && count($templatePages)) {
                    /** @var \NETopes\Core\Data\VirtualEntity $page */
                    foreach($templatePages as $page) {
                        $pageIndex=$page->getProperty('pindex',0,'is_integer');
                        $pageTargetId='df_template_fields_p'.$pageIndex;
                        ?>
						<div class="dft-content-page clearfix" id="<?= $pageTargetId ?>">
                            <?php ModulesProvider::Exec($this->class,'ShowContentTable',['id_template'=>$idTemplate,'pindex'=>$pageIndex,'target'=>$pageTargetId,'c_target'=>$target]); ?>
						</div>
                        <?php
                    }//END foreach
                }//if(is_iterable($templatePages) && count($templatePages))
                ?>
			</div>
		</div>
	</div>
<?php
$this->AddJsScript("
    $('.draggable.clone').draggable({
        revert: 'invalid',
        cursor: 'move',
        containment: '#dft-container',
        helper: 'clone',
        snap: true
    });
");