<?php
/**
	* Online-Store E-Conomic plugin
	* see http://www.e-conomic.co.uk/
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name' => function() {
		return __('Online Store E-conomic plugin');
	},
	'admin' => array( // {
		'menu' => array(
			'Online Store>E-conomic Setup'=>
				'plugin.php?_plugin=online-store-e-conomic&amp;_page=setup'
		)
	), // }
	'description'=>function() {
		return __(
			'Add e-conomic.co.uk integration to online store.'
		);
	},
	'frontend' => array( // {
	), // }
	'triggers' => array( // {
		'after-order-processed'=>'OnlineStoreEconomics_recordTransaction',
	), // }
	'version' => '0'
);
// }

// { OnlineStoreEconomics

/**
	* class for handling E-Conomic transactions
	*
	* @category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class OnlineStoreEconomics{
	private $_agreementNumber;
	private $_username;
	private $_password;
	private $_debtors=array();
	private $_products=array();

	// { __construct

	/**
		* constructor
		*
		* @param string $agreementNumber agreement number
		* @param string $username        username
		* @param string $password        password
		*
		* @return null
		*/
	function __construct($agreementNumber, $username, $password) {
		$this->_agreementNumber=$agreementNumber;
		$this->_username=$username;
		$this->_password=$password;
	}

	// }
	// { addInvoiceLine

	/**
		* add an invoice line
		*
		* @return details
		*/
	public function addInvoiceLine($invId, $itemId, $short_desc, $cost, $amt) {
		$client=$this->_connect();
		global $DBVARS;
		$lineHandle=$client->CurrentInvoiceLine_Create(array(
			'invoiceHandle'=>array('Id'=>$invId)
		));
		$lineId=$lineHandle->CurrentInvoiceLine_CreateResult->Id;
		$lineNr=$lineHandle->CurrentInvoiceLine_CreateResult->Number;
		$product=$this->getProductByNumber((int)$itemId);
		if (!$product) {
			$this->createProduct(
				$itemId,
				$DBVARS['economic_productgroup'],
				$short_desc
			);
			$product=$this->getProductByNumber((int)$itemId);
		}
		$client->CurrentInvoiceLine_SetProduct(array(
			'currentInvoiceLineHandle'=>array('Id'=>$lineId, 'Number'=>$lineNr),
			'valueHandle'=>array('Number'=>$itemId)
		));
		$client->CurrentInvoiceLine_SetDescription(array(
			'currentInvoiceLineHandle'=>array('Id'=>$lineId, 'Number'=>$lineNr),
			'value'=>$short_desc
		));
		$client->CurrentInvoiceLine_SetUnitNetPrice(array(
			'currentInvoiceLineHandle'=>array('Id'=>$lineId, 'Number'=>$lineNr),
			'value'=>$cost
		));
		$client->CurrentInvoiceLine_SetQuantity(array(
			'currentInvoiceLineHandle'=>array('Id'=>$lineId, 'Number'=>$lineNr),
			'value'=>$amt
		));
	}

	// }
	// { connect

	/**
		* connect to the e-conomic server
		*
		* @return the client object
		*/
	private function _connect() {
		try {
			$client = new SoapClient(
				'https://www.e-conomic.com/secure/api1/EconomicWebservice.asmx?WSDL',
				array("trace" => 1, "exceptions" => 1)
			);
			$client->Connect(
				array(
					'agreementNumber' => $this->_agreementNumber,
					'userName' => $this->_username,
					'password' => $this->_password
				)
			);
		}
		catch (Exception $e) {
			echo __('Could not connect to E-Conomic server');
			die();
		}
		return $client;
	}

	// }
	// { createDebtor

	/**
		* create a debtor
		*
		* @return details
		*/
	public function createDebtor($number, $debtorGroupHandle, $name) {
		$client=$this->_connect();
		$result=$client->Debtor_Create(array(
			'number'=>$number,
			'debtorGroupHandle'=>array('Number'=>$debtorGroupHandle),
			'name'=>$name,
			'vatZone'=>'HomeCountry'
		));
		return $result;
	}

	// }
	// { createInvoice

	/**
		* create an invoice
		*
		* @return details
		*/
	public function createInvoice($currency, $customer) {
		$client=$this->_connect();
		$result=$client->CurrentInvoice_Create(
			array(
				'debtorHandle'=>$customer
			)
		);
		$invId=$result->CurrentInvoice_CreateResult->Id;
		$client->CurrentInvoice_SetCurrency(array(
			'currentInvoiceHandle'=>array('Id'=>$invId),
			'valueHandle'=>array('Code'=>$currency)
		));
		return $invId;
	}

	// }
	// { createProduct

	/**
		* create a product
		*
		* @return details
		*/
	public function createProduct($number, $productGroupHandle, $name) {
		$client=$this->_connect();
		$result=$client->Product_Create(array(
			'number'=>$number,
			'productGroupHandle'=>array('Number'=>$productGroupHandle),
			'name'=>$name,
		));
		return $result;
	}

	// }
	// { getCashBooks

	/**
		* get all cash books
		*
		* @return array cashbooks
		*/
	public function getCashBooks() {
		$client=$this->_connect();
		if (isset($this->cashbooks)) {
			return $this->cashbooks;
		}
		$bookHandles=$client->CashBook_GetAll();
		$this->cashbooks=array();
		foreach ($bookHandles->CashBook_GetAllResult->CashBookHandle as $k=>$v) {
			$num=$v->Number;
			$this->getCashBookDetails($num);
		}
		return $this->cashbooks;
	}

	// }
	// { getCashBookDetails

	/**
		* get details about a cash book
		*
		* @param int $int ID of the cash book
		*
		* @return array of details
		*/
	public function getCashBookDetails($int) {
		$client=$this->_connect();
		if (!isset($this->cashbooks[$int])) {
			$result=$client->CashBook_GetData(
				array('entityHandle'=>array('Number'=>$int))
			);
			$this->cashbooks[$int]=$result->CashBook_GetDataResult;
		}
		return $this->cashbooks[$int];
	}

	// }
	// { getDebtorByNumber

	/**
		* get details about a debtor
		*
		* @param int $int the debtor's ID
		*
		* @return details
		*/
	public function getDebtorByNumber($int) {
		$client=$this->_connect();
		if (!isset($this->_debtors[$int])) {
			$result=$client->Debtor_FindByNumber(
				array('number'=>$int)
			);
			if (isset($result->Debtor_FindByNumberResult)) {
				$this->_debtors[$int]=$result->Debtor_FindByNumberResult;
			}
			else {
				$this->_debtors[$int]=false;
			}
		}
		return $this->_debtors[$int];
	}

	// }
	// { getDebtorGroups

	/**
		* get all debtor groups
		*
		* @return array of all debtor groups
		*/
	public function getDebtorGroups() {
		$client=$this->_connect();
		if (isset($this->debtorgroups)) {
			return $this->debtorgroups;
		}
		$bookHandles=$client->DebtorGroup_GetAll();
		$this->debtorgroups=array();
		foreach ($bookHandles->DebtorGroup_GetAllResult->DebtorGroupHandle as $k=>$v) {
			$num=$v->Number;
			$this->getDebtorGroupDetails($num);
		}
		return $this->debtorgroups;
	}

	// }
	// { getDebtorGroupDetails

	/**
		* get details about a debtor group
		*
		* @param int $int the debtor group's ID
		*
		* @return details
		*/
	public function getDebtorGroupDetails($int) {
		$client=$this->_connect();
		if (!isset($this->debtorgroups[$int])) {
			$result=$client->DebtorGroup_GetData(
				array('entityHandle'=>array('Number'=>$int))
			);
			$this->debtorgroups[$int]=$result->DebtorGroup_GetDataResult;
		}
		return $this->debtorgroups[$int];
	}

	// }
	// { getInvoiceAsPdf

	/**
		* get an invoice as a pdf
		*
		* @param int $int the invoice's ID
		*
		* @return details
		*/
	public function getInvoiceAsPdf($int) {
		$client=$this->_connect();
		$result=$client->CurrentInvoice_GetPdf(
			array(
				'currentInvoiceHandle'=>array('Id'=>$int)
			)
		);
		return $result->CurrentInvoice_GetPdfResult;
	}

	// }
	// { getProductByNumber

	/**
		* get details about a debtor
		*
		* @param int $int the debtor's ID
		*
		* @return details
		*/
	public function getProductByNumber($int) {
		$client=$this->_connect();
		if (!isset($this->_products[$int])) {
			$result=$client->Product_FindByNumber(
				array('number'=>$int)
			);
			if (isset($result->Product_FindByNumberResult)) {
				$this->_products[$int]=$result->Product_FindByNumberResult;
			}
			else {
				$this->_products[$int]=false;
			}
		}
		return $this->_products[$int];
	}

	// }
	// { getProductGroups

	/**
		* get all product groups
		*
		* @return array of all product groups
		*/
	public function getProductGroups() {
		$client=$this->_connect();
		if (isset($this->productgroups)) {
			return $this->productgroups;
		}
		$bookHandles=$client->ProductGroup_GetAll();
		$this->productgroups=array();
		foreach ($bookHandles->ProductGroup_GetAllResult->ProductGroupHandle as $k=>$v) {
			$num=$v->Number;
			$this->getProductGroupDetails($num);
		}
		return $this->productgroups;
	}

	// }
	// { getProductGroupDetails

	/**
		* get details about a product group
		*
		* @param int $int the product group's ID
		*
		* @return details
		*/
	public function getProductGroupDetails($int) {
		$client=$this->_connect();
		if (!isset($this->productgroups[$int])) {
			$result=$client->ProductGroup_GetData(
				array('entityHandle'=>array('Number'=>$int))
			);
			$this->productgroups[$int]=$result->ProductGroup_GetDataResult;
		}
		return $this->productgroups[$int];
	}

	// }
	// { setDebtorAddress

	/**
		* set a debtor's address
		*
		* @return details
		*/
	public function setDebtorAddress($number, $address) {
		$client=$this->_connect();
		$address=join(', ', $address);
		$address=preg_replace('/, $/', '', $address);
		$address=preg_replace('/, , /', ', ', $address);
		$result=$client->Debtor_SetAddress(array(
			'debtorHandle'=>array('Number'=>$number),
			'value'=>$address
		));
		return $result;
	}

	// }
	// { setDebtorCity

	/**
		* set a debtor's city
		*
		* @return details
		*/
	public function setDebtorCity($number, $city) {
		$client=$this->_connect();
		$result=$client->Debtor_SetCity(array(
			'debtorHandle'=>array('Number'=>$number),
			'value'=>$city
		));
		return $result;
	}

	// }
	// { setDebtorCounty

	/**
		* set a debtor's county
		*
		* @return details
		*/
	public function setDebtorCounty($number, $county) {
		$client=$this->_connect();
		$result=$client->Debtor_SetCounty(array(
			'debtorHandle'=>array('Number'=>$number),
			'value'=>$county
		));
		return $result;
	}

	// }
	// { setDebtorCountry

	/**
		* set a debtor's country
		*
		* @return details
		*/
	public function setDebtorCountry($number, $country) {
		$client=$this->_connect();
		$result=$client->Debtor_SetCountry(array(
			'debtorHandle'=>array('Number'=>$number),
			'value'=>$country
		));
		return $result;
	}

	// }
	// { setDebtorCurrency

	/**
		* set a debtor's currency
		*
		* @return details
		*/
	public function setDebtorCurrency($number, $currency) {
		$client=$this->_connect();
		$result=$client->Debtor_SetCurrency(array(
			'debtorHandle'=>array('Number'=>$number),
			'valueHandle'=>array('Code'=>$currency)
		));
		return $result;
	}

	// }
	// { setDebtorEmail

	/**
		* set a debtor's email
		*
		* @return details
		*/
	public function setDebtorEmail($number, $email) {
		$client=$this->_connect();
		$result=$client->Debtor_SetEmail(array(
			'debtorHandle'=>array('Number'=>$number),
			'value'=>$email
		));
		return $result;
	}

	// }
	// { setDebtorPhone

	/**
		* set a debtor's phone
		*
		* @return details
		*/
	public function setDebtorPhone($number, $phone) {
		$client=$this->_connect();
		$result=$client->Debtor_SetTelephoneAndFaxNumber(array(
			'debtorHandle'=>array('Number'=>$number),
			'value'=>$phone
		));
		return $result;
	}

	// }
	// { setDebtorPostCode

	/**
		* set a debtor's postcode
		*
		* @return details
		*/
	public function setDebtorPostCode($number, $postcode) {
		$client=$this->_connect();
		$result=$client->Debtor_SetPostalCode(array(
			'debtorHandle'=>array('Number'=>$number),
			'value'=>$postcode
		));
		return $result;
	}

	// }
}

// }
// { OnlineStoreEconomics_recordTransaction

/**
	* record a transaction
	*
	* @param object $PAGEDATA details about the page
	* @param array  $order    the order to record
	*
	* @return null
	*/
function OnlineStoreEconomics_recordTransaction($PAGEDATA, $order) {
	$details=json_decode($order['form_vals'], true);
	global $DBVARS;
	$OSE=new OnlineStoreEconomics(
		$DBVARS['economic_agreement_no'],
		$DBVARS['economic_user_id'],
		$DBVARS['economic_password']
	);
	// { check that customer is recorded in e-conomic
	if ($order['user_id']) { // use user_id as customer number
		$uid=(int)$order['user_id'];
	}
	else { // make customer number from the phone number
		$uid=preg_replace('/^[0-9]/', '', $details['Phone']);
		$uid=preg_replace('/^0*/', '', $uid);
		$uid=-(int)$uid;
	}
	$customer=$OSE->getDebtorByNumber($uid);
	if ($customer==false) { // record the customer
		$OSE->createDebtor(
			$uid,
			$DBVARS['economic_debtorgroup'],
			$details['FirstName'].' '.$details['Surname']
		);
		$OSE->setDebtorAddress(
			$uid,
			array(
				$details['Billing_Street'],
				$details['Billing_Street2']
			)
		);
		$OSE->setDebtorPostCode(
			$uid,
			$details['Billing_Postcode']
		);
		$OSE->setDebtorCity(
			$uid,
			$details['Billing_Town']
		);
		$OSE->setDebtorCounty(
			$uid,
			$details['Billing_County']
		);
		$OSE->setDebtorCountry(
			$uid,
			$details['Billing_Country']
		);
		$OSE->setDebtorEmail(
			$uid,
			$details['Billing_Email']
		);
		$OSE->setDebtorPhone(
			$uid,
			$details['Billing_Phone']
		);
		$OSE->setDebtorCurrency(
			$uid,
			$DBVARS['online_store_currency']
		);
		$customer=$uid;
	}
	// { create the invoice
	$invId=$OSE->createInvoice(
		$DBVARS['online_store_currency'],
		$customer
	);
	$items=json_decode($order['items']);
	foreach ($items as $item) {
		$OSE->addInvoiceLine(
			$invId,
			$item->id,
			$item->short_desc,
			$item->cost,
			$item->amt
		);
	}
	mail('kae.verens@gmail.com', 'test', print_r($order, true));
	// }
	// { get PDF
	$pdf=$OSE->getInvoiceAsPdf($invId);
	require_once SCRIPTBASE.'/ww.incs/mail.php';
	$dirname=USERBASE.'/ww.cache/online-store/invoices';
	$fname=$dirname.'/'.$invId.'.pdf';
	@mkdir($dirname);
	file_put_contents($fname, $pdf);
	send_mail(
		$details['Billing_Email'], 'admin@localhost.localdomain',
		'test4', 'test5', array(
			array(
				'tmp_name'=>$fname,
				'name'=>$invId.'.pdf',
				'type'=>'application/pdf'
			)
		)
	);
	// }
	// }
}

// }
