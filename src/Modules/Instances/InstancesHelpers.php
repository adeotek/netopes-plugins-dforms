<?php
/**
 * InstancesHelpers class file
 *
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Instances;
use Exception;
use NApp;
use NETopes\Core\App\ModulesProvider;
use NETopes\Core\App\Params;
use NETopes\Core\AppException;
use NETopes\Core\Controls\BasicForm;
use NETopes\Core\Controls\BasicFormBuilder;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\ControlsHelpers;
use NETopes\Core\Controls\HiddenInput;
use NETopes\Core\Controls\IControlBuilder;
use NETopes\Core\Controls\TabControlBuilder;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\DataSet;
use NETopes\Core\Data\IEntity;
use NETopes\Core\Data\VirtualEntity;
use NETopes\Core\Validators\Validator;
use NETopes\Plugins\DForms\Modules\Templates\Templates;
use Translate;

/**
 * Class InstancesHelpers
 *
 * @package NETopes\Plugins\DForms\Modules\Instances
 */
class InstancesHelpers {

    /**
     * @param int                        $templateId
     * @param \NETopes\Core\App\Params   $params
     * @param \NETopes\Core\Data\DataSet $relations
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    public static function GetInstanceUid(int $templateId,Params $params,DataSet $relations): ?string {
        $result=NULL;
        /** @var IEntity $relation */
        foreach($relations as $relation) {
            if(!$relation instanceof IEntity) {
                continue;
            }
            $dataType=$relation->getProperty('dtype',NULL,'is_string');
            $key=$relation->getProperty('key',NULL,'is_string');
            switch($relation->getProperty('rtype',NULL,'is_string')) {
                case 20:
                    $relRawValue=NApp::GetParam($key);
                    break;
                case 21:
                    $relRawValue=NApp::GetPageParam($key);
                    break;
                case 30:
                    $relRawValue=$params->safeGet($key,NULL,'isset');
                    break;
                case 10:
                default:
                    $relRawValue=NULL;
                    break;
            }//END switch
            $relValue=NULL;
            if(static::GetValidatedValue($relRawValue,$relValue,$dataType)) {
                throw new AppException('Invalid DynamicForm relation value ['.$key.']!');
            }
            $result.=(strlen($result) ? '-' : '').$relValue;
        }//END foreach
        if(strlen($result)) {
            $result=$templateId.'-'.$result;
        }
        return $result;
    }//END public static function GetInstanceUid

    /**
     * @param \NETopes\Core\Data\VirtualEntity $field
     * @param array                            $fParams
     * @param mixed                            $fValue
     * @param string|null                      $themeType
     * @param int                              $iCount
     * @return array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareRepeatableField(VirtualEntity $field,array $fParams,$fValue=NULL,?string $themeType=NULL,int $iCount=0): array {
        // NApp::Dlog(['$field'=>$field,'$fParams'=>$fParams,'$fValue'=>$fValue,'$themeType'=>$themeType,'$iCount'=>$iCount],'PrepareField');
        $instanceId=$field->getProperty('id_instance',NULL,'is_integer');
        $fieldUid=$field->getProperty('uid',NULL,'is_string');
        if(!strlen($fieldUid)) {
            throw new AppException('Invalid UID for field ['.$field->getProperty('name','','is_string').']!');
        }//if(!strlen($fieldUid))
        // $tagId=($instanceId ? $instanceId.'_' : '').$field->getProperty('uid','','is_string').'_'.$field->getProperty('name','','is_string');
        $tagId=($instanceId ?? 0).'_'.$fieldUid;
        $fValuesArray=explode('|::|',$fValue);
        $field->set('tag_id',$tagId.'-0');
        $field->set('tag_name',$fieldUid.'[]');
        $field->set('value',get_array_value($fValuesArray,0,NULL,'isset'));
        $fClass=$field->getProperty('class','','is_string');
        $idValuesList=$field->getProperty('id_values_list',0,'is_numeric');
        if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0) {
            $fParams['load_type']='database';
            $fParams['data_source']=[
                'ds_class'=>'Plugins\DForms\ValuesLists',
                'ds_method'=>'GetValues',
                'ds_params'=>['list_id'=>$idValuesList,'for_state'=>1],
            ];
        }//if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0)
        $fParams=ControlsHelpers::ReplaceDynamicParams($fParams,$field,TRUE,'_dfp_');
        $fParams['container_class']='ctrl-repeatable';
        $removeAction=[
            [
                'type'=>'Button',
                'params'=>[
                    // 'tooltip'=>Translate::GetButton('remove_field'),
                    'icon'=>'fa fa-minus-circle',
                    'class'=>NApp::$theme->GetBtnDangerClass('pull-right clsRemoveRepeatableCtrlBtn'),
                    'onclick'=>"RemoveRepeatableControl(this)",
                ],
            ],
        ];
        $iCustomActions=[];
        for($i=1; $i<$iCount; $i++) {
            $tmpCtrl=$fParams;
            $tmpCtrl['container']='none';
            $tmpCtrl['no_label']=TRUE;
            $tmpCtrl['label_width']=NULL;
            $tmpCtrl['width']=NULL;
            $tmpCtrl['tag_id']=$tagId.'-'.$i;
            $tmpCtrl['value']=get_array_value($fValuesArray,$i,NULL,'isset');
            if(strpos($themeType,'bootstrap')!==FALSE) {
                $tmpCtrl['class'].=' form-control';
            }
            $tmpCtrl['extra_tag_params']=(isset($tmpCtrl['extra_tag_params']) && $tmpCtrl['extra_tag_params'] ? $tmpCtrl['extra_tag_params'].' ' : '').'data-tid="'.$tagId.'" data-ti="'.$i.'"';
            $tmpCtrl['actions']=$removeAction;
            $iCustomActions[]=[
                'type'=>$fClass,
                'params'=>$tmpCtrl,
            ];
        }//END for
        $iCustomActions[]=[
            'type'=>'Button',
            'params'=>[
                'value'=>Translate::GetButton('add_field'),
                'icon'=>'fa fa-plus',
                'class'=>'add-ctrl-btn clsAddRepeatableCtrlBtn',
                'onclick'=>"AddRepeatableControl(this,'{$tagId}')",
                'extra_tag_params'=>'data-ract="&nbsp;'.Translate::GetButton('remove_field').'"',
            ],
        ];
        $fParams['extra_tag_params']=(isset($fParams['extra_tag_params']) && $fParams['extra_tag_params'] ? $fParams['extra_tag_params'].' ' : '').'data-tid="'.$tagId.'" data-ti="0"';
        $fParams['actions']=$removeAction;
        $fParams['custom_actions']=$iCustomActions;
        // NApp::Dlog($fClass,'$fClass');
        // NApp::Dlog($fParams,'$fParams');
        $colSpan=$field->getProperty('colspan',0,'is_integer');
        if($colSpan>1) {
            return [
                'colspan'=>$colSpan,
                'control_type'=>$fClass,
                'control_params'=>$fParams,
            ];
        }//if($colSpan>1)
        return [
            'control_type'=>$fClass,
            'control_params'=>$fParams,
        ];
    }//END public static function PrepareRepeatableField

    /**
     * @param \NETopes\Core\Data\VirtualEntity $field
     * @param array                            $fParams
     * @param mixed                            $fValue
     * @param string|null                      $themeType
     * @param bool                             $repeatable
     * @param int                              $iCount
     * @return array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareField(VirtualEntity $field,array $fParams,$fValue=NULL,?string $themeType=NULL,bool $repeatable=FALSE,int $iCount=0): array {
        // NApp::Dlog(['$field'=>$field,'$fParams'=>$fParams,'$fValue'=>$fValue,'$themeType'=>$themeType,'$iCount'=>$iCount],'PrepareField');
        if($repeatable) {
            return static::PrepareRepeatableField($field,$fParams,$fValue,$themeType,$iCount);
        }
        $instanceId=$field->getProperty('id_instance',NULL,'is_integer');
        $fieldUid=$field->getProperty('uid',NULL,'is_string');
        if(!strlen($fieldUid)) {
            throw new AppException('Invalid UID for field ['.$field->getProperty('name','','is_string').']!');
        }//if(!strlen($fieldUid))
        // $tagId=($instanceId ? $instanceId.'_' : '').$field->getProperty('uid','','is_string').'_'.$field->getProperty('name','','is_string');
        $field->set('tag_id',($instanceId ?? 0).'_'.$fieldUid);
        $field->set('tag_name',$fieldUid);
        // if(strlen($themeType)) { $fParams['theme_type'] = $themeType; }
        $fClass=$field->getProperty('class','','is_string');
        if($fClass=='Message') {
            $flabel=$field->getProperty('label','','is_string');
            $fdesc=$field->getProperty('description','','is_string');
            $fParams['text']=$flabel.$fdesc;
        }//if($fClass=='Message')
        $idValuesList=$field->getProperty('id_values_list',0,'is_numeric');
        if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0) {
            $fParams['load_type']='database';
            $fParams['data_source']=[
                'ds_class'=>'Plugins\DForms\ValuesLists',
                'ds_method'=>'GetValues',
                'ds_params'=>['list_id'=>$idValuesList,'for_state'=>1],
            ];
        }//if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0)
        if(in_array($fClass,['SmartComboBox'])) {
            $field->set('selected_value',$fValue);
        } else {
            $field->set('value',$fValue);
        }//if(in_array($fClass,['SmartComboBox']))
        $fParams=ControlsHelpers::ReplaceDynamicParams($fParams,$field,TRUE,'_dfp_');
        $colSpan=$field->getProperty('colspan',0,'is_integer');
        if($colSpan>1) {
            return [
                'colspan'=>$colSpan,
                'control_type'=>$fClass,
                'control_params'=>$fParams,
            ];
        }//if($colSpan>1)
        return [
            'control_type'=>$fClass,
            'control_params'=>$fParams,
        ];
    }//END public static function PrepareField

    /**
     * Prepare add/edit form/sub-form page
     *
     * @param Params|null $params Parameters object
     * @param IEntity     $template
     * @param IEntity     $page
     * @param string      $tName
     * @param int|null    $instanceId
     * @param int|null    $subFormId
     * @param int|null    $itemId
     * @param int|null    $index
     * @return IControlBuilder|null Returns BasicForm configuration array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareFormBuilder(?Params $params,IEntity $template,IEntity $page,string $tName,?int $instanceId=NULL,?int $subFormId=NULL,?int $itemId=NULL,?int $index=NULL): ?IControlBuilder {
        // NApp::Dlog(['$template'=>$template,'$page'=>$page,'$tName'=>$tName,'$instanceId'=>$instanceId,'$subFormId'=>$subFormId,'$itemId'=>$itemId,'$index'=>$index],'PrepareFormPage');
        if($subFormId) {
            $fields=DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
                'template_id'=>$template->getProperty('id'),
                'instance_id'=>($instanceId ? $instanceId : NULL),
                'for_pindex'=>$page->getProperty('pindex',-1,'is_integer'),
                'item_id'=>$itemId,
                'for_index'=>(is_numeric($index) ? $index : NULL),
            ]);
        } else {
            $fields=DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
                'template_id'=>$template->getProperty('id'),
                'instance_id'=>($instanceId ? $instanceId : NULL),
                'for_pindex'=>$page->getProperty('pindex',-1,'is_integer'),
            ]);
        }//if($subFormId)
        // NApp::Dlog($fields,'$fields');
        $viewOnly=$params->safeGet('view_only',FALSE,'bool');
        $themeType=$template->getProperty('theme_type','','is_string');
        $controlsSize=$template->getProperty('controls_size','','is_string');
        $labelCols=$template->getProperty('label_cols',NULL,'is_not0_integer');

        $pIndex=$page->getProperty('pindex');
        $colsNo=$page->getProperty('colsno');
        $iPrefix=($instanceId ? $instanceId.'_' : '');
        $formIdPrefix='df_'.$tName.(is_integer($pIndex) ? '_'.$pIndex : '');

        $builder=new BasicFormBuilder([
            'control_class'=>'BasicForm',
            'tname'=>$tName,
            'tag_id'=>$formIdPrefix.'_form',
            'cols_no'=>$colsNo,
        ]);
        if(!$subFormId) {
            $builder->SetParam('response_target',$formIdPrefix.'_errors');
        }
        if(strlen($themeType)) {
            $builder->SetParam('theme_type',$themeType);
        }
        if(is_numeric($labelCols) && $labelCols>=1 && $labelCols<=12) {
            $builder->SetParam('label_cols',$labelCols);
        }
        if(strlen($controlsSize)) {
            $builder->SetParam('controls_size',$controlsSize);
        }
        if($viewOnly) {
            $builder->SetParam('disabled',TRUE);
        }

        $columnsToSkip=0;
        /** @var VirtualEntity $field */
        foreach($fields as $field) {
            $row=$field->getProperty('frow',0,'is_numeric');
            if(!$row) {
                continue;
            }
            if(!$builder->RowExists($row)) {
                $columnsToSkip=0;
            }
            $fClass=$field->getProperty('class','','is_string');
            // if($subFormId) { NApp::Dlog($field,$fClass); }
            $fParamsStr=$field->getProperty('params','','is_string');
            $fParams=strlen($fParamsStr) ? json_decode($fParamsStr,TRUE) : [];
            switch($fClass) {
                case 'FormTitle':
                    $builder->SetRow([
                        'separator'=>'title',
                        'value'=>$field->getProperty('label','','is_string'),
                        'class'=>get_array_value($fParams,'class','','is_string'),
                    ],$row);
                    $columnsToSkip=$colsNo - 1;
                    break;
                case 'FormSubTitle':
                    $builder->SetRow([
                        'separator'=>'subtitle',
                        'value'=>$field->getProperty('label','','is_string'),
                        'class'=>get_array_value($fParams,'class','','is_string'),
                    ],$row);
                    $columnsToSkip=$colsNo - 1;
                    break;
                case 'FormSeparator':
                    $builder->SetRow(['separator'=>'separator'],$row);
                    $columnsToSkip=$colsNo - 1;
                    break;
                case 'BasicForm':
                    $colSpan=$field->getProperty('colspan',0,'is_integer');
                    if($colSpan>1) {
                        $columnsToSkip=$colSpan - 1;
                    }
                    $fParams=['value'=>''];
                    $tagId=$iPrefix.$field->getProperty('cell','','is_string').'_'.$field->getProperty('name','','is_string').($index ? '_'.$index : '');
                    $fIType=$field->getProperty('itype',1,'is_not0_numeric');
                    $fIdSubForm=$field->getProperty('id_sub_form',-1,'is_not0_integer');
                    $itemId=$field->getProperty('id',NULL,'is_not0_integer');

                    if($fIType==2 && $instanceId) {
                        $fICount=$field->getProperty('icount',1,'is_not0_numeric');
                        // $fValue = $field->getProperty('ivalues',NULL,'is_string');
                    } else {
                        $fICount=1;
                        // $fValue = NULL;
                    }//if($fIType==2 && $instanceId)

                    $fSubFormVersion=NULL;
                    for($i=0; $i<$fICount; $i++) {
                        $subFormBuilder=static::PrepareForm($params,$template,$instanceId,$fIdSubForm,$itemId,$i,$fSubFormVersion);
                        if(!$subFormBuilder instanceof IControlBuilder) {
                            throw new AppException('Invalid DynamicForm sub-form configuration!');
                        }
                        $subFormBuilder->SetParam('sub_form_tag_id',$tagId.'-'.$i);
                        if($fIType==2) {
                            $subFormBuilder->SetParam('tags_ids_sufix','-'.$i);
                            $subFormBuilder->SetParam('tags_names_sufix','[]');
                            $subFormBuilder->SetParam('sub_form_class','clsRepeatableField');
                            $subFormBuilder->SetParam('sub_form_extra_tag_params','data-tid="'.$tagId.'" data-ti="'.$i.'"');
                        }//if($fIType==2)
                        // NApp::Dlog($subFormBuilder->GetConfig(),'$subFormBuilder->GetConfig()');
                        $basicForm=new BasicForm($subFormBuilder->GetConfig());
                        $fParams['value'].=$basicForm->Show();
                        // NApp::Dlog($fParams['value'],'fcontent');
                        if($i>0) {
                            $ctrl_ract=new Button(['value'=>Translate::GetButton('remove_field'),'icon'=>'fa fa-minus-circle','class'=>NApp::$theme->GetBtnWarningClass('clsRepeatableCtrlBtn'),'clear_base_class'=>TRUE,'onclick'=>"RemoveRepeatableForm(this,'{$tagId}-{$i}')"]);
                            $fParams['value'].=$ctrl_ract->Show();
                        }//if($i>0)
                    }//END for
                    if($fIType==2) {
                        $ctrl_ract=new Button(['value'=>Translate::GetButton('add_element'),'icon'=>'fa fa-plus-circle','class'=>NApp::$theme->GetBtnDefaultClass('clsRepeatCtrlBtn'),'onclick'=>"RepeatForm(this,'{$tagId}')",'extra_tag_params'=>'data-ract="'.Translate::GetButton('remove_element').'" data-ract-class="'.NApp::$theme->GetBtnWarningClass('clsRepeatableCtrlBtn').'"']);
                        $fParams['value'].=$ctrl_ract->Show();
                    }//if($fIType==2)
                    $itemUid=$field->getProperty('uid',NULL,'is_string');
                    $fParams['value'].=<<<HTML
                        <input type="hidden" id="{$instanceId}_{$itemUid}_version" name="{$itemUid}" class="postable" value="{$fSubFormVersion}">
HTML;
                    $builder->AddControl([
                        'width'=>$field->getProperty('width','','is_string'),
                        'control_type'=>'CustomControl',
                        'control_params'=>$fParams,
                    ],$row);
                    break;
                default:
                    if(!is_array($fParams) || !count($fParams)) {
                        if($columnsToSkip>0) {
                            $columnsToSkip--;
                            continue 2;
                        }//if($columnsToSkip>0)
                        $builder->AddControl([],$row);
                    } else {
                        $colSpan=$field->getProperty('colspan',0,'is_integer');
                        if($colSpan>1) {
                            $columnsToSkip=$colSpan - 1;
                        }
                        $fIType=$field->getProperty('itype',1,'is_not0_numeric');
                        if($fIType==2) {
                            if($instanceId) {
                                $fICount=$field->getProperty('icount',0,'is_numeric');
                                $fValue=$field->getProperty('ivalues',NULL,'is_string');
                            } else {
                                $fICount=0;
                                $fValue=NULL;
                            }//if($instanceId)
                            $builder->AddControl(static::PrepareField($field,$fParams,$fValue,$themeType,TRUE,$fICount),$row);
                        } else {
                            $fValue=NULL;
                            if($instanceId) {
                                $fValue=$field->getProperty('ivalues',NULL,'is_string');
                            }
                            $builder->AddControl(static::PrepareField($field,$fParams,$fValue,$themeType),$row);
                        }//if($fIType==2)
                    }//if(!is_array($fParams) || !count($fParams))
                    break;
            }//END switch
        }//END foreach
        return $builder;
    }//END public static function PrepareFormBuilder

    /**
     * Prepare TabControl tab config
     *
     * @param Params|null $params Parameters object
     * @param IEntity     $template
     * @param IEntity     $page
     * @param string      $tName
     * @param int|null    $instanceId
     * @param int|null    $subFormId
     * @param int|null    $itemId
     * @param int|null    $index
     * @return array|null Returns BasicForm configuration array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareFormTab(?Params $params,IEntity $template,IEntity $page,string $tName,?int $instanceId=NULL,?int $subFormId=NULL,?int $itemId=NULL,?int $index=NULL): ?array {
        // NApp::Dlog(['$template'=>$template,'$page'=>$page,'$tName'=>$tName,'$multiPage'=>$multiPage,'$instanceId'=>$instanceId,'$subFormId'=>$subFormId,'$itemId'=>$itemId,'$index'=>$index],'PrepareFormPage');
        $pIndex=$page->getProperty('pindex');
        $builder=static::PrepareFormBuilder($params,$template,$page,$tName,$instanceId,$subFormId,$itemId,$index);
        return [
            'type'=>'fixed',
            'uid'=>$tName.'-'.$pIndex,
            'name'=>$page->getProperty('tr_title'),
            'content_type'=>'control',
            'content'=>[
                'control_type'=>'BasicForm',
                'control_params'=>$builder->GetConfig(),
            ],
        ];
    }//END public static function PrepareFormTab

    /**
     * Prepare add/edit form/sub-form
     *
     * @param \NETopes\Core\App\Params|null $params Parameters object
     * @param IEntity|null                  $mTemplate
     * @param int|null                      $instanceId
     * @param int|null                      $subFormId
     * @param int|null                      $itemId
     * @param int|null                      $index
     * @param int|null                      $formVersion
     * @return IControlBuilder|null Returns BasicForm configuration array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareForm(?Params $params,?IEntity $mTemplate,?int $instanceId=NULL,?int $subFormId=NULL,?int $itemId=NULL,?int $index=NULL,?int &$formVersion=NULL): ?IControlBuilder {
        // NApp::Dlog(['$mTemplate'=>$mTemplate,'$instanceId'=>$instanceId,'$subFormId'=>$subFormId,'$itemId'=>$itemId,'$index'=>$index],'PrepareForm');
        $templateId=$mTemplate->getProperty('id',NULL,'is_integer');
        if(!$templateId) {
            return NULL;
        }
        // NApp::Dlog($subFormId,'$subFormId');
        if($subFormId) {
            $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
                'for_id'=>($instanceId ? NULL : $subFormId),
                'for_code'=>NULL,
                'instance_id'=>($instanceId ? $instanceId : NULL),
                'item_id'=>$itemId,
                'for_state'=>1,
            ]);
            // NApp::Dlog($template,'$template');
            $subFormId=$template->getProperty('id',NULL,'is_integer');
            // NApp::Dlog($itemId,'$itemId');
            // NApp::Dlog($subFormId,'$subFormId');
            if(!$subFormId || !$itemId) {
                return NULL;
            }
            $pages=DataProvider::Get('Plugins\DForms\Instances','GetPages',[
                'for_id'=>NULL,
                'instance_id'=>($instanceId ? $instanceId : NULL),
                'template_id'=>$templateId,
                'for_template_code'=>NULL,
                'for_pindex'=>NULL,
            ]);
        } else {
            $template=$mTemplate;
            $pages=DataProvider::Get('Plugins\DForms\Instances','GetPages',[
                'for_id'=>NULL,
                'instance_id'=>($instanceId ? $instanceId : NULL),
                'template_id'=>$templateId,
                'for_template_code'=>NULL,
                'for_pindex'=>NULL,
            ]);
            // NApp::Dlog($relations,'$relations');
        }//if($subFormId)
        // NApp::Dlog($pages,'$pages');
        if(!is_iterable($pages) || !count($pages)) {
            return NULL;
        }
        $formVersion=$template->getProperty('version',NULL,'is_integer');
        $iPrefix=($instanceId ? $instanceId.'_' : '');
        $tName=$iPrefix.$templateId.'_'.$subFormId;
        $renderType=get_array_value($template,'render_type',1,'is_integer');
        if(in_array($renderType,[21,22])) {
            $builder=new TabControlBuilder([
                'control_class'=>'TabControl',
                'tname'=>$tName,
                'tag_id'=>'df_'.$tName.'_form',
                'mode'=>($renderType==22 ? 'accordion' : 'tabs'),
            ]);
            foreach($pages as $page) {
                $builder->AddTab(static::PrepareFormTab($params,$template,$page,$tName,$instanceId,$subFormId,$itemId,$index));
            }//END foreach
        } else {
            $builder=static::PrepareFormBuilder($params,$template,$pages->first(),$tName,$instanceId,$subFormId,$itemId,$index);
        }//if(in_array($renderType,[21,22]))
        return $builder;
    }//END public static function PrepareForm

    /**
     * @param int                      $templateId
     * @param int|null                 $instanceId
     * @param \NETopes\Core\App\Params $inputParams
     * @return array
     * @throws \NETopes\Core\AppException
     */
    public static function GetRelationsData(int $templateId,?int $instanceId,Params $inputParams): array {
        if($instanceId) {
            $relations=DataProvider::Get('Plugins\DForms\Instances','GetRelations',['instance_id'=>$instanceId]);
        } else {
            $relations=DataProvider::Get('Plugins\DForms\Templates','GetRelations',['template_id'=>$templateId,'validated'=>1]);
        }//if($instanceId)
        $result=[];
        if(is_iterable($relations) && count($relations)) {
            /** @var IEntity $rel */
            foreach($relations as $rel) {
                if($rel->getProperty('rtype')!=30) {
                    continue;
                }
                $rKey=$rel->getProperty('key');
                $result[$rKey]=$inputParams->safeGet($rKey,NULL,'?isset');
            }//END foreach
        }//if(is_iterable($relations) && count($relations))
        return $result;
    }//END public static function GetRelationsData

    /**
     * @param array $relationsData
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareRelationsFormPart(array $relationsData): ?string {
        $result=NULL;
        foreach($relationsData as $k=>$v) {
            if(is_null($v)) {
                continue;
            }
            $rCtrl=new HiddenInput(['tag_id'=>$k.'_'.str_replace('.','_',microtime(TRUE)),'tag_name'=>$k,'postable'=>TRUE,'value'=>$v]);
            $result.=$rCtrl->Show();
        }//END foreach
        return $result;
    }//END public static function PrepareRelationsFormPart

    /**
     * @param array $relationsData
     * @return string
     */
    public static function GetAddActionRelationsParams(array $relationsData): ?string {
        $result=NULL;
        foreach($relationsData as $k=>$v) {
            $result.=" '{$k}': '{$v}',";
        }//END foreach
        return $result;
    }//END public static function GetAddActionRelationsParams

    /**
     * @param array $relationsData
     * @return string
     */
    public static function GetRelationsFilterParam(array $relationsData): string {
        $result=NULL;
        foreach($relationsData as $k=>$v) {
            $result.=(strlen($result) ? '~' : '').$k.'|'.$v;
        }//END foreach
        return $result;
    }//END public static function GetRelationsFilterParam

    /**
     * @param \NETopes\Plugins\DForms\Modules\Instances\Instances $module
     * @param array                                               $ctrlParams
     * @param int|null                                            $instanceId
     * @param string                                              $saveMethod
     * @param string                                              $responseTarget
     * @param string                                              $tName
     * @param string                                              $fTagId
     * @param array                                               $relationsData
     * @param string                                              $cModule
     * @param string                                              $cMethod
     * @param string                                              $cTarget
     * @param bool                                                $viewOnly
     * @param bool                                                $noRedirect
     * @param array                                               $customActions
     * @return array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareFormActions(Instances $module,array $ctrlParams,?int $instanceId,string $saveMethod,string $responseTarget,string $tName,string $fTagId,array $relationsData,string $cModule,string $cMethod,string $cTarget,bool $viewOnly,bool $noRedirect,array $customActions=[]): array {
        $actions=['container'=>[],'form'=>[],'after'=>[]];
        $actions[$module->actionsLocation]=$customActions;
        if($module->formPrintAction && $instanceId) {
            if(strlen($module->printUrl)) {
                $printUrl=NApp::$appBaseUrl.$module->printUrl;
            } else {
                $printUrl=NApp::Url()->GetNewUrl(['vpath'=>$module->printUrlVirtualPath,'language'=>NApp::GetLanguageCode()]);
            }
            $actions[$module->formAsModal ? 'form' : $module->printActionLocation ?? $module->actionsLocation][]=[
                'type'=>'Link',
                'params'=>[
                    'value'=>Translate::GetButton('print'),
                    'icon'=>'fa fa-file-pdf-o',
                    'class'=>NApp::$theme->GetBtnSuccessClass(),
                    'href'=>$printUrl,
                    'target'=>'_blank',
                    'type'=>'ehash',
                    'payload'=>[
                        'module'=>$module->name,
                        'method'=>'PrintInstance',
                        'params'=>['id'=>$instanceId],
                    ],
                ],
            ];
        }//if($module->formPrintAction && $instanceId)
        if(strlen($fTagId) && strlen($responseTarget)) {
            if(!$viewOnly) {
                $actions[$module->actionsLocation][]=[
                    'params'=>['tag_id'=>'df_'.$tName.'_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','class'=>NApp::$theme->GetBtnPrimaryClass(),'onclick'=>NApp::Ajax()->PrepareAjaxRequest([
                        'module'=>$module->name,
                        'method'=>$saveMethod,
                        'params'=>[
                            'id_template'=>$module->templateId,
                            'id'=>$instanceId,
                            'relations'=>"{nGet|df_{$tName}_relations:form}",
                            'data'=>"{nGet|{$fTagId}:form}",
                            'no_redirect'=>(int)$noRedirect,
                            'is_modal'=>$module->formAsModal,
                            'form_id'=>$fTagId,
                            'c_module'=>$cModule,
                            'c_method'=>$cMethod,
                            'c_target'=>$cTarget,
                            'c_custom_actions'=>"{nGet|df_{$tName}_custom_actions:innerHTML}",
                        ],
                    ],['target_id'=>$responseTarget])],
                ];
            }
            if($module->formAsModal && strlen($module->backActionLocation)) {
                $actions['form'][]=[
                    'type'=>'CloseModal',
                    'params'=>['value'=>Translate::GetButton('cancel'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-ban'],
                ];
            } elseif(!$module->formAsModal && strlen($module->backActionLocation)) {
                $listingAddActionRelations=InstancesHelpers::GetAddActionRelationsParams($relationsData);
                $actions[$module->backActionLocation][]=[
                    'params'=>['value'=>Translate::GetButton('back'),'icon'=>'fa fa-chevron-left','class'=>NApp::$theme->GetBtnDefaultClass(),'onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$cModule}', 'method': '{$cMethod}', 'params': { 'id_template': {$module->templateId}, 'id': '{$instanceId}', {$listingAddActionRelations} 'target': '{$cTarget}' } }",$cTarget)],
                ];
            }//if($isModal && strlen($backAction))
        }//if(strlen($fTagId) && strlen($fResponseTarget))
        return $actions;
    }//END public static function PrepareFormActions

    /**
     * @param mixed       $value
     * @param mixed|null  $returnValue
     * @param string|null $dataType
     * @param string|null $key
     * @return bool
     * @throws \NETopes\Core\AppException
     */
    public static function GetValidatedValue($value,&$returnValue=NULL,?string $dataType=NULL,string $key=NULL): bool {
        if(is_array($value) && strlen($key)) {
            $lValue=get_array_param($value,$key,NULL,'isset');
        } else {
            $lValue=$value;
        }//if(is_array($value) && strlen($key))
        switch($dataType) {
            case 'integer':
                $returnValue=Validator::ValidateValue($lValue,NULL,'?is_integer');
                return !is_integer($returnValue);
            case 'numeric':
                $returnValue=Validator::ValidateValue($lValue,NULL,'?is_numeric');
                return !is_numeric($returnValue);
            case 'string':
            default:
                $returnValue=Validator::ValidateValue($lValue,NULL,'?is_string');
                return !strlen($returnValue);
        }//END switch
    }//END public static function GetValidatedValue

    /**
     * @param int                      $templateId
     * @param \NETopes\Core\App\Params $params
     * @return \NETopes\Core\Data\VirtualEntity
     * @throws \NETopes\Core\AppException
     */
    public static function GetSingletonInstance(int $templateId,Params $params): VirtualEntity {
        // NApp::Dlog($params->toArray(),'GetSingletonInstance::$params');
        $relations=DataProvider::Get('Plugins\DForms\Templates','GetRelations',['template_id'=>$templateId,'validated'=>1,'for_utype'=>Templates::RELATION_UTYPE_UID]);
        if(!($relations instanceof DataSet) || !count($relations)) {
            return new VirtualEntity();
        }
        $instanceUid=static::GetInstanceUid($templateId,$params,$relations);
        if(!strlen($instanceUid)) {
            return new VirtualEntity();
        }
        $instance=DataProvider::Get('Plugins\DForms\Instances','GetSingletonInstance',[
            'for_uid'=>$instanceUid,
            'template_id'=>$templateId,
        ]);
        if($instance instanceof VirtualEntity) {
            return $instance;
        }
        return new VirtualEntity();
    }//END public static function GetSingletonInstance

    /**
     * @param int                      $templateId
     * @param int|null                 $instanceId
     * @param \NETopes\Core\App\Params $params
     * @return bool
     * @throws \NETopes\Core\AppException
     */
    public static function ValidateRelations(int $templateId,?int $instanceId,Params &$params): bool {
        if($instanceId) {
            $relations=DataProvider::Get('Plugins\DForms\Instances','GetRelations',['instance_id'=>$instanceId]);
        } else {
            $relations=DataProvider::Get('Plugins\DForms\Templates','GetRelations',['template_id'=>$templateId,'validated'=>1]);
        }//if($instanceId)
        $relationsData=$params->safeGet('relations',[],'is_array');
        $fieldsData=$params->safeGet('data',[],'is_array');
        // NApp::Dlog($relations,'$relations');
        // NApp::Dlog($relationsData,'$relationsData');
        $errors=[];
        /** @var VirtualEntity $rel */
        foreach($relations as $rel) {
            $relKey=$rel->getProperty('key',NULL,'is_string');
            $dType=$rel->getProperty('dtype','','is_string');
            $relValue=NULL;
            switch($rel->getProperty('rtype',0,'is_integer')) {
                case 10: // USER INPUT (AS FORM ELEMENT)
                    static::GetValidatedValue($fieldsData,$relValue,$dType,$relKey);
                    break;
                case 20: // AUTO(FROM SESSION)
                    static::GetValidatedValue(NApp::GetParam($relKey),$relValue,$dType);
                    break;
                case 21: // AUTO(FROM PAGE SESSION)
                    static::GetValidatedValue(NApp::GetPageParam($relKey),$relValue,$dType);
                    break;
                case 30: // PROGRAMMATICALLY (METHOD INPUT PARAMETER)
                    static::GetValidatedValue($relationsData,$relValue,$dType,$relKey);
                    break;
            }//END switch
            if($rel->getProperty('required',FALSE,'bool') && !$relValue) {
                $errors[]=[
                    'id'=>$rel->getProperty('id',NULL,'is_integer'),
                    'key'=>$relKey,
                    'name'=>$rel->getProperty('name','','is_string'),
                    'type'=>'required_relation',
                ];
                continue;
            }
            $rel->set('ivalue',($dType=='integer' ? $relValue : NULL));
            $rel->set('svalue',($dType!='integer' ? $relValue : NULL));
        }//END foreach
        // NApp::Dlog($relations,'$relations');
        $params->set('df_relations_values',$relations);
        $params->set('df_relations_errors',$errors);
        return !count($errors);
    }//END public static function ValidateRelations

    /**
     * @param int                      $templateId
     * @param int|null                 $instanceId
     * @param \NETopes\Core\App\Params $params
     * @return bool
     * @throws \NETopes\Core\AppException
     */
    public static function ValidateFields(int $templateId,?int $instanceId,Params &$params): bool {
        $fieldsData=$params->safeGet('data',[],'is_array');
        $fields=DataProvider::Get('Plugins\DForms\Instances','GetFields',['template_id'=>$templateId]);
        // NApp::Dlog($fields,'$fields');
        $errors=[];
        /** @var VirtualEntity $field */
        foreach($fields as $k=>$field) {
            $fieldUid=$field->getProperty('uid',NULL,'is_string');
            $fieldName=$field->getProperty('name','N/A','is_string');
            if(!strlen($fieldUid)) {
                $errors[]=[
                    'name'=>$fieldName,
                    'label'=>$field->getProperty('label','','is_string'),
                    'type'=>'invalid_uid',
                ];
                continue;
            }//if(!strlen($fieldUid))
            if($field->getProperty('itype',0,'is_integer')==2 || $field->getProperty('parent_itype',0,'is_integer')==2) {
                $fieldsValues=get_array_value($fieldsData,$fieldUid,NULL,'is_array');
                if(!is_array($fieldsValues) || !count($fieldsValues)) {
                    if($field->getProperty('required',FALSE,'bool')) {
                        $errors[]=[
                            'uid'=>$fieldUid,
                            'name'=>$fieldName,
                            'label'=>$field->getProperty('label','','is_string'),
                            'type'=>'required_field',
                        ];
                        continue;
                    }
                    $fields->set($k,$field->set('value',NULL));
                } else {
                    $fieldValue=[];
                    foreach($fieldsValues as $i=>$fv) {
                        switch($field->getProperty('data_type',NULL,'is_string')) {
                            case 'numeric':
                                $fieldItemValue=Validator::ValidateValue($fv,NULL,'is_numeric');
                                if($field->getProperty('required',FALSE,'bool') && !is_numeric($fieldItemValue)) {
                                    $errors[]=[
                                        'uid'=>$fieldUid,
                                        'name'=>$fieldName,
                                        'label'=>$field->getProperty('label','','is_string'),
                                        'type'=>'required_field',
                                        'index'=>$i,
                                    ];
                                    continue 2;
                                }
                                $fieldValue[$i]=$fieldItemValue;
                                break;
                            case 'string':
                            default:
                                $fieldItemValue=Validator::ValidateValue($fv,NULL,'is_string');
                                if($field->getProperty('required',FALSE,'bool') && !strlen($fieldItemValue)) {
                                    $errors[]=[
                                        'uid'=>$fieldUid,
                                        'name'=>$fieldName,
                                        'label'=>$field->getProperty('label','','is_string'),
                                        'type'=>'required_field',
                                        'index'=>$i,
                                    ];
                                    continue 2;
                                }
                                $fieldValue[$i]=$fieldItemValue;
                                break;
                        }//END switch
                    }//END foreach
                    if(!count($errors)) {
                        $field->set('value',$fieldValue);
                    }
                }//if(!is_array($fieldsValues) || !count($fieldsValues))
            } else {
                switch($field->getProperty('data_type',NULL,'is_string')) {
                    case 'numeric':
                        $fieldValue=get_array_value($fieldsData,$fieldUid,NULL,'is_numeric');
                        if($field->getProperty('required',FALSE,'bool') && !is_numeric($fieldValue)) {
                            $errors[]=[
                                'uid'=>$fieldUid,
                                'name'=>$fieldName,
                                'label'=>$field->getProperty('label','','is_string'),
                                'type'=>'required_field',
                            ];
                            continue 2;
                        }
                        break;
                    case 'string':
                    default:
                        $fieldValue=get_array_value($fieldsData,$fieldUid,NULL,'is_string');
                        if($field->getProperty('required',FALSE,'bool') && !strlen($fieldValue)) {
                            $errors[]=[
                                'uid'=>$fieldUid,
                                'name'=>$fieldName,
                                'label'=>$field->getProperty('label','','is_string'),
                                'type'=>'required_field',
                            ];
                            continue 2;
                        }
                        break;
                }//END switch
                $field->set('value',$fieldValue);
            }//if($field->getProperty('itype',0,'is_integer')==2 || $field->getProperty('parent_itype',0,'is_integer')==2)
        }//END foreach
        $params->set('df_fields_values',$fields);
        $params->set('df_fields_errors',$errors);
        return !count($errors);
    }//END public static function ValidateFields

    /**
     * @param \NETopes\Core\App\Params $params
     * @return bool
     * @throws \NETopes\Core\AppException
     */
    public static function ValidateSaveParams(Params &$params): bool {
        $templateId=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $instanceId=$params->safeGet('id',NULL,'?is_integer');
        if(!static::ValidateRelations($templateId,$instanceId,$params)) {
            return FALSE;
        }
        if(!static::ValidateFields($templateId,$instanceId,$params)) {
            return FALSE;
        }
        return TRUE;
    }//END public static function ValidateSaveParams

    /**
     * @param \NETopes\Core\App\Params $params
     * @param Instances                $module
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public static function RedirectAfterAction(Params $params,Instances $module): void {
        if($params->safeGet('no_redirect',FALSE,'bool')) {
            return;
        }
        if($params->safeGet('form_as_modal',$module->formAsModal,'is_numeric')==1) {
            /** @var \NETopes\Core\App\Module $module */
            $module->CloseForm();
        }
        $cModule=$params->safeGet('c_module',$module->name,'is_notempty_string');
        $cMethod=$params->safeGet('c_method','Listing','is_notempty_string');
        $cTarget=$params->safeGet('c_target','main-content','is_notempty_string');
        $cCustomActions=$params->safeGet('c_custom_actions','','is_string');
        if(strlen($cCustomActions)) {
            try {
                $customActions=json_decode(str_replace('``','"',$cCustomActions),TRUE);
            } catch(Exception $e) {
                $customActions=NULL;
            }
        } else {
            $customActions=NULL;
        }
        $redirectParams=['id_template'=>$module->templateId,'id'=>$params->safeGet('id',NULL,'is_integer'),'target'=>$cTarget,'custom_actions'=>$customActions];
        $relations=$params->safeGet('relations',[],'is_array');
        ModulesProvider::Exec($cModule,$cMethod,array_merge($redirectParams,$relations),$cTarget);
    }//END public static function RedirectAfterAction
}//END class InstancesHelpers