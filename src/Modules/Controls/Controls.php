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
use NETopes\Core\Controls\TabControl;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\AppException;
use Translate;

/**
 * description
 *
 * @package  NETopes\Plugins\Modules\DForms
 */
class Controls extends Module {
    /**
     * description
     *
     * @param array       $data
     * @param int         $idControl
     * @param int         $idParent
     * @param string|null $parentGroupName
     * @return array
     * @throws \NETopes\Core\AppException
     * @throws \NETopes\Core\AppException
     */
    protected function GetTabControlStructure(array $data,int $idControl,int $idParent=0,?string $parentGroupName=NULL) {
        // \NApp::Dlog($data,'GetTabControlStructure:$data');
        // \NApp::Dlog(['$idControl'=>$idControl,'$idParent'=>$idParent,'$parentGroupName'=>$parentGroupName],'GetTabControlStructure');
        $fieldProperties=DataProvider::Get('Plugins\DForms\Controls','GetProperties',[
            'control_id'=>$idControl,
            'for_state'=>-1,
            'parent_id'=>$idParent,
        ]);
        // \NApp::Dlog($fieldProperties,'$fieldProperties');
        $result=[];
        if(!is_iterable($fieldProperties) || !count($fieldProperties)) {
            return $result;
        }
        foreach($fieldProperties as $fpi) {
            /** @var \NETopes\Core\Data\VirtualEntity $fpi */
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
                case 'small_text':
                    $fpCType='TextBox';
                    $fpValue=get_array_value($data,$fpKey,$fpi->getProperty('default_value','','is_string'),'is_string');
                    $fpFixedWidth=100;
                    break;
                case 'bool':
                    $fpCType='CheckBox';
                    $fpValue=get_array_value($data,$fpKey,$fpi->getProperty('default_value',0,'is_numeric'),'is_numeric');
                    $fpSParams['class']='pull-left';
                    break;
                case 'integer':
                    $fpCType='NumericTextBox';
                    $fp_nval=0;
                    if($fpi->getProperty('allow_null',0,'is_numeric')>0) {
                        $fpSParams['allow_null']=TRUE;
                        $fp_nval='';
                    }//if($fpi->getProperty('allow_null',0,'is_numeric')>0)
                    $fpValue=get_array_value($data,$fpKey,$fpi->getProperty('default_value',$fp_nval,'is_numeric'),'is_numeric');
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
                    $cResult=$this->GetTabControlStructure($cData,$idControl,$idp,$groupName);
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
            if($idParent>0) {
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
            }//if($idParent>0)
        }//END foreach
        return $result;
    }//END protected function GetTabControlStructure

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return \NETopes\Core\Controls\TabControl
     * @throws \NETopes\Core\AppException
     * @throws \Exception
     */
    public function GetControlPropertiesTab($params=NULL): TabControl {
        $idControl=$params->getOrFail('id_control','is_not0_integer','Invalid control identifier!');
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
        $ctrlTabs=$this->GetTabControlStructure($data,$idControl);
        $ctrlTabs=new TabControl(['tag_id'=>$target,'tabs'=>$ctrlTabs]);
        return $ctrlTabs;
    }//END public function GetControlPropertiesTab

    /**
     * description
     *
     * @param \NETopes\Core\App\Params|array|null $params Parameters
     * @return mixed
     * @throws \NETopes\Core\AppException
     */
    public function ProcessFieldProperties($params=NULL) {
        // \NApp::Dlog($params,'ProcessFieldProperties');
        $idControl=$params->safeGet('id_control',NULL,'is_not0_numeric');
        if(!$idControl) {
            throw new AppException('Invalid control identifier!');
        }
        $data=$params->safeGet('data',[],'is_array');
        $cParams=DataProvider::Get('Plugins\DForms\Controls','GetProperties',['control_id'=>$idControl,'for_state'=>-1,'parent_id'=>0]);
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
                        switch($cp->getProperty('allow_null',0,'is_integer')) {
                            case 2:
                                $cpVal=get_array_value($data,$propKey,'','is_string');
                                if(strlen($cpVal)) {
                                    $result[$propKey]=$cpVal;
                                }
                                break;
                            case 1:
                                $result[$propKey]=get_array_value($data,$propKey,NULL,'isset');
                                break;
                            default:
                                $result[$propKey]=get_array_value($data,$propKey,NULL,'isset');
                                break;
                        }//END switch
                        break;
                }//END switch
            }//END foreach
        }//if(is_iterable($cParams) && count($cParams))
        return json_encode($result);
    }//END public function ProcessFieldProperties
}//END class Controls extends Module