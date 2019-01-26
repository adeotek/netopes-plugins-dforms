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
namespace NETopes\Plugins\Modules\DForms\Instances;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\App\Validator;
use NETopes\Core\Controls\Control;
use NETopes\Core\Data\DataProvider;
use NETopes\Plugins\DForms\Instances\PdfTemplates\InstancesPdf;
use NETopes\Core\AppException;
use NApp;
use Translate;
/**
 * description
 * description
 * @package  NETopes\Plugins\Modules\DForms
 */
class Instances extends Module {
	/**
	 * @var integer Dynamic form template ID
	 */
	public $idTemplate = NULL;
	/**
	 * @var integer Dynamic form template code (numeric)
	 */
	public $templateCode = NULL;
	/**
	 * @var integer Flag for modal add/edit forms (1=modal; 0=non-modal)
	 */
	public $isModal = 0;
	/**
	 * @var array List of header fields to be displayed in Listing
	 */
	public $showInListing = ['template_code','template_name','create_date','user_full_name','last_modified','last_user_full_name'];
	/**
	 * @var array List CSS styles to be used for generating view HTML
	 */
	protected $html_styles = [
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
	 * @return void
	 */
	protected function _Init() {
		$this->templateCode = NULL;
	}//END protected function _Init
    /**
     * description
     * @param      $field
     * @param      $f_params
     * @param      $theme_type
     * @param null $fvalue
     * @param int  $icount
     * @return array
     * @throws \NETopes\Core\AppException
     */
	protected function PrepareRepeatableField(&$field,&$f_params,$theme_type,$fvalue = NULL,$icount = 0) {
		// NApp::Dlog($icount,'$icount');
		// NApp::Dlog($fvalue,'$fvalues');
		$id_instance = get_array_value($field,'id_instance',NULL,'is_integer');
		$tagid = ($id_instance ? $id_instance.'_' : '').get_array_value($field,'cell','','is_string').'_'.get_array_value($field,'name','','is_string');
		$fvalues = explode('|::|',$fvalue);
		$field = array_merge($field,array(
			'tag_id'=>$tagid.'-0',
			'tag_name'=>get_array_value($field,'id',NULL,'is_numeric').'[]',
			'value'=>get_array_value($fvalues,0,NULL,'isset'),
		));
		$fclass = get_array_value($field,'class','','is_string');
		$id_values_list = get_array_value($field,'id_values_list',0,'is_numeric');
		if(in_array($fclass,['SmartComboBox','GroupCheckBox']) && $id_values_list>0) {
			$f_params['load_type'] ='database';
			$f_params['data_source'] = array(
				'ds_class'=>'Plugins\DForms\ValuesLists',
				'ds_method'=>'GetValues',
				'ds_params'=>array('list_id'=>$id_values_list,'for_state'=>1),
			);
		}//if(in_array($fclass,['SmartComboBox','GroupCheckBox']) && $id_values_list>0)
		$f_params = Control::ReplaceDynamicParams($f_params,$field,TRUE);
		$i_custom_actions = [];
		for($i=1;$i<$icount;$i++) {
			$tmp_ctrl = $f_params;
			$tmp_ctrl['container'] = 'none';
			$tmp_ctrl['no_label'] = TRUE;
			$tmp_ctrl['labelwidth'] = NULL;
			$tmp_ctrl['width'] = NULL;
			$tmp_ctrl['tag_id'] = $tagid.'-'.$i;
			$tmp_ctrl['value'] = get_array_value($fvalues,$i,NULL,'isset');
			if(strpos($theme_type,'bootstrap')!==FALSE) { $tmp_ctrl['class'] .= ' form-control'; }
			$tmp_ctrl['extratagparam'] = (isset($tmp_ctrl['extratagparam']) && $tmp_ctrl['extratagparam'] ? $tmp_ctrl['extratagparam'].' ' : '').'data-tid="'.$tagid.'" data-ti="'.$i.'"';
			$i_custom_actions[] = array(
				'type'=>$fclass,
				'params'=>$tmp_ctrl,
			);
			$i_custom_actions[] = array(
				'type'=>'Button',
				'params'=>array(
					'value'=>'&nbsp;'.Translate::GetButton('remove_field'),
					'icon'=>'fa fa-minus-circle',
					'class'=>'clsRepeatableCtrlBtn remove-ctrl-btn',
					'clear_base_class'=>TRUE,
					'onclick'=>"RemoveRepeatableControl(this,'{$tagid}-{$i}')",
				),
			);
		}//END for
		$i_custom_actions[] = array(
			'type'=>'Button',
			'params'=>array(
				'value'=>Translate::GetButton('add_field'),
				'icon'=>'fa fa-plus-circle',
				'class'=>'clsRepeatCtrlBtn btn btn-default',
				// 'clear_base_class'=>($theme_type=='bootstrap3'),
				'onclick'=>"RepeatControl(this,'{$tagid}')",
				'extratagparam'=>'data-ract="&nbsp;'.Translate::GetButton('remove_field').'"',
			),
		);
		$f_params['extratagparam'] = (isset($f_params['extratagparam']) && $f_params['extratagparam'] ? $f_params['extratagparam'].' ' : '').'data-tid="'.$tagid.'" data-ti="0"';
		$f_params['custom_actions'] = $i_custom_actions;
		// NApp::Dlog($f_params['custom_actions'],'custom_actions');
		return array(
			'width'=>get_array_value($field,'width','','is_string'),
			'control_type'=>$fclass,
			'control_params'=>$f_params,
		);
	}//protected function PrepareRepeatableField
    /**
     * description
     * @param      $field
     * @param      $f_params
     * @param      $theme_type
     * @param null $fvalue
     * @param bool $repeatable
     * @param int  $icount
     * @return array
     * @throws \NETopes\Core\AppException
     */
	protected function PrepareField(&$field,&$f_params,$theme_type,$fvalue = NULL,$repeatable = FALSE,$icount = 0) {
		// NApp::Dlog(['$field'=>$field,'$f_params'=>$f_params,'$theme_type'=>$theme_type,'$fvalue'=>$fvalue,'$f_rid'=>$f_rid,'$repeat_action'=>$repeat_action],'PrepareField');
		if($repeatable) { return $this->PrepareRepeatableField($field,$f_params,$theme_type,$fvalue,$icount); }
		$id_instance = get_array_value($field,'id_instance',NULL,'is_integer');
		$tagid = ($id_instance ? $id_instance.'_' : '').get_array_value($field,'cell','','is_string').'_'.get_array_value($field,'name','','is_string');
		$field = array_merge($field,array(
			'tag_id'=>$tagid,
			'tag_name'=>get_array_value($field,'id',NULL,'is_numeric'),
			'value'=>$fvalue,
		));
		// if(strlen($theme_type)) { $f_params['theme_type'] = $theme_type; }
		$fclass = get_array_value($field,'class','','is_string');
		if($fclass=='Message') {
			$flabel = get_array_value($field,'label','','is_string');
			$fdesc = get_array_value($field,'description','','is_string');
			$f_params['text'] = $flabel.$fdesc;
		}//if($fclass=='Message')
		$id_values_list = get_array_value($field,'id_values_list',0,'is_numeric');
		if(in_array($fclass,['SmartComboBox','GroupCheckBox']) && $id_values_list>0) {
			$f_params['load_type'] ='database';
			$f_params['data_source'] = array(
				'ds_class'=>'Plugins\DForms\ValuesLists',
				'ds_method'=>'GetValues',
				'ds_params'=>array('list_id'=>$id_values_list,'for_state'=>1),
			);
		}//if(in_array($fclass,['SmartComboBox','GroupCheckBox']) && $id_values_list>0)
		$f_params = Control::ReplaceDynamicParams($f_params,$field,TRUE);
		return array(
			'width'=>get_array_value($field,'width','','is_string'),
			'control_type'=>$fclass,
			'control_params'=>$f_params,
		);
	}//END protected function PrepareField
    /**
     * Prepare add/edit form/sub-form
     * @param        $mtemplate
     * @param  array $params An array of parameters
     * @param null   $id_instance
     * @param null   $id_sub_form
     * @param null   $id_item
     * @param null   $index
     * @return array Returns BasicForm configuration array
     * @throws \NETopes\Core\AppException
     */
	protected function PrepareForm(&$mtemplate,$params = NULL,$id_instance = NULL,$id_sub_form = NULL,$id_item = NULL,$index = NULL) {
		// NApp::Dlog(['$mtemplate'=>$mtemplate,'$id_instance'=>$id_instance,'$id_sub_form'=>$id_sub_form,'$id_item'=>$id_item,'$index'=>$index],'PrepareForm');
		$idTemplate = get_array_value($mtemplate,'id',NULL,'is_integer');
		if(!$idTemplate) { return NULL; }
		if($id_sub_form) {
			$template = DataProvider::GetArray('Plugins\DForms\Instances','GetTemplate',array(
				'for_id'=>$id_sub_form,
				'for_code'=>NULL,
				'instance_id'=>($id_instance ? $id_instance : NULL),
				'for_state'=>1,
			));
			$id_sub_form = get_array_value($template,'id',NULL,'is_integer');
			// NApp::Dlog($id_item,'$id_item');
			// NApp::Dlog($id_sub_form,'$id_sub_form');
			// NApp::Dlog($template,'$template');
			if(!$id_sub_form || !$id_item) { return NULL; }
			$relations = NULL;
			$fields = DataProvider::GetArray('Plugins\DForms\Instances','GetStructure',[
				'template_id'=>$idTemplate,
				'instance_id'=>($id_instance ? $id_instance : NULL),
				'item_id'=>$id_item,
				'for_index'=>(is_numeric($index) ? $index : NULL),
			]);
			// NApp::Dlog($fields,'$fields');
		} else {
			$template = $mtemplate;
			if($id_instance) {
				$relations = DataProvider::GetArray('Plugins\DForms\Instances','GetRelations',array('instance_id'=>$id_instance));
			} else {
				$relations = DataProvider::GetArray('Plugins\DForms\Templates','GetRelations',array('template_id'=>$idTemplate));
			}//if($id_instance)
			$fields = DataProvider::GetArray('Plugins\DForms\Instances','GetStructure',[
				'template_id'=>$idTemplate,
				'instance_id'=>($id_instance ? $id_instance : NULL),
			]);
			// NApp::Dlog($fields,'$fields');
		}//if($id_sub_form)
		$theme_type = get_array_value($template,'theme_type','','is_string');
		$controls_size = get_array_value($template,'controls_size','','is_string');
		$separator_width = get_array_value($template,'separator_width','','is_string');
		$label_cols = get_array_value($template,'label_cols','','is_string');
		require($this->GetViewFile('PrepareForm'));
		return (isset($ctrl_params) ? $ctrl_params : NULL);
	}//END protected function PrepareForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function Listing($params = NULL) {
		$idTemplate = $params->safeGet('id_template',$this->idTemplate,'is_not0_integer');
		$templateCode = $params->safeGet('templateCode',$this->templateCode,'is_not0_integer');
		if(!$idTemplate && !$templateCode) { throw new AppException('Invalid DynamicForm template identifier!'); }
		$fields = DataProvider::Get('Plugins\DForms\Instances','GetFields',[
			'template_id'=>($idTemplate ? $idTemplate : NULL),
			'for_template_code'=>$templateCode,
			'for_listing'=>1,
		]);
		$ftypes = DataProvider::GetKeyValue('_Custom\DFormsOffline','GetDynamicFormsTemplatesFTypes');
		$cmodule = $params->safeGet('cmodule',$this->class,'is_notempty_string');
		$cmethod = $params->safeGet('cmethod',call_back_trace(0),'is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		$target = $params->safeGet('target','main-content','is_notempty_string');
		$listingTarget = $target.'_listing';

		$view = new AppView(get_defined_vars(),$this,($target=='main-content' ? 'main' : 'secondary'));
        $view->SetTitle('');
        $view->SetTargetId($listingTarget);
        $view->AddTableView($this->GetViewFile('Listing'));
        $view->Render();
	}//END public function Listing
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function GlobalListing($params = NULL) {
		$idTemplate = $params->safeGet('for_id',$this->id_template,'is_not0_integer');
		$template_code = $params->safeGet('for_code',$this->template_code,'is_not0_integer');
		$ftypes = DataProvider::GetKeyValueArray('_Custom\DFormsOffline','GetDynamicFormsTemplatesFTypes');
		$listingTarget = 'listing-content';
		$view = new AppView(get_defined_vars(),$this,'main');
        $view->SetTitle('');
        $view->SetTargetId($listingTarget);
        $view->AddTableView($this->GetViewFile('GlobalListing'));
        $view->Render();
	}//END public function GlobalListing
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddForm($params = NULL) {
		// NApp::Dlog($params,'ShowAddForm');
		$idTemplate = $params->safeGet('id_template',$this->id_template,'is_not0_integer');
		$template_code = $params->safeGet('template_code',$this->template_code,'is_not0_integer');
		if(!$idTemplate && !$template_code) { throw new AppException('Invalid DynamicForm template identifier!'); }
		$template = DataProvider::GetArray('Plugins\DForms\Instances','GetTemplate',array(
			'for_id'=>(is_numeric($idTemplate) ? $idTemplate : NULL),
			'for_code'=>(is_numeric($template_code) ? $template_code : NULL),
			'instance_id'=>NULL,
			'for_state'=>1,
		));
		$idTemplate = get_array_value($template,'id',NULL,'is_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template!'); }
		$cmodule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cmethod = $params->safeGet('cmethod',call_back_trace(0),'is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		$ctrl_params = $this->PrepareForm($template,$this->GetParams(),NULL);
		if(!$ctrl_params) { throw new AppException('Invalid DynamicForm configuration!'); }
		$is_modal = $params->safeGet('is_modal',$this->is_modal,'is_integer');
		$ftitle = $params->safeGet('form_title','&nbsp;','is_string');
		require($this->GetViewFile('AddInstanceForm'));
		if($is_modal) {
			NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".$params->safeGet('nav_item_name','','is_string')."'))");
		}//if($is_modal)
	}//END public function ShowAddForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function SaveNewRecord($params = NULL){
		// NApp::Dlog($params,'SaveNewRecord');
		$idTemplate = $params->safeGet('id_template',$this->id_template,'is_not0_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template identifier!'); }
		$target = $params->safeGet('target','','is_string');
		$data = $params->safeGet('data',[],'is_array');
		if(!count($data)) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!count($data))
		$error = FALSE;

		$fields = DataProvider::GetArray('Plugins\DForms\Instances','GetFields',array('template_id'=>$idTemplate));
		foreach($fields as $k=>$field) {
			if($field['itype']==2 || $field['parent_itype']==2) {
				$fvals = get_array_value($data,$field['id'],NULL,'is_array');
				if(!is_array($fvals) || !count($fvals)) {
					$error = $field['required']==1;
					$fval = NULL;
				} else {
					$fval = [];
					foreach($fvals as $i=>$fv) {
						switch($field['data_type']) {
							case 'numeric':
								$fval[$i] = Validator::ValidateParam($fv,NULL,'is_numeric');
								$error = ($field['required']==1 && !is_numeric($fval[$i]));
								break;
							case 'string':
							default:
								$fval[$i] = Validator::ValidateParam($fv,'','is_string');
								$error = ($field['required']==1 && !strlen($fval[$i]));
								break;
						}//END switch
						if($error) { break; }
					}//END foreach
				}//if(!is_array($fvals) || !count($fvals))
			} else {
				switch($field['data_type']) {
					case 'numeric':
						$fval = get_array_value($data,$field['id'],NULL,'is_numeric');
						$error = ($field['required']==1 && !is_numeric($fval));
						break;
					case 'string':
					default:
						$fval = get_array_value($data,$field['id'],'','is_string');
						$error = ($field['required']==1 && !strlen($fval));
						break;
				}//END switch
			}//if($field['itype']==2 || $field['parent_itype']==2)
			if($error) { break; }
			$fields[$k]['value'] = $fval;
		}//END foreach

		$relations = DataProvider::GetArray('Plugins\DForms\Templates','GetRelations',array('template_id'=>$idTemplate));
		foreach($relations as $k=>$rel) {
			$dtype = get_array_value($rel,'dtype','','is_string');
			$relations[$k]['ivalue'] = 0;
			$relations[$k]['svalue'] = '';
			switch($rel['rtype']) {
				case 1:
					$r_val = NApp::_GetParam($rel['key']);
					if($dtype=='integer') {
						if(is_numeric($r_val) && $r_val>0) {
							$relations[$k]['ivalue'] = $r_val;
							$relations[$k]['svalue'] = '';
						}//if(is_numeric($r_val) && $r_val>0)
					} else {
						if(is_string($r_val) && strlen($r_val)) {
							$relations[$k]['ivalue'] = 0;
							$relations[$k]['svalue'] = $r_val;
						}//if(is_string($r_val) && strlen($r_val))
					}//if($dtype=='integer')
					break;
				case 3:
					if($dtype=='integer') {
						$relations[$k]['ivalue'] = get_array_value($data,'relation-'.$rel['key'],0,'is_integer');
						$relations[$k]['svalue'] = '';
					} else {
						$relations[$k]['ivalue'] = 0;
						$relations[$k]['svalue'] = get_array_value($data,'relation-'.$rel['key'],'','is_string');
					}//if($dtype=='integer')
					break;
			}//END switch
			if($rel['required']==1 && !$relations[$k]['ivalue'] && !$relations[$k]['svalue']) {
				throw new AppException('Invalid relation value: ['.$rel['name'].']');
			}//if($rel['required']==1 && !$relations[$k]['ivalue'] && !$relations[$k]['svalue'])
		}//END foreach

		if($error) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if($error)

		$template = DataProvider::GetArray('Plugins\DForms\Instances','GetTemplate',array('for_id'=>$idTemplate));
		$transaction = \NETopes\Core\AppSession::GetNewUID(get_array_value($template,'code','N/A','is_notempty_string'));
		DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
		try {
			$result = DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstance',array(
				'template_id'=>$idTemplate,
				'user_id'=>NApp::GetCurrentUserId(),
			),['transaction'=>$transaction]);
			$id_instance = get_array_value($result,0,0,'is_numeric','inserted_id');
			if($id_instance<=0) { throw new AppException('Database error on instance insert!'); }

			foreach($fields as $f) {
				if(($f['itype']==2 || $f['parent_itype']==2) && is_array($f['value'])) {
					foreach($f['value'] as $index=>$fvalue) {
						$result = DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',array(
							'instance_id'=>$id_instance,
							'item_id'=>$f['id'],
							'in_value'=>$fvalue,
							'in_name'=>NULL,
							'in_index'=>$index,
						),['transaction'=>$transaction]);
						if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
					}//END foreach
				} else {
					$result = DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',array(
						'instance_id'=>$id_instance,
						'item_id'=>$f['id'],
						'in_value'=>(isset($f['value']) ? $f['value'] : NULL),
						'in_name'=>NULL,
						'in_index'=>NULL,
					),['transaction'=>$transaction]);
					if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
				}//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
			}//END foreach

			foreach($relations as $r) {
				$result = DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceRelation',array(
					'instance_id'=>$id_instance,
					'relation_id'=>$r['id'],
					'in_ivalue'=>$r['ivalue'],
					'in_svalue'=>$r['svalue'],
				),['transaction'=>$transaction]);
				if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
			}//END foreach

			DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,FALSE);
		} catch(AppException $e) {
			DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,TRUE);
			NApp::_Elog($e->getMessage());
			throw $e;
		}//END try
		if($params->safeGet('is_modal',$this->is_modal,'is_numeric')==1) { $this->CloseForm(); }
		$cmodule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cmethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		NApp::Ajax()->Execute("AjaxRequest('{$cmodule}','{$cmethod}','id_template'|{$idTemplate},'{$cTarget}')->{$cTarget}");
	}//END public function SaveNewRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowEditForm($params = NULL) {
		// NApp::Dlog($params,'ShowEditForm');
		$id_instance = $params->safeGet('id',NULL,'is_not0_integer');
		if(!$id_instance) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$template = DataProvider::GetArray('Plugins\DForms\Instances','GetTemplate',array(
			'for_id'=>NULL,
			'for_code'=>NULL,
			'instance_id'=>$id_instance,
			'for_state'=>1,
		));
		$idTemplate = get_array_value($template,'id',NULL,'is_integer');
		$template_code = get_array_value($template,'code',NULL,'is_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template!'); }
		$cmodule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cmethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		$ctrl_params = $this->PrepareForm($template,$this->GetParams(),$id_instance);
		if(!$ctrl_params) { throw new AppException('Invalid DynamicForm configuration!'); }
		$is_modal = $params->safeGet('is_modal',$this->is_modal,'is_integer');
		require($this->GetViewFile('EditInstanceForm'));
		if($is_modal) {
			NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".Translate::GetButton('edit')."'))");
		}//if($is_modal)
	}//END public function ShowEditForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddEditForm($params = NULL) {
		// NApp::Dlog($params,'ShowAddEditForm');
		$this->template_code = $params->safeGet('template_code',$this->template_code,'is_not0_integer');
		$id_instance = $params->safeGet('id',0,'is_integer');
		$template = DataProvider::GetArray('Plugins\DForms\Instances','GetTemplate',array(
			'for_id'=>NULL,
			'for_code'=>$this->template_code,
			'instance_id'=>$id_instance,
			'for_state'=>1,
		));
		$idTemplate = get_array_value($template,'id',NULL,'is_integer');
		$template_code = get_array_value($template,'code',NULL,'is_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template!'); }
		$cmodule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cmethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		$ctrl_params = $this->PrepareForm($template,$params,$id_instance);
		if(!$ctrl_params) { throw new AppException('Invalid DynamicForm configuration!'); }
		$is_modal = $params->safeGet('is_modal',$this->is_modal,'is_integer');
		require($this->GetViewFile('AddEditInstanceForm'));
		if($is_modal) {
			NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".Translate::GetButton('edit')."'))");
		}//if($is_modal)
	}//END public function ShowAddEditForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function SaveRecord($params = NULL) {
		// NApp::Dlog($params,'SaveRecord');
		$idTemplate = $params->safeGet('id_template',$this->id_template,'is_not0_integer');
		$id_instance = $params->safeGet('id',NULL,'is_not0_integer');
		if(!$idTemplate || !$id_instance) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$target = $params->safeGet('target','','is_string');
		$data = $params->safeGet('data',[],'is_array');
		if(!count($data)) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!count($data))
		$error = FALSE;
		$fields = DataProvider::GetArray('Plugins\DForms\Instances','GetFields',array('template_id'=>$idTemplate,'instance_id'=>$id_instance));
		foreach($fields as $k=>$field) {
			if($field['itype']==2 || $field['parent_itype']==2) {
				$fvals = get_array_value($data,$field['id'],NULL,'is_array');
				if(!is_array($fvals) || !count($fvals)) {
					$error = $field['required']==1;
					$fval = NULL;
				} else {
					$fval = [];
					foreach($fvals as $i=>$fv) {
						switch($field['data_type']) {
							case 'numeric':
								$fval[$i] = Validator::ValidateParam($fv,NULL,'is_numeric');
								$error = ($field['required']==1 && !is_numeric($fval[$i]));
								break;
							case 'string':
							default:
								$fval[$i] = Validator::ValidateParam($fv,'','is_string');
								$error = ($field['required']==1 && !strlen($fval[$i]));
								break;
						}//END switch
						if($error) { break; }
					}//END foreach
				}//if(!is_array($fvals) || !count($fvals))
			} else {
				switch($field['data_type']) {
					case 'numeric':
						$fval = get_array_value($data,$field['id'],NULL,'is_numeric');
						$error = ($field['required']==1 && !is_numeric($fval));
						break;
					case 'string':
					default:
						$fval = get_array_value($data,$field['id'],'','is_string');
						$error = ($field['required']==1 && !strlen($fval));
						break;
				}//END switch
			}//if($field['itype']==2 || $field['parent_itype']==2)
			if($error) { break; }
			$fields[$k]['value'] = $fval;
		}//END foreach

		// $relations = DataProvider::GetArray('Plugins\DForms\Instances','GetRelations',array('template_id'=>$idTemplate,'instance_id'=>$id_instance));
		// foreach($relations as $k=>$rel) {
		// 	$dtype = get_array_value($rel,'dtype','','is_string');
		// 	$relations[$k]['ivalue'] = 0;
		// 	$relations[$k]['svalue'] = '';
		// 	switch($rel['rtype']) {
		// 		case 1:
		// 			$r_val = NApp::_GetParam($rel['key']);
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

		$template = DataProvider::GetArray('Plugins\DForms\Instances','GetTemplate',array('for_id'=>$idTemplate));
		$transaction = \NETopes\Core\AppSession::GetNewUID(get_array_value($template,'code','N/A','is_notempty_string'));
		DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
		try {
			$result = DataProvider::GetArray('Plugins\DForms\Instances','UnsetInstanceValues',['instance_id'=>$id_instance],['transaction'=>$transaction]);
			if($result===FALSE) { throw new AppException('Database error on instance update!'); }

			foreach($fields as $f) {
				if(in_array($f['class'],['','FormTitle','FormSubTitle','FormSeparator','Message','BasicForm'])) { continue; }
				if(($f['itype']==2 || $f['parent_itype']==2) && is_array($f['value'])) {
					foreach($f['value'] as $index=>$fvalue) {
						$result = DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',array(
							'instance_id'=>$id_instance,
							'item_id'=>$f['id'],
							'in_value'=>$fvalue,
							'in_name'=>NULL,
							'in_index'=>$index,
						),['transaction'=>$transaction]);
						if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
					}//END foreach
				} else {
					$result = DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',array(
						'instance_id'=>$id_instance,
						'item_id'=>$f['id'],
						'in_value'=>(isset($f['value']) ? $f['value'] : NULL),
						'in_name'=>NULL,
						'in_index'=>NULL,
					),['transaction'=>$transaction]);
					if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
				}//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
			}//END foreach

			// foreach($relations as $r) {
			// 	$result = DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceRelation',array(
			// 		'instance_id'=>$id_instance,
			// 		'relation_id'=>$r['id'],
			// 		'in_ivalue'=>$r['ivalue'],
			// 		'in_svalue'=>$r['svalue'],
			// 	),['transaction'=>$transaction]);
			// 	if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
			// }//END foreach

			DataProvider::GetArray('Plugins\DForms\Instances','SetInstanceState',[
				'for_id'=>$id_instance,
				'user_id'=>NApp::GetCurrentUserId(),
			],['transaction'=>$transaction]);
			DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,FALSE);
		} catch(AppException $e) {
			DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,TRUE);
			NApp::_Elog($e->getMessage());
			throw $e;
		}//END try
		if($params->safeGet('is_modal',$this->is_modal,'is_numeric')==1) { $this->CloseForm(); }
		$cmodule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cmethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		NApp::Ajax()->Execute("AjaxRequest('{$cmodule}','{$cmethod}','id_template'|{$idTemplate},'{$cTarget}')->{$cTarget}");
	}//END public function SaveRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function SaveInstance($params = NULL) {
		$id_instance = $params->safeGet('id',0,'is_integer');
		if($id_instance>0) {
			$this->Exec('SaveRecord',$params);
		} else {
			$this->Exec('SaveNewRecord',$params);
		}//if($id_instance>0)
	}//END public function SaveInstance
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteRecord($params = NULL) {
		$idTemplate = $params->safeGet('id_template',$this->id_template,'is_not0_integer');
		$id = $params->safeGet('id',NULL,'is_not0_integer');
		if(!$idTemplate || !$id) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$result = DataProvider::GetArray('Plugins\DForms\Instances','UnsetInstance',array('for_id'=>$id));
		if($result===FALSE) { return; }
		$cmodule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cmethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		NApp::Ajax()->Execute("AjaxRequest('{$cmodule}','{$cmethod}','id_template'|{$idTemplate},'{$cTarget}')->{$cTarget}");
	}//END public function DeleteRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function EditRecordState($params = NULL) {
		$id = $params->safeGet('id',NULL,'is_not0_integer');
		if(!$id) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$result = DataProvider::GetArray('Plugins\DForms\Instances','SetInstanceState',array(
			'for_id'=>$id,
			'in_state'=>$params->safeGet('state',NULL,'is_integer'),
			'user_id'=>NApp::GetCurrentUserId(),
		));
		if($result===FALSE) { throw new AppException('Failed database operation!'); }
	}//END public function EditRecordState
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowViewForm($params = NULL) {
		// NApp::Dlog($params,'ShowViewForm');
		$id_instance = $params->safeGet('id',NULL,'is_not0_integer');
		if(!$id_instance) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$instance = DataProvider::GetArray('Plugins\DForms\Instances','GetInstanceItem',array('for_id'=>$id_instance));
		$idTemplate = get_array_value($template,'id',NULL,'is_integer');
		$idTemplate = $params->safeGet('id_template',$this->id_template,'is_not0_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template!'); }
		$is_modal = $params->safeGet('is_modal',$this->is_modal,'is_integer');
		require($this->GetViewFile('ViewInstanceForm'));
		if($is_modal) {
			NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".Translate::GetButton('view')."'))");
		}//if($is_modal)
	}//END public function ShowViewForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @throws \NETopes\Core\AppException
	 */
	public function PrepareFormHtml($params = NULL) {
		// NApp::Dlog($params,'PrepareFormHtml');
		$id_instance = $params->safeGet('id',NULL,'is_integer');
		if(!$id_instance) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$id_sub_form = $params->safeGet('id_sub_form',0,'is_integer');
		$id_item = $params->safeGet('id_item',0,'is_integer');
		$index = $params->safeGet('index',0,'is_integer');
		$output = $params->safeGet('output',FALSE,'bool');
		if($id_sub_form) {
			$instance = DataProvider::GetArray('Plugins\DForms\Instances','GetTemplate',array(
				'for_id'=>$id_sub_form,
				'for_code'=>NULL,
				'instance_id'=>$id_instance,
				'for_state'=>1,
			));
			$id_sub_form = get_array_value($instance,'id',NULL,'is_integer');
			// NApp::Dlog($id_item,'$id_item');
			// NApp::Dlog($id_sub_form,'$id_sub_form');
			// NApp::Dlog($template,'$template');
			if(!$id_sub_form || !$id_item) { return NULL; }
			$relations = NULL;
			$fields = DataProvider::GetArray('Plugins\DForms\Instances','GetStructure',[
				'instance_id'=>$id_instance,
				'item_id'=>$id_item,
				'for_index'=>(is_numeric($index) ? $index : NULL),
			]);
			// NApp::Dlog($fields,'$fields');
		} else {
			$instance = DataProvider::GetArray('Plugins\DForms\Instances','GetInstanceItem',['for_id'=>$id_instance]);
			$relations = DataProvider::GetArray('Plugins\DForms\Instances','GetRelations',['instance_id'=>$id_instance]);
			$fields = DataProvider::GetArray('Plugins\DForms\Instances','GetStructure',['instance_id'=>$id_instance]);
		}
		$theme_type = get_array_value($instance,'theme_type','','is_string');
		$controls_size = get_array_value($instance,'controls_size','','is_string');
		$separator_width = get_array_value($instance,'separator_width','','is_string');
		$label_cols = get_array_value($instance,'label_cols','','is_string');
		$html = NULL;
		require($this->GetViewFile('PrepareFormHtml'));
		return $html;
	}//END public function PrepareFormHtml
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return mixed return description
	 * @throws \NETopes\Core\AppException
	 */
	public function GetInstancePdf($params = NULL) {
		NApp::Dlog($params,'GetInstancePdf');
		$id_instance = $params->safeGet('id',NULL,'is_integer');
		if(!$id_instance) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$cache = $params->safeGet('cache',TRUE,'bool');
		$result_type = $params->safeGet('result_type',0,'is_integer');
		$instance = DataProvider::GetArray('Plugins\DForms\Instances','GetInstanceItem',['for_id'=>$id_instance]);
		$filename = get_array_value($instance,'uid','','is_string');
		if(!strlen($filename)) {
			$filename = date('Y-m-d_H-i-s').'.pdf';
		} else {
			$filename = str_replace(' ','_',trim($filename)).'.pdf';
		}//if(!strlen($filename))
		$category = get_array_value($instance,'category',get_array_value($instance,'template_code',$this->class,'is_notempty_string'),'is_notempty_string');
		if($cache && strlen($filename) && file_exists(NAPP::_GetRepositoryPath().'forms/'.$category.'/'.$filename)) {
			if($result_type==1) {
				$data = [
					'file_name'=>$filename,
					'path'=>NAPP::_GetRepositoryPath().'forms/'.$category.'/',
					'download_name'=>$filename,
				];
			} else {
				$data = file_get_contents(NAPP::_GetRepositoryPath().'forms/'.$category.'/'.$filename);
			}//if($result_type==1)
			return $data;
		}//if($cache && strlen($filename) && file_exists(NAPP::_GetRepositoryPath().$company.'/'.$filename))
		if($cache) {
			if(!file_exists(NAPP::_GetRepositoryPath().'forms')) { mkdir(NAPP::_GetRepositoryPath().'forms',755); }
			if(!file_exists(NAPP::_GetRepositoryPath().'forms/'.$category)) { mkdir(NAPP::_GetRepositoryPath().'forms/'.$category,755); }
		}//if($cache)
		$html_data = $this->Exec('PrepareFormHtml',['id'=>$id_instance]);
		$pdfdoc = new InstancesPdf(['html_data'=>$html_data,'file_name'=>$filename]);
		if($cache) {
			// file_put_contents(NAPP::_GetRepositoryPath().'forms/'.$category.'/'.$filename,$data);
			$pdfdoc->Output(['output_type'=>'F','file_name'=>NAPP::_GetRepositoryPath().'forms/'.$category.'/'.$filename]);
			if($result_type==1) {
				$data = [
					'file_name'=>$filename,
					'path'=>NAPP::_GetRepositoryPath().'forms/'.$category.'/',
					'download_name'=>$filename,
				];
			} else {
				$data = file_get_contents(NAPP::_GetRepositoryPath().'forms/'.$category.'/'.$filename);
			}//if($result_type==1)
		} else {
			$data = $pdfdoc->Output(['base64'=>FALSE,'file_name'=>NAPP::_GetRepositoryPath().'forms/'.$category.'/'.$filename]);
		}//if($cache)
		return $data;
	}//END public function GetInstancePdf
}//END class Instances extends Module
?>