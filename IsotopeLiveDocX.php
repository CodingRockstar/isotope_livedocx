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
 *
 * Class IsotopeLiveDocX
 *
 * Class for printing invoices with the LiveDocX-API
 * @copyright  Stephan Jahrling, 2011
 * @author     Stephan Jahrling <info@jahrling-software.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
 
 
class IsotopeLiveDocX extends Backend
{
	
	public function __construct()
	{
	
		// Turn up error reporting
		error_reporting (E_ALL|E_STRICT);
 
		// Turn off WSDL caching
		ini_set ('soap.wsdl_cache_enabled', 0);
 
		// SOAP WSDL endpoint
		define ('ENDPOINT', 'https://api.livedocx.com/1.2/mailmerge.asmx?WSDL');
 
		// Define timezone
		date_default_timezone_set('Europe/Berlin');


		parent::__construct();
		$this->import('Isotope');
	}
	

	public function printInvoice(DataContainer $objDc)
	{

		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE id=?")
										   ->limit(1)
										   ->execute($objDc->id);

		$strInvoiceTitle = $GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] . '_' . $objDc->id . '_' . time();


		// get LiveDocX-Data from store-config
		$objLiveDocX = $this->Database->prepare("SELECT * FROM `tl_iso_config` WHERE id=?")
									  ->limit(1)
									  ->execute($objOrder->config_id);
									  
		if( !($objLiveDocX->iso_livedocx_active == '1')
			|| !strlen($objLiveDocX->iso_livedocx_user)
				|| !strlen($objLiveDocX->iso_livedocx_pass)
					|| !strlen($objLiveDocX->iso_livedocx_template))
						return;
						
		
		// Define credentials for LD
		define ('USERNAME', $objLiveDocX->iso_livedocx_user);
		define ('PASSWORD', $objLiveDocX->iso_livedocx_pass);


		// Write the content of the invoice
		$arrContent = $this->generateContentAsArray( $objOrder->uniqid );
		
		
		// HOOK for altering order data
		if (isset($GLOBALS['TL_HOOKS']['iso_liveDocXOrderData']) && is_array($GLOBALS['TL_HOOKS']['iso_liveDocXOrderData']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_liveDocXOrderData'] as $callback)
			{
				$this->import($callback[0]);
				$arrContent = $this->$callback[0]->$callback[1]($objOrder->id, $arrContent);
			}
		}
			
		
		if( is_array( $arrContent ))
		{
			
			$this->loadLanguageFile('countries');
			$arrBillingAddress = deserialize( $arrContent['raw']['billing_address'] );
			
			if( is_array($arrBillingAddress) && count($arrBillingAddress) )
				$arrBillingAddress = array_map(array('IsotopeLiveDocX','html_entity_decode_utf8'), $arrBillingAddress);
			
			$arrShippingAddress = array();
			if( is_array($arrContent['info']['shipping_address']) ){
				$arrShippingAddress = deserialize( $arrContent['raw']['shipping_address'] );
				if( is_array($arrShippingAddress) && count($arrShippingAddress) )
					$arrShippingAddress = array_map(array('IsotopeLiveDocX','html_entity_decode_utf8'), $arrShippingAddress);
			}
			
			
			$soap = new SoapClient(ENDPOINT);
 
			$soap->LogIn(
    			array(
        			'username' => USERNAME,
        			'password' => PASSWORD
    			)
			);
			
			
			// HOOK: override template
			$docTemplate = $objLiveDocX->iso_livedocx_template;
			if (isset($GLOBALS['TL_HOOKS']['iso_liveDocXTemplate']) && is_array($GLOBALS['TL_HOOKS']['iso_liveDocXTemplate']))
			{
				foreach ($GLOBALS['TL_HOOKS']['iso_liveDocXTemplate'] as $callback)
				{
					$this->import($callback[0]);
					$docTemplate = $this->$callback[0]->$callback[1]($objOrder->id);
				}
			}
 
			
			// Upload template
			$path_parts = pathinfo(TL_ROOT . '/' . $docTemplate);
			
			switch( strtolower($path_parts['extension']) )
			{
				case 'docx':
					$format = 'docx';
					break;
				
				case 'doc':
					$format = 'doc';
					break;
				
				case 'rtf':
					$format = 'rtf';
					break;
					
				default:
					$format = '';
			}
			
			if( !strlen($format) )
				trigger_error(sprintf('No valid template file : %s', $docTemplate), E_USER_ERROR);
			
			
			$data = file_get_contents(TL_ROOT . '/' . $docTemplate);
			$soap->SetLocalTemplate(
    			array(
        			'template' => base64_encode($data),
        			'format'   => $format
    			)
			);
			
			
			// Assign data to template
			$fieldValues = array (
    			'billing_company'	 		=> $arrBillingAddress['company'],
    			'billing_firstname' 		=> $arrBillingAddress['firstname'],
    			'billing_lastname' 			=> $arrBillingAddress['lastname'],
    			'billing_street'  			=> $arrBillingAddress['street_1'],
    			'billing_postal'     		=> $arrBillingAddress['postal'],
    			'billing_city'     			=> $arrBillingAddress['city'],
    			'billing_country'  			=> $GLOBALS['TL_LANG']['CNT'][ $arrBillingAddress['country'] ],
    			
    			'shipping_company'	 		=> $arrShippingAddress['company'],
    			'shipping_firstname' 		=> $arrShippingAddress['firstname'],
    			'shipping_lastname' 		=> $arrShippingAddress['lastname'],
    			'shipping_street'  			=> $arrShippingAddress['street_1'],
    			'shipping_postal'     		=> $arrShippingAddress['postal'],
    			'shipping_city'     		=> $arrShippingAddress['city'],
    			'shipping_country'  		=> (strlen($arrShippingAddress['country']) ? $GLOBALS['TL_LANG']['CNT'][ $arrShippingAddress['country'] ] : ''),
    			
    			'orderId'					=> $objOrder->id,
    			'invoiceId'					=> $objOrder->order_id,
    			'orderDate'					=> $arrContent['date'],
    			
    			'shipping_method'			=> strip_tags( $this->html_entity_decode_utf8( $arrContent['info']['shipping_method']['info'] ) ),
    			'shipping_method_headline'	=> strip_tags( $this->html_entity_decode_utf8( $arrContent['info']['shipping_method']['headline'] ) ),
    			'shipping_method_note'		=> strip_tags( $this->html_entity_decode_utf8( $arrContent['info']['shipping_method']['note'] ) ),
    			'shippingDate'				=> (strlen($objOrder->date_shipped) ? $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrder->date_shipped) : ''),
    			
    			'payment_method'			=> strip_tags( $this->html_entity_decode_utf8( $arrContent['info']['payment_method']['info'] ) ),
    			'payment_method_headline'	=> strip_tags( $this->html_entity_decode_utf8( $arrContent['info']['payment_method']['headline'] ) ),
    			'payment_method_note'		=> strip_tags( $this->html_entity_decode_utf8( $arrContent['info']['payment_method']['note'] ) ),
    			'paymentDate'				=> (strlen($objOrder->date_payed) ? $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrder->date_payed) : ''),
    		);
    		foreach($arrContent as $key => $value)
    			$fieldValues[$key] = (is_string($value)) ? strip_tags($value) : $value;	
    			
 
			$soap->SetFieldValues(
   	 			array (
        			'fieldValues' => $this->assocArrayToArrayOfArrayOfString($fieldValues)
    			)
			);
			
			
			// HOOK for adding own fields to the 
			if (isset($GLOBALS['TL_HOOKS']['iso_liveDocXFieldValues']) && is_array($GLOBALS['TL_HOOKS']['iso_liveDocXFieldValues']))
			{
				foreach ($GLOBALS['TL_HOOKS']['iso_liveDocXFieldValues'] as $callback)
				{
					$this->import($callback[0]);
					$arrFieldValues = $this->$callback[0]->$callback[1]($objOrder->id, $fieldValues);
					
					// assign data to template
					if( is_array($arrFieldValues) && count($arrFieldValues) )
						$soap->SetFieldValues(
   	 						array (
        						'fieldValues' => $this->assocArrayToArrayOfArrayOfString($fieldValues)
    						)
						);
				}
			}
			
			
			
			// Assign surcharges
			$blockFieldValues = array();
			if( is_array($arrContent['surcharges']) && count($arrContent['surcharges']) )
				foreach($arrContent['surcharges'] as $surcharge)
				{
					
					if( is_array($surcharge) && count($surcharge) )
						$surcharge = array_map(array('IsotopeLiveDocX','html_entity_decode_utf8'), $surcharge);
					
					$arrSurcharge = array(
						'surcharge_name'  		=> $surcharge['label'], 
						'surcharge_price' 		=> strip_tags( $surcharge['price'] ),
						'surcharge_totalprice' 	=> strip_tags( $surcharge['total_price'] )
					);	
					
					$blockFieldValues[] = $arrSurcharge;
				
				}
				
			// Assign block field values data to template
			$soap->SetBlockFieldValues(
    			array (
        			'blockName'        => 'surcharges',
        			'blockFieldValues' => $this->multiAssocArrayToArrayOfArrayOfString($blockFieldValues)
    			)
			);
			
			
			
			// Assign products
			$blockFieldValues = array();
			$i = 1;
			if( is_array( $arrContent['items'] ) && count( $arrContent['items'] ) )
				foreach( $arrContent['items'] as $item )
				{
					
					$options = '';
					
					if( is_array( $item['product_options'] ) && count( $item['product_options'] ) )
						foreach($item['product_options'] as $option)
							$options .= $option['label'] . ' : ' . $option['value'] . "\r\n";
					
					$product = array(
						'item_number'		=> $i++,
						'product_name'		=> $item['name'], 
						'product_options'	=> $options, 
						'product_qty' 		=> $item['quantity'],
						'product_price' 	=> strip_tags( $item['price'] ),
						'product_total' 	=> strip_tags( $item['total'] )
					);	
					
					$blockFieldValues[] = $product;
					
				}
	
			
			// Assign block field values data to template
			$soap->SetBlockFieldValues(
    			array (
        			'blockName'        => 'products',
        			'blockFieldValues' => $this->multiAssocArrayToArrayOfArrayOfString($blockFieldValues)
    			)
			);
			
			
			// Add own block data
			if (isset($GLOBALS['TL_HOOKS']['iso_liveDocXBlockfields']) && is_array($GLOBALS['TL_HOOKS']['iso_liveDocXBlockfields']))
			{
				foreach ($GLOBALS['TL_HOOKS']['iso_liveDocXBlockfields'] as $callback)
				{
					$this->import($callback[0]);
					$arrBlockData = $this->$callback[0]->$callback[1]($objOrder->id);
					
					// assign to template
					if( is_array($arrBlockData) 
						&& strlen($arrBlockData['blockName']) 
							&& is_array($arrBlockData['blockFieldValues']) && count($arrBlockData['blockFieldValues']) )
						$soap->SetBlockFieldValues(
    						array (
        						'blockName'        => $arrBlockData['blockName'],
        						'blockFieldValues' => $this->multiAssocArrayToArrayOfArrayOfString( $arrBlockData['blockFieldValues'] )
    						)
						);
				}
			}




			// Build the document
 			$soap->CreateDocument();
 			//$soap->GetBitmaps(1,1, 'png');
 			//exit;
 			
 			
 			switch( strtolower($objLiveDocX->iso_livedocx_outputformat) )
 			{
 				// documents
 				case 'pdf':
 					$format = 'pdf';
 					$ctype  = 'application/pdf';
 					break;
 				
 				case 'doc':
 					$format = 'doc';
 					$ctype  = 'application/msword';
 					break;
 				
 				case 'docx':
 					$format = 'docx';
 					$ctype  = 'application/msword';
 					break;
 				
 				case 'rtf':
 					$format = 'rtf';
 					$ctype  = 'application/msword';
 					break;
 					
 				case 'html':
 					$format = 'html';
 					$ctype  = 'text/html';
 					break;
 				
 				case 'txt':
 					$format = 'txt';
 					$ctype  = 'text/plain';
 					break;
 				
 				case 'txd':
 					$format = 'txd';
 					$ctype  = '';
 					break;
 					
 					
 				// images
 				case 'bmp':
 					$format = 'bmp';
 					$ctype  = 'image/jpeg';
 					break;
 				
 				case 'jpg':
 					$format = 'jpg';
 					$ctype  = 'image/jpeg';
 					break;
 				
 				case 'gif':
 					$format = 'gif';
 					$ctype  = 'image/gif';
 					break;
 				
 				case 'png':
 					$format = 'png';
 					$ctype  = 'image/png';
 					break;
 				
 				case 'tiff':
 					$format = 'tiff';
 					$ctype  = 'image/tiff';
 					break;
 				
 				case 'wmf':
 					$format = 'wmf';
 					$ctype  = '';
 					break;
 				
 				
 				default:
 					$format = 'pdf';
 					$ctype  = 'application/pdf';
 			}
 			
 			
			if( array_key_exists(strtoupper($format), $GLOBALS['ISO_LIVEDOCX_DOC']) )
			{
				// Get document as PDF / DOC / DOCX / RTF / TXT / TXD / HTML
 				$result = $soap->RetrieveDocument(
    				array(
        				'format' => $format
    				)
				);
 				$data = $result->RetrieveDocumentResult;
 			}
 			
 			
 			if( array_key_exists(strtoupper($format), $GLOBALS['ISO_LIVEDOCX_IMG']) )
			{
				// Get document as image file
 				$bitmaps = $soap->GetAllBitmaps(100, $format);
 				
 				foreach ($bitmaps as $pageNumber => $bitmapData) { 
    				// $filename = sprintf('documentPage%d.png', $pageNumber); 
    				// file_put_contents($filename, $bitmapData); 
    				$data = $bitmapData;
    			}
 			}
			
			
			
			$soap->LogOut();
 			unset($soap);

 			
 			// output result
 			// @todo: provide more output formats
 			header('Content-Description: File Transfer');
			header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
			header('Pragma: public');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			// force download dialog
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream', false);
			header('Content-Type: application/download', false);
			
			if( strlen($ctype) )
				header('Content-Type: ' . $ctype, false);
			
			// use the Content-Disposition header to supply a recommended filename
			header('Content-Disposition: attachment; filename="' . $strInvoiceTitle . '.' . $format . '";');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . strlen(base64_decode($data)) );
			echo base64_decode($data);
 			exit;
 			
		}	

		$this->Isotope->resetStore(true); 	//Set store back to default.

	}
	
		
	
	protected function generateContentAsArray($varId)
	{
		
		$arrContent = array();
		
		$objOrderData = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE uniqid=?")->limit(1)->execute($varId);
		
		if (!$objOrderData->numRows)
		{
			return;
		}
		
		
		$arrContent['raw'] = $objOrderData->fetchAssoc();
		
		$this->import('Isotope');
		$this->Isotope->overrideConfig($objOrderData->config_id);
		
		// Invoice Logo
		$objInvoiceLogo = $this->Database->prepare("SELECT invoiceLogo FROM tl_iso_config WHERE id=?")
										 ->limit(1)
										 ->execute($objOrderData->config_id);
		
		if($objInvoiceLogo->numRows < 1)
		{
			$strInvoiceLogo = null;
		}else{
			$strInvoiceLogo = $objInvoiceLogo->invoiceLogo;
		}
		
		$arrContent['logoImage'] = strlen($strInvoiceLogo) && file_exists(TL_ROOT . '/' . $strInvoiceLogo) ? str_replace('src="', 'src="/', $this->generateImage($strInvoiceLogo)) : false;
				
		$arrContent['invoiceTitle'] = $GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] . ' ' . $objOrderData->uniqid . ' - ' . date('m-d-Y g:i', $objOrderData->tstamp);
		
		
		$arrAllDownloads = array();
		$arrItems = array();
		$objItems = $this->Database->prepare("SELECT p.*, o.*, t.downloads AS downloads_allowed, t.class AS product_class, (SELECT COUNT(*) FROM tl_iso_order_downloads d WHERE d.pid=o.id) AS has_downloads FROM tl_iso_order_items o LEFT OUTER JOIN tl_iso_products p ON o.product_id=p.id LEFT OUTER JOIN tl_iso_producttypes t ON p.type=t.id WHERE o.pid=?")->execute($objOrderData->id);
		
		
		while( $objItems->next() )
		{
			$strClass = $GLOBALS['ISO_PRODUCT'][$objItems->product_class]['class'];
			
			if (!$this->classFileExists($strClass))
			{
				$strClass = 'IsotopeProduct';
			}
																			
			$arrProduct = $objItems->row();
			$arrProduct['id'] = $objItems->product_id;
			unset($arrProduct['pid']);
			
			
			$objProduct = new $strClass($arrProduct);
			
			$objProduct->name = $arrProduct['product_name'];
			$objProduct->quantity_requested = $objItems->product_quantity;
			$objProduct->cart_id = $objItems->id;
			
			//$objProduct->reader_jumpTo_Override = $objProducts->href_reader;			
		
			if($objProduct->price==0)
				$objProduct->price = $objItems->price;
			
			$arrOptions = deserialize($objItems->product_options, true);
			
			$objProduct->setOptions($arrOptions);
			
			if (!is_object($objProduct))
				continue;
			
			
			$arrItems[] = array
			(
				'raw'				=> $objItems->row(),
				'product_options' 	=> $objProduct->getOptions(),
				'name'				=> $objProduct->name,
				'quantity'			=> $objItems->product_quantity,
				'price'				=> $this->Isotope->formatPriceWithCurrency($objItems->price),
				'total'				=> $this->Isotope->formatPriceWithCurrency(($objItems->price * $objItems->product_quantity)),
				'tax_id'			=> $objProduct->tax_id,
			);
		}
		
		
		
		$arrContent['info'] 					= deserialize($objOrderData->checkout_info, true);
		$arrContent['items'] 					= $arrItems;
		$arrContent['downloads'] 				= $arrAllDownloads;
		$arrContent['downloadsLabel'] 			= $GLOBALS['TL_LANG']['MSC']['downloadsLabel'];
		
		$arrContent['date'] 					= $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrderData->date);
		$arrContent['time'] 					= $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objOrderData->date);
		$arrContent['datim'] 					= $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objOrderData->date);
		$arrContent['orderDetailsHeadline'] 	= sprintf($GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline'], $objOrderData->order_id, $arrContent['datim']);
		$arrContent['orderStatus'] 				= sprintf($GLOBALS['TL_LANG']['MSC']['orderStatusHeadline'], $GLOBALS['TL_LANG']['ORDER'][ $objOrderData->status ]);
		$arrContent['orderStatusKey'] 			= $arrOrder['status'];
		$arrContent['subTotalPrice'] 			= $this->Isotope->formatPriceWithCurrency($objOrderData->subTotal);
		$arrContent['grandTotal'] 				= $this->Isotope->formatPriceWithCurrency($objOrderData->grandTotal);
		$arrContent['subTotalLabel'] 			= $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$arrContent['grandTotalLabel'] 			= $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		
		$arrSurcharges = deserialize($objOrderData->surcharges);
		if (is_array($arrSurcharges) && count($arrSurcharges))
		{
			foreach( $arrSurcharges as $k => $arrSurcharge )
			{
				$arrSurcharges[$k]['price']			= $this->Isotope->formatPriceWithCurrency($arrSurcharge['price']);
				$arrSurcharges[$k]['total_price']	= $this->Isotope->formatPriceWithCurrency($arrSurcharge['total_price']);
			}
		}
		else
		{
			$arrSurcharges = array();
		}
				
		$arrContent['surcharges'] = $arrSurcharges;
		
		$arrContent['billing_label'] 	= $GLOBALS['TL_LANG']['ISO']['billing_address'];
		$arrContent['billing_address'] 	= $this->Isotope->generateAddressString(deserialize($objOrderData->billing_address), $this->Isotope->Config->billing_fields);
		
		if (strlen($arrOrder['shipping_method']))
		{
			$arrShippingAddress = deserialize($objOrderData->shipping_address);
			if (!is_array($arrShippingAddress) || $arrShippingAddress['id'] == -1)
			{
				$arrContent['has_shipping'] 	= false;
				$arrContent['billing_label'] 	= $GLOBALS['TL_LANG']['ISO']['billing_shipping_address'];
			}
			else
			{
				$arrContent['has_shipping'] 	= true;
				$arrContent['shipping_label'] 	= $GLOBALS['TL_LANG']['ISO']['shipping_address'];
				$arrContent['shipping_address'] = $this->Isotope->generateAddressString($arrShippingAddress, $this->Isotope->Config->shipping_fields);
			}
		}
		
		return $arrContent;
	
	}
	
	
	/**
 	 * Convert a PHP assoc array to a SOAP array of array of string
 	 *
 	 * @param array $assoc
 	 * @return array
 	 */
	protected function assocArrayToArrayOfArrayOfString($assoc)
	{
    	$arrayKeys   = array_keys($assoc);
    	$arrayValues = array_values($assoc);

    	return array ($arrayKeys, $arrayValues);
	}
 
 
	/**
 	 * Convert a PHP multi-depth assoc array to a SOAP array of array of array of string
 	 *
 	 * @param array $multi
 	 * @return array
 	 */
	protected function multiAssocArrayToArrayOfArrayOfString($multi)
	{
    	$arrayKeys   = array_keys($multi[0]);
    	$arrayValues = array();

	    foreach ($multi as $v) {
    	    $arrayValues[] = array_values($v);
    	}

	    $_arrayKeys = array();
    	$_arrayKeys[0] = $arrayKeys;

	    return array_merge($_arrayKeys, $arrayValues);
	}
	
	
	public static function html_entity_decode_utf8($item)
	{
		return html_entity_decode($item, ENT_QUOTES, 'UTF-8');
	}
	
}
 
 
 
 
?>