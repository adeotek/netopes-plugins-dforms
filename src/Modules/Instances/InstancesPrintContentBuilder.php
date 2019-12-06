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
use Exception;
use NETopes\Core\App\Params;
use NETopes\Core\AppException;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\DataSet;
use NETopes\Core\Data\IEntity;
use NETopes\Core\Data\TPlaceholdersManipulation;
use Translate;

/**
 * Class InstancesPdfBuilder
 *
 * @package NETopes\Plugins\DForms\Modules\Instances
 */
class InstancesPrintContentBuilder {
    use TPlaceholdersManipulation;

    /**
     * @var int
     */
    public $instanceId;

    /**
     * @var int
     */
    public $templateId;

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
        $this->instanceId=$instance->getProperty('id',NULL,'?is_string');
        $this->templateId=$instance->getProperty('id_template',NULL,'?is_string');
        $this->content=$instance->getProperty('print_template','','is_string');
        $this->pageOrientation=$instance->getProperty('print_page_orientation','L','is_notempty_string');
    }//END public function __construct

    /**
     * @param \NETopes\Core\Data\IEntity $field
     * @param \NETopes\Core\App\Params   $params
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    protected function GetFieldValue(IEntity $field,Params $params): ?string {
        $value=$field->getProperty('ivalues',NULL,'isset');
        switch($field->getProperty('class',NULL,'is_string')) {
            case 'CheckBox':
                $result=$value ? Translate::GetLabel('yes') : Translate::GetLabel('no');
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
                break;
            default:
                $result=is_string($value) ? $value : NULL;
                break;
        }//END switch
        return $result;
    }//END protected function GetFieldValue

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
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    protected function PrepareFormContent(?string $content,?int $subFormId=NULL,?int $itemId=NULL): ?string {
        // NApp::Dlog(['$content'=>$content,'$subFormId'=>$subFormId,'$itemId'=>$itemId],'PrepareContent');
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
                $content=$this->PrepareFormContent($content,$fieldSubFormId,$fieldItemId);
            } else {
                $fieldValue=$this->GetFieldValue($field,$fieldParams);
                if($fieldParams->safeGet('labels_source','','is_string')==='form') {
                    $parameters[$fieldName]=$this->GetFieldHtml($field,$fieldParams,$fieldValue);
                } else {
                    $parameters[$fieldName]=$fieldValue;
                }
            }//if($fieldClass==='BasicForm')
        }//END foreach
        return $this->ReplacePlaceholders($content,$parameters,is_null($subFormId));
    }//END protected function PrepareFormContent

    /**
     * Prepare HTML content
     *
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function PrepareContent(): void {
        if(!is_string($this->content)) {
            throw new AppException('Invalid instance print HTML content!');
        }
        $this->content=$this->PrepareFormContent($this->content);
    }//END public function PrepareContent
}//END class class InstancesPrintContentBuilder