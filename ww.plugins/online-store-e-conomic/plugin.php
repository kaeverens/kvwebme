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
	$email=$details['Email'];
	$name=$details['FirstName'];
	$surname=$details['Surname'];
	mail('kae@verens.com', 'test', print_r($order, true));
}

// }
