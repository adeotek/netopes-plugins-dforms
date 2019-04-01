<?php
$vclass='clsDFViewBox';
$uid=get_array_value($instance,'uid','','is_string');
$category=get_array_value($instance,'category',get_array_value($instance,'template_code','','is_notempty_string'),'is_notempty_string');
$vcontent=get_array_value($instance,'print_template','','is_string');
if(strlen($vcontent)) {
    if(strlen($uid)) {
        str_replace('[form_uid]',$uid,$vcontent);
    }
    if(strlen($category)) {
        str_replace('[form_category]',$category,$vcontent);
    }
    $fields=DataProvider::GetArray('Plugins\DForms\Instances','GetStructure',['instance_id'=>$idInstance]);
    if(is_array($fields) && count($fields)) {
        foreach($fields as $field) {
            $fname=get_array_value($field,'name','','is_string');
            if(!strlen($fname)) {
                continue;
            }
            $fval=get_array_value($field,'ivalues','','is_string');
            $vcontent=str_replace('['.$fname.']',$fval,$vcontent);
        }//END foreach
    }//if(is_array($fields) && count($fields))
    $relations=DataProvider::GetArray('Plugins\DForms\Instances','GetRelations',['instance_id'=>$idInstance]);
    if(is_array($relations) && count($relations)) {
        foreach($relations as $rel) {
            $fname=get_array_value($rel,'name','','is_string');
            if(!strlen($fname)) {
                continue;
            }
            $fval=get_array_value($rel,'svalues',get_array_value($rel,'ivalues','','is_string'),'is_string');
            $vcontent=str_replace('['.$fname.']',$fval,$vcontent);
        }//END foreach
    }//if(is_array($relations) && count($relations))
} else {
    $vcontent=$this->Exec('PrepareFormHtml',['id'=>$idInstance]);
}//if(strlen($vcontent))

?>
<div class="<?php echo $vclass; ?>"><?php echo $vcontent; ?></div>
