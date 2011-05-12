<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * PHP version 5
 * @copyright  Stephan Jahrling, 2011
 * @author     Stephan Jahrling <info@jahrling-software.de>
 * @license    commercial
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