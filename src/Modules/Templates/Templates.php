<?php
/**
 * description
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Templates;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\App\ModulesProvider;
use NETopes\Core\Controls\BasicForm;
use NETopes\Core\Controls\Button;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\DataSet;
use NETopes\Core\Data\VirtualEntity;
use NETopes\Core\AppException;
use NApp;
use NETopes\Plugins\DForms\Modules\Controls\Controls;
use Translate;
/**
 * Class Templates
 *
 * @package NETopes\Plugins\Modules\DForms
 */
class Templates extends Module {
    /**
	 * Module class initializer
	 * @return void
	 */
	protected function _Init() {
	    $this->viewsExtension = '.php';
	}//END protected function _Init
	/**
	 * @var string Name of field to be used as label for the form items ('label'/'name')
	 */
	public $itemLabel = 'label';
	/**
	 * @var int Maximum box title length (in characters)
	 */
	public $maxBoxTitleLength = 29;
	/**
	 * Get item box title
     * @param  VirtualEntity $field Item parameters array
	 * @return string Returns item box title
     * @throws \NETopes\Core\AppException
	 */
	public function GetItemTitle($field) {
		$title = $field->getProperty($this->itemLabel,'','is_string');
		if(strlen($title)>($this->maxBoxTitleLength+2)) { $title = substr($title,0,$this->maxBoxTitleLength).'...'; }
		if($field->getProperty('required',0,'is_integer')==1) { $title .= '*'; }
		return $title;
	}//END public function GetItemTitle
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function Listing($params = NULL) {
		$listingTarget = 'dforms_listing';
		$view = new AppView(get_defined_vars(),$this,'main');
        $view->SetTitle('dynamic_forms_templates');
        $view->SetTargetId($listingTarget);
        if(!$this->AddDRights()) {
            $btnAdd = new Button(['value'=>Translate::GetButton('add').' '.Translate::GetLabel('template'),'class'=>NApp::$theme->GetBtnPrimaryClass(),'icon'=>'fa fa-plus','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','ShowAddForm')->modal")]);
	        $view->AddAction($btnAdd->Show());
        }//if(!$this->AddDRights())
        $view->AddTableView($this->GetViewFile('Listing'));
        $view->Render();
	}//END public function Listing
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddForm($params = NULL){
		$view = new AppView(get_defined_vars(),$this,'modal');
        $view->SetIsModalView(true);
        $view->AddBasicForm($this->GetViewFile('AddForm'));
        $view->SetTitle(Translate::GetButton('add').' '.Translate::GetLabel('template'));
        $view->SetModalWidth(620);
        $view->Render();
        NApp::Ajax()->ExecuteJs("$('#df_template_add_code').focus();");
	}//END public function ShowAddForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowEditForm($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid record identifier!');
		$item = DataProvider::Get('Plugins\DForms\Templates','GetItem',['for_id'=>$id]);
		if(!is_object($item)) { throw new AppException('Invalid record!'); }
		$version = $item->getProperty('version',0,'is_numeric');
		$view = new AppView(get_defined_vars(),$this,'main');
        $view->AddTabControl($this->GetViewFile('EditForm'));
        $view->SetTitle(Translate::GetButton('edit_template').': '.$item->getProperty('name').' ['.$item->getProperty('code').'] - Ver. '.$version.' ('.($version+1).')');
        if(!$this->ValidateDRights()) {
            $btnValidate = new Button(['value'=>Translate::GetButton('validate'),'class'=>NApp::$theme->GetBtnSuccessClass('mr10'),'icon'=>'fa fa-check-square-o','onclick'=>NApp::Ajax()->PrepareAjaxRequest(['module'=>$this->class,'method'=>'ValidateRecord','target'=>'errors','params'=>['id'=>$id]])]);
	        $view->AddAction($btnValidate->Show());
        }//if(!$this->ValidateDRights()) {
	    $btnBack = new Button(['value'=>Translate::GetButton('back'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-chevron-left','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','Listing')->main-content")]);
	    $view->AddAction($btnBack->Show());
        $view->Render();
        NApp::Ajax()->ExecuteJs("$('#df_template_edit_code').focus();");
	}//END public function ShowEditForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditRecord($params = NULL){
		$id = $params->safeGet('id',NULL,'is_not0_numeric');
		$ftype = $params->safeGet('ftype',NULL,'is_numeric');
		$code = $params->safeGet('code',NULL,'is_numeric');
		$name = trim($params->safeGet('name',NULL,'is_notempty_string'));
		$target = $params->safeGet('target','','is_string');
		if(!$ftype || !$code || !strlen($name)) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!$ftype || !$code || !strlen($name))
		$state = $params->safeGet('state',NULL,'is_numeric');
		$colsNo = $params->safeGet('colsno',NULL,'is_numeric');
		$rowsNo = $params->safeGet('rowsno',NULL,'is_numeric');
		$dmode = $params->safeGet('dmode',NULL,'is_numeric');
		if($id) {
			$result = DataProvider::Get('Plugins\DForms\Templates','SetItem',[
				'for_id'=>$id,
				'in_name'=>$name,
				'in_ftype'=>$ftype,
				'in_state'=>$state,
				'in_delete_mode'=>$dmode,
				'user_id'=>NApp::GetCurrentUserId(),
			]);
			if($result===FALSE) { throw new AppException('Unknown database error!'); }
			if($params->safeGet('close',1,'is_numeric')!=1) {
			    echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
				return;
			}//if($result!==FALSE)
			$this->Exec('Listing',[],'main-content');
		} else {
			$result = DataProvider::Get('Plugins\DForms\Templates','SetNewItem',[
				'in_code'=>$code,
				'in_name'=>$name,
				'in_ftype'=>$ftype,
				'in_state'=>$state,
				'in_colsno'=>$colsNo,
				'in_rowsno'=>$rowsNo,
				'in_delete_mode'=>$dmode,
				'user_id'=>NApp::GetCurrentUserId(),
			]);
			if(!is_object($result) || !count($result)) { throw new AppException('Unknown database error!'); }
			$id = $result->first()->getProperty('inserted_id',0,'is_integer');
			if($id<=0) { throw new AppException('Unknown database error!'); }
			$this->CloseForm();
			$this->Exec('ShowEditForm',['id'=>$id],'main-content');
		}//if($id)
	}//END public function AddEditRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function SetPrintTemplate($params = NULL) {
		$idTemplate = $params->getOrFail('id','is_not0_integer','Invalid record identifier!');
		$result = DataProvider::Get('Plugins\DForms\Templates','SetPropertiesItem',[
            'template_id'=>$idTemplate,
            'in_print_template'=>$params->safeGet('print_template','','is_string'),
        ]);
        if($result===FALSE) { throw new AppException('Unknown database error!'); }
        if($params->safeGet('close',1,'is_numeric')!=1) {
            echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
            return;
        }//if($result!==FALSE)
        $this->Exec('Listing',[],'main-content');
	}//END public function SetPrintTemplate
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteRecord($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid template identifier!');
		$result = DataProvider::GetArray('Plugins\DForms\Templates','UnsetItem',['for_id'=>$id]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$this->Exec('Listing',[],'main-content');
	}//END public function DeleteRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function EditRecordState($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid template identifier!');
		$state = $params->getOrFail('state','is_integer','Invalid state value!');
		$result = DataProvider::Get('Plugins\DForms\Templates','SetItemState',[
			'for_id'=>$id,
			'in_state'=>$state,
			'user_id'=>NApp::GetCurrentUserId(),
		]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
	}//END public function EditRecordState
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function CreateNewVersion($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid template identifier!');
		$result = DataProvider::Get('Plugins\DForms\Templates','SetItemValidated',['for_id'=>$id,'new_value'=>0]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$this->Exec('ShowEditForm',['id'=>$id],'main-content');
	}//END public function CreateNewVersion
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ValidateRecord($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid template identifier!');
		$new_value = $params->safeGet('new_value',1,'is_numeric');
		$result = DataProvider::GetArray('Plugins\DForms\Templates','SetItemValidated',[
			'for_id'=>$id,
			'new_value'=>$new_value,
			'user_id'=>NApp::GetCurrentUserId(),
		]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$this->Exec('Listing',[],'main-content');
	}//END public function ValidateRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowDesignEditForm($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$item = DataProvider::Get('Plugins\DForms\Templates','GetItemProperties',['template_id'=>$idTemplate]);
		if(!is_object($item) || $item instanceof DataSet) { $item = new VirtualEntity([]); }
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,'default');
        $view->AddBasicForm($this->GetViewFile('DesignEditForm'));
        $view->Render();
	}//END public function ShowDesignEditForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function EditDesignRecord($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$renderType = $params->safeGet('render_type',NULL,'is_integer');
		$target = $params->safeGet('target','','is_string');
		if(!$renderType) {
		    NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!$renderType)
		$result = DataProvider::Get('Plugins\DForms\Templates','SetPropertiesItem',[
            'template_id'=>$idTemplate,
            'in_render_type'=>$renderType,
            'in_theme_type'=>$params->safeGet('theme_type','','is_string'),
            'in_controls_size'=>$params->safeGet('controls_size','','is_string'),
            'in_label_cols'=>$params->safeGet('label_cols',NULL,'is_not0_integer'),
            'in_separator_width'=>$params->safeGet('separator_width','','is_string'),
            'in_iso_code'=>$params->safeGet('iso_code','','is_string'),
        ]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$cTarget = $params->safeGet('ctarget','','is_string');
		if(!strlen($cTarget)) {
		    echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
		    return;
		}//if(strlen($cTarget))
		$this->Exec('ShowDesignEditForm',['id_template'=>$idTemplate,'target'=>$cTarget],$cTarget);
	}//END public function EditDesignRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowRelationsEditForm($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$target = $params->safeGet('target','','is_string');
		$dgtarget = 'dg-'.$target;
        $view = new AppView(get_defined_vars(),$this,'default');
        $view->SetTargetId($dgtarget);
        if(!$this->AddDRights()) {
            $btnAdd = new Button(['value'=>Translate::GetButton('add'),'class'=>NApp::$theme->GetBtnPrimaryClass(),'icon'=>'fa fa-plus-circle','onclick'=>NApp::Ajax()->PrepareAjaxRequest(['module'=>$this->class,'method'=>'ShowRelationAddEditForm','target'=>'modal','params'=>['id_template'=>$idTemplate,'target'=>$target]])]);
            $view->AddAction($btnAdd->Show());
        }//if(!$this->AddDRights())
        $view->AddTableView($this->GetViewFile('RelationsEditForm'));
        $view->Render();
	}//END public function ShowRelationsEditForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowRelationAddEditForm($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$id = $params->safeGet('id',NULL,'is_integer');
		if($id) {
			$item = DataProvider::Get('Plugins\DForms\Templates','GetRelation',['for_id'=>$id]);
		} else {
			$item = new VirtualEntity();
		}//if($id)
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,'modal');
		$view->SetIsModalView(TRUE);
		$view->AddBasicForm($this->GetViewFile('RelationAddEditForm'));
		$view->SetTitle(Translate::GetLabel('relation').' - '.Translate::Get($id ? 'button_edit' : 'button_add'));
		$view->SetModalWidth(500);
		$view->Render();
		NApp::Ajax()->ExecuteJs("$('#df_template_rel_ae_type').focus();");
	}//END public function ShowRelationAddEditForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditRelationRecord($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$id = $params->safeGet('id',NULL,'is_integer');
		$idType = $params->safeGet('type',NULL,'is_integer');
		$rType = $params->safeGet('rtype',NULL,'is_integer');
		$uType = $params->safeGet('utype',NULL,'is_integer');
		$name = $params->safeGet('name',NULL,'is_string');
		$key = $params->safeGet('key',NULL,'is_string');
		$target = $params->safeGet('target','');
		if(!strlen($name) || !strlen($key) || !is_numeric($rType) || !is_numeric($uType) || (!$id && !$idType)) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!strlen($name) || !strlen($key) || !is_numeric($rType) || !is_numeric($uType) || (!$id && !$type))
		if($id) {
			$result = DataProvider::Get('Plugins\DForms\Templates','SetRelation',[
				'for_id'=>$id,
				'in_name'=>$name,
				'in_key'=>$key,
				'in_required'=>$params->safeGet('required',0,'is_integer'),
				'in_rtype'=>$rType,
				'in_utype'=>$uType,
			]);
			if($result===FALSE) { throw new AppException('Unknown database error!'); }
		} else {
			$result = DataProvider::Get('Plugins\DForms\Templates','SetNewRelation',[
				'template_id'=>$idTemplate,
				'relation_type_id'=>$idType,
				'in_name'=>$name,
				'in_key'=>$key,
				'in_required'=>$params->safeGet('required',0,'is_integer'),
				'in_rtype'=>$rType,
				'in_utype'=>$uType,
			]);
			if(!is_object($result) || !count($result) || $result->first()->getProperty('inserted_id',0,'is_integer')<=0) { throw new AppException('Unknown database error!'); }
		}//if($id)
        $this->CloseForm();
        $cTarget = $params->safeGet('ctarget','','is_string');
        $this->Exec('ShowRelationsEditForm',['id_template'=>$idTemplate,'target'=>$cTarget],$cTarget);
	}//END public function AddEditRelationRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteRelationRecord($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid record identifier!');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$result = DataProvider::Get('Plugins\DForms\Templates','UnsetRelation',['for_id'=>$id]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowRelationsEditForm',['id_template'=>$idTemplate,'target'=>$target],$target);
	}//END public function DeleteRelationRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowContentEditForm($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$templateProps = DataProvider::Get('Plugins\DForms\Templates','GetItemProperties',['template_id'=>$idTemplate]);
		$fieldsTypes = DataProvider::Get('Plugins\DForms\Controls','GetItems',['for_state'=>1]);
		$templatePages = DataProvider::Get('Plugins\DForms\Templates','GetItemPages',['template_id'=>$idTemplate],['sort'=>['pindex'=>'asc']]);
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,NULL);
        $view->AddFileContent($this->GetViewFile('ContentEditForm'));
        $view->Render();
	}//END public function ShowContentEditForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowContentTable($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pIndex = $params->getOrFail('pindex','is_integer','Invalid template page identifier!');
		$templatePage = DataProvider::Get('Plugins\DForms\Templates','GetItemPage',['template_id'=>$idTemplate,'for_pindex'=>$pIndex]);
		$fields = DataProvider::GetKeyValue('Plugins\DForms\Templates','GetFields',['template_id'=>$idTemplate,'for_pindex'=>$pIndex],['keyfield'=>'cell']);
		$target = $params->safeGet('target','','is_string');
		$cTarget = $params->safeGet('ctarget','','is_string');
		$view = new AppView(get_defined_vars(),$this,NULL);
        $view->AddFileContent($this->GetViewFile('ContentTable'));
        $view->Render();
	}//END public function ShowContentTable
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddPageForm($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$maxPos = $params->safeGet('pagesno',0,'is_numeric');
		if($maxPos<=0) { return; }
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,'modal');
		$view->SetIsModalView(TRUE);
		$view->SetModalWidth(250);
		$view->SetTitle(Translate::GetLabel('add_page'));
        $view->AddBasicForm($this->GetViewFile('AddPageForm'));
        $view->Render();
	}//END public function ShowAddPageForm
    /**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function UpdatePagesList($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$type = $params->getOrFail('type','is_integer','Invalid action type!');
		$pIndex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		if($type<0) {
			$result = DataProvider::Get('Plugins\DForms\Templates','UnsetTemplatePage',[
				'for_id'=>$idTemplate,
				'in_pindex'=>$pIndex,
			]);
		} elseif($type==0) {
			$result = DataProvider::Get('Plugins\DForms\Templates','SetTemplatePage',[
				'for_id'=>$idTemplate,
				'in_pindex'=>$pIndex,
			]);
		} else {
			$result = DataProvider::Get('Plugins\DForms\Templates','SetNewTemplatePage',[
				'for_id'=>$idTemplate,
				'in_pindex'=>$pIndex,
			]);
		}//if($type<0)
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		if($params->safeGet('close',0,'is_integer')==1) { $this->CloseForm(); }
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowContentEditForm',['id_template'=>$idTemplate,'target'=>$target],$target);
	}//END public function UpdatePagesList
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function SetPageTitle($params = NULL) {
        $idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $pIndex = $params->getOrFail('pindex','is_integer','Invalid page index!');
        $title = $params->safeGet('title','','is_string');
        $result = DataProvider::Get('Plugins\DForms\Templates','SetTemplatePageTitle',[
            'template_id'=>$idTemplate,
            'for_pindex'=>$pIndex,
            'in_title'=>$title,
        ]);
        if($result===FALSE) { throw new AppException('Unknown database error!'); }
    }//END public function SetPageTitle
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddTableElementForm($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pIndex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$type = $params->safeGet('type','','is_string');
		$lastPos = $params->safeGet('last_pos',0,'is_numeric');
		if(!strlen($type) || $lastPos<=0) { throw new AppException('Invalid table structure!'); }
		$maxPos = $lastPos+1;
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,'modal');
		$view->SetIsModalView(TRUE);
		$view->SetModalWidth(250);
		$view->SetTitle(Translate::GetLabel('add_'.$type));
        $view->AddBasicForm($this->GetViewFile('AddTableElementForm'));
        $view->Render();
	}//END public function ShowAddTableElementForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function UpdateContentTable($params = NULL) {
		// NApp::Dlog($params,'UpdateContentTable');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pIndex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$type = $params->getOrFail('type','is_integer','Invalid action type!');
		$colsNo = $params->safeGet('colsno',NULL,'is_not0_integer');
		$rowsNo = $params->safeGet('rowsno',NULL,'is_not0_integer');
		if($colsNo===NULL && $rowsNo===NULL) { return; }
		if($type<0) {
			$result = DataProvider::GetArray('Plugins\DForms\Templates','UnsetTableCell',[
				'for_id'=>$idTemplate,
				'in_col'=>$colsNo,
				'in_row'=>$rowsNo,
				'in_pindex'=>$pIndex,
			]);
		} elseif($type==0) {
			$result = DataProvider::GetArray('Plugins\DForms\Templates','SetTableCell',[
				'for_id'=>$idTemplate,
				'in_col'=>$colsNo,
				'in_row'=>$rowsNo,
				'in_pindex'=>$pIndex,
			]);
		} else {
			$result = DataProvider::GetArray('Plugins\DForms\Templates','SetNewTableCell',[
				'for_id'=>$idTemplate,
				'in_col'=>$colsNo,
				'in_row'=>$rowsNo,
				'in_pindex'=>$pIndex,
			]);
		}//if($type<0)
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		if($params->safeGet('close',0,'is_integer')==1) { $this->CloseForm(); }
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowContentTable',['id_template'=>$idTemplate,'pindex'=>$pIndex,'target'=>$target],$target);
	}//END public function UpdateContentTable
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditContentElement($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pIndex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$idControl = $params->getOrFail('id_control','is_not0_integer','Invalid control identifier!');
		$fieldType = DataProvider::Get('Plugins\DForms\Controls','GetItem',['for_id'=>$idControl]);
		$colsNo = $params->safeGet('cols_no',1,'is_not0_integer');
		$id = $params->safeGet('id_item',NULL,'is_not0_integer');
		if($id) {
			$item = DataProvider::Get('Plugins\DForms\Templates','GetField',['for_id'=>$id]);
			$fRow = $item->getProperty('frow',0,'is_integer');
			$fCol = $item->getProperty('fcol',0,'is_integer');
		} else {
			$cell = $params->safeGet('cell','','is_string');
			$cellArray = explode('-',$cell);
			if(!is_array($cellArray) || count($cellArray)!=3) { throw new AppException('Invalid template cell!'); }
			$fRow = $cellArray[1];
			$fCol = $cellArray[2];
			if(!$fRow || !$fCol) { throw new AppException('Invalid cell data!'); }
			$item = new VirtualEntity();
		}//if(!$id)
		$target = $params->safeGet('target','','is_string');
		$cClass = $fieldType->getProperty('class','','is_string');
        $cDataType = $fieldType->getProperty('data_type','','is_string');

		$view = new AppView(get_defined_vars(),$this,'modal');
		$view->SetTitle(Translate::GetLabel('field_properties'));
		$view->SetIsModalView(TRUE);
		$view->SetModalWidth(550);
		$customClose = NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','CancelAddEditContentElement','id_template'|{$idTemplate}~'pindex'|'{$pIndex}','{$target}')->dft_fp_errors");
		$view->SetModalCustomClose('"'.addcslashes($customClose,'\\').'"');
        $view->AddBasicForm($this->GetViewFile('FieldPropertiesForm'),[
            'container_type'=>'default',
        ]);
        $tabCtrl = ModulesProvider::Exec(Controls::class,'GetControlPropertiesTab',['id_control'=>$idControl,'data'=>$item->getProperty('params','','is_string'),'target'=>'dft_fp_properties_tab']);
        if(is_object($tabCtrl)) {
            $view->AddObjectContent($tabCtrl,'Show',[
                'container_type'=>'default',
                'title'=>Translate::GetTitle('control_properties'),
            ]);
        }//if(is_object($tabCtrl))
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="dft_fp_form_errors"></div></div>');
		$btnSave = new Button(['value'=>Translate::GetButton('save'),'class'=>NApp::$theme->GetBtnPrimaryClass(),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->class}','AddEditContentElementRecord',
                'id_template'|{$idTemplate}
                ~'pindex'|'{$pIndex}'
                ~'id_item'|'{$id}'
                ~'class'|'{$cClass}'
                ~'data_type'|'{$cDataType}'
                ~'frow'|'{$fRow}'
                ~'fcol'|'{$fCol}'
                ~'id_control'|'{$idControl}'
                ~'ctarget'|'{$target}'
                ~dft_fp_form:form
                ~'properties'|dft_fp_properties_tab:form
            ,'dft_fp_form')->dft_fp_form_errors")]);
        $view->AddAction($btnSave->Show());
        $btnCancel = new Button(['value'=>Translate::GetButton('cancel'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-ban','onclick'=>$customClose]);
        $view->AddAction($btnCancel->Show());
        $view->Render();
	}//END public function AddEditContentElement
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function CancelAddEditContentElement($params = NULL) {
	    $idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pIndex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$this->CloseForm();
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowContentTable',['id_template'=>$idTemplate,'pindex'=>$pIndex,'target'=>$target],$target);
	}//END public function CancelAddEditContentElement
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditContentElementRecord($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pIndex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$id = $params->safeGet('id_item',NULL,'is_not0_numeric');
		$name = $params->safeGet('name','','is_string');
		$label = $params->safeGet('label','','is_string');
		$label_required = $params->safeGet('label_required',FALSE,'is_bool');
		$target = $params->safeGet('target','','is_string');
		if(!strlen($name) || ($label_required && !strlen($label))) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!strlen($name) || ($label_required && !strlen($label)))
		$required = $params->safeGet('required',0,'is_integer');
		$listing = $params->safeGet('listing',0,'is_integer');
		$colSpan = $params->safeGet('colspan',0,'is_integer');
		$colSpan = $colSpan>1 ? $colSpan : NULL;
		$idValuesList = $params->safeGet('id_values_list',NULL,'is_numeric');
		// process field properties
		$fParams = ModulesProvider::Exec(Controls::class,'ProcessFieldProperties',[
			'id_control'=>$params->safeGet('id_control',NULL,'is_integer'),
			'data'=>$params->safeGet('properties',NULL,'is_array'),
		]);
		if($id) {
			$result = DataProvider::Get('Plugins\DForms\Templates','SetField',[
				'for_id'=>$id,
				'in_itype'=>$params->safeGet('itype',NULL,'is_not0_integer'),
				'in_frow'=>NULL,
				'in_fcol'=>NULL,
				'in_name'=>$name,
				'in_label'=>$label,
				'in_required'=>$required,
				'in_listing'=>$listing,
				'values_list_id'=>$idValuesList,
				'in_class'=>NULL,
				'in_data_type'=>NULL,
				'in_params'=>$fParams,
				'in_colspan'=>$colSpan,
				'in_description'=>$params->safeGet('description',NULL,'is_string'),
			]);
			if($result===FALSE) { throw new AppException('Unknown database error!'); }
		} else {
			$class = $params->safeGet('class','','is_string');
			$fCol = $params->safeGet('fcol',0,'is_integer');
			$fRow = $params->safeGet('frow',0,'is_integer');
			if(!$fRow || !$fCol || !strlen($class)) { throw new AppException('Invalid field data!'); }
			$data_type = $params->safeGet('data_type','','is_string');
			if($class=='BasicForm') {
				$idSubForm = $params->safeGet('id_sub_form',0,'is_integer');
				if(!$idSubForm) {
					NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
					echo Translate::GetMessage('required_fields');
					return;
				}//if(!$idSubForm)
				$name = NULL;
				$label = NULL;
			} else {
				$idSubForm = NULL;
			}//if($class=='BasicForm' && !$idSubForm)
			$result = DataProvider::Get('Plugins\DForms\Templates','SetNewField',[
				'template_id'=>$idTemplate,
				'sub_form_id'=>$idSubForm,
				'in_pindex'=>$pIndex,
				'in_itype'=>$params->safeGet('itype',1,'is_not0_integer'),
				'in_frow'=>$fRow,
				'in_fcol'=>$fCol,
				'in_name'=>$name,
				'in_label'=>$label,
				'in_required'=>$required,
				'in_listing'=>$listing,
				'values_list_id'=>$idValuesList,
				'in_class'=>$class,
				'in_data_type'=>$data_type,
				'in_params'=>$fParams,
				'in_colspan'=>$colSpan,
				'in_description'=>$params->safeGet('description',NULL,'is_string'),
			]);
			if(!is_object($result) || !is_object($result->first()) || $result->first()->getProperty('inserted_id',0,'is_integer')<=0) { throw new AppException('Unknown database error!'); }
		}//if($id)
		$this->CloseForm();
		$cTarget = $params->safeGet('ctarget','','is_string');
		$this->Exec('ShowContentTable',['id_template'=>$idTemplate,'pindex'=>$pIndex,'target'=>$cTarget],$cTarget);
	}//END public function AddEditContentElementRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function MoveContentElement($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$initialPageIndex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$id = $params->getOrFail('id_item','is_not0_integer','Invalid field identifier!');
		$cell = $params->safeGet('cell','','is_string');
		$cellArray = explode('-',$cell);
		if(!is_array($cellArray) || count($cellArray)!=3) { throw new AppException('Invalid template cell!'); }
		$pIndex = $cellArray[0];
		$fRow = $cellArray[1];
		$fCol = $cellArray[2];
		if(!$fRow || !$fCol) { throw new AppException('Invalid field data!'); }
		$result = DataProvider::Get('Plugins\DForms\Templates','SetField',[
			'for_id'=>$id,
			'in_pindex'=>$pIndex,
			'in_itype'=>NULL,
			'in_frow'=>$fRow,
			'in_fcol'=>$fCol,
			'in_name'=>NULL,
			'in_label'=>NULL,
			'in_required'=>NULL,
			'in_class'=>NULL,
			'in_data_type'=>NULL,
			'in_params'=>NULL,
		]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		// 	$target = $params->safeGetValue('target','');
		// 	NApp::Ajax()->Execute("AjaxRequest('{$this->class}','ShowContentTable','id_template'|{$idTemplate},'{$target}')->{$target}");
	}//END public function MoveContentElement
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteContentElementRecord($params = NULL) {
		// NApp::Dlog($params,'DeleteContentElementRecord');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pIndex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$id = $params->getOrFail('id','is_not0_integer','Invalid field identifier!');
		$result = DataProvider::Get('Plugins\DForms\Templates','UnsetField',['for_id'=>$id]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowContentTable',['id_template'=>$idTemplate,'pindex'=>$pIndex,'target'=>$target],$target);
	}//END public function DeleteContentElementRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function CloneRecord($params = NULL) {
		// NApp::Dlog($params,'DeleteContentElementRecord');
		$id = $params->getOrFail('id','is_not0_integer','Invalid field identifier!');
		$result = DataProvider::Get('Plugins\DForms\Templates','CloneItem',['for_id'=>$id,'user_id'=>NApp::GetCurrentUserId()]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$this->Exec('Listing',[],'main-content');
    }//END public function CloneRecord
}//END class Templates extends Module