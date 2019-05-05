/**
 * NETopes DForms plugin javascript file
 * Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * License    LICENSE.md
 * @author     George Benjamin-Schonberger
 * @version    1.0.1.0
 */
/**
 * @return {boolean}
 */
function AddRepeatableControl(obj,tagid) {
	// console.log('AddRepeatableControl...');
	if(!obj || !tagid) { return false; }
	let $parent = $(obj).parent().parent();
	let $lTag = $parent.find('[data-tid="'+tagid+'"]').last();
	let $lParentTag = $lTag.parent();
	if($lTag.length<=0) { return false; }
	let lIndex = Number($lTag.attr('data-ti'));
	let $newTag = $lTag.parent().clone();
	$newTag.find('[data-tid="'+tagid+'"]').first().attr('id',tagid+'-'+(lIndex+1));
	$newTag.find('[data-tid="'+tagid+'"]').first().data('ti',lIndex+1);
	$newTag.find('[data-tid="'+tagid+'"]').first().attr('data-ti',lIndex+1);
	$newTag.find('[data-tid="'+tagid+'"]').first().val('');
	$newTag.find('button.clsRemoveRepeatableFieldBtn').first().prop('disabled',false);
	$newTag.insertAfter($lParentTag);
}//END function AddRepeatableControl
/**
 * @return {boolean}
 */
function RemoveRepeatableControl(obj) {
	// console.log('RemoveRepeatableControl...');
	if($(obj).length<=0) { return false; }
	let $parent = $(obj).parent().parent().parent();
	if($parent.find('.input-group').length<=1) { return false; }
	// console.log('removing element...');
	$(obj).parent().parent().remove();
}//END function RemoveRepeatableControl

/**
 * @return {boolean}
 */
function RepeatForm(obj,tagid) {
	if(!obj || !tagid) { return false; }
	let $obj=$(obj);
	let $ltag=$obj.parent().find('[data-tid=\'' + tagid + '\']').last();
	if($ltag.length<=0) { return false; }
	let lindex=Number($ltag.attr('data-ti'));
	let lntagid=tagid + '-' + (lindex + 1);
	let $lntag=$ltag.clone();
	// console.log($lntag);
	if($lntag.hasClass('clsSubForm')) {
		$lntag.find('.postable').each(function() {
			let peid=$(this).attr('id');
			if(peid) {
				let penid=peid.substring(0,peid.lastIndexOf('-')) + '-' + (lindex + 1);
				$(this).attr('id',penid);
				$(this).val('');
			}//if(peid)
		});
	}//if($lntag.hasClass('clsSubForm'))
	$lntag.attr('data-ti',lindex + 1);
	$lntag.attr('id',lntagid);
	$lntag.val('');
	$lntag.addClass('ctrl-clone');
	$lntag.insertBefore($obj);
	let rAction=$obj.attr('data-ract');
	let rActionClass=$obj.attr('data-ract-class');
	$('<div class="cloned-form-ctrls"><button class="' + (rActionClass ? rActionClass : 'clsRepeatableCtrlBtn remove-ctrl-btn') + '" onclick="RemoveRepeatableForm(this,\'' + lntagid + '\')"><i class="fa fa-minus-circle" aria-hidden="true"></i>' + (rAction ? rAction : '') + '</button></div>').insertBefore($obj);
}//END function RepeatForm

function RemoveRepeatableForm(obj,formId) {
	$('#' + formId).remove();
	$(obj).parent().remove();
}//END function RemoveRepeatableForm