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
 
 
 // add fields to store configuration
 $GLOBALS['TL_DCA']['tl_iso_config']['palettes']['default'] = str_replace('{images_legend}', '{isotope_livedocx_legend},iso_livedocx_active,iso_livedocx_user,iso_livedocx_pass,iso_livedocx_outputformat,iso_livedocx_template;{images_legend}', $GLOBALS['TL_DCA']['tl_iso_config']['palettes']['default']);


 $GLOBALS['TL_DCA']['tl_iso_config']['fields']['iso_livedocx_active'] = array
 (
			'label'         => &$GLOBALS['TL_LANG']['tl_iso_config']['iso_livedocx_active'],
			'exclude'       => true,
			'inputType'     => 'checkbox',
			'eval'          => array('tl_class'=>''),
 );

 $GLOBALS['TL_DCA']['tl_iso_config']['fields']['iso_livedocx_template'] = array
 (
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['iso_livedocx_template'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'path'=>'system/modules/isotope_livedocx/templates', 'files'=>true, 'extensions'=>'doc,docx,rtf', 'tl_class'=>'clr'),
 );
 
 $GLOBALS['TL_DCA']['tl_iso_config']['fields']['iso_livedocx_user'] = array
 (
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['iso_livedocx_user'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
 );
 
 $GLOBALS['TL_DCA']['tl_iso_config']['fields']['iso_livedocx_pass'] = array
 (
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['iso_livedocx_pass'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
 );

 
 $GLOBALS['TL_DCA']['tl_iso_config']['fields']['iso_livedocx_outputformat'] = array
 (
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['iso_livedocx_outputformat'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'		  => array('tl_iso_config_livedocx', 'getOutputFormats'),
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
 );
 
 
 
class tl_iso_config_livedocx extends backend
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	public function getOutputFormats()
	{
		$options = array();
		if( is_array($GLOBALS['ISO_LIVEDOCX_DOC']) && count($GLOBALS['ISO_LIVEDOCX_DOC']) )
			foreach($GLOBALS['ISO_LIVEDOCX_DOC'] as $ext => $name)
			{
				$options["Dokumente"][$ext] = $ext . ' - ' . $name;
			}
		
		if( is_array($GLOBALS['ISO_LIVEDOCX_IMG']) && count($GLOBALS['ISO_LIVEDOCX_IMG']) )
			foreach($GLOBALS['ISO_LIVEDOCX_IMG'] as $ext => $name)
			{
				$options["Grafiken"][$ext] = $ext . ' - ' . $name;
			}
			
		return $options;
		
	}

}
 
?>