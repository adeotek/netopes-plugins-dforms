<?php
	$empty_val = get_array_value($this->html_styles,'empty_value','','is_string');
	if(!$output) { ob_start(); }
	if(!$idSubForm) {
?>
	<table <?php echo get_array_value($this->html_styles,'table_attr','','is_string'); ?>style="<?php echo get_array_value($this->html_styles,'table_style','','is_string'); ?>">
<?php
		if(is_array($relations) && count($relations)) {
			foreach($relations as $rel) {
				$rlabel = get_array_value($rel,'name','','is_string');
				$rvalue = get_array_value($rel,'display_fields',get_array_value($rel,'svalue',$empty_val,'is_notempty_string'),'is_notempty_string');
?>
		<tr><td><label style="<?php echo get_array_value($this->html_styles,'label_style','','is_string'); ?>"><?php echo $rlabel.get_array_value($this->html_styles,'label_value_sep','','is_string'); ?></label><span style="<?php echo get_array_value($this->html_styles,'relation_style','','is_string'); ?>"><?php echo $rvalue; ?></span></td></tr>
<?php
			}//END foreach
		} else {
			$uid = get_array_value($instance,'uid','','is_string');
			if(strlen($uid)) {
?>
		<tr><td><label style="<?php echo get_array_value($this->html_styles,'label_style','','is_string'); ?>"><?php echo Translate::GetLabel('form_uid').get_array_value($this->html_styles,'label_value_sep','','is_string'); ?></label><span style="<?php echo get_array_value($this->html_styles,'relation_style','','is_string'); ?>"><?php echo $uid; ?></span></td></tr>
<?php
			}//if(strlen($uid))
			$category = get_array_value($instance,'category',get_array_value($instance,'template_code','','is_notempty_string'),'is_notempty_string');
			if(strlen($category)) {
?>
		<tr><td><label style="<?php echo get_array_value($this->html_styles,'label_style','','is_string'); ?>"><?php echo Translate::GetLabel('form_category').get_array_value($this->html_styles,'label_value_sep','','is_string'); ?></label><span style="<?php echo get_array_value($this->html_styles,'relation_style','','is_string'); ?>"><?php echo $category; ?></span></td></tr>
<?php
			}//if(strlen($category))
		}//if(is_array($relations) && count($relations))
		$create_date = get_array_value($instance,'create_date','','is_string');
		$created_by = get_array_value($instance,'user_full_name',$empty_val,'is_notempty_string');
?>
		<tr><td><label style="<?php echo get_array_value($this->html_styles,'label_style','','is_string'); ?>"><?php echo Translate::GetLabel('created_at').get_array_value($this->html_styles,'label_value_sep','','is_string'); ?></label><span style="<?php echo get_array_value($this->html_styles,'relation_style','','is_string'); ?>"><?php echo $create_date; ?></span></td></tr>
		<tr><td><label style="<?php echo get_array_value($this->html_styles,'label_style','','is_string'); ?>"><?php echo Translate::GetLabel('created_by').get_array_value($this->html_styles,'label_value_sep','','is_string'); ?></label><span style="<?php echo get_array_value($this->html_styles,'relation_style','','is_string'); ?>"><?php echo $created_by; ?></span></td></tr>
<?php
		$last_modified = get_array_value($instance,'last_modified','','is_string');
		if(strlen($last_modified)) {
			$modified_by = get_array_value($instance,'last_user_full_name',$empty_val,'is_notempty_string');
?>
		<tr><td><label style="<?php echo get_array_value($this->html_styles,'label_style','','is_string'); ?>"><?php echo Translate::GetLabel('modified_at').get_array_value($this->html_styles,'label_value_sep','','is_string'); ?></label><span style="<?php echo get_array_value($this->html_styles,'relation_style','','is_string'); ?>"><?php echo $last_modified; ?></span></td></tr>
		<tr><td><label style="<?php echo get_array_value($this->html_styles,'label_style','','is_string'); ?>"><?php echo Translate::GetLabel('modified_by').get_array_value($this->html_styles,'label_value_sep','','is_string'); ?></label><span style="<?php echo get_array_value($this->html_styles,'relation_style','','is_string'); ?>"><?php echo $modified_by; ?></span></td></tr>
<?php
		}//if(strlen($last_modified))
?>
		<tr><td>&nbsp;</td></tr>
	</table>
<?php
	}//if(!$idSubForm)
	if(is_array($fields) && count($fields)) {
?>
	<table <?php echo get_array_value($this->html_styles,'table_attr','','is_string'); ?>style="<?php echo get_array_value($this->html_styles,'table_style','','is_string'); ?>">
<?php
		$cols = get_array_value($instance,'colsno',1,'is_numeric');
		$crow = 0;
		foreach($fields as $field) {
			$row = get_array_value($field,'frow',0,'is_numeric');
			if(!$row) { continue; }
			if($row!=$crow) {
				if($crow>0) {
?>
		</tr>
<?php
				}//if($crow>0)
				$crow = $row;
?>
		<tr>
<?php
			}//if($row!=$crow)
			$col = get_array_value($field,'fcol',1,'is_numeric');
			$fClass = get_array_value($field,'class','','is_string');
			if(!strlen($fClass)) {
?>
			<td>&nbsp;</td>
<?php
				continue;
			}//if(!strlen($fClass))
			// if($idSubForm) { NApp::Dlog($field,$fClass); }
			$fparams = get_array_value($field,'params','','is_string');
			$fParams = strlen($fparams) ? @unserialize($fparams) : [];
			$css_class = get_array_value($fParams,'class','','is_string');
			switch($fClass) {
				case 'FormTitle':
					if($css_class=='taleft') { $talign = 'left'; }
					elseif($css_class=='taright') { $talign = 'right'; }
					else { $talign = 'center'; }
?>
			<td><h1 style="text-align: <?php echo $talign.'; '.get_array_value($this->html_styles,'title_style','','is_string'); ?>"><?php echo get_array_value($field,'label',$empty_val,'is_notempty_string'); ?></h1></td>
<?php
					break;
				case 'FormSubTitle':
					if($css_class=='tacenter') { $talign = 'center'; }
					elseif($css_class=='taright') { $talign = 'right'; }
					else { $talign = 'left'; }
?>
			<td><br/><h3 style="text-align: <?php echo $talign.'; '.get_array_value($this->html_styles,'subtitle_style','','is_string'); ?>"><?php echo get_array_value($field,'label',$empty_val,'is_notempty_string'); ?></h3></td>
<?php
					break;
				case 'FormSeparator':
?>
			<td><hr style="margin: 10px 0;"></td>
<?php
					break;
				case 'BasicForm':
					$f_itype = get_array_value($field,'itype',1,'is_not0_numeric');
					$idSubForm = get_array_value($field,'id_sub_form',-1,'is_not0_numeric');
					$idItem = get_array_value($field,'id',NULL,'is_not0_numeric');
					$f_icount = $f_itype==2 ? get_array_value($field,'icount',0,'is_not0_numeric') : 1;
					$fValue = '';
					for($i=0;$i<$f_icount;$i++) {
						$fValue .= $this->Exec('PrepareFormHtml',['id'=>$idInstance,'id_sub_form'=>$idSubForm,'id_item'=>$idItem,'index'=>$i,'output'=>$output]);
					}//END for
?>
			<td><?php echo $fValue; ?></td>
<?php
					break;
				case 'Message':
					$flabel = get_array_value($field,'label','','is_string');
					$fdesc = get_array_value($field,'description','-','is_string');
?>
			<td><span style="<?php echo get_array_value($this->html_styles,'msg_style','','is_string'); ?>"><?php echo $flabel.$fdesc; ?></span></td>
<?php
					break;
				case 'SmartComboBox':
				case 'GroupCheckBox':
					$flabel = get_array_value($field,'label','','is_string');
					$fValue = get_array_value($field,'ivalues',NULL,'is_string');
					$idValuesList = get_array_value($field,'id_values_list',0,'is_numeric');
					if($idValuesList>0) {
						$vl_value = DataProvider::GetArray('Plugins\DForms\ValuesLists','GetValueItems',['for_id'=>$fValue,'list_id'=>$idValuesList]);
						$fValue = get_array_value($vl_value,[0,'name'],$empty_val,'is_notempty_string');
					} else {
						$fValue = $empty_val;
					}//if($idValuesList>0)
?>
			<td><label style="<?php echo get_array_value($this->html_styles,'label_style','','is_string'); ?>"><?php echo $flabel.get_array_value($this->html_styles,'label_value_sep','','is_string'); ?></label><span style="<?php echo get_array_value($this->html_styles,'value_style','','is_string'); ?>"><?php echo $fValue; ?></span></td>
<?php
					break;
				default:
					$flabel = get_array_value($field,'label','','is_string');
					$f_itype = get_array_value($field,'itype',1,'is_not0_numeric');
					if($f_itype==2) {
						$fValue = get_array_value($field,'ivalues',NULL,'is_string');
						$fValues = explode('|::|',$fValue);
						$f_icount = get_array_value($field,'icount',0,'is_numeric');
						$fValue = '';
						for($i=1;$i<$iCount;$i++) {
							$fi_val = get_array_value($fValues,$i,$empty_val,'is_notempty_string');
							$fValue .= '<label style="'.get_array_value($this->html_styles,'label_style','','is_string').'">'.$flabel.':</label>&nbsp;<span style="'.get_array_value($this->html_styles,'value_style','','is_string').'">'.$fi_val.'</span>';
						}//END for
?>
			<td><?php echo strlen($fValue) ? $fValue : '<label style="'.get_array_value($this->html_styles,'label_style','','is_string').'">'.$flabel.get_array_value($this->html_styles,'label_value_sep','','is_string').'</label><span style="'.get_array_value($this->html_styles,'value_style','','is_string').'">'.$empty_val.'</span>'; ?></td>
<?php
					} else {
						$fValue = get_array_value($field,'ivalues',$empty_val,'is_notempty_string');
?>
			<td><label style="<?php echo get_array_value($this->html_styles,'label_style','','is_string'); ?>"><?php echo $flabel.get_array_value($this->html_styles,'label_value_sep','','is_string'); ?></label><span style="<?php echo get_array_value($this->html_styles,'value_style','','is_string'); ?>"><?php echo $fValue; ?></span></td>
<?php
					}//if($f_itype==2)
					break;
			}//END switch
		}//END foreach
?>
		</tr>
		<tr><td>&nbsp;</td></tr>
	</table>
<?php
	}//if(is_array($fields) && count($fields))