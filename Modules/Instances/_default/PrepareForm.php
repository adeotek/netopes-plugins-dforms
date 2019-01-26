<?php
use NETopes\Core\Controls\BasicForm;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\HiddenInput;
use NETopes\Core\AppException;

/** @var \NETopes\Core\App\Params $params */
/** @var \NETopes\Core\Data\DataSet $fields */
/** @var \NETopes\Core\Data\DataSet $relations */
/** @var \NETopes\Core\Data\VirtualEntity $template */
/** @var int|null $idInstance */
/** @var int|null $idTemplate */
/** @var int|null $idSubForm */
/** @var string|null $themeType */
/** @var int|null $labelCols */
/** @var string|null $controlsSize */

if(is_iterable($fields) && count($fields)) {
    $iprefix = ($idInstance ? $idInstance.'_' : '');
    $tName = $iprefix.$idTemplate.'_'.$idSubForm;
    $formContent = [];
    foreach($fields as $field) {
        $row = $field->getProperty('frow',0,'is_numeric');
        if(!$row) { continue; }
        if(!isset($formContent[$row])) { $formContent[$row] = []; }
        $fClass = $field->getProperty('class','','is_string');
        // if($idSubForm) { NApp::Dlog($field,$fClass); }
        $fparams = $field->getProperty('params','','is_string');
        $fParams = strlen($fparams) ? @unserialize($fparams) : [];
        switch($fClass) {
            case 'FormTitle':
                $formContent[$row] = array(
                    'separator'=>'title',
                    'value'=>$field->getProperty('label','','is_string'),
                    'class'=>get_array_value($fParams,'class','','is_string'),
                );
                break;
            case 'FormSubTitle':
                $formContent[$row] = array(
                    'separator'=>'subtitle',
                    'value'=>$field->getProperty('label','','is_string'),
                    'class'=>get_array_value($fParams,'class','','is_string'),
                );
                break;
            case 'FormSeparator':
                $formContent[$row] = array('separator'=>'separator');
                break;
            case 'BasicForm':
                $fParams = ['value'=>''];
                $tagId = $iprefix.$field->getProperty('cell','','is_string').'_'.$field->getProperty('name','','is_string').($index ? '_'.$index : '');
                $f_itype = $field->getProperty('itype',1,'is_not0_numeric');
                $idSubForm = $field->getProperty('id_sub_form',-1,'is_not0_numeric');
                $idItem = $field->getProperty('id',NULL,'is_not0_numeric');
                if($f_itype==2 && $idInstance) {
                    $f_icount = $field->getProperty('icount',1,'is_not0_numeric');
                    // $fValue = $field->getProperty('ivalues',NULL,'is_string');
                } else {
                    $f_icount = 1;
                    // $fValue = NULL;
                }//if($f_itype==2 && $idInstance)
                for($i=0;$i<$f_icount;$i++) {
                    $ctrl_params = $this->PrepareForm($template,$params,$idInstance,$idSubForm,$idItem,$i);
                    if(!$ctrl_params) { throw new AppException('Invalid DynamicForm sub-form configuration!'); }
                    $ctrl_params['sub_form_tagid'] = $tagId.'-'.$i;
                    if($f_itype==2) {
                        $ctrl_params['tags_ids_sufix'] = '-'.$i;
                        $ctrl_params['tags_names_sufix'] = '[]';
                        $ctrl_params['sub_form_class'] = 'clsRepeatableField';
                        $ctrl_params['sub_form_extratagparam'] = 'data-tid="'.$tagId.'" data-ti="'.$i.'"';
                    }//if($f_itype==2)
                    // NApp::Dlog($ctrl_params,'$ctrl_params');
                    $basicform = new BasicForm($ctrl_params);
                    $fParams['value'] .= $basicform->Show();
                    // NApp::Dlog($fParams['value'],'fcontent');
                    if($i>0) {
                        $ctrl_ract = new Button(['value'=>'&nbsp;'.Translate::GetButton('remove_field'),'icon'=>'fa fa-minus-circle','class'=>'clsRepeatableCtrlBtn remove-ctrl-btn','clear_base_class'=>TRUE,'onclick'=>"RemoveRepeatableControl(this,'{$tagId}-{$i}')"]);
                        $fParams['value'] .= $ctrl_ract->Show();
                    }//if($i>0)
                }//END for
                if($f_itype==2) {
                    $ctrl_ract = new Button(['value'=>Translate::GetButton('add_element'),'icon'=>'fa fa-plus-circle','class'=>'clsRepeatCtrlBtn btn btn-default','onclick'=>"RepeatControl(this,'{$tagId}')",'extratagparam'=>'data-ract="&nbsp;'.Translate::GetButton('remove_element').'"']);
                    $fParams['value'] .= $ctrl_ract->Show();
                }//if($f_itype==2)
                $formContent[$row][] = [
                    'width'=>$field->getProperty('width','','is_string'),
                    'control_type'=>'CustomControl',
                    'control_params'=>$fParams,
                ];
                break;
            default:
                if(!is_array($fParams) || !count($fParams)) {
                    if(strlen(get_array_value($formContent,[$row,'separator'],'','is_string'))) { continue; }
                    $formContent[$row][] = [];
                } else {
                    $f_itype = $field->getProperty('itype',1,'is_not0_numeric');
                    if($f_itype==2) {
                        if($idInstance) {
                            $f_icount = $field->getProperty('icount',0,'is_numeric');
                            $fValue = $field->getProperty('ivalues',NULL,'is_string');
                        } else {
                            $f_icount = 0;
                            $fValue = NULL;
                        }//if($idInstance)
                        $formContent[$row][] = $this->PrepareField($field,$fParams,$themeType,$fValue,TRUE,$f_icount);
                    } else {
                        $fValue = NULL;
                        if($idInstance) { $fValue = $field->getProperty('ivalues',NULL,'is_string'); }
                        $formContent[$row][] = $this->PrepareField($field,$fParams,$themeType,$fValue);
                    }//if($f_itype==2)
                }//if(!is_array($fParams) || !count($fParams))
                break;
        }//END switch
    }//END foreach

    if(is_iterable($relations) && count($relations)) {
        $fParams = ['value'=>''];
        foreach($relations as $rel) {
            switch($rel['rtype']) {
                // case 10: //Form input
                    // $rValue = $params->safeGet($rel['key'],NULL,'?isset');
                    // break;
                case 20: //AUTO (from SESSION)
                    $rValue = NApp::GetParam($rel['key']);
                    break;
                case 21: //AUTO (from SESSION)
                    $rValue = NApp::GetPageParam($rel['key']);
                    break;
                case 30://Programatically (input parameter)
                    $rValue = $params->safeGet($rel['key'],NULL,'?isset');
                    break;
                default:
                    $rValue = NULL;
            }//END switch
            if(is_null($rValue)) { continue; }
            $rctrl = new HiddenInput(['tag_id'=>'relation-'.$rel['key'],'postable'=>TRUE,'value'=>$rValue]);
            $fParams['value'] .= $rctrl->Show();
        }//END foreach
        if(strlen($fParams['value'])) {
            $formContent[] = [[
                'hidden_row'=>TRUE,
                'control_type'=>'CustomControl',
                'control_params'=>$fParams,
            ]];
        }//if(strlen($rel_content))
    }//if(is_iterable($relations) && count($relations))

    $ctrl_params = [
        'tname'=>$tName,
        'tag_id'=>'df_'.$tName.'_form',
        'response_target'=>'df_'.$tName.'_errors',
        'cols_no'=>get_array_value($template,'colsno',1,'is_not0_numeric'),
    ];
    if(strlen($themeType)) { $ctrl_params['theme_type'] = $themeType; }
    if(is_numeric($labelCols) && $labelCols>=1 && $labelCols<=12) { $ctrl_params['label_cols'] = $labelCols; }
    if(strlen($controlsSize)) { $ctrl_params['controls_size'] = $controlsSize; }
    $ctrl_params['content'] = $formContent;
} else {
    $ctrl_params = NULL;
}//if(is_iterable($fields) && count($fields))