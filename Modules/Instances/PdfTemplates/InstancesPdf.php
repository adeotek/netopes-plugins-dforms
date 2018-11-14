<?php
/**
 * PdfDocument class file
 *
 * PDF document generator that implements abstract class PdfDocumentBase
 *
 * @package    DKMed\Modules\Application
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2019 AdeoTEK Software
 * @license    LICENSE.txt
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Instances\PdfTemplates;
use NETopes\Core\Reporting\PdfDocument;
use Translate;
/**
 * PdfDocument class
 *
 * PDF document generator that implements abstract class PdfDocumentBase
 *
 * @package  DKMed\Modules\Application
 * @access   public
 */
class InstancesPdf extends PdfDocument {

	protected function _Init() {
		$this->type = X_HTML_TYPE_PDF;
		$this->footer_params = array(
			'bottom_margin'=>-10,
			'font'=>'freeserif',
			'font_style'=>'',
			'font_size'=>8,
			'mask'=>Translate::Get('dlabel_page',$this->langcode).' {{page}} '.Translate::Get('dlabel_from',$this->langcode).' {{pages_no}}',
			'align'=>'C',
		);
		parent::_Init();
	}//END protected function _Init
}//END class InstancesPdf extends PdfDocument
?>