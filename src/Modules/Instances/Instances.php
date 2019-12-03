<?php
/**
 * Dynamic forms Instances class file
 *
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Instances;
use Mpdf\Tag\IndexEntry;
use NApp;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\App\ModulesProvider;
use NETopes\Core\App\Params;
use NETopes\Core\AppException;
use NETopes\Core\AppSession;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\IControlBuilder;
use NETopes\Core\Controls\TableView;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\IEntity;
use Translate;

/**
 * Class Instances
 *
 * @package NETopes\Plugins\DForms\Modules\Instances
 */
class Instances extends Module {
    /**
     * @var integer Dynamic form template ID
     */
    public $templateId=NULL;
    /**
     * @var integer Dynamic form template code (numeric)
     */
    public $templateCode=NULL;
    /**
     * @var bool Flag for modal add/edit forms
     */
    public $formAsModal=FALSE;
    /**
     * @var bool Flag for modal add/edit forms
     */
    public $viewAsModal=FALSE;
    /**
     * @var string AppView container type
     */
    public $containerType='main';
    /**
     * @var bool Render actions in TableView control
     */
    public $inListingActions=TRUE;
    /**
     * @var string Forms actions location (form/container/after)
     */
    public $actionsLocation='form';
    /**
     * @var string|null Forms back action location (form/container/after)
     */
    public $backActionLocation='container';
    /**
     * @var array List of header fields to be displayed in Listing
     */
    public $showInListing=['template_code','template_name','create_date','user_full_name','last_modified','last_user_full_name'];
    /**
     * @var bool Flag for showing print action in listing
     */
    public $listingPrintAction=TRUE;
    /**
     * @var bool Flag for showing print action in edit/view forms
     */
    public $formPrintAction=TRUE;
    /**
     * @var string Print relative URL
     */
    public $printUrl='/pipe/cdn.php';
    /**
     * @var array List CSS styles to be used for generating view HTML
     */
    protected $htmlStyles=[
        'table_attr'=>'border="0" ',
        'table_style'=>'width: 100%;',
        'title_style'=>'font-size: 16px; font-weight: bold; margin: 0;',//margin-bottom: 20px;
        'subtitle_style'=>'font-size: 14px; font-weight: bold; margin: 0;',//margin-bottom: 10px;
        'label_style'=>'font-size: 14px; font-style: italic;',
        'label_value_sep'=>':&nbsp;&nbsp;&nbsp;&nbsp;',
        'relation_style'=>'font-weight: bold;',
        'value_style'=>'font-size: 14px;',
        'msg_style'=>'font-style: italic;',
        'empty_value'=>'&nbsp;-&nbsp;',
    ];

    /**
     * Module class initializer
     *
     * @return void
     */
    protected function _Init() {
        $this->viewsExtension='.php';
    }//END protected function _Init

    /**
     * @param \NETopes\Core\App\Params $params
     * @throws \NETopes\Core\AppException
     */
    protected function PrepareConfigParams(Params $params): void {
        $this->templateId=$params->safeGet('id_template',$this->templateId,'is_not0_integer');
        $this->templateCode=$params->safeGet('template_code',$this->templateCode,'is_not0_integer');
        if(!$this->templateId && !$this->templateCode) {
            throw new AppException('Invalid DynamicForm template identifier!');
        }
        $this->formAsModal=$params->safeGet('forms_as_modal',$this->formAsModal,'is_integer');
        $this->viewAsModal=$params->safeGet('view_as_modal',$this->viewAsModal,'is_integer');
        $this->containerType=$params->safeGet('container_type',$this->containerType,'is_string');
        $this->inListingActions=$params->safeGet('in_listing_actions',$this->inListingActions,'bool');
        $this->actionsLocation=$params->safeGet('actions_location',$this->actionsLocation,'is_notempty_string');
        $this->backActionLocation=$params->safeGet('back_action_location',$this->backActionLocation,'is_notempty_string');
        $this->showInListing=$params->safeGet('show_in_listing',$this->showInListing,'is_array');
    }//END protected function PrepareConfigParams

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function Listing($params=NULL) {
        $this->PrepareConfigParams($params);
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>$this->templateId,
            'for_code'=>$this->templateCode,
            'instance_id'=>NULL,
            'for_state'=>1,
        ]);
        $this->templateId=$template->getProperty('id',NULL,'is_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $fields=DataProvider::Get('Plugins\DForms\Instances','GetFields',[
            'template_id'=>$this->templateId,
            'for_template_code'=>NULL,
            'for_listing'=>1,
        ]);
        $fTypes=DataProvider::GetKeyValue('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
        $cModule=$params->safeGet('c_module',$this->class,'is_notempty_string');
        $cMethod=$params->safeGet('c_method',call_back_trace(0),'is_notempty_string');
        $cTarget=$params->safeGet('c_target','main-content','is_notempty_string');
        $target=$params->safeGet('target','main-content','is_notempty_string');
        $listingTarget=$target.'_listing';
        $listingAddAction=[
            'value'=>Translate::GetButton('add'),
            'class'=>NApp::$theme->GetBtnPrimaryClass(),
            'icon'=>'fa fa-plus',
            'onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->name}', 'method': 'ShowAddForm', 'params': { 'id_template': '{$this->templateId}', 'c_module': '{$cModule}', 'c_method': '{$cMethod}', 'c_target': '{$cTarget}', 'target': '{$target}' } }",$target),
        ];

        $view=new AppView(get_defined_vars(),$this,($target=='main-content' ? 'main' : 'default'));
        $view->SetTitle($params->safeGet('title',$template->getProperty('name','','is_string'),'is_string'));
        $view->SetTargetId($listingTarget);
        $view->AddControlBuilderContent($this->GetViewFile('Listing'),TableView::class);
        if(!$this->inListingActions) {
            if(!$this->AddDRights()) {
                $btnAdd=new Button($listingAddAction);
                $view->AddAction($btnAdd->Show());
            }
        }
        $view->Render();
    }//END public function Listing

    /**
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function GlobalListing($params=NULL) {
        $this->PrepareConfigParams($params);
        $fTypes=DataProvider::GetKeyValue('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
        $listingTarget='listing-content';
        $view=new AppView(get_defined_vars(),$this,'main');
        $view->SetTitle('');
        $view->SetTargetId($listingTarget);
        $view->AddTableView($this->GetViewFile('GlobalListing'));
        $view->Render();
    }//END public function GlobalListing

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddEditForm($params=NULL) {
        // NApp::Dlog($params,'ShowAddEditForm');
        $this->PrepareConfigParams($params);
        $instanceId=$params->safeGet('id',NULL,'is_not0_integer');
        /** @var \NETopes\Core\Data\VirtualEntity $template */
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>NULL,
            'for_code'=>$this->templateCode,
            'instance_id'=>$instanceId,
            'for_state'=>1,
        ]);
        if(!is_object($template)) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $this->templateId=$template->getProperty('id',NULL,'is_integer');
        $this->templateCode=$template->getProperty('code',NULL,'is_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid DynamicForm template!');
        }
        if(!$instanceId && $template->getProperty('ftype',0,'is_integer')==2 && $params instanceof Params) {
            $instance=InstancesHelpers::GetSingletonInstance($this->templateId,$params);
            $instanceId=$instance->getProperty('id',NULL,'is_integer');
        }//if(!$instanceId && $template->getProperty('ftype',0,'is_integer')==2 && $params instanceof Params)
        $viewOnly=$params->safeGet('view_only',FALSE,'bool');
        $noRedirect=$params->safeGet('no_redirect',FALSE,'bool');
        $cModule=$params->safeGet('c_module',$this->name,'is_notempty_string');
        $cMethod=$params->safeGet('c_method','Listing','is_notempty_string');
        $cTarget=$params->safeGet('c_target','main-content','is_notempty_string');

        $builder=InstancesHelpers::PrepareForm($params,$template,$instanceId);
        if(!$builder instanceof IControlBuilder) {
            throw new AppException('Invalid DynamicForm configuration!');
        }
        $ctrl_params=$builder->GetConfig();
        $controlClass=get_array_value($ctrl_params,'control_class','','is_string');
        $aeSaveInstanceMethod='SaveInstance';
        $tName=get_array_value($ctrl_params,'tname',microtime(),'is_string');
        $fTagId=get_array_value($ctrl_params,'tag_id','','is_string');

        if($controlClass!='BasicForm' && $this->actionsLocation=='form') {
            $this->actionsLocation='container';
        }
        if($this->formAsModal) {
            $view=new AppView(get_defined_vars(),$this,'modal');
            $view->SetIsModalView(TRUE);
            $view->SetModalWidth('80%');
            $view->SetTitle($template->getProperty('name','','is_string'));
        } else {
            $view=new AppView(get_defined_vars(),$this,$this->containerType);
            $view->SetTitle($template->getProperty('name','','is_string'));
        }//if($this->formAsModal)
        $fResponseTarget=get_array_value($ctrlParams,'response_target','df_'.$tName.'_errors','is_string');

        $formActions=InstancesHelpers::PrepareFormActions($this,$ctrl_params,$instanceId,$aeSaveInstanceMethod,$fResponseTarget,$tName,$fTagId,$cModule,$cMethod,$cTarget,$viewOnly,$noRedirect);
        if(count($formActions['container'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            foreach($formActions['container'] as $formAct) {
                $view->AddAction((new Button($formAct['params']))->Show());
            }//END foreach
        }//if(count($formActions['container']))
        if(count($formActions['form'])) {
            $ctrl_params['actions']=$formActions['form'];
        }//if(count($formActions['form']))

        $addContentMethod='Add'.$controlClass;
        $view->$addContentMethod($ctrl_params);
        $relationsHtml=InstancesHelpers::PrepareRelationsFormPart($this->templateId,$instanceId,$params);
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_relations">'.$relationsHtml.'</div></div>');
        if(count($formActions['after'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            $afterFormActions=[];
            foreach($formActions['after'] as $formAct) {
                $formActionsArray[]=(new Button($formAct['params']))->Show();
            }//END foreach
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 actions-group">'.implode('',$afterFormActions).'</div></div>');
        }//if(count($formActions['after']))
        $view->Render();
    }//END public function ShowAddEditForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddForm($params=NULL) {
        // NApp::Dlog($params,'ShowAddForm');
        $this->PrepareConfigParams($params);
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>$this->templateId,
            'for_code'=>$this->templateCode,
            'instance_id'=>NULL,
            'for_state'=>1,
        ]);
        $this->templateId=get_array_value($template,'id',NULL,'is_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $noRedirect=$params->safeGet('no_redirect',FALSE,'bool');
        $cModule=$params->safeGet('c_module',$this->name,'is_notempty_string');
        $cMethod=$params->safeGet('c_method','Listing','is_notempty_string');
        $cTarget=$params->safeGet('c_target','main-content','is_notempty_string');

        $builder=InstancesHelpers::PrepareForm($params,$template);
        if(!$builder instanceof IControlBuilder) {
            throw new AppException('Invalid DynamicForm configuration!');
        }
        $ctrl_params=$builder->GetConfig();
        $controlClass=get_array_value($ctrl_params,'control_class','','is_string');
        $aeSaveInstanceMethod='SaveNewRecord';
        $tName=get_array_value($ctrl_params,'tname',microtime(),'is_string');
        $fTagId=get_array_value($ctrl_params,'tag_id','','is_string');

        if($controlClass!='BasicForm' && $this->actionsLocation=='form') {
            $this->actionsLocation='container';
        }
        if($this->formAsModal) {
            $view=new AppView(get_defined_vars(),$this,'modal');
            $view->SetIsModalView(TRUE);
            $view->SetModalWidth('80%');
            $view->SetTitle($template->getProperty('name','','is_string'));
        } else {
            $view=new AppView(get_defined_vars(),$this,$this->containerType);
            $view->SetTitle($template->getProperty('name','','is_string'));
        }//if($this->formAsModal)
        $fResponseTarget=get_array_value($ctrl_params,'response_target','df_'.$tName.'_errors','is_notempty_string');

        $formActions=InstancesHelpers::PrepareFormActions($this,$ctrl_params,NULL,$aeSaveInstanceMethod,$fResponseTarget,$tName,$fTagId,$cModule,$cMethod,$cTarget,FALSE,$noRedirect);
        if(count($formActions['container'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            foreach($formActions['container'] as $formAct) {
                $view->AddAction((new Button($formAct['params']))->Show());
            }//END foreach
        }//if(count($formActions['container']))
        if(count($formActions['form'])) {
            $ctrl_params['actions']=$formActions['form'];
        }//if(count($formActions['form']))

        $addContentMethod='Add'.$controlClass;
        $view->$addContentMethod($ctrl_params);
        $relationsHtml=InstancesHelpers::PrepareRelationsFormPart($this->templateId,NULL,$params);
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_relations">'.$relationsHtml.'</div></div>');
        if(count($formActions['after'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            $afterFormActions=[];
            foreach($formActions['after'] as $formAct) {
                $formActionsArray[]=(new Button($formAct['params']))->Show();
            }//END foreach
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 actions-group">'.implode('',$afterFormActions).'</div></div>');
        }//if(count($formActions['after']))
        $view->Render();
    }//END public function ShowAddForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowEditForm($params=NULL) {
        // NApp::Dlog($params,'ShowEditForm');
        $instanceId=$params->safeGet('id',NULL,'is_not0_integer');
        if(!$instanceId) {
            throw new AppException('Invalid DynamicForm instance identifier!');
        }
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>NULL,
            'for_code'=>NULL,
            'instance_id'=>$instanceId,
            'for_state'=>1,
        ]);
        $this->templateId=$template->getProperty('id',NULL,'is_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $this->templateCode=$template->getProperty('code',NULL,'is_integer');
        $this->PrepareConfigParams($params);
        $viewOnly=$params->safeGet('view_only',FALSE,'bool');
        $noRedirect=$params->safeGet('no_redirect',FALSE,'bool');
        $cModule=$params->safeGet('c_module',$this->name,'is_notempty_string');
        $cMethod=$params->safeGet('c_method','Listing','is_notempty_string');
        $cTarget=$params->safeGet('c_target','main-content','is_notempty_string');

        $builder=InstancesHelpers::PrepareForm($params,$template);
        if(!$builder instanceof IControlBuilder) {
            throw new AppException('Invalid DynamicForm configuration!');
        }
        $ctrl_params=$builder->GetConfig();
        $controlClass=get_array_value($ctrl_params,'control_class','','is_string');
        $aeSaveInstanceMethod='SaveRecord';
        $tName=get_array_value($ctrl_params,'tname',microtime(),'is_string');
        $fTagId=get_array_value($ctrl_params,'tag_id','','is_string');

        if($controlClass!='BasicForm' && $this->actionsLocation=='form') {
            $this->actionsLocation='container';
        }
        if($this->formAsModal) {
            $view=new AppView(get_defined_vars(),$this,'modal');
            $view->SetIsModalView(TRUE);
            $view->SetModalWidth('80%');
            $view->SetTitle($template->getProperty('name','','is_string'));
        } else {
            $view=new AppView(get_defined_vars(),$this,$this->containerType);
            $view->SetTitle($template->getProperty('name','','is_string'));
        }//if($this->formAsModal)
        $fResponseTarget=get_array_value($ctrl_params,'response_target','df_'.$tName.'_errors','is_notempty_string');

        $formActions=InstancesHelpers::PrepareFormActions($this,$ctrl_params,$instanceId,$aeSaveInstanceMethod,$fResponseTarget,$tName,$fTagId,$cModule,$cMethod,$cTarget,$viewOnly,$noRedirect);
        if(count($formActions['container'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            foreach($formActions['container'] as $formAct) {
                $view->AddAction((new Button($formAct['params']))->Show());
            }//END foreach
        }//if(count($formActions['container']))
        if(count($formActions['form'])) {
            $ctrl_params['actions']=$formActions['form'];
        }//if(count($formActions['form']))

        $addContentMethod='Add'.$controlClass;
        $view->$addContentMethod($ctrl_params);
        $relationsHtml=InstancesHelpers::PrepareRelationsFormPart($this->templateId,NULL,$params);
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_relations">'.$relationsHtml.'</div></div>');
        if(count($formActions['after'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            $afterFormActions=[];
            foreach($formActions['after'] as $formAct) {
                $formActionsArray[]=(new Button($formAct['params']))->Show();
            }//END foreach
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 actions-group">'.implode('',$afterFormActions).'</div></div>');
        }//if(count($formActions['after']))
        $view->Render();
    }//END public function ShowEditForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function SaveInstance($params=NULL) {
        // NApp::Dlog($params->toArray(),'SaveInstance');
        $idInstance=$params->safeGet('id',NULL,'is_integer');
        $formId=$params->safeGet('form_id','','is_string');
        $check=InstancesHelpers::ValidateSaveParams($params);
        if(!$check) {
            $message=NULL;
            $relationsErrors=$params->safeGet('df_relations_errors',[],'is_array');
            foreach($relationsErrors as $error) {
                $type=get_array_param($error,'type','','is_string');
                $name=get_array_param($error,'name','','is_string');
                $message.='<li>'.$name.': '.Translate::GetError('required_field').'</li>';
                if(strlen($type)=='required_field') {
                    NApp::Ajax()->ExecuteJs("AddClassOnErrorByName('{$formId}','".get_array_param($error,'key','','is_string')."')");
                }//if(strlen($type)=='required_field')
            }//END foreach
            $fieldsErrors=$params->safeGet('df_fields_errors',[],'is_array');
            foreach($fieldsErrors as $error) {
                // $type=get_array_param($error,'type','','is_string');
                $name=get_array_param($error,'label','','is_string');
                $fieldUid=get_array_param($error,'uid',NULL,'is_string');
                $message.='<li>'.$name.': '.Translate::GetError('required_field').'</li>';
                if(strlen($fieldUid)) {
                    NApp::Ajax()->ExecuteJs("AddClassOnErrorByName('{$formId}','{$fieldUid}')");
                }//if(strlen($fieldUid))
            }//END foreach
            if(strlen($message)) {
                echo '<ul class="errors-list">'.$message.'</ul>';
            } else {
                NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$formId}')");
                echo Translate::GetMessage('required_fields');
            }//if(strlen($message))
            return;
        }//if(!$check)
        $this->templateId=$params->safeGet('id_template',$this->templateId,'is_not0_integer');
        $params->set('save_only',TRUE);
        if($idInstance>0) {
            $this->Exec('SaveRecord',$params);
        } else {
            $this->Exec('SaveNewRecord',$params);
        }//if($idInstance>0)
        InstancesHelpers::RedirectAfterAction($params,$this);
    }//END public function SaveInstance

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function SaveNewRecord($params=NULL) {
        // NApp::Dlog($params,'SaveNewRecord');
        $this->templateId=$params->safeGet('id_template',$this->templateId,'is_not0_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid DynamicForm template identifier!');
        }
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',['for_id'=>$this->templateId]);
        if(!$template instanceof IEntity) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $fieldsData=$params->safeGet('df_fields_values');
        if(!count($fieldsData)) {
            throw new AppException('Invalid DynamicForm fields data!');
        }
        $relationsData=$params->safeGet('df_relations_values');

        $transaction=AppSession::GetNewUID($template->getProperty('code',$this->templateId,'is_notempty_string'));
        DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
        try {
            $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstance',[
                'template_id'=>$this->templateId,
                'user_id'=>NApp::GetCurrentUserId(),
            ],['transaction'=>$transaction]);
            $idInstance=get_array_value($dbResult,[0,'inserted_id'],0,'is_integer');
            if($idInstance<=0) {
                NApp::Dlog($dbResult,'SetNewInstance>>$dbResult');
                throw new AppException('Database error on instance insert!');
            }
            /** @var \NETopes\Core\Data\VirtualEntity $f */
            foreach($fieldsData as $f) {
                $fieldValue=$f->getProperty('value');
                if(($f->getProperty('itype',0,'is_integer')==2 || $f->getProperty('parent_itype',0,'is_integer')==2) && is_array($fieldValue)) {
                    foreach($fieldValue as $index=>$fValue) {
                        $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',[
                            'instance_id'=>$idInstance,
                            'item_uid'=>$f->getProperty('uid',NULL,'is_notempty_string'),
                            'in_value'=>$fValue,
                            'in_name'=>NULL,
                            'in_index'=>$index,
                        ],['transaction'=>$transaction]);
                        if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                            NApp::Dlog($dbResult,'SetNewInstanceValue>>$dbResult');
                            throw new AppException('Database error on instance value insert!');
                        }
                    }//END foreach
                } else {
                    $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',[
                        'instance_id'=>$idInstance,
                        'item_uid'=>$f->getProperty('uid',NULL,'is_notempty_string'),
                        'in_value'=>$fieldValue,
                        'in_name'=>NULL,
                        'in_index'=>NULL,
                    ],['transaction'=>$transaction]);
                    if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                        NApp::Dlog($dbResult,'SetNewInstanceValue>>$dbResult');
                        throw new AppException('Database error on instance value insert!');
                    }
                }//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
            }//END foreach
            /** @var \NETopes\Core\Data\VirtualEntity $r */
            foreach($relationsData as $r) {
                $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceRelation',[
                    'instance_id'=>$idInstance,
                    'relation_id'=>$r->getProperty('id',NULL,'is_integer'),
                    'in_ivalue'=>$r->getProperty('ivalue',NULL,'?is_integer'),
                    'in_svalue'=>$r->getProperty('svalue',NULL,'?is_string'),
                ],['transaction'=>$transaction]);
                if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                    NApp::Dlog($dbResult,'SetNewInstanceRelation>>$dbResult');
                    throw new AppException('Database error on instance value insert!');
                }
            }//END foreach

            $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetInstanceUid',['for_id'=>$idInstance],['transaction'=>$transaction]);
            DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,FALSE);
        } catch(AppException $e) {
            DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,TRUE);
            NApp::Elog($e->getMessage());
            throw $e;
        }//END try
        InstancesHelpers::RedirectAfterAction($params,$this);
    }//END public function SaveNewRecord

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function SaveRecord($params=NULL) {
        // NApp::Dlog($params,'SaveRecord');
        $idTemplate=$params->safeGet('id_template',$this->templateId,'is_not0_integer');
        $idInstance=$params->safeGet('id',NULL,'is_not0_integer');
        if(!$idTemplate || !$idInstance) {
            throw new AppException('Invalid DynamicForm instance identifier!');
        }
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',['for_id'=>$idTemplate]);
        if(!is_object($template)) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $fieldsData=$params->safeGet('df_fields_values');
        if(!count($fieldsData)) {
            throw new AppException('Invalid DynamicForm fields data!');
        }
        $relationsData=$params->safeGet('df_relations_values');

        $transaction=AppSession::GetNewUID($template->getProperty('code',$idTemplate,'is_notempty_string'));
        DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
        try {
            $result=DataProvider::Get('Plugins\DForms\Instances','UnsetInstanceValues',['for_id'=>$idInstance],['transaction'=>$transaction]);
            if($result===FALSE) {
                throw new AppException('Database error on instance update!');
            }
            /** @var \NETopes\Core\Data\VirtualEntity $f */
            foreach($fieldsData as $f) {
                $fieldValue=$f->getProperty('value');
                if(($f->getProperty('itype',0,'is_integer')==2 || $f->getProperty('parent_itype',0,'is_integer')==2) && is_array($fieldValue)) {
                    foreach($fieldValue as $index=>$fValue) {
                        $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',[
                            'instance_id'=>$idInstance,
                            'item_uid'=>$f->getProperty('uid',NULL,'is_notempty_string'),
                            'in_value'=>$fValue,
                            'in_name'=>NULL,
                            'in_index'=>$index,
                        ],['transaction'=>$transaction]);
                        if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                            NApp::Dlog($dbResult,'SetNewInstanceValue>>$dbResult');
                            throw new AppException('Database error on instance value insert!');
                        }
                    }//END foreach
                } else {
                    $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',[
                        'instance_id'=>$idInstance,
                        'item_uid'=>$f->getProperty('uid',NULL,'is_notempty_string'),
                        'in_value'=>$fieldValue,
                        'in_name'=>NULL,
                        'in_index'=>NULL,
                    ],['transaction'=>$transaction]);
                    if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                        NApp::Dlog($dbResult,'SetNewInstanceValue>>$dbResult');
                        throw new AppException('Database error on instance value insert!');
                    }
                }//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
            }//END foreach

            DataProvider::Get('Plugins\DForms\Instances','SetInstanceState',[
                'for_id'=>$idInstance,
                'user_id'=>NApp::GetCurrentUserId(),
            ],['transaction'=>$transaction]);

            DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,FALSE);
        } catch(AppException $e) {
            DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,TRUE);
            NApp::Elog($e->getMessage());
            throw $e;
        }//END try
        InstancesHelpers::RedirectAfterAction($params,$this);
    }//END public function SaveRecord

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function DeleteRecord($params=NULL) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid record identifier!');
        $this->templateId=$params->safeGet('id_template',$this->templateId,'is_not0_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid template identifier!');
        }
        $result=DataProvider::Get('Plugins\DForms\Instances','UnsetInstance',['for_id'=>$id]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        $cModule=$params->safeGet('c_module',get_called_class(),'is_notempty_string');
        $cMethod=$params->safeGet('c_method','Listing','is_notempty_string');
        $cTarget=$params->safeGet('c_target','main-content','is_notempty_string');
        ModulesProvider::Exec($cModule,$cMethod,['id_template'=>$this->templateId,'target'=>$cTarget],$cTarget);
    }//END public function DeleteRecord

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function EditRecordState($params=NULL) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid DynamicForm instance identifier!');
        $result=DataProvider::Get('Plugins\DForms\Instances','SetInstanceState',[
            'for_id'=>$id,
            'in_state'=>$params->safeGet('state',NULL,'is_integer'),
            'user_id'=>NApp::GetCurrentUserId(),
        ]);
        if($result===FALSE) {
            throw new AppException('Failed database operation!');
        }
    }//END public function EditRecordState

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowViewForm($params=NULL) {
        // NApp::Dlog($params,'ShowViewForm');
        $params->set('view_only',1);
        $this->ShowEditForm($params);
    }//END public function ShowViewForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return mixed return description
     * @throws \NETopes\Core\AppException
     */
    public function PrintInstancePdf($params=NULL) {
        NApp::Dlog($params,'PrintInstancePdf');
        $idInstance=$params->safeGet('id',NULL,'is_integer');
        if(!$idInstance) {
            throw new AppException('Invalid DynamicForm instance identifier!');
        }
        $cache=$params->safeGet('cache',TRUE,'bool');
        $result_type=$params->safeGet('result_type',0,'is_integer');
        $instance=DataProvider::Get('Plugins\DForms\Instances','GetInstanceItem',['for_id'=>$idInstance]);
        $filename=get_array_value($instance,'uid','','is_string');
        if(!strlen($filename)) {
            $filename=date('Y-m-d_H-i-s').'.pdf';
        } else {
            $filename=str_replace(' ','_',trim($filename)).'.pdf';
        }//if(!strlen($filename))
        $category=get_array_value($instance,'category',get_array_value($instance,'template_code',$this->name,'is_notempty_string'),'is_notempty_string');
        if($cache && strlen($filename) && file_exists(NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename)) {
            if($result_type==1) {
                $data=[
                    'file_name'=>$filename,
                    'path'=>NAPP::GetRepositoryPath().'forms/'.$category.'/',
                    'download_name'=>$filename,
                ];
            } else {
                $data=file_get_contents(NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename);
            }//if($result_type==1)
            return $data;
        }//if($cache && strlen($filename) && file_exists(NAPP::GetRepositoryPath().$company.'/'.$filename))
        if($cache) {
            if(!file_exists(NAPP::GetRepositoryPath().'forms')) {
                mkdir(NAPP::GetRepositoryPath().'forms',755);
            }
            if(!file_exists(NAPP::GetRepositoryPath().'forms/'.$category)) {
                mkdir(NAPP::GetRepositoryPath().'forms/'.$category,755);
            }
        }//if($cache)
        $html_data=$this->Exec('PrepareFormHtml',['id'=>$idInstance]);
        $pdfdoc=new InstancesPdf(['html_data'=>$html_data,'file_name'=>$filename]);
        if($cache) {
            // file_put_contents(NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename,$data);
            $pdfdoc->Output(['output_type'=>'F','file_name'=>NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename]);
            if($result_type==1) {
                $data=[
                    'file_name'=>$filename,
                    'path'=>NAPP::GetRepositoryPath().'forms/'.$category.'/',
                    'download_name'=>$filename,
                ];
            } else {
                $data=file_get_contents(NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename);
            }//if($result_type==1)
        } else {
            $data=$pdfdoc->Output(['base64'=>FALSE,'file_name'=>NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename]);
        }//if($cache)
        return $data;
    }//END public function PrintInstancePdf
}//END class Instances extends Module