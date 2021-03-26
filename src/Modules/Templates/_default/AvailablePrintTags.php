<?php
use NETopes\Core\Data\DataSet;

/** @var \NETopes\Core\Data\DataSet|null $fields */
/** @var \NETopes\Core\Data\DataSet|null $relations */
/** @var string $targetTagId */
/** @var string $targetTagType */

$actionsDisabled=!strlen($targetTagId) || !strlen($targetTagType);

if($fields instanceof DataSet && $fields->count()) {
    ?>
    <div class="row">
        <div class="col-md-8">
            <h4 style="margin: 0 0 5px 0;"><?= Translate::GetLabel('fields') ?></h4>
        </div>
        <div class="col-md-4">
            <button class="btn btn-info btn-xs pull-right" onclick="<?= NApp::Ajax()->Prepare("{ 'module': '{$this->name}', 'method': 'AddTagToPrintTemplate', 'params': { 'target_tag_id': '{$targetTagId}', 'target_tag_type': '{$targetTagType}', 'tags': '{nGet|df_fields_available_tags:form}' } }",'errors') ?>"<?= $actionsDisabled ? ' disabled="disabled"' : '' ?>><i class="fa fa-plus-circle" aria-hidden="true"></i><?= Translate::GetButton('add_all_fields') ?></button>
        </div>
    </div>
    <div class="form-sm mt10 mb20" id="df_fields_available_tags">
        <?php
        /** @var \NETopes\Core\Data\IEntity $field */
        foreach($fields as $field) {
            if(in_array($field->getProperty('class'),['FormTitle','FormSubTitle','FormSeparator','Message'])) {
                continue;
            }
            $fieldUid=$field->getProperty('uid');
            ?>
            <div class="row">
                <div class="form-group col-md-6">
                    <div class="input-group input-group-sm">
                <span class="input-group-btn">
                    <button class="btn btn-primary" onclick="<?= NApp::Ajax()->Prepare("{ 'module': '{$this->name}', 'method': 'AddTagToPrintTemplate', 'params': { 'target_tag_id': '{$targetTagId}', 'target_tag_type': '{$targetTagType}', 'tag': '{nGet|dff-tag-{$fieldUid}:value}' } }",'errors') ?>"<?= $actionsDisabled ? ' disabled="disabled"' : '' ?>><i class="fa fa-plus" aria-hidden="true"></i><?= Translate::GetButton('add') ?></button>
                </span>
                        <input type="text" id="dff-tag-<?= $fieldUid ?>" name="<?= $fieldUid ?>" class="clsTextBox form-control text-center postable" readonly="readonly" value="[[<?= $field->getProperty('name') ?>]]">
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="form-control" readonly="readonly">
                        <?= $field->getProperty('label') ?>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;<i><?= $field->getProperty('control_name') ?></i>
                    </div>
                </div>
            </div>
            <?php
        }//END foreach
        ?>
    </div>
    <?php
}//if($fields instanceof DataSet && $fields->count())
if($relations instanceof DataSet && $fields->count()) {
    ?>
    <div class="row">
        <div class="col-md-8">
            <h4 style="margin: 0 0 5px 0;"><?= Translate::GetLabel('relations') ?></h4>
        </div>
        <div class="col-md-4">
            <button class="btn btn-info btn-xs pull-right" onclick="<?= NApp::Ajax()->Prepare("{ 'module': '{$this->name}', 'method': 'AddTagToPrintTemplate', 'params': { 'target_tag_id': '{$targetTagId}', 'target_tag_type': '{$targetTagType}', 'tags': '{nGet|df_relations_available_tags:form}' } }",'errors') ?>"<?= $actionsDisabled ? ' disabled="disabled"' : '' ?>><i class="fa fa-plus-circle" aria-hidden="true"></i><?= Translate::GetButton('add_all_relations') ?></button>
        </div>
    </div>
    <div class="form-sm mt10 mb20" id="df_relations_available_tags">
        <?php
        /** @var \NETopes\Core\Data\IEntity $relation */
        foreach($relations as $relation) {
            $displayFieldsString=$relation->getProperty('display_fields',NULL,'is_string');
            $displayFields=strlen($displayFieldsString) ? explode(';',$displayFieldsString) : [];
            if(!count($displayFields)) {
                continue;
            }
            $relationUid=$relation->getProperty('id');
            ?>
            <div class="row mb10">
                <div class="col-12">
                    <h5 style="margin: 0 0 10px 0;"><?= $relation->getProperty('name') ?>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;<i><?= $relation->getProperty('relation_type') ?></i></h5>
                    <?php
                    foreach($displayFields as $displayField) {
                        $field=trim($displayField,' ][');
                        if(!strlen($field)) {
                            continue;
                        }
                        $tag=$relation->getProperty('key').'-'.strtolower($field);
                        $fieldUid=$relationUid.'-'.strtoupper($field);
                        ?>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" onclick="<?= NApp::Ajax()->Prepare("{ 'module': '{$this->name}', 'method': 'AddTagToPrintTemplate', 'params': { 'target_tag_id': '{$targetTagId}', 'target_tag_type': '{$targetTagType}', 'tag': '{nGet|dfr-tag-{$fieldUid}:value}' } }",'errors') ?>"<?= $actionsDisabled ? ' disabled="disabled"' : '' ?>><i class="fa fa-plus" aria-hidden="true"></i><?= Translate::GetButton('add') ?></button>
                        </span>
                                    <input type="text" id="dfr-tag-<?= $fieldUid ?>" name="<?= $fieldUid ?>" class="clsTextBox form-control text-center postable" readonly="readonly" value="[[<?= $tag ?>]]">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-control" readonly="readonly">
                                    <?= $relation->getProperty('name').' - '.strtoupper($field) ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }//END foreach
                    ?>
                </div>
            </div>
            <?php
        }//END foreach
        ?>
    </div>
    <?php
}//if($relations instanceof DataSet && $fields->count())