<?php
use NETopes\Core\Controls\BasicForm;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\HiddenInput;
use NETopes\Core\AppException;

if(is_array($fields) && count($fields)) {
    $iprefix = ($id_instance ? $id_instance.'_' : '');
    $tname = $iprefix.$idTemplate.'_'.$id_sub_form;
    $form_content = [];
    foreach($fields as $field) {
        $row = get_array_value($field,'frow',0,'is_numeric');
        if(!$row) { continue; }
        if(!isset($form_content[$row])) { $form_content[$row] = []; }
        $fclass = get_array_value($field,'class','','is_string');
        // if($id_sub_form) { NApp::_Dlog($field,$fclass); }
        $fparams = get_array_value($field,'params','','is_string');
        $f_params = strlen($fparams) ? @unserialize($fparams) : [];
        switch($fclass) {
            case 'FormTitle':
                $form_content[$row] = array(
                    'separator'=>'title',
                    'value'=>get_array_value($field,'label','','is_string'),
                    'class'=>get_array_value($f_params,'class','','is_string'),
                );
                break;
            case 'FormSubTitle':
                $form_content[$row] = array(
                    'separator'=>'subtitle',
                    'value'=>get_array_value($field,'label','','is_string'),
                    'class'=>get_array_value($f_params,'class','','is_string'),
                );
                break;
            case 'FormSeparator':
                $form_content[$row] = array('separator'=>'separator');
                break;
            case 'BasicForm':
                $f_params = ['value'=>''];
                $tagid = $iprefix.get_array_value($field,'cell','','is_string').'_'.get_array_value($field,'name','','is_string').($index ? '_'.$index : '');
                $f_itype = get_array_value($field,'itype',1,'is_not0_numeric');
                $id_sub_form = get_array_value($field,'id_sub_form',-1,'is_not0_numeric');
                $id_item = get_array_value($field,'id',NULL,'is_not0_numeric');
                if($f_itype==2 && $id_instance) {
                    $f_icount = get_array_value($field,'icount',1,'is_not0_numeric');
                    // $f_value = get_array_value($field,'ivalues',NULL,'is_string');
                } else {
                    $f_icount = 1;
                    // $f_value = NULL;
                }//if($f_itype==2 && $id_instance)
                for($i=0;$i<$f_icount;$i++) {
                    $ctrl_params = $this->PrepareForm($template,$params,$id_instance,$id_sub_form,$id_item,$i);
                    if(!$ctrl_params) { throw new AppException('Invalid DynamicForm sub-form configuration!'); }
                    $ctrl_params['sub_form_tagid'] = $tagid.'-'.$i;
                    if($f_itype==2) {
                        $ctrl_params['tags_ids_sufix'] = '-'.$i;
                        $ctrl_params['tags_names_sufix'] = '[]';
                        $ctrl_params['sub_form_class'] = 'clsRepeatableField';
                        $ctrl_params['sub_form_extratagparam'] = 'data-tid="'.$tagid.'" data-ti="'.$i.'"';
                    }//if($f_itype==2)
                    // NApp::_Dlog($ctrl_params,'$ctrl_params');
                    $basicform = new BasicForm($ctrl_params);
                    $f_params['value'] .= $basicform->Show();
                    // NApp::_Dlog($f_params['value'],'fcontent');
                    if($i>0) {
                        $ctrl_ract = new Button(['value'=>'&nbsp;'.Translate::GetButton('remove_field'),'icon'=>'fa fa-minus-circle','class'=>'clsRepeatableCtrlBtn remove-ctrl-btn','clear_base_class'=>TRUE,'onclick'=>"RemoveRepeatableControl(this,'{$tagid}-{$i}')"]);
                        $f_params['value'] .= $ctrl_ract->Show();
                    }//if($i>0)
                }//END for
                if($f_itype==2) {
                    $ctrl_ract = new Button(['value'=>Translate::GetButton('add_element'),'icon'=>'fa fa-plus-circle','class'=>'clsRepeatCtrlBtn btn btn-default','onclick'=>"RepeatControl(this,'{$tagid}')",'extratagparam'=>'data-ract="&nbsp;'.Translate::GetButton('remove_element').'"']);
                    $f_params['value'] .= $ctrl_ract->Show();
                }//if($f_itype==2)
                $form_content[$row][] = [
                    'width'=>get_array_value($field,'width','','is_string'),
                    'control_type'=>'CustomControl',
                    'control_params'=>$f_params,
                ];
                break;
            default:
                if(!is_array($f_params) || !count($f_params)) {
                    if(strlen(get_array_value($form_content,$row,'','is_string','separator'))) { continue; }
                    $form_content[$row][] = [];
                } else {
                    $f_itype = get_array_value($field,'itype',1,'is_not0_numeric');
                    if($f_itype==2) {
                        if($id_instance) {
                            $f_icount = get_array_value($field,'icount',0,'is_numeric');
                            $f_value = get_array_value($field,'ivalues',NULL,'is_string');
                        } else {
                            $f_icount = 0;
                            $f_value = NULL;
                        }//if($id_instance)
                        $form_content[$row][] = $this->PrepareField($field,$f_params,$theme_type,$f_value,TRUE,$f_icount);
                    } else {
                        $f_value = NULL;
                        if($id_instance) { $f_value = get_array_value($field,'ivalues',NULL,'is_string'); }
                        $form_content[$row][] = $this->PrepareField($field,$f_params,$theme_type,$f_value);
                    }//if($f_itype==2)
                }//if(!is_array($f_params) || !count($f_params))
                break;
        }//END switch
    }//END foreach

    if(is_array($relations) && count($relations)) {
        $f_params = ['value'=>''];
        foreach($relations as $rel) {
            switch($rel['rtype']) {
                case 3://Programatically (input parameter)
                    $r_value = get_array_value($params,$rel['key'],NULL,'is_string');
                    break;
                case 1: //AUTO (from SESSION)
                case 2: //Form input
                default:
                    $r_value = NULL;
            }//END switch
            if(is_null($r_value)) { continue; }
            $rctrl = new HiddenInput(['tag_id'=>'relation-'.$rel['key'],'postable'=>TRUE,'value'=>$r_value]);
            $f_params['value'] .= $rctrl->Show();
        }//END foreach
        if(strlen($f_params['value'])) {
            $form_content[] = [[
                'hidden_row'=>TRUE,
                'control_type'=>'CustomControl',
                'control_params'=>$f_params,
            ]];
        }//if(strlen($rel_content))
    }//if(is_array($relations) && count($relations))

    $ctrl_params = [
        'tname'=>$tname,
        'tag_id'=>'df_'.$tname.'_form',
        'response_target'=>'df_'.$tname.'_errors',
        'cols_no'=>get_array_value($template,'colsno',1,'is_not0_numeric'),
    ];
    if(strlen($theme_type)) { $ctrl_params['theme_type'] = $theme_type; }
    if(is_numeric($label_cols) && $label_cols>=1 && $label_cols<=12) { $ctrl_params['label_cols'] = $label_cols; }
    if(strlen($controls_size)) { $ctrl_params['controls_size'] = $controls_size; }
    $ctrl_params['content'] = $form_content;
} else {
    $ctrl_params = NULL;
}//if(is_array($fields) && count($fields))