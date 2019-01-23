<?php
/**
 * description
 * description
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\Modules\DForms\Templates;
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
use Translate;

/**
 * description
 * description
 * @package  NETopes\Plugins\Modules\DForms
 */
class Templates extends Module {
	/**
	 * @var string Name of field to be used as label for the form items ('label'/'name')
	 */
	public $item_label = 'label';
	/**
	 * @var int Maximum box title length (in characters)
	 */
	public $max_box_title_length = 29;
	/**
	 * Get item box title
	 * @param  array $field Item parameters array
	 * @return string Returns item box title
	 */
	public function GetItemTitle($field) {
		$title = $field->getProperty($this->item_label,'','is_string');
		if(strlen($title)>($this->max_box_title_length+2)) { $title = substr($title,0,$this->max_box_title_length).'...'; }
		if($field->getProperty('required',0,'is_integer')==1) { $title .= '*'; }
		return $title;
	}//END public function GetItemTitle
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function Listing($params = NULL) {
		// $this->Exec('ShowContentEditForm',array('id_template'=>1));
		$ftypes = DataProvider::GetKeyValueArray('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
		$view = new AppView(get_defined_vars(),$this,'main');
        $view->SetTitle('dynamic_forms_templates');
        $view->SetTargetId('listing_content');
        $btn_add = new Button(array('value'=>Translate::GetButton('add').' '.Translate::GetLabel('template'),'class'=>NApp::$theme->GetBtnInfoClass(),'icon'=>'fa fa-plus','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','ShowAddForm')->modal")));
	    $view->AddAction($btn_add->Show());
        $view->AddTableView($this->GetViewFile('Listing'));
        $view->Render();
	}//END public function Listing
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddForm($params = NULL){
		$ftypes = DataProvider::GetKeyValueArray('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
		$view = new AppView(get_defined_vars(),$this,'modal');
        $view->SetIsModalView(true);
        $view->AddBasicForm($this->GetViewFile('AddForm'));
        $view->SetTitle(Translate::GetButton('add').' '.Translate::GetLabel('template'));
        $view->SetModalWidth(620);
        $view->Render();
        NApp::arequest()->ExecuteJs("$('#df_template_add_code').focus();");
	}//END public function ShowAddForm
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowEditForm($params = NULL) {
		$id = $params->getOrFail('id','is_numeric','Invalid record identifier!');
		$item = DataProvider::Get('Components\DForms\Templates','GetItem',array('for_id'=>$id));
		if(!is_object($item)) { throw new AppException('Invalid record!'); }
		$ftypes = DataProvider::GetKeyValueArray('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
		$view = new AppView(get_defined_vars(),$this,'main');
        $view->AddTabControl($this->GetViewFile('EditForm'));
        $view->SetTitle(Translate::GetButton('edit').' '.Translate::GetLabel('template'));
        $btn_validate = new Button(array('value'=>Translate::GetButton('validate'),'class'=>NApp::$theme->GetBtnSuccessClass('mr10'),'icon'=>'fa fa-check-square-o','onclick'=>NApp::arequest()->PrepareAjaxRequest(['module'=>$this->name,'method'=>'ValidateRecord','target'=>'errors','params'=>['id'=>$id]])));
	    $view->AddAction($btn_validate->Show());
	    $btn_back = new Button(array('value'=>Translate::GetButton('back'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-chevron-left','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','Listing')->main-content")));
	    $view->AddAction($btn_back->Show());
        $view->Render();
        NApp::arequest()->ExecuteJs("$('#df_template_edit_code').focus();");
	}//END public function ShowEditForm
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditRecord($params = NULL){
		// NApp::_Dlog($params,'AddEditRecord');
		$id = $params->safeGet('id',NULL,'is_not0_numeric');
		$ftype = $params->safeGet('ftype',NULL,'is_numeric');
		$code = $params->safeGet('code',NULL,'is_numeric');
		$name = $params->safeGet('name',NULL,'is_notempty_string');
		$target = $params->safeGet('target','','is_string');
		if(!$ftype || !$code || !$name) {
			NApp::arequest()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!$ftype || !$code || !$name)
		$state = $params->safeGet('state',NULL,'is_numeric');
		$colsno = $params->safeGet('colsno',NULL,'is_numeric');
		$rowsno = $params->safeGet('rowsno',NULL,'is_numeric');
		$dmode = $params->safeGet('dmode',NULL,'is_numeric');
		$iso_code = $params->safeGet('separator_width','','is_string');
		$print_template = $params->safeGet('print_template','','is_string');
		if($id) {
			$result = DataProvider::GetArray('Components\DForms\Templates','SetItem',array(
				'for_id'=>$id,
				'in_name'=>$name,
				'in_ftype'=>$ftype,
				'in_state'=>$state,
				'in_delete_mode'=>$dmode,
				'in_iso_code'=>$iso_code,
				'in_print_template'=>$print_template,
				'user_id'=>NApp::_GetCurrentUserId(),
			));
			if($result===FALSE || $params->safeGet('close',1,'is_numeric')!=1) {
			    echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
				return;
			}//if($result!==FALSE)
			$this->Exec('Listing',[],'main-content');
		} else {
			$result = DataProvider::GetArray('Components\DForms\Templates','SetNewItem',array(
				'in_code'=>$code,
				'in_name'=>$name,
				'in_ftype'=>$ftype,
				'in_state'=>$state,
				'in_colsno'=>$colsno,
				'in_rowsno'=>$rowsno,
				'in_delete_mode'=>$dmode,
				'in_iso_code'=>$iso_code,
				'in_print_template'=>$print_template,
				'user_id'=>NApp::_GetCurrentUserId(),
			));
			$id = get_array_value($result,0,0,'is_numeric','inserted_id');
			if($result===FALSE || $id<=0) { throw new AppException('Unknown database error!'); }
			$this->CloseForm();
			$this->Exec('ShowEditForm',['id'=>$id],'main-content');
		}//if($id)
	}//END public function AddEditRecord
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function SetPrintTemplate($params = NULL) {
		// NApp::_Dlog($params,'SetPrintTemplate');
		$id = $params->getOrFail('id','is_not0_integer','Invalid record identifier!');
		$result = DataProvider::GetArray('Components\DForms\Templates','SetItem',array(
            'for_id'=>$id,
            'in_print_template'=>$params->safeGet('print_template','','is_string'),
            'user_id'=>NApp::_GetCurrentUserId(),
        ));
        if($result===FALSE || $params->safeGet('close',1,'is_numeric')!=1) {
            echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
            return;
        }//if($result===FALSE || $params->safeGet('close',1,'is_numeric')!=1)
        $this->Exec('Listing',[],'main-content');
	}//END public function SetPrintTemplate
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteRecord($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid template identifier!');
		$result = DataProvider::GetArray('Components\DForms\Templates','UnsetItem',array('for_id'=>$id));
		if($result===FALSE) { return; }
		$this->Exec('Listing',[],'main-content');
	}//END public function DeleteRecord
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function EditRecordState($params = NULL) {
		$id = $params->safeGet('id',NULL,'is_not0_numeric');
		$state = $params->safeGet('state',NULL,'is_numeric');
		if(!$id || is_null($state)) { throw new AppException('Invalid record identifier!'); }
		$result = DataProvider::GetArray('Components\DForms\Templates','SetItemState',array(
			'for_id'=>$id,
			'in_state'=>$state,
			'user_id'=>NApp::_GetCurrentUserId(),
		));
	}//END public function EditRecordState
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function CreateNewVersion($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid template identifier!');
		$result = DataProvider::GetArray('Components\DForms\Templates','SetItemValidated',array('for_id'=>$id,'new_value'=>0));
		if($result===FALSE) { return; }
		$this->Exec('ShowEditForm',['id'=>$id],'main-content');
	}//END public function CreateNewVersion
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ValidateRecord($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid template identifier!');
		$new_value = $params->safeGet('new_value',1,'is_numeric');
		$result = DataProvider::GetArray('Components\DForms\Templates','SetItemValidated',array(
			'for_id'=>$id,
			'new_value'=>$new_value,
			'user_id'=>NApp::_GetCurrentUserId(),
		));
		if($result===FALSE) { return; }
		$this->Exec('Listing',[],'main-content');
	}//END public function ValidateRecord
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowDesignEditForm($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$item = DataProvider::Get('Components\DForms\Templates','GetItemProperties',['template_id'=>$idTemplate]);
		if(!is_object($item) || $item instanceof DataSet) { $item = new VirtualEntity([]); }
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,'secondary');
        $view->AddBasicForm($this->GetViewFile('DesignEditForm'));
        $view->Render();
	}//END public function ShowDesignEditForm
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function EditDesignRecord($params = NULL){
		// NApp::_Dlog($params,'EditDesignRecord');
		$id_template = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$renderType = $params->safeGet('render_type',NULL,'is_integer');
		$target = $params->safeGet('target','','is_string');
		if(!$renderType) {
		    NApp::arequest()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!$renderType)
		$result = DataProvider::Get('Components\DForms\Templates','SetPropertiesItem',array(
            'template_id'=>$id_template,
            'in_render_type'=>$renderType,
            'in_theme_type'=>$params->safeGet('theme_type','','is_string'),
            'in_controls_size'=>$params->safeGet('controls_size','','is_string'),
            'in_label_cols'=>$params->safeGet('label_cols',NULL,'is_not0_integer'),
            'in_separator_width'=>$params->safeGet('separator_width','','is_string'),
        ));
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$ctarget = $params->safeGet('ctarget','','is_string');
		if(strlen($ctarget)) {
		    $this->Exec('ShowDesignEditForm',['id_template'=>$id_template,'target'=>$ctarget],$ctarget);
		} else {
		    echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
		}//if(strlen($ctarget))
	}//END public function EditDesignRecord
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowRelationsEditForm($params = NULL) {
		$id_template = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,NULL);
        $view->AddContent($this->GetViewFile('RelationsEditForm'));
        $view->Render();
	}//END public function ShowRelationsEditForm
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowRelationAddEditForm($params = NULL) {
		// NApp::_Dlog($params,'ShowRelationAddEditForm');
		$id_template = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$id = $params->safeGet('id',NULL,'is_not0_numeric');
		if($id) {
			$item = DataProvider::GetArray('Components\DForms\Templates','GetRelations',array('for_id'=>$id));
			$item = $item[0];
		} else {
			$item = [];
		}//if($id)
		$target = $params->safeGet('target','','is_string');
		require($this->GetViewFile('RelationAddEditForm'));
		$basicform = new BasicForm($ctrl_params);
		echo $basicform->Show();
		NApp::arequest()->ExecuteJs("ShowModalForm(500,($('#page-title').html()+' - ".Translate::GetLabel('relation').' - '.Translate::Get($id ? 'button_edit' : 'button_add')."'))");
		NApp::arequest()->ExecuteJs("\$('#df_template_rel_ae_type').focus();");
	}//END public function ShowRelationAddEditForm
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditRelationRecord($params = NULL){
		// NApp::_Dlog($params,'AddEditRelationRecord');
		$id_template = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$id = $params->safeGet('id',NULL,'is_integer');
		$id_type = $params->safeGet('type',NULL,'is_integer');
		$rtype = $params->safeGet('rtype',NULL,'is_integer');
		$utype = $params->safeGet('utype',NULL,'is_integer');
		$name = $params->safeGet('name',NULL,'is_string');
		$key = $params->safeGet('key',NULL,'is_string');
		$target = $params->safeGet('target','');
		if(!strlen($name) || !strlen($key) || !is_numeric($rtype) || !is_numeric($utype) || (!$id && !$id_type)) {
			NApp::arequest()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!strlen($name) || !strlen($key) || !is_numeric($rtype) || !is_numeric($utype) || (!$id && !$type))
		if($id) {
			$result = DataProvider::GetArray('Components\DForms\Templates','SetRelation',array(
				'for_id'=>$id,
				'in_name'=>$name,
				'in_key'=>$key,
				'in_required'=>$params->safeGet('required',0,'is_integer'),
				'in_rtype'=>$rtype,
				'in_utype'=>$utype,
			));
		} else {
			$result = DataProvider::GetArray('Components\DForms\Templates','SetNewRelation',array(
				'template_id'=>$id_template,
				'relation_type_id'=>$id_type,
				'in_name'=>$name,
				'in_key'=>$key,
				'in_required'=>$params->safeGet('required',0,'is_integer'),
				'in_rtype'=>$rtype,
				'in_utype'=>$utype,
			));
			$result = get_array_value($result,0,0,'is_numeric','inserted_id')>0;
		}//if($id)
		if($result!==FALSE) {
			$this->CloseForm();
			$ctarget = $params->safeGet('ctarget','','is_string');
			NApp::arequest()->Execute("AjaxRequest('{$this->name}','ShowRelationsEditForm','id_template'|{$id_template},'{$ctarget}')->{$ctarget}");
		}//if($result!==FALSE)
	}//END public function AddEditRelationRecord
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteRelationRecord($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid record identifier!');
		$id_template = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$result = DataProvider::GetArray('Components\DForms\Templates','UnsetRelation',array('for_id'=>$id));
		if($result===FALSE) { return; }
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowRelationsEditForm',['id_template'=>$id_template,'target'=>$target],$target);
	}//END public function DeleteRelationRecord
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowContentEditForm($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$templateProps = DataProvider::Get('Components\DForms\Templates','GetItemProperties',['template_id'=>$idTemplate]);
		$fieldsTypes = DataProvider::Get('Components\DForms\Controls','GetItems',['for_state'=>1]);
		$templatePages = DataProvider::Get('Components\DForms\Templates','GetItemPages',['template_id'=>$idTemplate],['sort'=>['pindex'=>'asc']]);
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,NULL);
        $view->AddContent($this->GetViewFile('ContentEditForm'));
        $view->Render();
	}//END public function ShowContentEditForm
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowContentTable($params = NULL) {
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pindex = $params->getOrFail('pindex','is_integer','Invalid template page identifier!');
		$templatePage = DataProvider::Get('Components\DForms\Templates','GetItemPage',['template_id'=>$idTemplate,'for_pindex'=>$pindex]);
		$fields = DataProvider::GetKeyValue('Components\DForms\Templates','GetFields',['template_id'=>$idTemplate,'for_pindex'=>$pindex],['keyfield'=>'cell']);
		$target = $params->safeGet('target','','is_string');
		$ctarget = $params->safeGet('ctarget','','is_string');
		$view = new AppView(get_defined_vars(),$this,NULL);
        $view->AddContent($this->GetViewFile('ContentTable'));
        $view->Render();
	}//END public function ShowContentTable
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddPageForm($params = NULL) {
		// NApp::_Dlog($params,'ShowAddPageForm');
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
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function UpdatePagesList($params = NULL) {
		// NApp::_Dlog($params,'UpdatePagesList');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$type = $params->getOrFail('type','is_integer','Invalid action type!');
		$pindex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		if($type<0) {
			$result = DataProvider::Get('Components\DForms\Templates','UnsetTemplatePage',[
				'for_id'=>$idTemplate,
				'in_pindex'=>$pindex,
			]);
		} elseif($type==0) {
			$result = DataProvider::Get('Components\DForms\Templates','SetTemplatePage',[
				'for_id'=>$idTemplate,
				'in_pindex'=>$pindex,
			]);
		} else {
			$result = DataProvider::Get('Components\DForms\Templates','SetNewTemplatePage',[
				'for_id'=>$idTemplate,
				'in_pindex'=>$pindex,
			]);
		}//if($type<0)
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		if($params->safeGet('close',0,'is_integer')==1) { $this->CloseForm(); }
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowContentEditForm',['id_template'=>$idTemplate,'target'=>$target],$target);
	}//END public function UpdatePagesList
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddTableElementForm($params = NULL) {
		// NApp::_Dlog($params,'ShowAddTableElementForm');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pindex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$type = $params->safeGet('type','','is_string');
		$lastPos = $params->safeGet('last_pos',0,'is_numeric');
		if(!strlen($type) || $lastPos<=0) { return; }
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
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function UpdateContentTable($params = NULL) {
		// NApp::_Dlog($params,'UpdateContentTable');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pindex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$type = $params->getOrFail('type','is_integer','Invalid action type!');
		$colsno = $params->safeGet('colsno',NULL,'is_not0_integer');
		$rowsno = $params->safeGet('rowsno',NULL,'is_not0_integer');
		if($colsno===NULL && $rowsno===NULL) { return; }
		if($type<0) {
			$result = DataProvider::GetArray('Components\DForms\Templates','UnsetTableCell',array(
				'for_id'=>$idTemplate,
				'in_col'=>$colsno,
				'in_row'=>$rowsno,
				'in_pindex'=>$pindex,
			));
		} elseif($type==0) {
			$result = DataProvider::GetArray('Components\DForms\Templates','SetTableCell',array(
				'for_id'=>$idTemplate,
				'in_col'=>$colsno,
				'in_row'=>$rowsno,
				'in_pindex'=>$pindex,
			));
		} else {
			$result = DataProvider::GetArray('Components\DForms\Templates','SetNewTableCell',array(
				'for_id'=>$idTemplate,
				'in_col'=>$colsno,
				'in_row'=>$rowsno,
				'in_pindex'=>$pindex,
			));
		}//if($type<0)
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		if($params->safeGet('close',0,'is_integer')==1) { $this->CloseForm(); }
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowContentTable',['id_template'=>$idTemplate,'pindex'=>$pindex,'target'=>$target],$target);
	}//END public function UpdateContentTable
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditContentElement($params = NULL) {
		// NApp::_Dlog($params,'AddEditContentElement');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pindex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$idControl = $params->getOrFail('id_control','is_not0_integer','Invalid control identifier!');
		$fieldType = DataProvider::Get('Components\DForms\Controls','GetItem',['for_id'=>$idControl]);
		$id = $params->safeGet('id_item',NULL,'is_not0_integer');
		if($id) {
			$item = DataProvider::Get('Components\DForms\Templates','GetField',['for_id'=>$id]);
			$frow = $item->getProperty('frow',0,'is_integer');
			$fcol = $item->getProperty('fcol',0,'is_integer');
		} else {
			$cell = $params->safeGet('cell','','is_string');
			$cell_arr = explode('-',$cell);
			if(!is_array($cell_arr) || count($cell_arr)!=3) { throw new AppException('Invalid template cell!'); }
			$frow = $cell_arr[1];
			$fcol = $cell_arr[2];
			if(!$frow || !$fcol) { throw new AppException('Invalid cell data!'); }
			$item = new VirtualEntity();
		}//if(!$id)
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,'modal');
		$view->SetIsModalView(TRUE);
		$view->SetModalWidth(560);
		$view->SetModalCustomClose('"'.$custom_close = addcslashes(NApp::arequest()->Prepare("AjaxRequest('{$this->name}','CancelAddEditContentElement','id_template'|{$idTemplate}~'pindex'|'{$pindex}','{$target}')->dft_fp_errors"),'\\').'"');
		$view->SetTitle(Translate::GetLabel('field_properties'));
        $view->AddBasicForm($this->GetViewFile('FieldPropertiesForm'));
        $view->Render();
	}//END public function AddEditContentElement
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function CancelAddEditContentElement($params = NULL) {
	    $idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pindex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$this->CloseForm();
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowContentTable',['id_template'=>$idTemplate,'pindex'=>$pindex,'target'=>$target],$target);
	}//END public function CancelAddEditContentElement
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditContentElementRecord($params = NULL) {
		// NApp::_Dlog($params,'AddEditContentElementRecord');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pindex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$id = $params->safeGet('id_item',NULL,'is_not0_numeric');
		$name = $params->safeGet('name','','is_string');
		$label = $params->safeGet('label','','is_string');
		$label_required = $params->safeGet('label_required',FALSE,'is_bool');
		$target = $params->safeGet('target','','is_string');
		if(!strlen($name) || ($label_required && !strlen($label))) {
			NApp::arequest()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!strlen($name) || ($label_required && !strlen($label)))
		$required = $params->safeGet('required',0,'is_integer');
		$listing = $params->safeGet('listing',0,'is_integer');
		$id_values_list = $params->safeGet('id_values_list',NULL,'is_numeric');
		// process field properties
		$fparams = ModulesProvider::Exec('Components\DForms\Controls\Controls','ProcessFieldProperties',[
			'id_control'=>$params->safeGet('id_control',NULL,'is_integer'),
			'data'=>$params->safeGet('properties',NULL,'is_array'),
		]);
		if($id) {
			$result = DataProvider::Get('Components\DForms\Templates','SetField',[
				'for_id'=>$id,
				'in_itype'=>$params->safeGet('itype',NULL,'is_not0_integer'),
				'in_frow'=>NULL,
				'in_fcol'=>NULL,
				'in_name'=>$name,
				'in_label'=>$label,
				'in_required'=>$required,
				'in_listing'=>$listing,
				'values_list_id'=>$id_values_list,
				'in_class'=>NULL,
				'in_data_type'=>NULL,
				'in_params'=>$fparams,
				'in_description'=>$params->safeGet('description',NULL,'is_string'),
			]);
			if($result===FALSE) { throw new AppException('Unknown database error!'); }
		} else {
			$class = $params->safeGet('class','','is_string');
			$fcol = $params->safeGet('fcol',0,'is_integer');
			$frow = $params->safeGet('frow',0,'is_integer');
			if(!$frow || !$fcol || !strlen($class)) { throw new AppException('Invalid field data!'); }
			$data_type = $params->safeGet('data_type','','is_string');
			if($class=='BasicForm') {
				$id_sub_form = $params->safeGet('id_sub_form',0,'is_integer');
				if(!$id_sub_form) {
					NApp::arequest()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
					echo Translate::GetMessage('required_fields');
					return;
				}//if(!$id_sub_form)
				$name = NULL;
				$label = NULL;
			} else {
				$id_sub_form = NULL;
			}//if($class=='BasicForm' && !$id_sub_form)
			$result = DataProvider::Get('Components\DForms\Templates','SetNewField',[
				'template_id'=>$idTemplate,
				'sub_form_id'=>$id_sub_form,
				'in_pindex'=>$pindex,
				'in_itype'=>$params->safeGet('itype',1,'is_not0_integer'),
				'in_frow'=>$frow,
				'in_fcol'=>$fcol,
				'in_name'=>$name,
				'in_label'=>$label,
				'in_required'=>$required,
				'in_listing'=>$listing,
				'values_list_id'=>$id_values_list,
				'in_class'=>$class,
				'in_data_type'=>$data_type,
				'in_params'=>$fparams,
				'in_description'=>$params->safeGet('description',NULL,'is_string'),
			]);
			if(!is_object($result) || !is_object($result->first()) || $result->first()->getProperty('inserted_id',0,'is_integer')<=0) { throw new AppException('Unknown database error!'); }
		}//if($id)
		$this->CloseForm();
		$ctarget = $params->safeGet('ctarget','','is_string');
		$this->Exec('ShowContentTable',['id_template'=>$idTemplate,'pindex'=>$pindex,'target'=>$ctarget],$ctarget);
	}//END public function AddEditContentElementRecord
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function MoveContentElement($params = NULL) {
		// NApp::_Dlog($params,'MoveContentElement');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pindex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$id = $params->getOrFail('id','is_not0_integer','Invalid field identifier!');
		$cell = $params->safeGet('cell','','is_string');
		$cell_arr = explode('-',$cell);
		if(!is_array($cell_arr) || count($cell_arr)!=3) { throw new AppException('Invalid template cell!'); }
		$frow = $cell_arr[1];
		$fcol = $cell_arr[2];
		if(!$frow || !$fcol) { throw new AppException('Invalid field data!'); }
		$result = DataProvider::Get('Components\DForms\Templates','SetField',[
			'for_id'=>$id,
			'in_itype'=>NULL,
			'in_frow'=>$frow,
			'in_fcol'=>$fcol,
			'in_name'=>NULL,
			'in_label'=>NULL,
			'in_required'=>NULL,
			'in_class'=>NULL,
			'in_data_type'=>NULL,
			'in_params'=>NULL,
		]);
		// if($result!==FALSE) {
		// 	$target = $params->safeGetValue('target','');
		// 	NApp::arequest()->Execute("AjaxRequest('{$this->name}','ShowContentTable','id_template'|{$id_template},'{$target}')->{$target}");
		// }//if($result!==FALSE)
	}//END public function MoveContentElement
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteContentElementRecord($params = NULL) {
		// NApp::_Dlog($params,'DeleteContentElementRecord');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$pindex = $params->getOrFail('pindex','is_integer','Invalid page position!');
		$id = $params->getOrFail('id','is_not0_integer','Invalid field identifier!');
		$result = DataProvider::Get('Components\DForms\Templates','UnsetField',['for_id'=>$id]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ShowContentTable',['id_template'=>$idTemplate,'pindex'=>$pindex,'target'=>$target],$target);
	}//END public function DeleteContentElementRecord
	/**
	 * description
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function CloneRecord($params = NULL) {
		// NApp::_Dlog($params,'DeleteContentElementRecord');
		$id = $params->getOrFail('id','is_not0_integer','Invalid field identifier!');
		$result = DataProvider::Get('Components\DForms\Templates','CloneItem',['for_id'=>$id,'user_id'=>NApp::_GetCurrentUserId()]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$this->Exec('Listing',[],'main-content');
    }//END public function CloneRecord
}//END class Templates extends Module