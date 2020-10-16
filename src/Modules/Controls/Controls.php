<?php
/**
 * description
 * description
 *
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Controls;
use Error;
use Exception;
use NApp;
use NETopes\Core\App\Module;
use NETopes\Core\App\Params;
use NETopes\Core\Controls\TabControl;
use NETopes\Core\Controls\TabControlBuilder;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\AppException;
use NETopes\Core\Data\IEntity;
use Translate;

/**
 * description
 *
 * @package  NETopes\Plugins\Modules\DForms
 */
class Controls extends Module {
    /**
     * @param array       $data
     * @param int         $controlId
     * @param int         $parentId
     * @param string|null $parentGroupName
     * @return array
     * @throws \NETopes\Core\AppException
     */
    protected function GetTabControlStructure(array $data,int $controlId,int $parentId=0,?string $parentGroupName=NULL): array {
        // \NApp::Dlog($data,'GetTabControlStructure:$data');
        // \NApp::Dlog(['$controlId'=>$controlId,'$parentId'=>$parentId,'$parentGroupName'=>$parentGroupName],'GetTabControlStructure');
        $fieldProperties=DataProvider::Get('Plugins\DForms\Controls','GetProperties',[
            'control_id'=>$controlId,
            'for_state'=>-1,
            'parent_id'=>$parentId,
        ]);
        // \NApp::Dlog($fieldProperties,'$fieldProperties');
        $result=[];
        if(!is_iterable($fieldProperties) || !count($fieldProperties)) {
            return $result;
        }
        foreach($fieldProperties as $fpi) {
            /** @var \NETopes\Core\Data\IEntity $fpi */
            $skip=FALSE;
            $hidden=FALSE;
            if(isset($parentGroupName)) {
                $groupName=$parentGroupName;
                $fpLabel='==> ';
            } else {
                $groupName=$fpi->getProperty('group_name','','is_string');
                $fpLabel='';
            }//if(isset($parentGroupName))
            $fpKey=$fpi->getProperty('key','','is_string');
            $fpPType=$fpi->getProperty('ptype','','is_string');
            $fpLabel.=$fpi->getProperty('name',$fpKey,'is_notempty_string');
            $fpRequired=$fpi->getProperty('required',FALSE,'bool');
            $fpFixedWidth=NULL;
            $fpLCols=4;
            $fpCCols=NULL;
            $fpValue=NULL;
            $fpCType=NULL;
            $fpSParams=[];
            switch($fpPType) {
                case 'text':
                    $fpCType='TextBox';
                    $fpValue=get_array_value($data,$fpKey,$fpi->getProperty('default_value','','is_string'),'is_string');
                    break;
                case 'smalltext':
                    $fpCType='TextBox';
                    $fpValue=get_array_value($data,$fpKey,$fpi->getProperty('default_value','','is_string'),'is_string');
                    $fpFixedWidth=100;
                    break;
                case 'bool':
                    $fpCType='CheckBox';
                    $fpValue=intval(get_array_value($data,$fpKey,$fpi->getProperty('default_value',0,'bool'),'bool'));
                    $fpSParams['class']='pull-left';
                    break;
                case 'integer':
                    $fpCType='NumericTextBox';
                    $fpNVal=0;
                    if($fpi->getProperty('allow_null',0,'is_numeric')>0) {
                        $fpSParams['allow_null']=TRUE;
                        $fpNVal='';
                    }//if($fpi->getProperty('allow_null',0,'is_numeric')>0)
                    $fpValue=get_array_value($data,$fpKey,$fpi->getProperty('default_value',$fpNVal,'is_numeric'),'is_numeric');
                    $fpSParams['number_format']='0|||';
                    $fpSParams['align']='center';
                    $fpFixedWidth=100;
                    $fpCCols=4;
                    break;
                case 'flist':
                    $fpCType='SmartComboBox';
                    $fpValue=[];
                    foreach(explode(';',$fpi->getProperty('values','','is_string')) as $fpflv) {
                        $fpValue[]=['id'=>$fpflv,'name'=>$fpflv];
                    }//END foreach
                    $fpSParams['load_type']='value';
                    if($fpRequired) {
                        $fpSParams['allow_clear']=FALSE;
                    } else {
                        $fpSParams['allow_clear']=TRUE;
                        $fpSParams['placeholder']='['.Translate::GetLabel('default').']';
                    }//if($fpRequired)
                    $fpSParams['minimum_results_for_search']=0;
                    $fpSParams['fixed_width']='100%';
                    $fpSParams['selected_value']=get_array_value($data,$fpKey,$fpi->getProperty('default_value','','is_string'),'is_string');
                    $fpSParams['selected_text']=$fpSParams['selected_value'];
                    break;
                case 'kvlist':
                    $fpCType='KVList';
                    $fpValue=get_array_value($data,$fpKey,[],'is_array');
                    break;
                case 'children':
                    $skip=TRUE;
                    $idp=$fpi->getProperty('id',NULL,'is_not0_numeric');
                    if(!$idp) {
                        break;
                    }
                    $cData=get_array_value($data,$fpKey,[],'is_array');
                    $cResult=$this->GetTabControlStructure($cData,$controlId,$idp,$groupName);
                    $result[$groupName]['content']['control_params']['content'][]=['separator'=>'subtitle','value'=>$fpLabel];
                    $result[$groupName]['content']['control_params']['content']=array_merge($result[$groupName]['content']['control_params']['content'],$cResult);
                    break;
                case 'auto':
                    $fpCType='HiddenInput';
                    $fpValue='{_dfp_!'.$fpKey.'!}';
                    $fpLabel=NULL;
                    $hidden=TRUE;
                    break;
                default:
                    $fpCType='HiddenInput';
                    $fpValue=$fpi->getProperty('default_value','','is_string');
                    $fpLabel=NULL;
                    $hidden=TRUE;
                    break;
            }//END switch
            if($skip) {
                continue;
            }
            if($parentId>0) {
                $result[]=[
                    [
                        'hidden_row'=>$hidden,
                        'control_type'=>$fpCType,
                        'control_params'=>array_merge(['tag_id'=>'dft_fpe_'.$fpKey,'tag_name'=>$fpKey,'value'=>$fpValue,'label'=>$fpLabel,'label_width'=>150,'fixed_width'=>$fpFixedWidth,'cols'=>$fpCCols,'required'=>$fpRequired],$fpSParams),
                    ],
                ];
            } else {
                if(!array_key_exists($groupName,$result)) {
                    $result[$groupName]=[
                        'type'=>'fixed',
                        'uid'=>$groupName,
                        'name'=>$groupName,
                        'content_type'=>'control',
                        'content'=>[
                            'control_type'=>'BasicForm',
                            'control_params'=>[
                                'tag_id'=>'ctrlp_'.$groupName.'_form',
                                'cols_no'=>1,
                                'content'=>[],
                            ],
                        ],
                    ];
                }//if(!array_key_exists($groupName,$fp_tabs))
                $result[$groupName]['content']['control_params']['content'][]=[
                    [
                        'hidden_row'=>$hidden,
                        'control_type'=>$fpCType,
                        'control_params'=>array_merge(['tag_id'=>'dft_fpe_'.$fpKey,'tag_name'=>$fpKey,'value'=>$fpValue,'label'=>$fpLabel,'label_cols'=>$fpLCols,'fixed_width'=>$fpFixedWidth,'cols'=>$fpCCols,'required'=>$fpi->getProperty('required',FALSE,'bool')],$fpSParams),
                    ],
                ];
            }//if($parentId>0)
        }//END foreach
        return $result;
    }//END protected function GetTabControlStructure

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return \NETopes\Core\Controls\TabControlBuilder
     * @throws \NETopes\Core\AppException
     * @throws \Exception
     */
    public function GetControlPropertiesTabBuilder(Params $params): TabControlBuilder {
        $controlId=$params->getOrFail('id_control','is_not0_integer','Invalid control identifier!');
        $data=$params->safeGet('data',NULL,'is_array');
        if(!is_array($data)) {
            $data=$params->safeGet('data','','is_string');
            if(strlen($data)) {
                try {
                    $data=json_decode($data,TRUE);
                } catch(Error | Exception $e) {
                    NApp::Elog($e);
                    $data=[];
                }//END try
            } else {
                $data=[];
            }//if(strlen($data))
        }//if(is_string($data))
        $target=$params->safeGet('target','ctrl_properties_tab','is_notempty_string');
        $builder=new TabControlBuilder(['tag_id'=>$target]);
        $builder->SetTabs($this->GetTabControlStructure($data,$controlId));
        return $builder;
    }//END public function GetControlPropertiesTabBuilder

    protected function GetPropertyValue(string $key,IEntity $property,array $data) {
        switch($property->getProperty('ptype')) {
            case 'bool':
                $value=get_array_value($data,$key,NULL,'bool');
                break;
            case 'integer':
                $value=get_array_value($data,$key,NULL,'is_integer');
                break;
            case 'array':
                $value=get_array_value($data,$key,NULL,'is_array');
                break;
            case 'flist':
            case 'text':
            case 'smalltext':
                if($property->getProperty('allow_null',0,'is_integer')===2) {
                    $value=get_array_value($data,$key,NULL,'is_notempty_string');
                } else {
                    $value=get_array_value($data,$key,NULL,'is_string');
                }
                break;
            default:
                $value=get_array_value($data,$key,NULL,'isset');
                break;
        }//END switch
        return $value;
    }//END protected function GetPropertyValue

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return mixed
     * @throws \NETopes\Core\AppException
     */
    public function ProcessFieldProperties(Params $params) {
        // \NApp::Dlog($params,'ProcessFieldProperties');
        $controlId=$params->safeGet('id_control',NULL,'is_not0_numeric');
        if(!$controlId) {
            throw new AppException('Invalid control identifier!');
        }
        $data=$params->safeGet('data',[],'is_array');
        $cParams=DataProvider::Get('Plugins\DForms\Controls','GetProperties',['control_id'=>$controlId,'for_state'=>-1,'parent_id'=>0]);
        $result=[];
        if(is_iterable($cParams) && count($cParams)) {
            foreach($cParams as $cp) {
                /** @var \NETopes\Core\Data\VirtualEntity $cp */
                $propKey=$cp->getProperty('key',NULL,'is_notempty_string');
                if(is_null($propKey)) {
                    continue;
                }
                switch($propKey) {
                    case 'data_source':
                        $dsModule=get_array_value($data,'ds_class','','is_string');
                        $dsMethod=get_array_value($data,'ds_method','','is_string');
                        $dsParams=get_array_value($data,'ds_params',[],'is_array');
                        $dsExtraParams=get_array_value($data,'ds_extra_params',[],'is_array');
                        switch(get_array_value($data,'load_type','N/A','is_string')) {
                            case 'database':
                            case 'N/A':
                                $result['data_source']=[
                                    'ds_class'=>$dsModule,
                                    'ds_method'=>$dsMethod,
                                    'ds_params'=>$dsParams,
                                    'ds_extra_params'=>$dsExtraParams,
                                ];
                                break;
                            case 'ajax':
                                $result['data_source']=[
                                    'ds_class'=>$dsModule,
                                    'ds_method'=>$dsMethod,
                                    'ds_params'=>$dsParams,
                                ];
                                break;
                            default:
                                $result['load_type']='value';
                                $result['data_source']=[];
                                break;
                        }//END switch
                        break;
                    default:
                        $cpVal=$this->GetPropertyValue($propKey,$cp,$data);
                        if($cp->getProperty('allow_null',0,'is_integer')===2) {
                            if(isset($cpVal)) {
                                $result[$propKey]=$cpVal;
                            }
                        } else {
                            $result[$propKey]=$cpVal;
                        }
                        break;
                }//END switch
            }//END foreach
        }//if(is_iterable($cParams) && count($cParams))
        return json_encode($result);
    }//END public function ProcessFieldProperties
}//END class Controls extends Module