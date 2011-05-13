<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Stephan Jahrling, 2011
 * @author     Stephan Jahrling <info@jahrling-software.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
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