<?php
/**
 * Arrays data source file
 * Contains calls for arrays data.
 *
 * @package    NETopes\Plugins\DataSources\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\DataSources;
use NETopes\Core\DataSources\OfflineBase;
use Translate;

abstract class BaseDFormsOffline extends OfflineBase {
    /**
     * description
     *
     * @param array $params Parameters array
     * @param array $extra_params
     * @return array
     * @access public
     * @throws \NETopes\Core\AppException
     */
    public function GetDynamicFormsTemplatesFTypes($params=[],$extra_params=[]) {
        $langCode=get_array_value($params,'lang_code','','is_string');
        $result=[
            ['id'=>1,'name'=>Translate::GetLabel('standard',$langCode).' ('.Translate::GetLabel('multi-instance',$langCode).')'],
            ['id'=>2,'name'=>Translate::GetLabel('single-instance',$langCode)],
        ];
        return $result;
    }//END public function GetDynamicFormsTemplatesFTypes

    /**
     * description
     *
     * @param array $params Parameters array
     * @param array $extra_params
     * @return array
     * @throws \NETopes\Core\AppException
     * @access public
     */
    public function GetDynamicFormsFieldsITypes($params=[],$extra_params=[]) {
        $langCode=get_array_value($params,'lang_code','','is_string');
        $result=[
            ['id'=>1,'name'=>Translate::GetLabel('standard',$langCode).' ('.Translate::GetLabel('single_field',$langCode).')'],
            ['id'=>2,'name'=>Translate::GetLabel('repeatable',$langCode)],
        ];
        return $result;
    }//END public function GetDynamicFormsFieldsITypes

    /**
     * description
     *
     * @param array $params Parameters array
     * @param array $extra_params
     * @return array
     * @throws \NETopes\Core\AppException
     * @access public
     */
    public function GetDynamicFormsRelationsRTypes($params=[],$extra_params=[]) {
        $langCode=get_array_value($params,'lang_code','','is_string');
        $result=[
            ['id'=>10,'name'=>Translate::GetLabel('user_input',$langCode).' ('.Translate::GetLabel('form_element',$langCode).')'],
            ['id'=>20,'name'=>Translate::GetLabel('auto',$langCode).' ('.Translate::GetLabel('from_session',$langCode).')'],
            ['id'=>21,'name'=>Translate::GetLabel('auto',$langCode).' ('.Translate::GetLabel('from_page_session',$langCode).')'],
            ['id'=>30,'name'=>Translate::GetLabel('programmatically',$langCode).' ('.Translate::GetLabel('method_input_parameter',$langCode).')'],
        ];
        return $result;
    }//END public function GetDynamicFormsRelationsRTypes

    /**
     * description
     *
     * @param array $params Parameters array
     * @param array $extra_params
     * @return array
     * @throws \NETopes\Core\AppException
     * @access public
     */
    public function GetDynamicFormsRelationsUTypes($params=[],$extra_params=[]) {
        $langCode=get_array_value($params,'lang_code','','is_string');
        $result=[
            ['id'=>0,'name'=>Translate::GetLabel('standard',$langCode)],
            ['id'=>1,'name'=>Translate::GetLabel('category',$langCode)],
            ['id'=>10,'name'=>Translate::GetLabel('uid',$langCode)],
        ];
        return $result;
    }//END public function GetDynamicFormsRelationsUTypes

    /**
     * description
     *
     * @param array $params Parameters array
     * @param array $extra_params
     * @return array
     * @throws \NETopes\Core\AppException
     * @access public
     */
    public function GetDynamicFormsDesignRenderTypes($params=[],$extra_params=[]) {
        $langCode=get_array_value($params,'lang_code','','is_string');
        $forType=get_array_value($params,'for_type',1,'is_integer');
        $result=[
            ['id'=>1,'name'=>Translate::GetLabel('single_page',$langCode),'state'=>($forType>1 ? 0 : 1)],
            ['id'=>21,'name'=>Translate::GetLabel('tabbed_form',$langCode),'state'=>1],
            ['id'=>22,'name'=>Translate::GetLabel('accordion_form',$langCode),'state'=>1],
        ];
        return $result;
    }//END public function GetDynamicFormsDesignRenderTypes
}//END abstract class BaseDFormsOffline extends OfflineBase