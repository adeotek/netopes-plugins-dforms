<?php
/**
 * RelationsTypes class file
 *
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\RelationsTypes;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\Controls\Button;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\VirtualEntity;
use NETopes\Core\AppException;
use NApp;
use Translate;

/**
 * Class RelationsTypes
 *
 * @package  NETopes\Plugins\Modules\DForms
 */
class RelationsTypes extends Module {
    /**
     * Module class initializer
     *
     * @return void
     */
    protected function _Init() {
        $this->viewsExtension='.php';
    }//END protected function _Init

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function Listing($params=NULL) {
        $view=new AppView(get_defined_vars(),$this,'main');
        $view->AddTableView($this->GetViewFile('Listing'));
        $view->SetTitle(Translate::GetLabel('relations_types'));
        if(!$this->AddDRights()) {
            $btn_add=new Button(['value'=>Translate::GetButton('add_relation_type'),'class'=>NApp::$theme->GetBtnInfoClass(),'icon'=>'fa fa-plus','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'ShowAddForm', 'params': {  } }",'modal')]);
            $view->AddAction($btn_add->Show());
        }//if(!$this->AddDRights())
        $view->SetTargetId('listing-content');
        $view->Render();
    }//END public function Listing

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddForm($params=NULL) {
        $id=NULL;
        $item=new VirtualEntity();
        $view=new AppView(get_defined_vars(),$this,'modal');
        $view->SetIsModalView(TRUE);
        $view->AddBasicForm($this->GetViewFile('AddEditForm'));
        $view->SetTitle(Translate::GetTitle('add_relation_type'));
        $view->SetModalWidth(500);
        $view->Render();
        NApp::Ajax()->ExecuteJs("$('#df_rel_type_ae_name').focus();");
    }//END public function ShowAddForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowEditForm($params=NULL) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid record identifier!');
        $item=DataProvider::Get('Plugins\DForms\Relations','GetTypeItem',['for_id'=>$id]);
        $title=Translate::GetTitle('edit_relation_type').': '.$item->getProperty('name');
        $view=new AppView(get_defined_vars(),$this,'modal');
        $view->AddBasicForm($this->GetViewFile('AddEditForm'));
        $view->SetIsModalView(TRUE);
        $view->SetTitle($title);
        $view->SetModalWidth(500);
        $view->Render();
        NApp::Ajax()->ExecuteJs("$('#df_rel_type_ae_name').focus();");
    }//END public function ShowEditForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function AddEditRecord($params=NULL) {
        $id=$params->safeGet('id',NULL,'is_integer');
        $dType=$params->safeGet('dtype',NULL,'is_notempty_string');
        $name=trim($params->safeGet('name','','is_notempty_string'));
        $tableName=$params->safeGet('table_name',NULL,'is_notempty_string');
        $columnName=$params->safeGet('column_name',NULL,'is_notempty_string');
        $target=$params->safeGet('target','','is_string');
        if(!strlen($name) || !$dType || !$tableName | !$columnName) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//ifif(!strlen($name) || !$dType || !$tableName | !$columnName)
        if($id) {
            $result=DataProvider::Get('Plugins\DForms\Relations','SetTypeItem',[
                'for_id'=>$id,
                'in_dtype'=>$dType,
                'in_name'=>$name,
                'in_table_name'=>$tableName,
                'in_column_name'=>$columnName,
                'in_display_fields'=>$params->safeGet('display_fields',NULL,'is_string'),
                'in_state'=>$params->safeGet('state',1,'is_integer'),
            ]);
            if($result===FALSE) {
                throw new AppException('Unknown database error!');
            }
        } else {
            $result=DataProvider::Get('Plugins\DForms\Relations','SetNewTypeItem',[
                'in_dtype'=>$dType,
                'in_name'=>$name,
                'in_table_name'=>$tableName,
                'in_column_name'=>$columnName,
                'in_display_fields'=>$params->safeGet('display_fields',NULL,'is_string'),
                'in_state'=>$params->safeGet('state',1,'is_integer'),
            ]);
            if(!is_object($result) || !count($result)) {
                throw new AppException('Unknown database error!');
            }
            $id=$result->first()->getProperty('inserted_id',0,'is_integer');
            if($id<=0) {
                throw new AppException('Unknown database error!');
            }
        }//if($id)
        $this->CloseForm();
        $this->Exec('Listing',[],'main-content');
    }//END public function AddEditRecord

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function DeleteRecord($params=NULL) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid record identifier!');
        $result=DataProvider::Get('Plugins\DForms\Relations','UnsetTypeItem',['for_id'=>$id]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        $this->Exec('Listing',[],'main-content');
    }//END public function DeleteRecord
}//END class RelationsTypes extends Module