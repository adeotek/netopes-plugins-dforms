<?php
/**
 * description
 *
 * description
 *
 * @package    DKMed\Modules\Application
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2019 AdeoTEK Software
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\ValuesLists;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\Controls\Button;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\AppException;
use NApp;
use Translate;

/**
 * description
 *
 * description
 *
 * @package  DKMed\Modules\Application
 * @access   public
 */
class ValuesLists extends Module {
	/**
	 * description
	 *
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function Listing($params = NULL) {
		$view = new AppView(get_defined_vars(),$this,'main');
		$view->AddTableView($this->GetViewFile('Listing'));
		$view->SetTitle(Translate::GetLabel('values_lists'));
		$btn_add = new Button(array('tagid'=>'df_list_add_btn','value'=>Translate::GetButton('add').' '.Translate::GetLabel('values_list'),'class'=>NApp::$theme->GetBtnInfoClass(),'icon'=>'fa fa-plus','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->name}','ShowAddForm')->modal")));
		$view->AddAction($btn_add->Show());
		$view->SetTargetId('listing-content');
		$view->Render();
	}//END public function Listing
	/**
	 * description
	 *
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddForm($params = NULL) {
		$view = new AppView(get_defined_vars(),$this,'modal');
		$view->SetIsModalView(true);
		$view->AddBasicForm($this->GetViewFile('AddForm'));
		$view->SetTitle(Translate::GetTitle('add_values_list'));
		$view->SetModalWidth(500);
		$view->Render();
		NApp::arequest()->ExecuteJs("$('#df_list_add_code').focus();");
	}//END public function ShowAddForm
	/**
	 * description
	 *
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowEditForm($params = NULL) {
		$id = $params->safeGet('id',NULL,'is_integer');
		if(!$id) { throw new AppException('Invalid record identifier!'); }
		$item = DataProvider::GetArray('Components\DForms\ValuesLists','GetItems',array('for_id'=>$id));
		$item = $item[0];
		$view = new AppView(get_defined_vars(),$this,'main');
		$view->AddTabControl($this->GetViewFile('EditForm'));
		$view->SetTitle(Translate::GetLabel('values_list').' - '.Translate::GetButton('edit'));
		$btn_cancel = new Button(array('tagid'=>'df_list_edit_cancel','value'=>Translate::GetButton('back'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-chevron-left','onclick'=>NApp::arequest()->PrepareAjaxRequest(['module'=>$this->name,'method'=>'Listing','target'=>'main-content'])));
		$view->AddAction($btn_cancel->Show());
		$view->Render();
		NApp::arequest()->ExecuteJs("$('#df_list_edit_code').focus();");
	}//END public function ShowEditForm
	/**
	 * description
	 *
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditRecord($params = NULL) {
		// NApp::_Dlog($params,'AddEditRecord');
		$id = $params->safeGet('id',NULL,'is_integer');
		$ltype = $params->safeGet('ltype',NULL,'is_notempty_string');
		$name = $params->safeGet('name',NULL,'is_notempty_string');
		$target = $params->safeGet('target','','is_string');
		if(!$name || (!$id && !$ltype)) {
			NApp::arequest()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!$name || (!$id && !$ltype))
		if($id) {
			$result = DataProvider::GetArray('Components\DForms\ValuesLists','SetItem',array(
				'for_id'=>$id,
				'in_name'=>$name,
				'in_state'=>$params->safeGet('state',1,'is_integer'),
			));
			if($result!==FALSE && $params->safeGet('close',1,'is_integer')==1) {
				// $this->CloseForm();
				NApp::arequest()->Execute("AjaxRequest('Components\DForms\ValuesLists\ValuesLists','Listing')->main-content");
				return;
			}//if($result!==FALSE)
			echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
		} else {
			$result = DataProvider::GetArray('Components\DForms\ValuesLists','SetNewItem',array(
				'in_ltype'=>$ltype,
				'in_name'=>$name,
				'in_state'=>$params->safeGet('state',1,'is_integer'),
			));
			$id = get_array_value($result,0,0,'is_numeric','inserted_id');
			if($result!==FALSE && $id>0) {
				$this->CloseForm();
				NApp::arequest()->Execute("AjaxRequest('Components\DForms\ValuesLists\ValuesLists','ShowEditForm','id'|{$id})->main-content");
			}//if($result!==FALSE && $id>0)
		}//if($id)
	}//END public function AddEditRecord
	/**
	 * description
	 *
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteRecord($params = NULL) {
		$id = $params->safeGet('id',NULL,'is_integer');
		if(!$id) { throw new AppException('Invalid record identifier!'); }
		$result = DataProvider::GetArray('Components\DForms\ValuesLists','UnsetItem',array('for_id'=>$id));
		if($result!==FALSE) {
			NApp::arequest()->Execute("AjaxRequest('Components\DForms\ValuesLists\ValuesLists','Listing')->main-content");
		}//if($result!==FALSE)
	}//END public function DeleteRecord
	/**
	 * description
	 *
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ValuesListing($params = NULL) {
		// NApp::_Dlog($params,'ValuesListing');
		$id_list = $params->safeGet('id_list',NULL,'is_integer');
		if(!$id_list) { throw new AppException('Invalid list identifier!'); }
		$edit = $params->safeGet('edit',0,'is_integer');
		$target = $params->safeGet('target','','is_string');
		if($edit) {
			$dgtarget = 'dg-'.$target;
			$view = new AppView(get_defined_vars(),$this,'secondary');
			$view->SetTargetId($dgtarget);
			$btn_add = new Button(array('tagid'=>'df_list_edit_add','value'=>Translate::GetButton('add'),'class'=>NApp::$theme->GetBtnInfoClass('btn-xs pull-left'),'icon'=>'fa fa-plus-circle','onclick'=>NApp::arequest()->PrepareAjaxRequest(['module'=>'Components\DForms\ValuesLists\ValuesLists','method'=>'ShowValueAddEditForm','target'=>'modal','params'=>['id_list'=>$id_list,'target'=>$target]])));
			$view->AddAction($btn_add->Show());
		} else {
			$dgtarget = $target;
			$view = new AppView(get_defined_vars(),$this,'modal');
			$view->SetIsModalView(true);
			$view->SetTitle(Translate::GetLabel('values_list').' - '.Translate::GetLabel('values'));
			$view->SetModalWidth('80%');
		}//if($edit)
		$view->AddTableView($this->GetViewFile('ValuesListing'));
		$view->Render();
	}//END public function ValuesListing
	/**
	 * description
	 *
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowValueAddEditForm($params = NULL) {
		// NApp::_Dlog($params,'ShowValueAddEditForm');
		$id_list = $params->safeGet('id_list',NULL,'is_integer');
		if(!$id_list) { throw new AppException('Invalid list identifier!'); }
		$id = $params->safeGet('id',NULL,'is_integer');
		if($id) {
			$item = DataProvider::GetArray('Components\DForms\ValuesLists','GetValues',array('for_id'=>$id));
			$item = $item[0];
		} else {
			$item = [];
		}//if($id)
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,'modal');
		$view->SetIsModalView(true);
		$view->AddBasicForm($this->GetViewFile('ValueAddEditForm'));
		$view->SetTitle(Translate::GetTitle('add_values_list').' - '.Translate::GetLabel('value').' - '.Translate::Get($id ? 'button_edit' : 'button_add'));
		$view->SetModalWidth(500);
		$view->Render();
		NApp::arequest()->ExecuteJs("\$('#df_lv_ae_value').focus();");
	}//END public function ShowValueAddEditForm
	/**
	 * description
	 *
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditValueRecord($params = NULL){
		// NApp::_Dlog($params,'AddEditValueRecord');
		$id_list = $params->safeGet('id_list',NULL,'is_integer');
		if(!$id_list) { throw new AppException('Invalid list identifier!'); }
		$id = $params->safeGet('id',NULL,'is_integer');
		$value = $params->safeGet('value','','is_string');
		$name = $params->safeGet('name',NULL,'is_notempty_string');
		$target = $params->safeGet('target','','is_string');
		if(!strlen($value)) {
			NApp::arequest()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!strlen($value))
		if($id) {
			$result = DataProvider::GetArray('Components\DForms\ValuesLists','SetValue',array(
				'for_id'=>$id,
				'in_value'=>$value,
				'in_name'=>$name,
				'in_state'=>$params->safeGet('state',1,'is_integer'),
				'in_implicit'=>$params->safeGet('implicit',0,'is_integer'),
			));
		} else {
			$result = DataProvider::GetArray('Components\DForms\ValuesLists','SetNewValue',array(
				'list_id'=>$id_list,
				'in_value'=>$value,
				'in_name'=>$name,
				'in_state'=>$params->safeGet('state',1,'is_integer'),
				'in_implicit'=>$params->safeGet('implicit',0,'is_integer'),
			));
			$result = get_array_value($result,0,0,'is_numeric','inserted_id')>0;
		}//if($id)
		if($result!==FALSE) {
			$this->CloseForm();
			$ctarget = $params->safeGet('ctarget','','is_string');
			NApp::arequest()->Execute("AjaxRequest('Components\DForms\ValuesLists\ValuesLists','ValuesListing','id_list'|{$id_list}~'edit'|1,'{$ctarget}')->{$ctarget}");
		}//if($result!==FALSE)
	}//END public function AddEditValueRecord
	/**
	 * description
	 *
	 * @param object|null $params Parameters object (instance of [Params])
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteValueRecord($params = NULL) {
		$id = $params->safeGet('id',NULL,'is_integer');
		$id_list = $params->safeGet('id_list',NULL,'is_integer');
		if(!$id || !$id_list) { throw new AppException('Invalid record identifier!'); }
		$result = DataProvider::GetArray('Components\DForms\ValuesLists','UnsetValue',array('for_id'=>$id));
		if($result!==FALSE) {
			$target = $params->safeGet('target','','is_string');
			NApp::arequest()->Execute("AjaxRequest('Components\DForms\ValuesLists\ValuesLists','ValuesListing','id_list'|{$id_list}~'edit'|1,'{$target}')->{$target}");
		}//if($result!==FALSE)
	}//END public function DeleteValueRecord
}//END class ValuesLists extends Module
?>