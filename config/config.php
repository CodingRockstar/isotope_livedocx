<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * PHP version 5
 * @copyright  Stephan Jahrling, 2011
 * @author     Stephan Jahrling <info@jahrling-software.de>
 * @license    commercial
 */
 
 
 // add print-action to orders
 $GLOBALS['BE_MOD']['isotope']['iso_orders']['livedocx']	= array('IsotopeLiveDocX','printInvoice');
 
 
 // valid output-formats (document + images)
 $GLOBALS['ISO_LIVEDOCX_DOC'] = array(
 	"DOCX" 	=> "Office Open XML Format",
    "DOC" 	=> "Microsoft® Word DOC Format",
    "HTML" 	=> "XHTML 1.0 Transitional Format",
    "RTF" 	=> "Rich Text Format",
    "PDF" 	=> "Acrobat® Portable Document Format",
    "TXD" 	=> "TX Text Control Format",
    "TXT" 	=> "ANSI Plain Text"
 );
 
 $GLOBALS['ISO_LIVEDOCX_IMG'] = array(
 	"BMP" 	=> "Bitmap Image Format",
    "GIF" 	=> "Graphics Interchange Format",
    "JPG" 	=> "Joint Photographic Experts Group Format",
    "PNG" 	=> "Portable Network Graphics Format",
    "TIFF" 	=> "Tagged Image File Format",
    "WMF" 	=> "Windows Meta File Format"
 );

 
?>