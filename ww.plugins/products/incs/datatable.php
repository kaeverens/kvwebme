<?php
/**
	* Products_datatable
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

$product= $smarty->smarty->tpl_vars['product']->value;
$ptid=$product->get('product_type_id');
$type= ProductType::getInstance($ptid);
if (!$type) {
	$c=__('Missing Product Type: %1', array($ptid), 'core');
}
$datafields= $type->data_fields;
if (!is_array($datafields)) {
	$datafields=array();
}
$c = '<table>';
if (!isset($params['align']) || $params['align']!='horizontal') {
	foreach ($datafields as $data) {
		$name = $data->ti
			?$data->ti
			:ucwords(str_replace('_', ' ', $data->n));
		$c.= '<tr><th class="left">';
		$c.= htmlspecialchars(ucfirst($name));
		$c.= '</th><td>';
		if (!isset($product->vals[$data->n])) {
			$product->vals[$data->n]='';
		}
		switch($data->t) {
			case 'date': // {
				$c.=Core_dateM2H($product->vals[$data->n]);
			break; // }
			case 'checkbox': // {
				if ($product->vals[$data->n]) {
					$c.=__('Yes');
				}
				else {
					$c.=__('No');
				}
			break; // }
			case 'textarea': // {
				$c.=__FromJson($product->vals[$data->n]);
			break; // }
			default: // {
				if (isset($product->vals[$data->n])) {
					$c.=htmlspecialchars(__FromJson($product->vals[$data->n]));
				}
				else {
					$c.= '&nbsp;';
				}
				// }
		}
		$c.='</td></tr>';
	}
}
else {
	$c.= '<thead>';
	$c.= '<tr>';
	foreach ($datafields as $data) {
		$name = $data->ti
			?$data->ti
			:ucwords(str_replace('_', ' ', $data->n));
		$c.= '<th>'.htmlspecialchars(ucfirst($name)).'</th>';
	}
	$c.= '</tr>';
	$c.= '</thead>';
	$c.='<tbody>';
	$c.= '<tr>';
	foreach ($datafields as $data) {
		$c.= '<td>';
		switch ($data->t) {
			case 'date' : // {
				$c.= Core_dateM2H($product->vals[$data->n]);
			break; // }
			case 'checkbox': // {
				if (isset($product->vals[$data->n])) {
					$c.=__('Yes');
				}
				else{ 
					$c.=__('No');
				}
			break; // }
			case 'textarea': // {
				$c.= $product->vals[$data->n];
			break; // }
			default: // {
				if (isset($product->vals[$data->n])) {
					$c.=htmlspecialchars($product->vals[$data->n]);
				}
				else {
					$c.='&nbsp;';
				}
				// }
		}
		$c.='</td>';
	}
	$c.= '</tr>';
	$c.= '</tbody>';
}
$c.= '</table>';
