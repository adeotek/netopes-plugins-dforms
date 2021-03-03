<?php
/**
 * InstancesPdfBuilder class file
 *
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Instances;
use DateTime;
use Exception;
use NApp;
use NETopes\Core\App\Params;
use NETopes\Core\AppException;
use NETopes\Core\Controls\NumericTextBox;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\DataSet;
use NETopes\Core\Data\IEntity;
use NETopes\Core\Data\TPlaceholdersManipulation;
use NETopes\Core\Data\VirtualEntity;
use Translate;

/**
 * Class InstancesPdfBuilder
 *
 * @package NETopes\Plugins\DForms\Modules\Instances
 */
class InstancesPrintContentBuilder {
    use TPlaceholdersManipulation;

    /**
     * @var IEntity
     */
    public $instance;

    /**
     * @var int
     */
    public $instanceId;

    /**
     * @var int
     */
    public $templateId;

    /**
     * @var DataSet|array|null
     */
    public $relations;

    /**
     * @var string|null Document title
     */
    public $documentTitle=NULL;

    /**
     * @var string Document orientation
     */
    public $pageOrientation='P';

    /**
     * @var string|null HTML content
     */
    public $content=NULL;

    /**
     * InstancesPdfBuilder constructor.
     *
     * @param \NETopes\Core\Data\IEntity $instance
     * @throws \NETopes\Core\AppException
     */
    public function __construct(IEntity $instance) {
        $this->instance=$instance;
        $this->instanceId=$instance->getProperty('id',NULL,'?is_string');
        $this->templateId=$instance->getProperty('id_template',NULL,'?is_string');
        $this->content=$instance->getProperty('print_template','','is_string');
        $this->pageOrientation=$instance->getProperty('print_page_orientation','P','is_notempty_string');
    }//END public function __construct

    /**
     * @param \NETopes\Core\Data\IEntity $field
     * @param \NETopes\Core\App\Params   $params
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    protected function GetFieldValue(IEntity $field,Params $params): ?string {
        $value=$field->getProperty('ivalues',NULL,'isset');
        $emptyValue=$params->safeGet('empty_value',NULL,'is_string');
        switch($field->getProperty('class',NULL,'is_string')) {
            case 'CheckBox':
                if($params->safeGet('display_as_checkbox',FALSE,'bool')) {
                    // $result='<div style="display: block;">';
                    $result='<input type="checkbox"'.($value ? ' checked="checked"' : '').'>';
                    // $result.='</div>';
                } else {
                    $result=$value ? Translate::GetLabel('yes') : Translate::GetLabel('no');
                }
                break;
            case 'NumericTextBox':
                $result=NumericTextBox::FormatValue($value,$params->safeGet('number_format','','is_string'),$params->safeGet('allow_null',FALSE,'bool'));
                $result=strlen($result) ? $result : $emptyValue;
                break;
            case 'GroupCheckBox':
                $displayAsCheckbox=$params->safeGet('display_as_checkbox',FALSE,'bool');
                $valuesListId=$field->getProperty('id_values_list',0,'is_numeric');
                if($valuesListId>0) {
                    $result=NULL;
                    if($params->safeGet('show_all_options',FALSE,'bool')) {
                        $listValues=DataProvider::Get('Plugins\DForms\ValuesLists','GetSelectedValues',[
                            'instance_id'=>$this->instanceId,
                            'field_id'=>$field->getProperty('id',NULL,'is_integer'),
                            'list_id'=>$valuesListId,
                            'all_list_values'=>1,
                        ]);
                        if($listValues instanceof DataSet) {
                            $result=$displayAsCheckbox ? $this->GetFieldValuesAsCheckboxList($listValues) : $this->GetFieldValuesAsEnum($listValues);
                        }
                    } else {
                        $listValues=DataProvider::Get('Plugins\DForms\ValuesLists','GetSelectedValues',[
                            'instance_id'=>$this->instanceId,
                            'field_id'=>$field->getProperty('id',NULL,'is_integer'),
                            'list_id'=>$valuesListId,
                        ]);
                        if($listValues instanceof DataSet) {
                            $result=$displayAsCheckbox ? $this->GetFieldValuesAsCheckboxList($listValues) : $this->GetFieldValuesAsEnum($listValues);
                        }
                    }//if($params->safeGet('show_all_options',FALSE,'bool'))
                } else {
                    $result=$value;
                }//if($valuesListId>0)
                $result=strlen($result) ? $result : $emptyValue;
                break;
            case 'SmartComboBox':
                $valuesListId=$field->getProperty('id_values_list',0,'is_numeric');
                if($valuesListId>0) {
                    $result=NULL;
                    $listValues=DataProvider::Get('Plugins\DForms\ValuesLists','GetSelectedValues',[
                        'instance_id'=>$this->instanceId,
                        'field_id'=>$field->getProperty('id',NULL,'is_integer'),
                        'list_id'=>$valuesListId,
                    ]);
                    if($listValues instanceof DataSet) {
                        /** @var IEntity $listValue */
                        foreach($listValues as $listValue) {
                            $result.=(strlen($result) ? '; ' : '').$listValue->getProperty('name',NULL,'is_string');
                        }//END foreach
                    }
                } else {
                    $result=$value;
                }//if($valuesListId>0)
                $result=strlen($result) ? $result : $emptyValue;
                break;
            case 'EditBox':
            case 'CkEditor':
                $result=is_string($value) && strlen($value) ? nl2br($value) : $emptyValue;
                break;
            default:
                $result=is_string($value) && strlen($value) ? $value : $emptyValue;
                break;
        }//END switch
        return $result;
    }//END protected function GetFieldValue

    /**
     * @param \NETopes\Core\Data\IEntity $field
     * @param string                     $valueField
     * @param string                     $nameField
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    protected function GetFieldValueAsCheckboxList(IEntity $field,string $valueField='is_selected',string $nameField='name'): ?string {
        $result='<div style="display: block;">';
        $result.='<input type="checkbox"'.($field->getProperty($valueField,0,'is_integer')==1 ? ' checked="checked"' : '').'>&nbsp;&nbsp;&nbsp;'.$field->getProperty($nameField,NULL,'is_string');
        $result.='</div>';
        return $result;
    }//END protected function GetFieldValueAsCheckboxList

    /**
     * @param \NETopes\Core\Data\DataSet $listValues
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    protected function GetFieldValuesAsCheckboxList(DataSet $listValues): ?string {
        $result='';
        /** @var IEntity $listValue */
        foreach($listValues as $listValue) {
            $result.=$this->GetFieldValueAsCheckboxList($listValue);
        }//END foreach
        return $result;
    }//END protected function GetFieldValuesAsCheckboxList

    /**
     * @param \NETopes\Core\Data\DataSet $listValues
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    protected function GetFieldValuesAsEnum(DataSet $listValues): ?string {
        $result='';
        /** @var IEntity $listValue */
        foreach($listValues as $listValue) {
            $result.=(strlen($result) ? '; ' : '').$listValue->getProperty('name',NULL,'is_string');
        }//END foreach
        return $result;
    }//END protected function GetFieldValuesAsEnum

    /**
     * @param \NETopes\Core\Data\IEntity $field
     * @param \NETopes\Core\App\Params   $params
     * @param mixed|null                 $value
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    protected function GetFieldHtml(IEntity $field,Params $params,$value=NULL): ?string {
        $sValue=!is_string($value) ? NULL : $value;
        if($params->safeGet('hide_empty',FALSE,'bool') && !strlen($sValue)) {
            return NULL;
        }
        $label=$field->getProperty('label','','is_string');
        if(strlen($label)) {
            $label='<td><label>'.$label.'</label>:&nbsp;</td>';
        }
        return <<<HTML
<table>
    <tr>
        {$label}
        <td>{$sValue}</td>
    </tr>
</table>
HTML;
    }//END protected function GetFieldHtml

    /**
     * @param string|null $content
     * @param int|null    $subFormId
     * @param int|null    $itemId
     * @param string|null $formTag
     * @param bool        $preserveUnusedPlaceholders
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    protected function PrepareFormContent(?string $content,?int $subFormId=NULL,?int $itemId=NULL,?string $formTag=NULL,bool $preserveUnusedPlaceholders=FALSE): ?string {
        // NApp::Dlog(['$content'=>$content,'$subFormId'=>$subFormId,'$itemId'=>$itemId],'PrepareContent');
        if($subFormId && strlen($formTag)) {
            $formTemplate=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
                'for_id'=>$subFormId,
                'for_code'=>NULL,
                'instance_id'=>$this->instanceId,
                'item_id'=>$itemId,
                'for_state'=>1,
            ]);
            if(!$formTemplate instanceof IEntity) {
                throw new AppException('Invalid sub-form template object!');
            }
            $content.=$formTemplate->getProperty('print_template','','is_string');
        }
        if($subFormId) {
            $fields=DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
                'template_id'=>$this->templateId,
                'instance_id'=>$this->instanceId,
                'for_pindex'=>NULL,
                'item_id'=>$itemId,
                'for_index'=>NULL,
            ]);
        } else {
            $fields=DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
                'template_id'=>$this->templateId,
                'instance_id'=>$this->instanceId,
                'for_pindex'=>NULL,
            ]);
        }//if($subFormId)
        // vprint($fields);
        $parameters=[];
        /** @var \NETopes\Core\Data\IEntity $field */
        foreach($fields as $field) {
            $fieldName=$field->getProperty('name',NULL,'is_string');
            $fieldClass=$field->getProperty('class',NULL,'is_string');
            $fieldParamsString=$field->getProperty('params',NULL,'is_string');
            if(!strlen($fieldName) || !strlen($fieldClass) || in_array($fieldClass,['FormSeparator','FormTitle','FormSubTitle']) || !strlen($fieldParamsString)) {
                continue;
            }
            try {
                $fieldParams=new Params(json_decode($fieldParamsString,TRUE));
            } catch(Exception $je) {
                continue;
            }//END try
            if($fieldClass==='BasicForm') {
                $fieldSubFormId=$field->getProperty('id_sub_form',NULL,'is_integer');
                $fieldItemId=$field->getProperty('id',NULL,'is_not0_integer');
                if($fieldParams->safeGet('print_mode','','is_string')==='form_placeholder') {
                    $formTag=$field->getProperty('name','','is_string');
                    $formContent=$this->PrepareFormContent(NULL,$fieldSubFormId,$fieldItemId,$formTag);
                    $parameters[$formTag]=$formContent;
                } else {
                    $content=$this->PrepareFormContent($content,$fieldSubFormId,$fieldItemId);
                }
            } else {
                $fieldValue=$this->GetFieldValue($field,$fieldParams);
                if($fieldParams->safeGet('labels_source','','is_string')==='form') {
                    $parameters[$fieldName]=$this->GetFieldHtml($field,$fieldParams,$fieldValue);
                } else {
                    $parameters[$fieldName]=$fieldValue;
                }
            }//if($fieldClass==='BasicForm')
        }//END foreach
        return $this->ReplacePlaceholders($content,$parameters,is_null($subFormId) || $preserveUnusedPlaceholders);
    }//END protected function PrepareFormContent

    /**
     * @param string $content
     * @return string
     * @throws \NETopes\Core\AppException
     */
    protected function PrepareFormRelations(string $content): string {
        $this->relations=DataProvider::Get('Plugins\DForms\Instances','GetRelations',['instance_id'=>$this->instanceId]);
        $relationsData=[];
        /** @var IEntity $relation */
        foreach($this->relations as $relation) {
            $key=$relation->getProperty('key',NULL,'is_string');
            $value=$relation->getProperty('svalue',$relation->getProperty('ivalue',NULL,'is_string'),'is_integer');
            $relationsData[$key]=$value;
            $displayFields=$relation->getProperty('display_fields',NULL,'is_string');
            $displayFieldsValues=explode('|',$relation->getProperty('display_fields_value',NULL,'is_string'));
            foreach(explode(';',$displayFields) as $i=>$dField) {
                $relationsData[$key.'-'.strtolower(trim($dField,' ]['))]=get_array_value($displayFieldsValues,$i,NULL,'is_string');
            }//END foreach
        }//END foreach
        return $this->ReplacePlaceholders($content,$relationsData,FALSE);
    }//END protected function PrepareFormRelations

    /**
     * @param string $content
     * @return string
     * @throws \NETopes\Core\AppException
     */
    protected function PrepareFormMetaData(string $content): string {
        $printedAt=new DateTime();
        $createdAt=$this->instance->getProperty('created_at',NULL,'is_datetime');
        $parameters=[
            'created_by_full_name'=>$this->instance->getProperty('created_by_full_name'),
            'created_at_date'=>($createdAt ? $createdAt->format(NApp::GetDateFormat(TRUE)) : NULL),
            'created_at_time'=>($createdAt ? $createdAt->format(NApp::GetTimeFormat(TRUE)) : NULL),
            'printed_by_full_name'=>NApp::GetParam('user_full_name'),
            'printed_at_date'=>($printedAt ? $printedAt->format(NApp::GetDateFormat(TRUE)) : NULL),
            'printed_at_time'=>($printedAt ? $printedAt->format(NApp::GetTimeFormat(TRUE)) : NULL),
        ];
        return $this->ReplacePlaceholders($content,$parameters,FALSE);
    }//END protected function PrepareFormMetaData

    /**
     * Prepare HTML content
     *
     * @param bool $preserveUnusedPlaceholders
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function PrepareContent(bool $preserveUnusedPlaceholders=FALSE): void {
        if(!is_string($this->content)) {
            throw new AppException('Invalid instance print HTML content!');
        }
        $this->content=$this->PrepareFormRelations($this->content);
        $this->content=$this->PrepareFormMetaData($this->content);
        $this->content=$this->PrepareFormContent($this->content,NULL,NULL,NULL,$preserveUnusedPlaceholders);
    }//END public function PrepareContent
}//END class class InstancesPrintContentBuilder