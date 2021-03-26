<?php
/**
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Templates;
use NApp;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\App\Params;
use NETopes\Core\AppException;
use NETopes\Core\Controls\Button;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\DataSet;
use NETopes\Core\Data\IEntity;
use NETopes\Core\Data\VirtualEntity;
use Translate;

/**
 * Class Templates
 *
 * @package NETopes\Plugins\Modules\DForms
 */
class Templates extends Module {
    /**
     * string Denied rights GUID
     */
    const DRIGHTS_UID='';
    /**
     * int Relation UTYPE standard
     */
    public const RELATION_UTYPE_STANDARD=10;
    /**
     * int Relation UTYPE category
     */
    public const RELATION_UTYPE_CATEGORY=1;
    /**
     * int Relation UTYPE UID
     */
    public const RELATION_UTYPE_UID=10;

    /**
     * Module class initializer
     *
     * @return void
     */
    protected function _Init() {
        $this->viewsExtension='.php';
    }//END protected function _Init

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function Listing(Params $params) {
        $listingTarget='dforms_listing';
        $view=new AppView(get_defined_vars(),$this,'main');
        $view->SetTitle('dynamic_forms_templates');
        $view->SetTargetId($listingTarget);
        if(!$this->AddDRights()) {
            $btnAdd=new Button(['value'=>Translate::GetButton('add').' '.Translate::GetLabel('template'),'class'=>NApp::$theme->GetBtnPrimaryClass(),'icon'=>'fa fa-plus','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'ShowAddForm', 'params': {  } }",'modal')]);
            $view->AddAction($btnAdd->Show());
        }//if(!$this->AddDRights())
        $view->AddTableView($this->GetViewFile('Listing'));
        $view->Render();
    }//END public function Listing

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddForm(Params $params) {
        $view=new AppView(get_defined_vars(),$this,'modal');
        $view->SetIsModalView(TRUE);
        $view->AddBasicForm($this->GetViewFile('AddForm'));
        $view->SetTitle(Translate::GetButton('add').' '.Translate::GetLabel('template'));
        $view->SetModalWidth(620);
        $view->Render();
        NApp::Ajax()->ExecuteJs("$('#df_template_add_code').focus();");
    }//END public function ShowAddForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function AddRecord(Params $params) {
        $fType=$params->safeGet('ftype',NULL,'is_numeric');
        $code=$params->safeGet('code',NULL,'is_numeric');
        $name=trim($params->safeGet('name',NULL,'is_notempty_string'));
        $formId=$params->safeGet('form_id','','is_string');
        if(!$fType || !$code || !strlen($name)) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$formId}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//if(!$ftype || !$code || !strlen($name))
        $result=DataProvider::Get('Plugins\DForms\Templates','SetNewItem',[
            'in_code'=>$code,
            'in_name'=>$name,
            'in_ftype'=>$fType,
            'in_state'=>$params->safeGet('state',NULL,'is_integer'),
            'in_colsno'=>$params->safeGet('colsno',NULL,'is_integer'),
            'in_rowsno'=>$params->safeGet('rowsno',NULL,'is_integer'),
            'in_delete_mode'=>$params->safeGet('dmode',NULL,'is_integer'),
            'user_id'=>NApp::GetCurrentUserId(),
        ]);
        if(!$result instanceof DataSet || !count($result)) {
            throw new AppException('Unknown database error!');
        }
        $id=$result->first()->getProperty('inserted_id',0,'is_integer');
        if($id<=0) {
            throw new AppException('Unknown database error!');
        }
        $this->CloseForm();
        $this->Exec('ShowEditForm',['id'=>$id],'main-content');
    }//END public function AddRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowEditForm(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid DynamicForms template record identifier!');
        $item=DataProvider::Get('Plugins\DForms\Templates','GetItem',['for_id'=>$id]);
        if(!$item instanceof IEntity) {
            throw new AppException('Invalid DynamicForms template record!');
        }
        $version=$item->getProperty('version',0,'is_numeric');
        $view=new AppView(get_defined_vars(),$this,'main');
        $view->AddTabControl($this->GetViewFile('EditForm'));
        $view->SetTitle(Translate::GetButton('edit_template').': '.$item->getProperty('name').' ['.$item->getProperty('code').'] - Ver. '.$version.' ('.($version + 1).')');
        if(!$this->ValidateDRights()) {
            $btnValidate=new Button(['value'=>Translate::GetButton('validate'),'class'=>NApp::$theme->GetBtnSuccessClass('mr10'),'icon'=>'fa fa-check-square-o','onclick'=>NApp::Ajax()->PrepareAjaxRequest(['module'=>$this->class,'method'=>'ValidateRecord','params'=>[
                'id'=>$id,
                'design_form_id'=>'df_template_design_edit_form',
                'design_data'=>'{nGet|df_template_design_edit_form:form}',
                'print_template_form_id'=>'df_template_edit_print_template_form',
                'print_template_data'=>'{nGet|df_template_edit_print_template_form:form}',
            ]],['target_id'=>'errors'])]);
            $view->AddAction($btnValidate->Show());
        }//if(!$this->ValidateDRights()) {
        $btnBack=new Button(['value'=>Translate::GetButton('back'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-chevron-left','onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': 'Listing', 'params': {  } }",'main-content')]);
        $view->AddAction($btnBack->Show());
        $view->Render();
        NApp::Ajax()->ExecuteJs("$('#df_template_edit_code').focus();");
    }//END public function ShowEditForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function EditRecord(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid DynamicForm template record identifier!');
        $fType=$params->safeGet('ftype',NULL,'is_numeric');
        $code=$params->safeGet('code',NULL,'is_numeric');
        $name=trim($params->safeGet('name',NULL,'is_notempty_string'));
        $formId=$params->safeGet('form_id','','is_string');
        if(!$fType || !$code || !strlen($name)) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$formId}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//if(!$ftype || !$code || !strlen($name))
        $result=DataProvider::Get('Plugins\DForms\Templates','SetItem',[
            'for_id'=>$id,
            'in_name'=>$name,
            'in_ftype'=>$fType,
            'in_state'=>$params->safeGet('state',NULL,'is_integer'),
            'in_delete_mode'=>$params->safeGet('dmode',NULL,'is_integer'),
            'user_id'=>NApp::GetCurrentUserId(),
        ]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        if($params->safeGet('close',1,'is_numeric')!=1) {
            echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
            return;
        }//if($result!==FALSE)
        $this->Exec('Listing',[],'main-content');
    }//END public function EditRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowPrintTemplateEditForm(Params $params) {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid record identifier!');
        $item=DataProvider::Get('Plugins\DForms\Templates','GetItemProperties',['template_id'=>$templateId]);
        if(!$item instanceof IEntity) {
            throw new AppException('Invalid DynamicForms template properties record!');
        }
        $view=new AppView(get_defined_vars(),$this,'default');
        $view->AddBasicForm($this->GetViewFile('PrintTemplateForm'));
        $view->Render();
    }//END public function ShowPrintTemplateEditForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function SetPrintTemplate(Params $params) {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid record identifier!');
        $preValidation=$params->safeGet('pre_validation',FALSE,'bool');
        $result=DataProvider::Get('Plugins\DForms\Templates','SetPropertiesItem',[
            'template_id'=>$templateId,
            'in_print_page_orientation'=>strtoupper($params->safeGet('page_orientation','P','is_notempty_string')),
            'in_print_template'=>str_replace('``','"',$params->safeGet('print_template','','is_string')),
        ]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        if(!$preValidation) {
            echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
        }
    }//END public function SetPrintTemplate

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function DeleteRecord(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid template identifier!');
        $result=DataProvider::GetArray('Plugins\DForms\Templates','UnsetItem',['for_id'=>$id]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        $this->Exec('Listing',[],'main-content');
    }//END public function DeleteRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function EditRecordState(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid template identifier!');
        $state=$params->getOrFail('state','is_integer','Invalid state value!');
        $result=DataProvider::Get('Plugins\DForms\Templates','SetItemState',[
            'for_id'=>$id,
            'in_state'=>$state,
            'user_id'=>NApp::GetCurrentUserId(),
        ]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
    }//END public function EditRecordState

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function CreateNewVersion(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid template identifier!');
        $result=DataProvider::Get('Plugins\DForms\Templates','SetItemValidated',['for_id'=>$id,'new_value'=>0]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        $this->Exec('ShowEditForm',['id'=>$id],'main-content');
    }//END public function CreateNewVersion

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ValidateRecord(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid template identifier!');
        $designFormId=$params->safeGet('design_form_id',NULL,'is_string');
        $designData=$params->safeGet('design_data',[],'is_array');
        $printTemplateFormId=$params->safeGet('print_template_form_id',NULL,'is_string');
        $printTemplateData=$params->safeGet('print_template_data',[],'is_array');

        if(strlen($designFormId) && count($designData)) {
            $this->Exec('EditDesignRecord',array_merge($designData,[
                'id_template'=>$id,
                'form_id'=>$designFormId,
                'pre_validation'=>TRUE,
            ]));
        }//if(strlen($designFormId) && count($designData))
        if(strlen($printTemplateFormId) && count($printTemplateData)) {
            $this->Exec('SetPrintTemplate',array_merge($printTemplateData,[
                'id_template'=>$id,
                'form_id'=>$printTemplateFormId,
                'pre_validation'=>TRUE,
            ]));
        }//if(strlen($printTemplateFormId) && count($printTemplateData))

        $result=DataProvider::GetArray('Plugins\DForms\Templates','SetItemValidated',[
            'for_id'=>$id,
            'new_value'=>$params->safeGet('new_value',1,'is_numeric'),
            'user_id'=>NApp::GetCurrentUserId(),
        ]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }

        $this->Exec('Listing',[],'main-content');
    }//END public function ValidateRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowDesignEditForm(Params $params) {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $item=DataProvider::Get('Plugins\DForms\Templates','GetItemProperties',['template_id'=>$templateId]);
        if(!$item instanceof IEntity) {
            $item=new VirtualEntity([]);
        }
        $target=$params->safeGet('target','','is_string');
        $view=new AppView(get_defined_vars(),$this,'default');
        $view->AddBasicForm($this->GetViewFile('DesignEditForm'));
        $view->Render();
    }//END public function ShowDesignEditForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function EditDesignRecord(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $renderType=$params->safeGet('render_type',NULL,'is_integer');
        $formId=$params->safeGet('form_id','','is_string');
        $preValidation=$params->safeGet('pre_validation',FALSE,'bool');
        if(!$renderType) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$formId}')");
            if($preValidation) {
                throw new AppException(Translate::GetLabel('design_form').': '.Translate::GetMessage('required_fields'));
            }
            echo Translate::GetMessage('required_fields');
            return;
        }//if(!$renderType)
        $result=DataProvider::Get('Plugins\DForms\Templates','SetPropertiesItem',[
            'template_id'=>$template,
            'in_render_type'=>$renderType,
            'in_theme_type'=>$params->safeGet('theme_type','','is_string'),
            'in_controls_size'=>$params->safeGet('controls_size','','is_string'),
            'in_label_cols'=>$params->safeGet('label_cols',NULL,'is_not0_integer'),
            'in_separator_width'=>$params->safeGet('separator_width','','is_string'),
            'in_iso_code'=>$params->safeGet('iso_code','','is_string'),
            'in_title'=>$params->safeGet('title','','is_string'),
        ]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        if($preValidation) {
            return;
        }
        $cTarget=$params->safeGet('c_target','','is_string');
        if(!strlen($cTarget)) {
            echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
            return;
        }//if(strlen($cTarget))
        $this->Exec('ShowDesignEditForm',['id_template'=>$template,'target'=>$cTarget],$cTarget);
    }//END public function EditDesignRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowRelationsEditForm(Params $params) {
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $target=$params->safeGet('target','','is_string');
        $dgtarget='dg-'.$target;
        $view=new AppView(get_defined_vars(),$this,'default');
        $view->SetTargetId($dgtarget);
        $view->AddTableView($this->GetViewFile('RelationsEditForm'));
        $view->Render();
    }//END public function ShowRelationsEditForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowRelationAddEditForm(Params $params) {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $id=$params->safeGet('id',NULL,'is_integer');
        if($id) {
            $item=DataProvider::Get('Plugins\DForms\Templates','GetRelation',['for_id'=>$id]);
        } else {
            $item=new VirtualEntity();
        }//if($id)
        $target=$params->safeGet('target','','is_string');
        $view=new AppView(get_defined_vars(),$this,'modal');
        $view->SetIsModalView(TRUE);
        $view->AddBasicForm($this->GetViewFile('RelationAddEditForm'));
        $view->SetTitle(Translate::GetLabel('relation').' - '.Translate::Get($id ? 'button_edit' : 'button_add'));
        $view->SetModalWidth(500);
        $view->Render();
        NApp::Ajax()->ExecuteJs("$('#df_template_rel_ae_type').focus();");
    }//END public function ShowRelationAddEditForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function AddEditRelationRecord(Params $params) {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $id=$params->safeGet('id',NULL,'is_integer');
        $idType=$params->safeGet('type',NULL,'is_integer');
        $rType=$params->safeGet('rtype',NULL,'is_integer');
        $uType=$params->safeGet('utype',NULL,'is_integer');
        $name=$params->safeGet('name',NULL,'is_string');
        $key=$params->safeGet('key',NULL,'is_string');
        $formId=$params->safeGet('form_id','');
        if(!strlen($name) || !strlen($key) || !is_numeric($rType) || !is_numeric($uType) || (!$id && !$idType)) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$formId}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//if(!strlen($name) || !strlen($key) || !is_numeric($rType) || !is_numeric($uType) || (!$id && !$type))
        if($id) {
            $result=DataProvider::Get('Plugins\DForms\Templates','SetRelation',[
                'for_id'=>$id,
                'in_name'=>$name,
                'in_key'=>$key,
                'in_required'=>$params->safeGet('required',0,'is_integer'),
                'in_rtype'=>$rType,
                'in_utype'=>$uType,
            ]);
            if($result===FALSE) {
                throw new AppException('Unknown database error!');
            }
        } else {
            $result=DataProvider::Get('Plugins\DForms\Templates','SetNewRelation',[
                'template_id'=>$templateId,
                'relation_type_id'=>$idType,
                'in_name'=>$name,
                'in_key'=>$key,
                'in_required'=>$params->safeGet('required',0,'is_integer'),
                'in_rtype'=>$rType,
                'in_utype'=>$uType,
            ]);
            if(!is_object($result) || !count($result) || $result->first()->getProperty('inserted_id',0,'is_integer')<=0) {
                throw new AppException('Unknown database error!');
            }
        }//if($id)
        $this->CloseForm();
        $cTarget=$params->safeGet('c_target','','is_string');
        $this->Exec('ShowRelationsEditForm',['id_template'=>$templateId,'target'=>$cTarget],$cTarget);
    }//END public function AddEditRelationRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function DeleteRelationRecord(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid record identifier!');
        $template=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $result=DataProvider::Get('Plugins\DForms\Templates','UnsetRelation',['for_id'=>$id]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        $target=$params->safeGet('target','','is_string');
        $this->Exec('ShowRelationsEditForm',['id_template'=>$template,'target'=>$target],$target);
    }//END public function DeleteRelationRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function CloneRecord(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid field identifier!');
        $result=DataProvider::Get('Plugins\DForms\Templates','CloneItem',['for_id'=>$id,'user_id'=>NApp::GetCurrentUserId()]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        $this->Exec('Listing',[],'main-content');
    }//END public function CloneRecord

    /**
     * @param \NETopes\Core\App\Params $params
     * @throws \NETopes\Core\AppException
     */
    public function ShowAvailablePrintTags(Params $params) {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $targetTagId=$params->safeGet('target_tag_id',NULL,'is_string');
        $targetTagType=$params->safeGet('target_tag_type',NULL,'is_string');
        $relations=DataProvider::Get('Plugins\DForms\Templates','GetRelations',['template_id'=>$templateId]);
        $fields=DataProvider::Get('Plugins\DForms\Templates','GetFields',['template_id'=>$templateId]);
        $view=new AppView(get_defined_vars(),$this,'modal');
        $view->SetIsModalView(TRUE);
        $view->AddFileContent($this->GetViewFile('AvailablePrintTags'));
        $view->SetTitle(Translate::GetLabel('available_print_tags'));
        $view->SetModalWidth(850);
        $view->Render();
    }//END public function ShowAvailablePrintTags

    /**
     * @param \NETopes\Core\App\Params $params
     * @throws \NETopes\Core\AppException
     */
    public function AddTagToPrintTemplate(Params $params) {
        // NApp::Dlog($params->toArray(),__METHOD__);
        $targetTagId=$params->safeGet('target_tag_id',NULL,'is_string');
        $targetTagType=$params->safeGet('target_tag_type',NULL,'is_string');

        $tags=$params->safeGet('tags',[],'is_array');
        $tag=$params->safeGet('tag',NULL,'is_string');
        if(strlen($tag)) {
            $tags[$tag]=$tag;
        }

        $data=trim(implode('<br />',$tags));
        if(!strlen($targetTagId) || !strlen($targetTagType) || !strlen($data)) {
            return;
        }

        switch($targetTagType) {
            case 'CkEditor':
                $this->AddJsScript("InsertCkEditorData('$targetTagId','{$data}');");
                break;
            default:
                $this->AddJsScript("$('#{$targetTagId}').val($('#{$targetTagId}').val() + '{$data}');");
                break;
        }//END switch
    }//END public function AddTagToPrintTemplate
}//END class Templates extends Module