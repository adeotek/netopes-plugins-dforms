<?php
/**
 * description
 * description
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Controls;
use NETopes\Core\App\Module;
use NETopes\Core\Controls\TabControl;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\AppException;
use Translate;
/**
 * description
 * @package  NETopes\Plugins\Modules\DForms
 */
class Controls extends Module {
	/**
	 * description
	 * @param      $data
	 * @param      $id_control
	 * @param int  $id_parent
	 * @param null $parent_group_name
	 * @return array
	 * @throws \NETopes\Core\AppException
	 */
	protected function GetTabControlStructure($data,$id_control,$id_parent = 0,$parent_group_name = NULL) {
		// NApp::Dlog($data,'$data');
		// NApp::Dlog($id_control,'$id_control');
		// NApp::Dlog($id_parent,'$id_parent');
		// NApp::Dlog($parent_group_name,'$parent_group_name');
		$field_properties = DataProvider::GetArray('Plugins\DForms\Controls','GetProperties',array(
			'control_id'=>$id_control,
			'for_state'=>-1,
			'parent_id'=>$id_parent,
		));
		// NApp::Dlog($field_properties,'$field_properties');
		$result = [];
		if(!is_array($field_properties)) { return $result; }
		foreach($field_properties as $fpi) {
			$skip = FALSE;
			$hidden = FALSE;
			if(isset($parent_group_name)) {
				$group_name = $parent_group_name;
				$fp_label = '==> ';
			} else {
				$group_name = get_array_value($fpi,'group_name','','is_string');
				$fp_label = '';
			}//if(isset($parent_group_name))
			$fp_key = get_array_value($fpi,'key','','is_string');
			$fp_ptype = get_array_value($fpi,'ptype','','is_string');
			$fp_label .= get_array_value($fpi,'name',$fp_key,'is_notempty_string');
			$fp_required = get_array_value($fpi,'required',FALSE,'bool');
			$fp_cwidth = 350;
			$fp_ccols = 0;
			$fp_sparams = [];
			switch($fp_ptype) {
				case 'text':
					$fp_ctype = 'TextBox';
					$fp_value = get_array_value($data,$fp_key,get_array_value($fpi,'default_value','','is_string'),'is_string');
					break;
				case 'smalltext':
					$fp_ctype = 'TextBox';
					$fp_value = get_array_value($data,$fp_key,get_array_value($fpi,'default_value','','is_string'),'is_string');
					$fp_cwidth = 100;
					break;
				case 'bool':
					$fp_ctype = 'CheckBox';
					$fp_value = get_array_value($data,$fp_key,get_array_value($fpi,'default_value',0,'is_numeric'),'is_numeric');
					$fp_sparams['class'] = 'pull-left';
					break;
				case 'integer':
					$fp_ctype = 'NumericTextBox';
					$fp_nval = 0;
					if(get_array_value($fpi,'allow_null',0,'is_numeric')>0) {
						$fp_sparams['allownull'] = TRUE;
						$fp_nval = '';
					}//if(get_array_value($fpi,'allow_null',0,'is_numeric')>0)
					$fp_value = get_array_value($data,$fp_key,get_array_value($fpi,'default_value',$fp_nval,'is_numeric'),'is_numeric');
					$fp_sparams['number_format'] = '0|||';
					$fp_sparams['align'] = 'center';
					$fp_cwidth = 100;
					$fp_ccols = 4;
					break;
				case 'flist':
					$fp_ctype = 'SmartComboBox';
					$fp_value = [];
					foreach(explode(';',get_array_value($fpi,'values','','is_string')) as $fpflv) {
						$fp_value[] = array('id'=>$fpflv,'name'=>$fpflv);
					}//END foreach
					$fp_sparams['load_type'] = 'value';
					if($fp_required) {
						$fp_sparams['allow_clear'] = FALSE;
					} else {
						$fp_sparams['allow_clear'] = TRUE;
						$fp_sparams['placeholder'] = '['.Translate::GetLabel('default').']';
					}//if($fp_required)
					$fp_sparams['minimum_results_for_search'] = 0;
					$fp_sparams['selected_value'] = get_array_value($data,$fp_key,get_array_value($fpi,'default_value','','is_string'),'is_string');
					$fp_sparams['selected_text'] = $fp_sparams['selected_value'];
					break;
				case 'kvlist':
					$fp_ctype = 'KVList';
					$fp_value = get_array_value($data,$fp_key,[],'is_array');
					break;
				case 'children':
					$skip = TRUE;
					$idp = get_array_value($fpi,'id',NULL,'is_not0_numeric');
					if(!$idp) { break; }
					$cdata = get_array_value($data,$fp_key,[],'is_array');
					$cresult = $this->GetTabControlStructure($cdata,$id_control,$idp,$group_name);
					$result[$group_name]['content']['control_params']['content'][] = array('separator'=>'subtitle','value'=>$fp_label);
					$result[$group_name]['content']['control_params']['content'] = array_merge($result[$group_name]['content']['control_params']['content'],$cresult);
					break;
				case 'auto':
					$fp_ctype = 'HiddenInput';
					$fp_value = '{{'.$fp_key.'}}';
					$fp_label = NULL;
					$hidden = TRUE;
					break;
				default:
					$fp_ctype = 'HiddenInput';
					$fp_value = get_array_value($fpi,'default_value','','is_string');
					$fp_label = NULL;
					$hidden = TRUE;
					break;
			}//END switch
			if($skip) { continue; }
			if($id_parent>0) {
				$result[] = array(
					array(
						'width'=>'500',
						'hidden_row'=>$hidden,
						'control_type'=>$fp_ctype,
						'control_params'=>array_merge(['tag_id'=>$fp_key,'tag_name'=>$fp_key,'value'=>$fp_value,'label'=>$fp_label,'label_width'=>150,'fixed_width'=>$fp_cwidth,'cols'=>$fp_ccols,'required'=>$fp_required],$fp_sparams),
					),
				);
			} else {
				if(!array_key_exists($group_name,$result)) {
					$result[$group_name] = array(
						'type'=>'fixed',
						'uid'=>$group_name,
						'name'=>$group_name,
						'content_type'=>'control',
						'content'=>array(
							'control_type'=>'BasicForm',
							'control_params'=>array(
								'tag_id'=>'ctrlp_'.$group_name.'_form',
								'cols_no'=>1,
								'content'=>[],
							),
						),
					);
				}//if(!array_key_exists($group_name,$fp_tabs))
				$result[$group_name]['content']['control_params']['content'][] = array(
					array(
						'width'=>'500',
						'hidden_row'=>$hidden,
						'control_type'=>$fp_ctype,
						'control_params'=>array_merge(['tag_id'=>$fp_key,'tag_name'=>$fp_key,'value'=>$fp_value,'label'=>$fp_label,'label_width'=>150,'width'=>$fp_cwidth,'cols'=>$fp_ccols,'required'=>get_array_value($fpi,'required',FALSE,'bool')],$fp_sparams),
					),
				);
			}//if($id_parent>0)
		}//END foreach
		return $result;
	}//END protected function GetTabControlStructure
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return \NETopes\Core\Controls\TabControl
	 * @throws \NETopes\Core\AppException
	 */
	public function GetControlPropertiesTab($params = NULL) {
		$id_control = $params->safeGet('id_control',NULL,'is_not0_numeric');
		if(!$id_control) { throw new AppException('Invalid control identifier!'); }
		$data = $params->safeGet('data',NULL,'is_array');
		if(!is_array($data)) {
			$data = $params->safeGet('data','','is_string');
			if(strlen($data)) {
				$data = @unserialize($data);
			} else {
				$data = [];
			}//if(strlen($data))
		}//if(is_string($data))
		$target = $params->safeGet('target','ctrl_properties_tab','is_notempty_string');
		$ctrl_tabs = $this->GetTabControlStructure($data,$id_control);
		$ctrl_tab = new TabControl(array('tag_id'=>$target,'tabs'=>$ctrl_tabs));
		return $ctrl_tab;
	}//END public function GetControlPropertiesTab
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return mixed
	 * @throws \NETopes\Core\AppException
	 */
	public function ProcessFieldProperties($params = NULL) {
		// NApp::Dlog($params,'ProcessFieldProperties');
		$id_control = $params->safeGet('id_control',NULL,'is_not0_numeric');
		if(!$id_control) { throw new AppException('Invalid control identifier!'); }
		$data = $params->safeGet('data',[],'is_array');
		$cparams = DataProvider::GetArray('Plugins\DForms\Controls','GetProperties',['control_id'=>$id_control,'for_state'=>-1,'parent_id'=>0]);
		$result = [];
		if(is_array($cparams) && count($cparams)) {
			foreach($cparams as $cp) {
				switch($cp['key']) {
					case 'data_source':
						$ds_module = get_array_value($data,'ds_class','','is_string');
						$ds_method = get_array_value($data,'ds_method','','is_string');
						$ds_params = get_array_value($data,'ds_params',[],'is_array');
						$ds_eparams = get_array_value($data,'ds_extra_params',[],'is_array');
						switch(get_array_value($data,'load_type','N/A','is_string')) {
							case 'database':
							case 'N/A':
								$result['data_source'] = array(
									'ds_class'=>$ds_module,
									'ds_method'=>$ds_method,
									'ds_params'=>$ds_params,
									'ds_extra_params'=>$ds_eparams,
								);
								break;
							case 'ajax':
								$result['data_source'] = array(
									'ds_class'=>$ds_module,
									'ds_method'=>$ds_method,
									'ds_params'=>$ds_params,
								);
								break;
							default:
								$result['load_type'] = 'value';
								$result['data_source'] = [];
								break;
						}//END switch
						break;
					default:
						switch(get_array_value($cp,'allow_null',0,'is_integer')) {
							case 2:
								$cp_val = get_array_value($data,$cp['key'],'','is_string');
								if(strlen($cp_val)) { $result[$cp['key']] = $cp_val; }
								break;
							case 1:
								$result[$cp['key']] = get_array_value($data,$cp['key'],NULL,'isset');
								break;
							default:
								$result[$cp['key']] = get_array_value($data,$cp['key'],NULL,'isset');
								break;
						}//END switch
						break;
				}//END switch
			}//END foreach
		}//if(is_array($cparams) && count($cparams))
		return serialize($result);
	}//END public function ProcessFieldProperties
}//END class Controls extends Module