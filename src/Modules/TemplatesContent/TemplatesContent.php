<?php
/**
 * description
 *
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\TemplatesContent;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\App\ModulesProvider;
use NETopes\Core\App\Params;
use NETopes\Core\Controls\BasicForm;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\TabControl;
use NETopes\Core\Controls\TabControlBuilder;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\DataSet;
use NETopes\Core\Data\IEntity;
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
class TemplatesContent extends Module {
    /**
     * Module class initializer
     *
     * @return void
     */
    protected function _Init() {
        $this->viewsExtension='.php';
    }//END protected function _Init

    /**
     * @var string Name of field to be used as label for the form items ('label'/'name')
     */
    public $itemLabel='label';
    /**
     * @var int Maximum box title length (in characters)
     */
    public $maxBoxTitleLength=29;

    /**
     * Get item box title
     *
     * @param IEntity $field Item parameters array
     * @return string Returns item box title
     * @throws \NETopes\Core\AppException
     */
    public function GetItemTitle(IEntity $field) {
        $title=$field->getProperty($this->itemLabel,'','is_string');
        if(strlen($title)>($this->maxBoxTitleLength + 2)) {
            $title=substr($title,0,$this->maxBoxTitleLength).'...';
        }
        if($field->getProperty('required',0,'is_integer')==1) {
            $title.='*';
        }
        return $title;
    }//END public function GetItemTitle

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowContentEditForm(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $templateProps=DataProvider::Get('Plugins\DForms\Templates','GetItemProperties',['template_id'=>$template]);
        $templatePages=DataProvider::Get('Plugins\DForms\Templates','GetItemPages',['template_id'=>$template],['sort'=>['pindex'=>'asc']]);
        if(!$templateProps instanceof IEntity || !$templatePages instanceof DataSet) {
            throw new AppException('Invalid DynamicForms template properties/pages!');
        }
        $fieldsTypes=DataProvider::Get('Plugins\DForms\Controls','GetItems',['for_state'=>1]);
        $target=$params->safeGet('target','','is_string');
        $view=new AppView(get_defined_vars(),$this,NULL);
        $view->AddFileContent($this->GetViewFile('ContentEditForm'));
        $view->Render();
    }//END public function ShowContentEditForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowContentTable(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $pIndex=$params->getOrFail('pindex','is_integer','Invalid template page identifier!');
        $templatePage=DataProvider::Get('Plugins\DForms\Templates','GetItemPage',['template_id'=>$template,'for_pindex'=>$pIndex]);
        $fields=DataProvider::GetKeyValue('Plugins\DForms\Templates','GetFields',['template_id'=>$template,'for_pindex'=>$pIndex],['keyfield'=>'cell']);
        $target=$params->safeGet('target','','is_string');
        $cTarget=$params->safeGet('c_target','','is_string');
        $view=new AppView(get_defined_vars(),$this,NULL);
        $view->AddFileContent($this->GetViewFile('ContentTable'));
        $view->Render();
    }//END public function ShowContentTable

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddPageForm(Params $params) {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $maxPos=$params->safeGet('pagesno',0,'is_numeric');
        if($maxPos<=0) {
            return;
        }
        $target=$params->safeGet('target','','is_string');
        $view=new AppView(get_defined_vars(),$this,'modal');
        $view->SetIsModalView(TRUE);
        $view->SetModalWidth(250);
        $view->SetTitle(Translate::GetLabel('add_page'));
        $view->AddBasicForm($this->GetViewFile('AddPageForm'));
        $view->Render();
    }//END public function ShowAddPageForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function UpdatePagesList(Params $params) {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $type=$params->getOrFail('type','is_integer','Invalid action type!');
        $pIndex=$params->getOrFail('pindex','is_integer','Invalid page position!');
        if($type<0) {
            $result=DataProvider::Get('Plugins\DForms\Templates','UnsetTemplatePage',[
                'for_id'=>$templateId,
                'in_pindex'=>$pIndex,
            ]);
        } elseif($type==0) {
            $result=DataProvider::Get('Plugins\DForms\Templates','SetTemplatePage',[
                'for_id'=>$templateId,
                'in_pindex'=>$pIndex,
            ]);
        } else {
            $result=DataProvider::Get('Plugins\DForms\Templates','SetNewTemplatePage',[
                'for_id'=>$templateId,
                'in_pindex'=>$pIndex,
            ]);
        }//if($type<0)
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        if($params->safeGet('close',0,'is_integer')==1) {
            $this->CloseForm();
        }
        $target=$params->safeGet('target','','is_string');
        $this->Exec('ShowContentEditForm',['id_template'=>$templateId,'target'=>$target],$target);
    }//END public function UpdatePagesList

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function SetPageTitle(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $pIndex=$params->getOrFail('pindex','is_integer','Invalid page index!');
        $title=$params->safeGet('title','','is_string');
        $result=DataProvider::Get('Plugins\DForms\Templates','SetTemplatePageTitle',[
            'template_id'=>$template,
            'for_pindex'=>$pIndex,
            'in_title'=>$title,
        ]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
    }//END public function SetPageTitle

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddTableElementForm(Params $params) {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $pIndex=$params->getOrFail('pindex','is_integer','Invalid page position!');
        $type=$params->safeGet('type','','is_string');
        $lastPos=$params->safeGet('last_pos',0,'is_numeric');
        if(!strlen($type) || $lastPos<=0) {
            throw new AppException('Invalid table structure!');
        }
        $maxPos=$lastPos + 1;
        $target=$params->safeGet('target','','is_string');
        $cTarget=$params->safeGet('c_target','','is_string');
        $view=new AppView(get_defined_vars(),$this,'modal');
        $view->SetIsModalView(TRUE);
        $view->SetModalWidth(250);
        $view->SetTitle(Translate::GetLabel('add_'.$type));
        $view->AddBasicForm($this->GetViewFile('AddTableElementForm'));
        $view->Render();
    }//END public function ShowAddTableElementForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function UpdateContentTable(Params $params) {
        // NApp::Dlog($params,'UpdateContentTable');
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $pIndex=$params->getOrFail('pindex','is_integer','Invalid page position!');
        $type=$params->getOrFail('type','is_integer','Invalid action type!');
        $colsNo=$params->safeGet('colsno',NULL,'is_not0_integer');
        $rowsNo=$params->safeGet('rowsno',NULL,'is_not0_integer');
        if($colsNo===NULL && $rowsNo===NULL) {
            return;
        }
        if($type<0) {
            $result=DataProvider::GetArray('Plugins\DForms\Templates','UnsetTableCell',[
                'for_id'=>$templateId,
                'in_col'=>$colsNo,
                'in_row'=>$rowsNo,
                'in_pindex'=>$pIndex,
            ]);
        } elseif($type==0) {
            $result=DataProvider::GetArray('Plugins\DForms\Templates','SetTableCell',[
                'for_id'=>$templateId,
                'in_col'=>$colsNo,
                'in_row'=>$rowsNo,
                'in_pindex'=>$pIndex,
            ]);
        } else {
            $result=DataProvider::GetArray('Plugins\DForms\Templates','SetNewTableCell',[
                'for_id'=>$templateId,
                'in_col'=>$colsNo,
                'in_row'=>$rowsNo,
                'in_pindex'=>$pIndex,
            ]);
        }//if($type<0)
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        if($params->safeGet('close',0,'is_integer')==1) {
            $this->CloseForm();
        }
        $target=$params->safeGet('target','','is_string');
        $cTarget=$params->safeGet('c_target','','is_string');
        $this->Exec('ShowContentTable',['id_template'=>$templateId,'pindex'=>$pIndex,'target'=>$target,'c_target'=>$cTarget],$target);
    }//END public function UpdateContentTable

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function AddEditContentElement(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $pIndex=$params->getOrFail('pindex','is_integer','Invalid page position!');
        $controlId=$params->getOrFail('id_control','is_not0_integer','Invalid control identifier!');
        $fieldType=DataProvider::Get('Plugins\DForms\Controls','GetItem',['for_id'=>$controlId]);
        $colsNo=$params->safeGet('cols_no',1,'is_not0_integer');
        $id=$params->safeGet('id_item',NULL,'is_not0_integer');
        if($id) {
            $item=DataProvider::Get('Plugins\DForms\Templates','GetField',['for_id'=>$id]);
            $fRow=$item->getProperty('frow',0,'is_integer');
            $fCol=$item->getProperty('fcol',0,'is_integer');
        } else {
            $cell=$params->safeGet('cell','','is_string');
            $cellArray=explode('-',$cell);
            if(!is_array($cellArray) || count($cellArray)!=3) {
                throw new AppException('Invalid template cell!');
            }
            $fRow=$cellArray[1];
            $fCol=$cellArray[2];
            if(!$fRow || !$fCol) {
                throw new AppException('Invalid cell data!');
            }
            $item=new VirtualEntity();
        }//if(!$id)
        $target=$params->safeGet('target','','is_string');
        $cTarget=$params->safeGet('c_target','','is_string');
        $cClass=$fieldType->getProperty('class','','is_string');
        $cDataType=$fieldType->getProperty('data_type','','is_string');

        $view=new AppView(get_defined_vars(),$this,'modal');
        $view->SetTitle(Translate::GetLabel('field_properties'));
        $view->SetIsModalView(TRUE);
        $view->SetModalWidth(550);
        $customClose=NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'CancelAddEditContentElement', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'target': '{$target}' } }",'dft_fp_errors');
        $view->SetModalCustomClose('"'.addcslashes($customClose,'\\').'"');
        $view->AddControlBuilderContent($this->GetViewFile('FieldPropertiesForm'),BasicForm::class,['container_type'=>'default']);
        $tabCtrlBuilder=ModulesProvider::Exec(Controls::class,'GetControlPropertiesTabBuilder',['id_control'=>$controlId,'data'=>$item->getProperty('params','','is_string'),'target'=>'dft_fp_properties_tab']);
        $view->AddControlBuilderContent($tabCtrlBuilder,TabControl::class,[
            'container_type'=>'default',
            'title'=>Translate::GetTitle('control_properties'),
        ]);
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="dft_fp_form_errors"></div></div>');
        $btnSave=new Button(['value'=>Translate::GetButton('save'),'class'=>NApp::$theme->GetBtnPrimaryClass(),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'AddEditContentElementRecord', 'params': { 'id_template': {$template}, 'pindex': '{$pIndex}', 'id_item': '{$id}', 'class': '{$cClass}', 'data_type': '{$cDataType}', 'frow': '{$fRow}', 'fcol': '{$fCol}', 'id_control': '{$controlId}', 'ptarget': '{$target}', 'c_target': '{$cTarget}', 'properties': '{nGet|dft_fp_properties_tab:form}', 'target': 'dft_fp_form' }, 'arrayParams': [ '{nGet|dft_fp_form:form}' ] }",'dft_fp_form_errors')]);
        $view->AddAction($btnSave->Show());
        $btnCancel=new Button(['value'=>Translate::GetButton('cancel'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-ban','onclick'=>$customClose]);
        $view->AddAction($btnCancel->Show());
        $view->Render();
    }//END public function AddEditContentElement

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function CancelAddEditContentElement(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $pIndex=$params->getOrFail('pindex','is_integer','Invalid page position!');
        $this->CloseForm();
        $target=$params->safeGet('target','','is_string');
        $cTarget=$params->safeGet('c_target','','is_string');
        $this->Exec('ShowContentTable',['id_template'=>$template,'pindex'=>$pIndex,'target'=>$target,'c_target'=>$cTarget],$target);
    }//END public function CancelAddEditContentElement

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function AddEditContentElementRecord(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $pIndex=$params->getOrFail('pindex','is_integer','Invalid page position!');
        $id=$params->safeGet('id_item',NULL,'is_not0_numeric');
        $name=$params->safeGet('name','','is_string');
        $label=$params->safeGet('label','','is_string');
        $label_required=$params->safeGet('label_required',FALSE,'is_bool');
        $target=$params->safeGet('target','','is_string');
        if(!strlen($name) || ($label_required && !strlen($label))) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//if(!strlen($name) || ($label_required && !strlen($label)))
        $required=$params->safeGet('required',0,'is_integer');
        $listing=$params->safeGet('listing',0,'is_integer');
        $colSpan=$params->safeGet('colspan',0,'is_integer');
        $colSpan=$colSpan>1 ? $colSpan : NULL;
        $idValuesList=$params->safeGet('id_values_list',NULL,'is_numeric');
        // process field properties
        $fParams=ModulesProvider::Exec(Controls::class,'ProcessFieldProperties',[
            'id_control'=>$params->safeGet('id_control',NULL,'is_integer'),
            'data'=>$params->safeGet('properties',NULL,'is_array'),
        ]);
        if($id) {
            $result=DataProvider::Get('Plugins\DForms\Templates','SetField',[
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
            if($result===FALSE) {
                throw new AppException('Unknown database error!');
            }
        } else {
            $class=$params->safeGet('class','','is_string');
            $fCol=$params->safeGet('fcol',0,'is_integer');
            $fRow=$params->safeGet('frow',0,'is_integer');
            if(!$fRow || !$fCol || !strlen($class)) {
                throw new AppException('Invalid field data!');
            }
            $data_type=$params->safeGet('data_type','','is_string');
            if($class=='BasicForm') {
                $subFormId=$params->safeGet('id_sub_form',0,'is_integer');
                if(!$subFormId) {
                    NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
                    echo Translate::GetMessage('required_fields');
                    return;
                }//if(!$subFormId)
                $name=NULL;
                $label=NULL;
            } else {
                $subFormId=NULL;
            }//if($class=='BasicForm' && !$subFormId)
            $result=DataProvider::Get('Plugins\DForms\Templates','SetNewField',[
                'template_id'=>$template,
                'sub_form_id'=>$subFormId,
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
            if(!is_object($result) || !is_object($result->first()) || $result->first()->getProperty('inserted_id',0,'is_integer')<=0) {
                throw new AppException('Unknown database error!');
            }
        }//if($id)
        $this->CloseForm();
        $pTarget=$params->safeGet('ptarget','','is_string');
        $cTarget=$params->safeGet('c_target','','is_string');
        $this->Exec('ShowContentTable',['id_template'=>$template,'pindex'=>$pIndex,'target'=>$pTarget,'c_target'=>$cTarget],$pTarget);
    }//END public function AddEditContentElementRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function MoveContentElement(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $initialPageIndex=$params->getOrFail('pindex','is_integer','Invalid page position!');
        $id=$params->getOrFail('id_item','is_not0_integer','Invalid field identifier!');
        $cell=$params->safeGet('cell','','is_string');
        $cellArray=explode('-',$cell);
        if(!is_array($cellArray) || count($cellArray)!=3) {
            throw new AppException('Invalid template cell!');
        }
        $pIndex=$cellArray[0];
        $fRow=$cellArray[1];
        $fCol=$cellArray[2];
        if(!$fRow || !$fCol) {
            throw new AppException('Invalid field data!');
        }
        $result=DataProvider::Get('Plugins\DForms\Templates','SetField',[
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
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
    }//END public function MoveContentElement

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function DeleteContentElementRecord(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $pIndex=$params->getOrFail('pindex','is_integer','Invalid page position!');
        $id=$params->getOrFail('id','is_not0_integer','Invalid field identifier!');
        $result=DataProvider::Get('Plugins\DForms\Templates','UnsetField',['for_id'=>$id]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        $target=$params->safeGet('target','','is_string');
        $cTarget=$params->safeGet('c_target','','is_string');
        $this->Exec('ShowContentTable',['id_template'=>$template,'pindex'=>$pIndex,'target'=>$target,'c_target'=>$cTarget],$target);
    }//END public function DeleteContentElementRecord
}//END class TemplatesContent extends Module