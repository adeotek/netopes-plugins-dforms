<?php
/**
 * description
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Instances;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\App\ModulesProvider;
use NETopes\Core\App\Validator;
use NETopes\Core\Controls\Control;
use NETopes\Core\Controls\ControlsHelpers;
use NETopes\Core\Data\DataProvider;
use NETopes\Plugins\DForms\Instances\PdfTemplates\InstancesPdf;
use NETopes\Core\AppException;
use NApp;
use Translate;
/**
 * Class Instances
 *
 * @package NETopes\Plugins\DForms\Modules\Instances
 */
class Instances extends Module {
	/**
	 * @var integer Dynamic form template ID
	 * @access protected
	 */
	public $idTemplate = NULL;
	/**
	 * @var integer Dynamic form template code (numeric)
	 * @access protected
	 */
	public $templateCode = NULL;
	/**
	 * @var integer Flag for modal add/edit forms
	 * @access protected
	 */
	public $isModal = FALSE;
	/**
	 * @var array List of header fields to be displayed in Listing
	 * @access protected
	 */
	public $showInListing = ['template_code','template_name','create_date','user_full_name','last_modified','last_user_full_name'];
	/**
	 * @var array List CSS styles to be used for generating view HTML
	 * @access protected
	 */
	protected $htmlStyles = [
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
	 *
	 * @return void
	 * @access protected
	 */
	protected function _Init() {
		$this->templateCode = NULL;
	}//END protected function _Init
    /**
     * description
     *
     * @param \NETopes\Core\Data\VirtualEntity|array $field
     * @param array                                  $fParams
     * @param string|null     $themeType
     * @param mixed $fValue
     * @param int  $iCount
     * @return array
     * @throws \NETopes\Core\AppException
     * @access protected
     */
	protected function PrepareRepeatableField(&$field,array &$fParams,?string $themeType,$fValue = NULL,int $iCount = 0): array {
		// NApp::Dlog($iCount,'$iCount');
		// NApp::Dlog($fValue,'$fValues');
		$idInstance = $field->getProperty('id_instance',NULL,'is_integer');
		$tagId = ($idInstance ? $idInstance.'_' : '').$field->getProperty('cell','','is_string').'_'.$field->getProperty('name','','is_string');
		$fValues = explode('|::|',$fValue);
		$field = array_merge($field,[
			'tag_id'=>$tagId.'-0',
			'tag_name'=>$field->getProperty('id',NULL,'is_numeric').'[]',
			'value'=>get_array_value($fValues,0,NULL,'isset'),
		]);
		$fClass = $field->getProperty('class','','is_string');
		$idValuesList = $field->getProperty('id_values_list',0,'is_numeric');
		if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0) {
			$fParams['load_type'] ='database';
			$fParams['data_source'] = [
				'ds_class'=>'Plugins\DForms\ValuesLists',
				'ds_method'=>'GetValues',
				'ds_params'=>['list_id'=>$idValuesList,'for_state'=>1],
			];
		}//if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0)
		$fParams = ControlsHelpers::ReplaceDynamicParams($fParams,$field,TRUE);
		$i_custom_actions = [];
		for($i=1;$i<$iCount;$i++) {
			$tmp_ctrl = $fParams;
			$tmp_ctrl['container'] = 'none';
			$tmp_ctrl['no_label'] = TRUE;
			$tmp_ctrl['label_width'] = NULL;
			$tmp_ctrl['width'] = NULL;
			$tmp_ctrl['tag_id'] = $tagId.'-'.$i;
			$tmp_ctrl['value'] = get_array_value($fValues,$i,NULL,'isset');
			if(strpos($themeType,'bootstrap')!==FALSE) { $tmp_ctrl['class'] .= ' form-control'; }
			$tmp_ctrl['extra_tag_params'] = (isset($tmp_ctrl['extra_tag_params']) && $tmp_ctrl['extra_tag_params'] ? $tmp_ctrl['extra_tag_params'].' ' : '').'data-tid="'.$tagId.'" data-ti="'.$i.'"';
			$i_custom_actions[] = [
				'type'=>$fClass,
				'params'=>$tmp_ctrl,
			];
			$i_custom_actions[] = [
				'type'=>'Button',
				'params'=>[
					'value'=>'&nbsp;'.Translate::GetButton('remove_field'),
					'icon'=>'fa fa-minus-circle',
					'class'=>'clsRepeatableCtrlBtn remove-ctrl-btn',
					'clear_base_class'=>TRUE,
					'onclick'=>"RemoveRepeatableControl(this,'{$tagId}-{$i}')",
				],
			];
		}//END for
		$i_custom_actions[] = [
			'type'=>'Button',
			'params'=>[
				'value'=>Translate::GetButton('add_field'),
				'icon'=>'fa fa-plus-circle',
				'class'=>'clsRepeatCtrlBtn btn btn-default',
				// 'clear_base_class'=>($themeType=='bootstrap3'),
				'onclick'=>"RepeatControl(this,'{$tagId}')",
				'extra_tag_params'=>'data-ract="&nbsp;'.Translate::GetButton('remove_field').'"',
			],
		];
		$fParams['extra_tag_params'] = (isset($fParams['extra_tag_params']) && $fParams['extra_tag_params'] ? $fParams['extra_tag_params'].' ' : '').'data-tid="'.$tagId.'" data-ti="0"';
		$fParams['custom_actions'] = $i_custom_actions;
		// NApp::Dlog($fParams['custom_actions'],'custom_actions');
		return [
			'width'=>$field->getProperty('width','','is_string'),
			'control_type'=>$fClass,
			'control_params'=>$fParams,
		];
	}//protected function PrepareRepeatableField
    /**
     * description
     *
     * @param \NETopes\Core\Data\VirtualEntity|array $field
     * @param array                                  $fParams
     * @param string|null     $themeType
     * @param mixed $fValue
     * @param bool $repeatable
     * @param int  $iCount
     * @return array
     * @access protected
     * @throws \NETopes\Core\AppException
     */
	protected function PrepareField(&$field,array &$fParams,?string $themeType,$fValue = NULL,bool $repeatable = FALSE,int $iCount = 0): array {
		// NApp::Dlog(['$field'=>$field,'$fParams'=>$fParams,'$themeType'=>$themeType,'$fValue'=>$fValue,'$f_rid'=>$f_rid,'$repeat_action'=>$repeat_action],'PrepareField');
		if($repeatable) { return $this->PrepareRepeatableField($field,$fParams,$themeType,$fValue,$iCount); }
		$idInstance = $field->getProperty('id_instance',NULL,'is_integer');
		$tagId = ($idInstance ? $idInstance.'_' : '').$field->getProperty('cell','','is_string').'_'.$field->getProperty('name','','is_string');
		$field = array_merge($field,[
			'tag_id'=>$tagId,
			'tag_name'=>$field->getProperty('id',NULL,'is_numeric'),
			'value'=>$fValue,
		]);
		// if(strlen($themeType)) { $fParams['theme_type'] = $themeType; }
		$fClass = $field->getProperty('class','','is_string');
		if($fClass=='Message') {
			$flabel = $field->getProperty('label','','is_string');
			$fdesc = $field->getProperty('description','','is_string');
			$fParams['text'] = $flabel.$fdesc;
		}//if($fClass=='Message')
		$idValuesList = $field->getProperty('id_values_list',0,'is_numeric');
		if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0) {
			$fParams['load_type'] ='database';
			$fParams['data_source'] = [
				'ds_class'=>'Plugins\DForms\ValuesLists',
				'ds_method'=>'GetValues',
				'ds_params'=>['list_id'=>$idValuesList,'for_state'=>1],
			];
		}//if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0)
		$fParams = ControlsHelpers::ReplaceDynamicParams($fParams,$field,TRUE);
		return [
			'width'=>$field->getProperty('width','','is_string'),
			'control_type'=>$fClass,
			'control_params'=>$fParams,
		];
	}//END protected function PrepareField
    /**
     * Prepare add/edit form/sub-form
     *
     * @param object $mtemplate
     * @param \NETopes\Core\App\Params $params An array of parameters
     * @param int|null   $idInstance
     * @param int|null   $idSubForm
     * @param int|null   $idItem
     * @param int|null   $index
     * @return array Returns BasicForm configuration array
     * @throws \NETopes\Core\AppException
     * @access protected
     */
	protected function PrepareForm($mtemplate,$params = NULL,?int $idInstance = NULL,?int $idSubForm = NULL,?int $idItem = NULL,?int $index = NULL): ?array {
		NApp::Dlog(['$mtemplate'=>$mtemplate,'$idInstance'=>$idInstance,'$idSubForm'=>$idSubForm,'$idItem'=>$idItem,'$index'=>$index],'PrepareForm');
		$idTemplate = $mtemplate->getProperty('id',NULL,'is_integer');
		if(!$idTemplate) { return NULL; }
		if($idSubForm) {
			$template = DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
				'for_id'=>$idSubForm,
				'for_code'=>NULL,
				'instance_id'=>($idInstance ? $idInstance : NULL),
				'for_state'=>1,
			]);
			$idSubForm = get_array_value($template,'id',NULL,'is_integer');
			// NApp::Dlog($idItem,'$idItem');
			// NApp::Dlog($idSubForm,'$idSubForm');
			// NApp::Dlog($template,'$template');
			if(!$idSubForm || !$idItem) { return NULL; }
			$relations = NULL;
			$fields = DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
				'template_id'=>$idTemplate,
				'instance_id'=>($idInstance ? $idInstance : NULL),
				'item_id'=>$idItem,
				'for_index'=>(is_numeric($index) ? $index : NULL),
			]);
			// NApp::Dlog($fields,'$fields');
		} else {
			$template = $mtemplate;
			if($idInstance) {
				$relations = DataProvider::Get('Plugins\DForms\Instances','GetRelations',['instance_id'=>$idInstance]);
			} else {
				$relations = DataProvider::Get('Plugins\DForms\Templates','GetRelations',['template_id'=>$idTemplate]);
			}//if($idInstance)
			$fields = DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
				'template_id'=>$idTemplate,
				'instance_id'=>($idInstance ? $idInstance : NULL),
			]);
			NApp::Dlog($fields,'$fields');
		}//if($idSubForm)
		$themeType = get_array_value($template,'theme_type','','is_string');
		$controlsSize = get_array_value($template,'controls_size','','is_string');
		$separatorWidth = get_array_value($template,'separator_width','','is_string');
		$labelCols = get_array_value($template,'label_cols','','is_string');
		require($this->GetViewFile('PrepareForm'));
		return (isset($ctrl_params) ? $ctrl_params : NULL);
	}//END protected function PrepareForm
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
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
		$fTypes = DataProvider::GetKeyValue('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
		$cModule = $params->safeGet('cmodule',$this->class,'is_notempty_string');
		$cMethod = $params->safeGet('cmethod',call_back_trace(0),'is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		$target = $params->safeGet('target','main-content','is_notempty_string');
		$listingTarget = $target.'_listing';

		$view = new AppView(get_defined_vars(),$this,($target=='main-content' ? 'main' : 'default'));
        $view->SetTitle('');
        $view->SetTargetId($listingTarget);
        $view->AddTableView($this->GetViewFile('Listing'));
        $view->Render();
	}//END public function Listing
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function GlobalListing($params = NULL) {
		$idTemplate = $params->safeGet('for_id',$this->idTemplate,'is_not0_integer');
		$templateCode = $params->safeGet('for_code',$this->templateCode,'is_not0_integer');
		$fTypes = DataProvider::GetKeyValueArray('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
		$listingTarget = 'listing-content';
		$view = new AppView(get_defined_vars(),$this,'main');
        $view->SetTitle('');
        $view->SetTargetId($listingTarget);
        $view->AddTableView($this->GetViewFile('GlobalListing'));
        $view->Render();
	}//END public function GlobalListing
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddEditForm($params = NULL) {
		// NApp::Dlog($params,'ShowAddEditForm');
		$this->templateCode = $params->safeGet('template_code',$this->templateCode,'is_not0_integer');
		$idInstance = $params->safeGet('id',NULL,'is_not0_integer');
		$template = DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
			'for_id'=>NULL,
			'for_code'=>$this->templateCode,
			'instance_id'=>$idInstance,
			'for_state'=>1,
		]);
		if(!is_object($template)) { throw new AppException('Invalid DynamicForm template!'); }
		$idTemplate = $template->getProperty('id',NULL,'is_integer');
		$templateCode = $template->getProperty('code',NULL,'is_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template!'); }
		$ctrl_params = $this->PrepareForm($template,$params,$idInstance);
		if(!$ctrl_params) { throw new AppException('Invalid DynamicForm configuration!'); }
        $cModule = $params->safeGet('cmodule',$this->class,'is_notempty_string');
		$cMethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		$is_modal = $params->safeGet('is_modal',$this->isModal,'is_integer');
		if($is_modal) {
		    $containerType = 'modal';
		    $view = new AppView(get_defined_vars(),$this,$containerType);
		    $view->SetIsModalView(TRUE);
		    $view->SetModalWidth('80%');
		    $view->SetTitle($template->getProperty('name','','is_string'));
		} else {
		    $containerType = $params->safeGet('container_type',($cTarget=='main-content' ? ' main' : NULL),'?is_string');
		    $view = new AppView(get_defined_vars(),$this,$containerType);
		}//if($is_modal)
        $view->AddBasicForm($this->GetViewFile('AddEditInstanceForm'));
        $view->Render();
	}//END public function ShowAddEditForm
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddForm($params = NULL) {
		// NApp::Dlog($params,'ShowAddForm');
		$idTemplate = $params->safeGet('id_template',$this->idTemplate,'is_not0_integer');
		$templateCode = $params->safeGet('template_code',$this->templateCode,'is_not0_integer');
		if(!$idTemplate && !$templateCode) { throw new AppException('Invalid DynamicForm template identifier!'); }
		$template = DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
			'for_id'=>(is_numeric($idTemplate) ? $idTemplate : NULL),
			'for_code'=>(is_numeric($templateCode) ? $templateCode : NULL),
			'instance_id'=>NULL,
			'for_state'=>1,
		]);
		$idTemplate = get_array_value($template,'id',NULL,'is_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template!'); }
		$cModule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cMethod = $params->safeGet('cmethod',call_back_trace(0),'is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		$ctrl_params = $this->PrepareForm($template,$this->GetParams(),NULL);
		if(!$ctrl_params) { throw new AppException('Invalid DynamicForm configuration!'); }
		$is_modal = $params->safeGet('is_modal',$this->isModal,'is_integer');
		$ftitle = $params->safeGet('form_title','&nbsp;','is_string');
		require($this->GetViewFile('AddInstanceForm'));
		if($is_modal) {
			NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".$params->safeGet('nav_item_name','','is_string')."'))");
		}//if($is_modal)
	}//END public function ShowAddForm
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function SaveNewRecord($params = NULL){
		// NApp::Dlog($params,'SaveNewRecord');
		$idTemplate = $params->safeGet('id_template',$this->idTemplate,'is_not0_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template identifier!'); }
		$target = $params->safeGet('target','','is_string');
		$data = $params->safeGet('data',[],'is_array');
		if(!count($data)) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!count($data))
		$error = FALSE;

		$fields = DataProvider::Get('Plugins\DForms\Instances','GetFields',['template_id'=>$idTemplate]);
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
								$fval[$i] = Validator::ValidateValue($fv,NULL,'is_numeric');
								$error = ($field['required']==1 && !is_numeric($fval[$i]));
								break;
							case 'string':
							default:
								$fval[$i] = Validator::ValidateValue($fv,'','is_string');
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

		$relations = DataProvider::Get('Plugins\DForms\Templates','GetRelations',['template_id'=>$idTemplate]);
		foreach($relations as $k=>$rel) {
			$dtype = get_array_value($rel,'dtype','','is_string');
			$relations[$k]['ivalue'] = 0;
			$relations[$k]['svalue'] = '';
			switch($rel['rtype']) {
				case 1:
					$r_val = NApp::GetParam($rel['key']);
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

		$template = DataProvider::Get('Plugins\DForms\Instances','GetTemplate',['for_id'=>$idTemplate]);
		$transaction = \NETopes\Core\AppSession::GetNewUID(get_array_value($template,'code','N/A','is_notempty_string'));
		DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
		try {
			$result = DataProvider::Get('Plugins\DForms\Instances','SetNewInstance',[
				'template_id'=>$idTemplate,
				'user_id'=>NApp::GetCurrentUserId(),
			],['transaction'=>$transaction]);
			$idInstance = get_array_value($result,0,0,'is_numeric','inserted_id');
			if($idInstance<=0) { throw new AppException('Database error on instance insert!'); }

			foreach($fields as $f) {
				if(($f['itype']==2 || $f['parent_itype']==2) && is_array($f['value'])) {
					foreach($f['value'] as $index=>$fValue) {
						$result = DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceValue',[
							'instance_id'=>$idInstance,
							'item_id'=>$f['id'],
							'in_value'=>$fValue,
							'in_name'=>NULL,
							'in_index'=>$index,
						],['transaction'=>$transaction]);
						if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
					}//END foreach
				} else {
					$result = DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceValue',[
						'instance_id'=>$idInstance,
						'item_id'=>$f['id'],
						'in_value'=>(isset($f['value']) ? $f['value'] : NULL),
						'in_name'=>NULL,
						'in_index'=>NULL,
					],['transaction'=>$transaction]);
					if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
				}//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
			}//END foreach

			foreach($relations as $r) {
				$result = DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceRelation',[
					'instance_id'=>$idInstance,
					'relation_id'=>$r['id'],
					'in_ivalue'=>$r['ivalue'],
					'in_svalue'=>$r['svalue'],
				],['transaction'=>$transaction]);
				if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
			}//END foreach

			DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,FALSE);
		} catch(AppException $e) {
			DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,TRUE);
			NApp::Elog($e->getMessage());
			throw $e;
		}//END try
		if($params->safeGet('is_modal',$this->isModal,'is_numeric')==1) { $this->CloseForm(); }
		$cModule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cMethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		NApp::Ajax()->Execute("AjaxRequest('{$cModule}','{$cMethod}','id_template'|{$idTemplate},'{$cTarget}')->{$cTarget}");
	}//END public function SaveNewRecord
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowEditForm($params = NULL) {
		// NApp::Dlog($params,'ShowEditForm');
		$idInstance = $params->safeGet('id',NULL,'is_not0_integer');
		if(!$idInstance) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$template = DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
			'for_id'=>NULL,
			'for_code'=>NULL,
			'instance_id'=>$idInstance,
			'for_state'=>1,
		]);
		$idTemplate = get_array_value($template,'id',NULL,'is_integer');
		$templateCode = get_array_value($template,'code',NULL,'is_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template!'); }
		$cModule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cMethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		$ctrl_params = $this->PrepareForm($template,$this->GetParams(),$idInstance);
		if(!$ctrl_params) { throw new AppException('Invalid DynamicForm configuration!'); }
		$is_modal = $params->safeGet('is_modal',$this->isModal,'is_integer');
		require($this->GetViewFile('EditInstanceForm'));
		if($is_modal) {
			NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".Translate::GetButton('edit')."'))");
		}//if($is_modal)
	}//END public function ShowEditForm
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function SaveRecord($params = NULL) {
		// NApp::Dlog($params,'SaveRecord');
		$idTemplate = $params->safeGet('id_template',$this->idTemplate,'is_not0_integer');
		$idInstance = $params->safeGet('id',NULL,'is_not0_integer');
		if(!$idTemplate || !$idInstance) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$target = $params->safeGet('target','','is_string');
		$data = $params->safeGet('data',[],'is_array');
		if(!count($data)) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!count($data))
		$error = FALSE;
		$fields = DataProvider::Get('Plugins\DForms\Instances','GetFields',['template_id'=>$idTemplate,'instance_id'=>$idInstance]);
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
								$fval[$i] = Validator::ValidateValue($fv,NULL,'is_numeric');
								$error = ($field['required']==1 && !is_numeric($fval[$i]));
								break;
							case 'string':
							default:
								$fval[$i] = Validator::ValidateValue($fv,'','is_string');
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

		// $relations = DataProvider::Get('Plugins\DForms\Instances','GetRelations',['template_id'=>$idTemplate,'instance_id'=>$idInstance));
		// foreach($relations as $k=>$rel) {
		// 	$dtype = get_array_value($rel,'dtype','','is_string');
		// 	$relations[$k]['ivalue'] = 0;
		// 	$relations[$k]['svalue'] = '';
		// 	switch($rel['rtype']) {
		// 		case 1:
		// 			$r_val = NApp::GetParam($rel['key']);
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

		$template = DataProvider::Get('Plugins\DForms\Instances','GetTemplate',['for_id'=>$idTemplate]);
		$transaction = \NETopes\Core\AppSession::GetNewUID(get_array_value($template,'code','N/A','is_notempty_string'));
		DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
		try {
			$result = DataProvider::Get('Plugins\DForms\Instances','UnsetInstanceValues',['instance_id'=>$idInstance],['transaction'=>$transaction]);
			if($result===FALSE) { throw new AppException('Database error on instance update!'); }

			foreach($fields as $f) {
				if(in_array($f['class'],['','FormTitle','FormSubTitle','FormSeparator','Message','BasicForm'])) { continue; }
				if(($f['itype']==2 || $f['parent_itype']==2) && is_array($f['value'])) {
					foreach($f['value'] as $index=>$fValue) {
						$result = DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceValue',[
							'instance_id'=>$idInstance,
							'item_id'=>$f['id'],
							'in_value'=>$fValue,
							'in_name'=>NULL,
							'in_index'=>$index,
						],['transaction'=>$transaction]);
						if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
					}//END foreach
				} else {
					$result = DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceValue',[
						'instance_id'=>$idInstance,
						'item_id'=>$f['id'],
						'in_value'=>(isset($f['value']) ? $f['value'] : NULL),
						'in_name'=>NULL,
						'in_index'=>NULL,
					],['transaction'=>$transaction]);
					if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
				}//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
			}//END foreach

			// foreach($relations as $r) {
			// 	$result = DataProvider::Get('Plugins\DForms\Instances','SetNewInstanceRelation',[
			// 		'instance_id'=>$idInstance,
			// 		'relation_id'=>$r['id'],
			// 		'in_ivalue'=>$r['ivalue'],
			// 		'in_svalue'=>$r['svalue'],
			// 	),['transaction'=>$transaction]);
			// 	if(get_array_value($result,0,0,'is_integer','inserted_id')<=0) { throw new AppException('Database error on instance value insert!'); }
			// }//END foreach

			DataProvider::Get('Plugins\DForms\Instances','SetInstanceState',[
				'for_id'=>$idInstance,
				'user_id'=>NApp::GetCurrentUserId(),
			],['transaction'=>$transaction]);
			DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,FALSE);
		} catch(AppException $e) {
			DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,TRUE);
			NApp::Elog($e->getMessage());
			throw $e;
		}//END try
		if($params->safeGet('is_modal',$this->isModal,'is_numeric')==1) { $this->CloseForm(); }
		$cModule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cMethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		NApp::Ajax()->Execute("AjaxRequest('{$cModule}','{$cMethod}','id_template'|{$idTemplate},'{$cTarget}')->{$cTarget}");
	}//END public function SaveRecord
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function SaveInstance($params = NULL) {
		$idInstance = $params->safeGet('id',0,'is_integer');
		if($idInstance>0) {
			$this->Exec('SaveRecord',$params);
		} else {
			$this->Exec('SaveNewRecord',$params);
		}//if($idInstance>0)
	}//END public function SaveInstance
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteRecord($params = NULL) {
	    $id = $params->getOrFail('id','is_not0_integer','Invalid record identifier!');
		$idTemplate = $params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
		$result = DataProvider::Get('Plugins\DForms\Instances','UnsetInstance',['for_id'=>$id]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$cModule = $params->safeGet('cmodule',get_called_class(),'is_notempty_string');
		$cMethod = $params->safeGet('cmethod','Listing','is_notempty_string');
		$cTarget = $params->safeGet('ctarget','main-content','is_notempty_string');
		ModulesProvider::Exec($cModule,$cMethod,['id_template'=>$idTemplate,'target'=>$cTarget],$cTarget);
	}//END public function DeleteRecord
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function EditRecordState($params = NULL) {
		$id = $params->safeGet('id',NULL,'is_not0_integer');
		if(!$id) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$result = DataProvider::Get('Plugins\DForms\Instances','SetInstanceState',[
			'for_id'=>$id,
			'in_state'=>$params->safeGet('state',NULL,'is_integer'),
			'user_id'=>NApp::GetCurrentUserId(),
		]);
		if($result===FALSE) { throw new AppException('Failed database operation!'); }
	}//END public function EditRecordState
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowViewForm($params = NULL) {
		// NApp::Dlog($params,'ShowViewForm');
		$idInstance = $params->getOrFail('id','is_not0_integer','Invalid instance identifier!');
		$instance = DataProvider::Get('Plugins\DForms\Instances','GetInstanceItem',['for_id'=>$idInstance]);
		$idTemplate = $instance->getProperty('id_template',$this->idTemplate,'is_integer');
		if(!$idTemplate) { throw new AppException('Invalid DynamicForm template!'); }
		$is_modal = $params->safeGet('is_modal',$this->isModal,'is_integer');
		require($this->GetViewFile('ViewInstanceForm'));
		if($is_modal) {
			NApp::Ajax()->ExecuteJs("ShowModalForm('90%',($('#page-title').html()+' - ".Translate::GetButton('view')."'))");
		}//if($is_modal)
	}//END public function ShowViewForm
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function PrepareFormHtml($params = NULL) {
		// NApp::Dlog($params,'PrepareFormHtml');
		$idInstance = $params->safeGet('id',NULL,'is_integer');
		if(!$idInstance) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$idSubForm = $params->safeGet('id_sub_form',0,'is_integer');
		$idItem = $params->safeGet('id_item',0,'is_integer');
		$index = $params->safeGet('index',0,'is_integer');
		$output = $params->safeGet('output',FALSE,'bool');
		if($idSubForm) {
			$instance = DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
				'for_id'=>$idSubForm,
				'for_code'=>NULL,
				'instance_id'=>$idInstance,
				'for_state'=>1,
			]);
			$idSubForm = get_array_value($instance,'id',NULL,'is_integer');
			// NApp::Dlog($idItem,'$idItem');
			// NApp::Dlog($idSubForm,'$idSubForm');
			// NApp::Dlog($template,'$template');
			if(!$idSubForm || !$idItem) { return NULL; }
			$relations = NULL;
			$fields = DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
				'instance_id'=>$idInstance,
				'item_id'=>$idItem,
				'for_index'=>(is_numeric($index) ? $index : NULL),
			]);
			// NApp::Dlog($fields,'$fields');
		} else {
			$instance = DataProvider::Get('Plugins\DForms\Instances','GetInstanceItem',['for_id'=>$idInstance]);
			$relations = DataProvider::Get('Plugins\DForms\Instances','GetRelations',['instance_id'=>$idInstance]);
			$fields = DataProvider::Get('Plugins\DForms\Instances','GetStructure',['instance_id'=>$idInstance]);
		}
		$themeType = get_array_value($instance,'theme_type','','is_string');
		$controlsSize = get_array_value($instance,'controls_size','','is_string');
		$separatorWidth = get_array_value($instance,'separator_width','','is_string');
		$labelCols = get_array_value($instance,'label_cols','','is_string');
		$html = NULL;
		require($this->GetViewFile('PrepareFormHtml'));
		return $html;
	}//END public function PrepareFormHtml
	/**
	 * description
	 *
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return mixed return description
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function GetInstancePdf($params = NULL) {
		NApp::Dlog($params,'GetInstancePdf');
		$idInstance = $params->safeGet('id',NULL,'is_integer');
		if(!$idInstance) { throw new AppException('Invalid DynamicForm instance identifier!'); }
		$cache = $params->safeGet('cache',TRUE,'bool');
		$result_type = $params->safeGet('result_type',0,'is_integer');
		$instance = DataProvider::Get('Plugins\DForms\Instances','GetInstanceItem',['for_id'=>$idInstance]);
		$filename = get_array_value($instance,'uid','','is_string');
		if(!strlen($filename)) {
			$filename = date('Y-m-d_H-i-s').'.pdf';
		} else {
			$filename = str_replace(' ','_',trim($filename)).'.pdf';
		}//if(!strlen($filename))
		$category = get_array_value($instance,'category',get_array_value($instance,'template_code',$this->name,'is_notempty_string'),'is_notempty_string');
		if($cache && strlen($filename) && file_exists(NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename)) {
			if($result_type==1) {
				$data = [
					'file_name'=>$filename,
					'path'=>NAPP::GetRepositoryPath().'forms/'.$category.'/',
					'download_name'=>$filename,
				];
			} else {
				$data = file_get_contents(NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename);
			}//if($result_type==1)
			return $data;
		}//if($cache && strlen($filename) && file_exists(NAPP::GetRepositoryPath().$company.'/'.$filename))
		if($cache) {
			if(!file_exists(NAPP::GetRepositoryPath().'forms')) { mkdir(NAPP::GetRepositoryPath().'forms',755); }
			if(!file_exists(NAPP::GetRepositoryPath().'forms/'.$category)) { mkdir(NAPP::GetRepositoryPath().'forms/'.$category,755); }
		}//if($cache)
		$html_data = $this->Exec('PrepareFormHtml',['id'=>$idInstance]);
		$pdfdoc = new InstancesPdf(['html_data'=>$html_data,'file_name'=>$filename]);
		if($cache) {
			// file_put_contents(NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename,$data);
			$pdfdoc->Output(['output_type'=>'F','file_name'=>NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename]);
			if($result_type==1) {
				$data = [
					'file_name'=>$filename,
					'path'=>NAPP::GetRepositoryPath().'forms/'.$category.'/',
					'download_name'=>$filename,
				];
			} else {
				$data = file_get_contents(NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename);
			}//if($result_type==1)
		} else {
			$data = $pdfdoc->Output(['base64'=>FALSE,'file_name'=>NAPP::GetRepositoryPath().'forms/'.$category.'/'.$filename]);
		}//if($cache)
		return $data;
	}//END public function GetInstancePdf
}//END class Instances extends Module