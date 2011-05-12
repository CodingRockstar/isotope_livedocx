<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * PHP version 5
 * @copyright  Stephan Jahrling, 2011
 * @author     Stephan Jahrling <info@jahrling-software.de>
 * @license    commercial
 */
 

// insert new button
array_insert($GLOBALS['TL_DCA']['tl_iso_orders']['list']['operations'], count($GLOBALS['TL_DCA']['tl_iso_orders']['list']['operations'])-1, array(
	
	'livedocx_print_order' => array
	(
		'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['livedocx_print_order'],
		'href'				=> 'key=livedocx',
		'icon'				=> 'system/modules/isotope_livedocx/html/printer.png',
		'attributes'   	 	=> 'onclick="Backend.getScrollOffset();"',
		'button_callback'	=> array('tl_iso_orders_livedocx', 'printOrderBtn')
	)

));
 
 
// hide isotope-print btn
$GLOBALS['TL_DCA']['tl_iso_orders']['list']['operations']['print_order']['button_callback']	= array('tl_iso_orders_livedocx', 'hidePrintBtn');
 
 
 
class tl_iso_orders_livedocx extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Return the LiveDocX Print-Order-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function printOrderBtn($row, $href, $label, $title, $icon, $attributes)
	{
		$objConfig = $this->Database->prepare("SELECT `iso_livedocx_active` FROM `tl_iso_config` WHERE `id`=?")
								   ->limit(1)
								   ->execute($row['config_id']);

		return ($objConfig->iso_livedocx_active == '1') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : '';
	}
	
	
	/**
	 * Return the Print-Order-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function hidePrintBtn($row, $href, $label, $title, $icon, $attributes)
	{
		$objConfig = $this->Database->prepare("SELECT `iso_livedocx_active` FROM `tl_iso_config` WHERE `id`=?")
								   ->limit(1)
								   ->execute($row['config_id']);

		return ($objConfig->iso_livedocx_active != '1') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : '';
	}

}


 
?>