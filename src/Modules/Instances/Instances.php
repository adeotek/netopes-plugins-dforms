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
namespace NETopes\Plugins\DForms\Modules\Instances;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\App\ModulesProvider;
use NETopes\Core\App\Params;
use NETopes\Core\AppSession;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\HiddenInput;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\VirtualEntity;
use NETopes\Core\Validators\Validator;
use NETopes\Core\AppException;
use NApp;
use Translate;

/**
 * Class Instances
 *
 * @package NETopes\Plugins\DForms\Modules\Instances
 */
class Instances extends Module {
    /**
     * @var integer Dynamic form template ID
     * @access protected
     */
    public $idTemplate=NULL;
    /**
     * @var integer Dynamic form template code (numeric)
     * @access protected
     */
    public $templateCode=NULL;
    /**
     * @var integer Flag for modal add/edit forms
     * @access protected
     */
    public $isModal=FALSE;
    /**
     * @var array List of header fields to be displayed in Listing
     * @access protected
     */
    public $showInListing=['template_code','template_name','create_date','user_full_name','last_modified','last_user_full_name'];
    /**
     * @var array List CSS styles to be used for generating view HTML
     * @access protected
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
        $this->templateCode=NULL;
    }//END protected function _Init

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function Listing($params=NULL) {
        $idTemplate=$params->safeGet('id_template',$this->idTemplate,'is_not0_integer');
        $templateCode=$params->safeGet('templateCode',$this->templateCode,'is_not0_integer');
        if(!$idTemplate && !$templateCode) {
            throw new AppException('Invalid DynamicForm template identifier!');
        }
        $fields=DataProvider::Get('Plugins\DForms\Instances','GetFields',[
            'template_id'=>($idTemplate ? $idTemplate : NULL),
            'for_template_code'=>$templateCode,
            'for_listing'=>1,
        ]);
        $fTypes=DataProvider::GetKeyValue('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
        $cModule=$params->safeGet('cmodule',$this->class,'is_notempty_string');
        $cMethod=$params->safeGet('cmethod',call_back_trace(0),'is_notempty_string');
        $cTarget=$params->safeGet('ctarget','main-content','is_notempty_string');
        $target=$params->safeGet('target','main-content','is_notempty_string');
        $listingTarget=$target.'_listing';

        $view=new AppView(get_defined_vars(),$this,($target=='main-content' ? 'main' : 'default'));
        $view->SetTitle('');
        $view->SetTargetId($listingTarget);
        $view->AddTableView($this->GetViewFile('Listing'));
        $view->Render();
    }//END public function Listing

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function GlobalListing($params=NULL) {
        $idTemplate=$params->safeGet('for_id',$this->idTemplate,'is_not0_integer');
        $templateCode=$params->safeGet('for_code',$this->templateCode,'is_not0_integer');
        $fTypes=DataProvider::GetKeyValueArray('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
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
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddEditForm($params=NULL) {
        // NApp::Dlog($params,'ShowAddEditForm');
        $this->templateCode=$params->safeGet('template_code',$this->templateCode,'is_not0_integer');
        $idInstance=$params->safeGet('id',NULL,'is_not0_integer');
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>NULL,
            'for_code'=>$this->templateCode,
            'instance_id'=>$idInstance,
            'for_state'=>1,
        ]);
        if(!is_object($template)) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $idTemplate=$template->getProperty('id',NULL,'is_integer');
        $templateCode=$template->getProperty('code',NULL,'is_integer');
        if(!$idTemplate) {
            throw new AppException('Invalid DynamicForm template!');
        }

        $ctrl_params=InstancesHelpers::PrepareForm($params,$template,$idInstance);
        if(!$ctrl_params) {
            throw new AppException('Invalid DynamicForm configuration!');
        }
        $controlClass=get_array_value($ctrl_params,'control_class','','is_string');
        $cModule=$params->safeGet('cmodule',$this->class,'is_notempty_string');
        $cMethod=$params->safeGet('cmethod','Listing','is_notempty_string');
        $cTarget=$params->safeGet('ctarget','main-content','is_notempty_string');
        $isModal=$params->safeGet('is_modal',$this->isModal,'is_integer');
        $aeSaveInstanceMethod='SaveInstance';
        $tName=get_array_value($ctrl_params,'tname',microtime(),'is_string');
        $fTagId=get_array_value($ctrl_params,'tag_id','','is_string');
        $actionsLocation=$params->safeGet('actions_location','form','is_notempty_string');
        if($controlClass!='BasicForm' && $actionsLocation=='form') {
            $actionsLocation='container';
        }
        if($isModal) {
            $containerType='modal';
            $view=new AppView(get_defined_vars(),$this,$containerType);
            $view->SetIsModalView(TRUE);
            $view->SetModalWidth('80%');
            $view->SetTitle($template->getProperty('name','','is_string'));
        } else {
            $containerType=$params->safeGet('container_type',($cTarget=='main-content' ? 'main' : NULL),'?is_string');
            $view=new AppView(get_defined_vars(),$this,$containerType);
            $view->SetTitle($template->getProperty('name','','is_string'));
        }//if($isModal)
        $formActions=[];
            $fResponseTarget=get_array_value($ctrl_params,'response_target','df_'.$tName.'_errors','is_notempty_string');
        if(strlen($fTagId)) {
            $btnSave=new Button(['value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','class'=>NApp::$theme->GetBtnPrimaryClass(),'onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->class}', 'method': '{$aeSaveInstanceMethod}', 'params': { 'id_template': '{$idTemplate}', 'id': '{$idInstance}', 'relations': '{nGet|df_{$tName}_relations:form}', 'data': '{nGet|df_{$tName}_form:form}', 'is_modal': '{$isModal}', 'cmodule': '{$cModule}', 'cmethod': '{$cMethod}', 'ctarget': '{$cTarget}', 'target': '{$fTagId}' } }",$fResponseTarget)]);
            $formActions[]=$btnSave->Show();
            if($params->safeGet('back_action',TRUE,'bool')) {
                if($isModal) {
                    $btnBack=new Button(['tag_id'=>'df_'.$tName.'_cancel','value'=>Translate::GetButton('cancel'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-ban','onclick'=>"CloseModalForm()",]);
                } else {
                    $btnBack=new Button(['tag_id'=>'df_'.$tName.'_back','value'=>Translate::GetButton('back'),'icon'=>'fa fa-chevron-left','class'=>NApp::$theme->GetBtnDefaultClass(),'onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$cModule}', 'method': '{$cMethod}', 'params': { 'id_template': '{$idTemplate}', 'id': '{$idInstance}', 'target': '{$cTarget}' } }",$cTarget)]);
                }//if($isModal)
                $formActions[]=$btnBack->Show();
            }//if($params->safeGet('back_action',TRUE,'bool'))
        }//if(strlen($fTagId))
        if($actionsLocation=='container') {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            $view->SetActions($formActions);
        }//if($actionsLocation=='container')
        $addContentMethod='Add'.$controlClass;
        $view->$addContentMethod($this->GetViewFile('AddEditInstanceForm'));
        $relationsHtml=InstancesHelpers::PrepareRelationsFormPart($idTemplate,$idInstance,$params);
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_relations">'.$relationsHtml.'</div></div>');
        if($actionsLocation=='after') {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 actions-group">'.implode('',$formActions).'</div></div>');
        }//if($actionsLocation=='after')
        $view->Render();
    }//END public function ShowAddEditForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function SaveInstance($params=NULL) {
        NApp::Dlog($params,'SaveInstance');
        $target=$params->safeGet('target','','is_string');
        $ckeck=InstancesHelpers::ValidateSaveParams($params);
        if(!$ckeck) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//if(!$ckeck)
        // $idInstance=$params->safeGet('id_instance');
        // if($idInstance>0) {
        //     $this->Exec('SaveRecord',$params);
        // } else {
        //     $this->Exec('SaveNewRecord',$params);
        // }//if($idInstance>0)
    }//END public function SaveInstance

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddForm($params=NULL) {
        // NApp::Dlog($params,'ShowAddForm');
        $idTemplate=$params->safeGet('id_template',$this->idTemplate,'is_not0_integer');
        $templateCode=$params->safeGet('template_code',$this->templateCode,'is_not0_integer');
        if(!$idTemplate && !$templateCode) {
            throw new AppException('Invalid DynamicForm template identifier!');
        }
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>(is_numeric($idTemplate) ? $idTemplate : NULL),
            'for_code'=>(is_numeric($templateCode) ? $templateCode : NULL),
            'instance_id'=>NULL,
            'for_state'=>1,
        ]);
        $idTemplate=get_array_value($template,'id',NULL,'is_integer');
        if(!$idTemplate) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $cModule=$params->safeGet('cmodule',get_called_class(),'is_notempty_string');
        $cMethod=$params->safeGet('cmethod',call_back_trace(0),'is_notempty_string');
        $cTarget=$params->safeGet('ctarget','main-content','is_notempty_string');
        $ctrl_params=$this->PrepareForm($params,$template);
        if(!$ctrl_params) {
            throw new AppException('Invalid DynamicForm configuration!');
        }
        $isModal=$params->safeGet('is_modal',$this->isModal,'is_integer');
        $ftitle=$params->safeGet('form_title','&nbsp;','is_string');
        require($this->GetViewFile('AddInstanceForm'));
        if($isModal) {
            NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".$params->safeGet('nav_item_name','','is_string')."'))");
        }//if($isModal)
    }//END public function ShowAddForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function SaveNewRecord($params=NULL) {
        // NApp::Dlog($params,'SaveNewRecord');
        $idTemplate=$params->safeGet('id_template',$this->idTemplate,'is_not0_integer');
        if(!$idTemplate) {
            throw new AppException('Invalid DynamicForm template identifier!');
        }
        $target=$params->safeGet('target','','is_string');
        $data=$params->safeGet('data',[],'is_array');
        if(!count($data)) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//if(!count($data))
        $error=FALSE;




        if($error) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//if($error)

        return;

        // $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',['for_id'=>$idTemplate]);
        // $transaction=AppSession::GetNewUID(get_array_value($template,'code','N/A','is_notempty_string'));
        // DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
        // try {
        //     $result=DataProvider::Get('Plugins\DForms\Instances','SetNewInstance',[
        //         'template_id'=>$idTemplate,
        //         'user_id'=>NApp::GetCurrentUserId(),
        //     ],['transaction'=>$transaction]);
        //     $idInstance=get_array_value($result,[0,'inserted_id'],0,'is_numeric');
        //     if($idInstance<=0) {
        //         throw new AppException('Database error on instance insert!');
        //     }
        //
        //     foreach($fields as $f) {
        //         if(($f['itype']==2 || $f['parent_itype']==2) && is_array($f['value'])) {
        //             foreach($f['value'] as $index=>$fValue) {
        //                 $result=DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceValue',[
        //                     'instance_id'=>$idInstance,
        //                     'item_id'=>$f['id'],
        //                     'in_value'=>$fValue,
        //                     'in_name'=>NULL,
        //                     'in_index'=>$index,
        //                 ],['transaction'=>$transaction]);
        //                 if(get_array_value($result,[0,'inserted_id'],0,'is_integer')<=0) {
        //                     throw new AppException('Database error on instance value insert!');
        //                 }
        //             }//END foreach
        //         } else {
        //             $result=DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceValue',[
        //                 'instance_id'=>$idInstance,
        //                 'item_id'=>$f['id'],
        //                 'in_value'=>(isset($f['value']) ? $f['value'] : NULL),
        //                 'in_name'=>NULL,
        //                 'in_index'=>NULL,
        //             ],['transaction'=>$transaction]);
        //             if(get_array_value($result,[0,'inserted_id'],0,'is_integer')<=0) {
        //                 throw new AppException('Database error on instance value insert!');
        //             }
        //         }//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
        //     }//END foreach
        //
        //     foreach($relations as $r) {
        //         $result=DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceRelation',[
        //             'instance_id'=>$idInstance,
        //             'relation_id'=>$r['id'],
        //             'in_ivalue'=>$r['ivalue'],
        //             'in_svalue'=>$r['svalue'],
        //         ],['transaction'=>$transaction]);
        //         if(get_array_value($result,[0,'inserted_id'],0,'is_integer')<=0) {
        //             throw new AppException('Database error on instance value insert!');
        //         }
        //     }//END foreach
        //
        //     DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,FALSE);
        // } catch(AppException $e) {
        //     DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,TRUE);
        //     NApp::Elog($e->getMessage());
        //     throw $e;
        // }//END try

        if($params->safeGet('is_modal',$this->isModal,'is_numeric')==1) {
            $this->CloseForm();
        }
        $cModule=$params->safeGet('cmodule',get_called_class(),'is_notempty_string');
        $cMethod=$params->safeGet('cmethod','Listing','is_notempty_string');
        $cTarget=$params->safeGet('ctarget','main-content','is_notempty_string');
        ModulesProvider::Exec($cModule,$cMethod,['id_template'=>$idTemplate,'target'=>$cTarget],$cTarget);
        // NApp::Ajax()->Execute("{ 'module': '{$cModule}', 'method': '{$cMethod}', 'params': { 'id_template': {$idTemplate}, 'target': '{$cTarget}' } }",$cTarget);
    }//END public function SaveNewRecord

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function ShowEditForm($params=NULL) {
        // NApp::Dlog($params,'ShowEditForm');
        $idInstance=$params->safeGet('id',NULL,'is_not0_integer');
        if(!$idInstance) {
            throw new AppException('Invalid DynamicForm instance identifier!');
        }
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>NULL,
            'for_code'=>NULL,
            'instance_id'=>$idInstance,
            'for_state'=>1,
        ]);
        $idTemplate=get_array_value($template,'id',NULL,'is_integer');
        $templateCode=get_array_value($template,'code',NULL,'is_integer');
        if(!$idTemplate) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $cModule=$params->safeGet('cmodule',get_called_class(),'is_notempty_string');
        $cMethod=$params->safeGet('cmethod','Listing','is_notempty_string');
        $cTarget=$params->safeGet('ctarget','main-content','is_notempty_string');
        $ctrl_params=$this->PrepareForm($params,$template,$idInstance);
        if(!$ctrl_params) {
            throw new AppException('Invalid DynamicForm configuration!');
        }
        $isModal=$params->safeGet('is_modal',$this->isModal,'is_integer');
        require($this->GetViewFile('EditInstanceForm'));
        if($isModal) {
            NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".Translate::GetButton('edit')."'))");
        }//if($isModal)
    }//END public function ShowEditForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function SaveRecord($params=NULL) {
        // NApp::Dlog($params,'SaveRecord');
        $idTemplate=$params->safeGet('id_template',$this->idTemplate,'is_not0_integer');
        $idInstance=$params->safeGet('id',NULL,'is_not0_integer');
        if(!$idTemplate || !$idInstance) {
            throw new AppException('Invalid DynamicForm instance identifier!');
        }
        $target=$params->safeGet('target','','is_string');
        $data=$params->safeGet('data',[],'is_array');
        if(!count($data)) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//if(!count($data))
        $error=FALSE;
        $fields=DataProvider::Get('Plugins\DForms\Instances','GetFields',['template_id'=>$idTemplate,'instance_id'=>$idInstance]);
        foreach($fields as $k=>$field) {
            if($field['itype']==2 || $field['parent_itype']==2) {
                $fvals=get_array_value($data,$field['id'],NULL,'is_array');
                if(!is_array($fvals) || !count($fvals)) {
                    $error=$field['required']==1;
                    $fval=NULL;
                } else {
                    $fval=[];
                    foreach($fvals as $i=>$fv) {
                        switch($field['data_type']) {
                            case 'numeric':
                                $fval[$i]=Validator::ValidateValue($fv,NULL,'is_numeric');
                                $error=($field['required']==1 && !is_numeric($fval[$i]));
                                break;
                            case 'string':
                            default:
                                $fval[$i]=Validator::ValidateValue($fv,'','is_string');
                                $error=($field['required']==1 && !strlen($fval[$i]));
                                break;
                        }//END switch
                        if($error) {
                            break;
                        }
                    }//END foreach
                }//if(!is_array($fvals) || !count($fvals))
            } else {
                switch($field['data_type']) {
                    case 'numeric':
                        $fval=get_array_value($data,$field['id'],NULL,'is_numeric');
                        $error=($field['required']==1 && !is_numeric($fval));
                        break;
                    case 'string':
                    default:
                        $fval=get_array_value($data,$field['id'],'','is_string');
                        $error=($field['required']==1 && !strlen($fval));
                        break;
                }//END switch
            }//if($field['itype']==2 || $field['parent_itype']==2)
            if($error) {
                break;
            }
            $fields[$k]['value']=$fval;
        }//END foreach

        // $relations = DataProvider::Get('Plugins\DForms\Instances','GetRelations',['template_id'=>$idTemplate,'instance_id'=>$idInstance));
        // foreach($relations as $k=>$rel) {
        // 	$dtype = get_array_value($rel,'dtype','','is_string');
        // 	$relations[$k]['ivalue'] = 0;
        // 	$relations[$k]['svalue'] = '';
        // 	switch($rel['rtype']) {
        // 		case 1:
        // 			$r_val = NApp::GetParam($rel['key']);
        // 			if($dtype=='integer') {
        // 				if(is_numeric($r_val) && $r_val>0) {
        // 					$relations[$k]['ivalue'] = $r_val;
        // 					$relations[$k]['svalue'] = '';
        // 				}//if(is_numeric($r_val) && $r_val>0)
        // 			} else {
        // 				if(is_string($r_val) && strlen($r_val)) {
        // 					$relations[$k]['ivalue'] = 0;
        // 					$relations[$k]['svalue'] = $r_val;
        // 				}//if(is_string($r_val) && strlen($r_val))
        // 			}//if($dtype=='integer')
        // 			break;
        // 		case 3:
        // 			if($dtype=='integer') {
        // 				$r_val = get_array_value($data,'relation-'.$rel['key'],0,'is_integer');
        // 			} else {
        // 				$r_val = get_array_value($data,'relation-'.$rel['key'],'','is_string');
        // 			}//if($dtype=='integer')
        // 			break;
        // 	}//END switch
        // 	if($rel['required']==1 && (!$relations[$k]['ivalue'] || !$relations[$k]['svalue'])) {
        // 		throw new AppException('Invalid relation value: ['.$rel['name'].']');
        // 	}//if($rel['required']==1 && (!$relations[$k]['ivalue'] || !$relations[$k]['svalue']))
        // }//END foreach

        if($error) {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
            echo Translate::GetMessage('required_fields');
            return;
        }//if($error)

        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',['for_id'=>$idTemplate]);
        $transaction=AppSession::GetNewUID(get_array_value($template,'code','N/A','is_notempty_string'));
        DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
        try {
            $result=DataProvider::Get('Plugins\DForms\Instances','UnsetInstanceValues',['instance_id'=>$idInstance],['transaction'=>$transaction]);
            if($result===FALSE) {
                throw new AppException('Database error on instance update!');
            }

            foreach($fields as $f) {
                if(in_array($f['class'],['','FormTitle','FormSubTitle','FormSeparator','Message','BasicForm'])) {
                    continue;
                }
                if(($f['itype']==2 || $f['parent_itype']==2) && is_array($f['value'])) {
                    foreach($f['value'] as $index=>$fValue) {
                        $result=DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceValue',[
                            'instance_id'=>$idInstance,
                            'item_id'=>$f['id'],
                            'in_value'=>$fValue,
                            'in_name'=>NULL,
                            'in_index'=>$index,
                        ],['transaction'=>$transaction]);
                        if(get_array_value($result,[0,'inserted_id'],0,'is_integer')<=0) {
                            throw new AppException('Database error on instance value insert!');
                        }
                    }//END foreach
                } else {
                    $result=DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceValue',[
                        'instance_id'=>$idInstance,
                        'item_id'=>$f['id'],
                        'in_value'=>(isset($f['value']) ? $f['value'] : NULL),
                        'in_name'=>NULL,
                        'in_index'=>NULL,
                    ],['transaction'=>$transaction]);
                    if(get_array_value($result,[0,'inserted_id'],0,'is_integer')<=0) {
                        throw new AppException('Database error on instance value insert!');
                    }
                }//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
            }//END foreach

            // foreach($relations as $r) {
            // 	$result = DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceRelation',[
            // 		'instance_id'=>$idInstance,
            // 		'relation_id'=>$r['id'],
            // 		'in_ivalue'=>$r['ivalue'],
            // 		'in_svalue'=>$r['svalue'],
            // 	),['transaction'=>$transaction]);
            // 	if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
            // }//END foreach

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
        if($params->safeGet('is_modal',$this->isModal,'is_numeric')==1) {
            $this->CloseForm();
        }
        $cModule=$params->safeGet('cmodule',get_called_class(),'is_notempty_string');
        $cMethod=$params->safeGet('cmethod','Listing','is_notempty_string');
        $cTarget=$params->safeGet('ctarget','main-content','is_notempty_string');
        ModulesProvider::Exec($cModule,$cMethod,['id_template'=>$idTemplate,'target'=>$cTarget],$cTarget);
        //NApp::Ajax()->Execute("{ 'module': '{$cModule}', 'method': '{$cMethod}', 'params': { 'id_template': {$idTemplate}, 'target': '{$cTarget}' } }",$cTarget);
    }//END public function SaveRecord

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function DeleteRecord($params=NULL) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid record identifier!');
        $idTemplate=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $result=DataProvider::Get('Plugins\DForms\Instances','UnsetInstance',['for_id'=>$id]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        $cModule=$params->safeGet('cmodule',get_called_class(),'is_notempty_string');
        $cMethod=$params->safeGet('cmethod','Listing','is_notempty_string');
        $cTarget=$params->safeGet('ctarget','main-content','is_notempty_string');
        ModulesProvider::Exec($cModule,$cMethod,['id_template'=>$idTemplate,'target'=>$cTarget],$cTarget);
    }//END public function DeleteRecord

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function EditRecordState($params=NULL) {
        $id=$params->safeGet('id',NULL,'is_not0_integer');
        if(!$id) {
            throw new AppException('Invalid DynamicForm instance identifier!');
        }
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
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function ShowViewForm($params=NULL) {
        // NApp::Dlog($params,'ShowViewForm');
        $idInstance=$params->getOrFail('id','is_not0_integer','Invalid instance identifier!');
        $instance=DataProvider::Get('Plugins\DForms\Instances','GetInstanceItem',['for_id'=>$idInstance]);
        $idTemplate=$instance->getProperty('id_template',$this->idTemplate,'is_integer');
        if(!$idTemplate) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $isModal=$params->safeGet('is_modal',$this->isModal,'is_integer');
        require($this->GetViewFile('ViewInstanceForm'));
        if($isModal) {
            NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".Translate::GetButton('view')."'))");
        }//if($isModal)
    }//END public function ShowViewForm

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return void
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function PrepareFormHtml($params=NULL) {
        // NApp::Dlog($params,'PrepareFormHtml');
        $idInstance=$params->safeGet('id',NULL,'is_integer');
        if(!$idInstance) {
            throw new AppException('Invalid DynamicForm instance identifier!');
        }
        $idSubForm=$params->safeGet('id_sub_form',0,'is_integer');
        $idItem=$params->safeGet('id_item',0,'is_integer');
        $index=$params->safeGet('index',0,'is_integer');
        $output=$params->safeGet('output',FALSE,'bool');
        if($idSubForm) {
            $instance=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
                'for_id'=>$idSubForm,
                'for_code'=>NULL,
                'instance_id'=>$idInstance,
                'for_state'=>1,
            ]);
            $idSubForm=get_array_value($instance,'id',NULL,'is_integer');
            // NApp::Dlog($idItem,'$idItem');
            // NApp::Dlog($idSubForm,'$idSubForm');
            // NApp::Dlog($template,'$template');
            if(!$idSubForm || !$idItem) {
                return NULL;
            }
            $relations=NULL;
            $fields=DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
                'instance_id'=>$idInstance,
                'item_id'=>$idItem,
                'for_index'=>(is_numeric($index) ? $index : NULL),
            ]);
            // NApp::Dlog($fields,'$fields');
        } else {
            $instance=DataProvider::Get('Plugins\DForms\Instances','GetInstanceItem',['for_id'=>$idInstance]);
            $relations=DataProvider::Get('Plugins\DForms\Instances','GetRelations',['instance_id'=>$idInstance]);
            $fields=DataProvider::Get('Plugins\DForms\Instances','GetStructure',['instance_id'=>$idInstance]);
        }
        $themeType=get_array_value($instance,'theme_type','','is_string');
        $controlsSize=get_array_value($instance,'controls_size','','is_string');
        $separatorWidth=get_array_value($instance,'separator_width','','is_string');
        $labelCols=get_array_value($instance,'label_cols','','is_string');
        $html=NULL;
        require($this->GetViewFile('PrepareFormHtml'));
        return $html;
    }//END public function PrepareFormHtml

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return mixed return description
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function GetInstancePdf($params=NULL) {
        NApp::Dlog($params,'GetInstancePdf');
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
    }//END public function GetInstancePdf
}//END class Instances extends Module