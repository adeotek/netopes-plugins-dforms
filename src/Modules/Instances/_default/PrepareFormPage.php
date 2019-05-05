<?php
use NETopes\Core\Controls\BasicForm;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\HiddenInput;
use NETopes\Core\AppException;

/** @var \NETopes\Core\App\Params $params */
/** @var \NETopes\Core\Data\VirtualEntity $template */
/** @var \NETopes\Core\Data\DataSet $relations */
/** @var \NETopes\Core\Data\VirtualEntity $page */
/** @var \NETopes\Core\Data\DataSet $fields */
/** @var bool $multiPage */
/** @var string $tName */
/** @var int|null $idInstance */
/** @var int|null $idSubForm */
/** @var int|null $index */
/** @var string|null $themeType */
/** @var int|null $labelCols */
/** @var string|null $controlsSize */

$pIndex=$page->getProperty('pindex');
$colsNo=$page->getProperty('colsno');
$iPrefix=($idInstance ? $idInstance.'_' : '');
$columnsToSkip=0;
$formContent=[];
foreach($fields as $field) {
    $row=$field->getProperty('frow',0,'is_numeric');
    if(!$row) {
        continue;
    }
    if(!isset($formContent[$row])) {
        $formContent[$row]=[];
        $columnsToSkip=0;
    }//if(!isset($formContent[$row]))
    $fClass=$field->getProperty('class','','is_string');
    // if($idSubForm) { NApp::Dlog($field,$fClass); }
    $fParamsStr=$field->getProperty('params','','is_string');
    $fParams=strlen($fParamsStr) ? json_decode($fParamsStr,TRUE) : [];
    switch($fClass) {
        case 'FormTitle':
            $formContent[$row]=[
                'separator'=>'title',
                'value'=>$field->getProperty('label','','is_string'),
                'class'=>get_array_value($fParams,'class','','is_string'),
            ];
            $columnsToSkip=$colsNo - 1;
            break;
        case 'FormSubTitle':
            $formContent[$row]=[
                'separator'=>'subtitle',
                'value'=>$field->getProperty('label','','is_string'),
                'class'=>get_array_value($fParams,'class','','is_string'),
            ];
            $columnsToSkip=$colsNo - 1;
            break;
        case 'FormSeparator':
            $formContent[$row]=['separator'=>'separator'];
            $columnsToSkip=$colsNo - 1;
            break;
        case 'BasicForm':
            $colSpan=$field->getProperty('colspan',0,'is_integer');
            if($colSpan>1) {
                $columnsToSkip=$colSpan - 1;
            }
            $fParams=['value'=>''];
            $tagId=$iPrefix.$field->getProperty('cell','','is_string').'_'.$field->getProperty('name','','is_string').($index ? '_'.$index : '');
            $fIType=$field->getProperty('itype',1,'is_not0_numeric');
            $idSubForm=$field->getProperty('id_sub_form',-1,'is_not0_numeric');
            $idItem=$field->getProperty('id',NULL,'is_not0_numeric');
            if($fIType==2 && $idInstance) {
                $fICount=$field->getProperty('icount',1,'is_not0_numeric');
                // $fValue = $field->getProperty('ivalues',NULL,'is_string');
            } else {
                $fICount=1;
                // $fValue = NULL;
            }//if($fIType==2 && $idInstance)
            for($i=0; $i<$fICount; $i++) {
                $ctrl_params=$this->PrepareForm($params,$template,$idInstance,$idSubForm,$idItem,$i);
                if(!$ctrl_params) {
                    throw new AppException('Invalid DynamicForm sub-form configuration!');
                }
                $ctrl_params['sub_form_tag_id']=$tagId.'-'.$i;
                if($fIType==2) {
                    $ctrl_params['tags_ids_sufix']='-'.$i;
                    $ctrl_params['tags_names_sufix']='[]';
                    $ctrl_params['sub_form_class']='clsRepeatableField';
                    $ctrl_params['sub_form_extra_tag_params']='data-tid="'.$tagId.'" data-ti="'.$i.'"';
                }//if($fIType==2)
                // NApp::Dlog($ctrl_params,'$ctrl_params');
                $basicForm=new BasicForm($ctrl_params);
                $fParams['value'].=$basicForm->Show();
                // NApp::Dlog($fParams['value'],'fcontent');
                if($i>0) {
                    $ctrl_ract=new Button(['value'=>Translate::GetButton('remove_field'),'icon'=>'fa fa-minus-circle','class'=>NApp::$theme->GetBtnWarningClass('clsRepeatableCtrlBtn'),'clear_base_class'=>TRUE,'onclick'=>"RemoveRepeatableForm(this,'{$tagId}-{$i}')"]);
                    $fParams['value'].=$ctrl_ract->Show();
                }//if($i>0)
            }//END for
            if($fIType==2) {
                $ctrl_ract=new Button(['value'=>Translate::GetButton('add_element'),'icon'=>'fa fa-plus-circle','class'=>NApp::$theme->GetBtnDefaultClass('clsRepeatCtrlBtn'),'onclick'=>"RepeatForm(this,'{$tagId}')",'extra_tag_params'=>'data-ract="'.Translate::GetButton('remove_element').'" data-ract-class="'.NApp::$theme->GetBtnWarningClass('clsRepeatableCtrlBtn').'"']);
                $fParams['value'].=$ctrl_ract->Show();
            }//if($fIType==2)
            $formContent[$row][]=[
                'width'=>$field->getProperty('width','','is_string'),
                'control_type'=>'CustomControl',
                'control_params'=>$fParams,
            ];
            break;
        default:
            if(!is_array($fParams) || !count($fParams)) {
                if($columnsToSkip>0) {
                    $columnsToSkip--;
                    continue 2;
                }//if($columnsToSkip>0)
                $formContent[$row][]=[];
            } else {
                $colSpan=$field->getProperty('colspan',0,'is_integer');
                if($colSpan>1) {
                    $columnsToSkip=$colSpan - 1;
                }
                $fIType=$field->getProperty('itype',1,'is_not0_numeric');
                if($fIType==2) {
                    if($idInstance) {
                        $fICount=$field->getProperty('icount',0,'is_numeric');
                        $fValue=$field->getProperty('ivalues',NULL,'is_string');
                    } else {
                        $fICount=0;
                        $fValue=NULL;
                    }//if($idInstance)
                    $formContent[$row][]=$this->PrepareField($field,$fParams,$fValue,$themeType,TRUE,$fICount);
                } else {
                    $fValue=NULL;
                    if($idInstance) {
                        $fValue=$field->getProperty('ivalues',NULL,'is_string');
                    }
                    $formContent[$row][]=$this->PrepareField($field,$fParams,$fValue,$themeType);
                }//if($fIType==2)
            }//if(!is_array($fParams) || !count($fParams))
            break;
    }//END switch
}//END foreach

if(is_iterable($relations) && count($relations)) {
    $fParams=['value'=>''];
    foreach($relations as $rel) {
        if($rel->getProperty('rtype')!=30) {
            continue;
        }
        //Programatically (input parameter)
        $rValue=$params->safeGet($rel->getProperty('key'),NULL,'?isset');
        if(is_null($rValue)) {
            continue;
        }
        $rctrl=new HiddenInput(['tag_id'=>'relation-'.$rel->getProperty('key'),'postable'=>TRUE,'value'=>$rValue]);
        $fParams['value'].=$rctrl->Show();
    }//END foreach
    if(strlen($fParams['value'])) {
        $formContent[]=[[
            'hidden_row'=>TRUE,
            'control_type'=>'CustomControl',
            'control_params'=>$fParams,
        ]];
    }//if(strlen($rel_content))
}//if(is_iterable($relations) && count($relations))

if($multiPage) {
    $ctrl_params=[
        'type'=>'fixed',
        'uid'=>$tName.'-'.$pIndex,
        'name'=>$page->getProperty('tr_title'),
        'content_type'=>'control',
        'content'=>[
            'control_type'=>'BasicForm',
            'control_params'=>[
                'tag_id'=>'df_'.$tName.'_'.$pIndex.'_form',
                'response_target'=>'df_'.$tName.'_'.$pIndex.'_errors',
                'cols_no'=>$colsNo,
            ],
        ],
    ];
    if(strlen($themeType)) {
        $ctrl_params['content']['control_params']['theme_type']=$themeType;
    }
    if(is_numeric($labelCols) && $labelCols>=1 && $labelCols<=12) {
        $ctrl_params['content']['control_params']['label_cols']=$labelCols;
    }
    if(strlen($controlsSize)) {
        $ctrl_params['content']['control_params']['controls_size']=$controlsSize;
    }
    $ctrl_params['content']['control_params']['content']=$formContent;
} else {
    $ctrl_params=[
        'control_class'=>'BasicForm',
        'tname'=>$tName,
        'tag_id'=>'df_'.$tName.'_form',
        'response_target'=>'df_'.$tName.'_errors',
        'cols_no'=>$colsNo,
    ];
    if(strlen($themeType)) {
        $ctrl_params['theme_type']=$themeType;
    }
    if(is_numeric($labelCols) && $labelCols>=1 && $labelCols<=12) {
        $ctrl_params['label_cols']=$labelCols;
    }
    if(strlen($controlsSize)) {
        $ctrl_params['controls_size']=$controlsSize;
    }
    $ctrl_params['content']=$formContent;
}//if($multiPage)
