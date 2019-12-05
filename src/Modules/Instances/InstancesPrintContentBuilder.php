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
use NETopes\Core\Data\IEntity;
use NETopes\Core\Data\TPlaceholdersManipulation;

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
     * @param \NETopes\Core\App\Params $params
     * @param mixed|null               $value
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    public function GetFieldHtml(Params $params,$value=NULL): ?string {
        $sValue=!is_string($value) ? NULL : $value;
        if($params->safeGet('hide_empty',FALSE,'bool') && !strlen($sValue)) {
            return NULL;
        }
        $label=$params->safeGet('label','','is_string');
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
    }//END public function GetFieldHtml

    /**
     * Prepare HTML content
     *
     * @param int|null $subFormId
     * @param int|null $itemId
     * @return string|null Returns HTML string for print
     * @throws \NETopes\Core\AppException
     */
    public function PrepareContent(?int $subFormId=NULL,?int $itemId=NULL): ?string {
        // NApp::Dlog(['$this->content'=>$this->content,'$subFormId'=>$subFormId,'$itemId'=>$itemId],'PrepareContent');
        if(!is_string($this->content)) {
            throw new AppException('Invalid instance print HTML content!');
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
            $fieldParamsString=$field->getProperty('class',NULL,'is_string');
            if(!strlen($fieldName) || !strlen($fieldClass) || in_array($fieldClass,['FormSeparator','FormTitle','FormSubTitle']) || !strlen($fieldParamsString)) {
                continue;
            }
            try {
                $fieldParams=new Params(json_decode($fieldParamsString,TRUE));
            } catch(Exception $je) {
                continue;
            }//END try
            if($fieldClass==='BasicForm') {
                $fieldValue=NULL;
            } else {
                $fieldValue=NULL;
                if($fieldParams->safeGet('labels_source','','is_string')==='form') {
                    $parameters[]=[$fieldName=>$this->GetFieldHtml($fieldParams,$fieldValue)];
                } else {
                    $parameters[]=[$fieldName=>$fieldValue];
                }
            }//if($fieldClass==='BasicForm')
        }//END foreach
        $this->content=$this->ReplacePlaceholders($this->content,$parameters);
        return $this->content;
    }//END public function PrepareContent
}//END class class InstancesPrintContentBuilder