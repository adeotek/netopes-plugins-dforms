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
use NETopes\Core\AppException;
use NETopes\Core\Data\DataProvider;
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
     * @param int         $instanceId
     * @param int         $templateId
     * @param string|null $printTemplate
     */
    public function __construct(int $instanceId,int $templateId,?string $printTemplate=NULL) {
        $this->instanceId=$instanceId;
        $this->templateId=$templateId;
        $this->content=$printTemplate;
        //$this->pageOrientation
    }//END public function __construct

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

        $parameters=[];
        /** @var \NETopes\Core\Data\IEntity $field */
        foreach($fields as $field) {
            $parameters[]=[$field->getProperty('name',NULL,'is_string')=>NULL];
        }//END foreach

        $this->content=$this->ReplacePlaceholders($this->content,$parameters);
        return $this->content;
    }//END public function PrepareContent
}//END class class InstancesPrintContentBuilder