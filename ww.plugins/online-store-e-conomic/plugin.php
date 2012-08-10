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

class OnlineStoreEconomics {
	private $agreementNumber;
	private $username;
	private $wsdlUrl;
	private $password;

	function __construct($agreementNumber, $username, $password) {
		$this->agreementNumber=$agreementNumber;
		$this->username=$username;
		$this->password=$password;
		$this->wsdlUrl='https://www.e-conomic.com/secure/api1/'
			.'EconomicWebservice.asmx?WSDL';
#		$this->wsdlUrl='https://secure.e-conomic.com/secure/api1/'
#			.'EconomicWebService.asmx?WSDL';
	}
	private function connect() {
		try {
			$client = new SoapClient($this->wsdlUrl,
				array("trace" => 1, "exceptions" => 1)
			);
			$client->Connect(
				array(
					'agreementNumber' => $this->agreementNumber,
					'userName' => $this->username,
					'password' => $this->password
				)
			);
		}
		catch (Exception $e) {
			echo __('Could not connect to E-Conomic server');
			die();
		}
		return $client;
	}
	public function Klassekladde(
		$accountNumber, $modkonto, $vatCode, $klassekladdetekst, $klassekladdebelob
	) {
		$klassekladde = 1;
		$date = date("Y-m-d\TH:i:s");
		$client=$this->connect();
		$PartOne = $client->CashBookEntry_CreateFinanceVoucher(
			array(
			'cashBookHandle' => array('Number' => $klassekladde),
			'accountHandle' => array('Number' => $accountNumber),
			'contraAccountHandle' => array('Number' => $modkonto)
			)
		);
		$id1 = $PartOne->CashBookEntry_CreateFinanceVoucherResult->Id1;
		$id2 = $PartOne->CashBookEntry_CreateFinanceVoucherResult->Id2;
		$PartOneAndAHalf = $client->CashBookEntry_GetVoucherNumber(
			array(
				'cashBookEntryHandle' => array(
				'Id1' => $id1,
				'Id2' => $id2
				)
			)
		);
		$bilagsnummer = $PartOneAndAHalf->CashBookEntry_GetVoucherNumberResult;
		$PartTwo = $client->CashBookEntry_UpdateFromData(
			array(
				'data' => array(
					'Handle' => array('Id1' => $id1, 'Id2' => $id2),
					'Id1' => $id1,
					'Id2' => $id2,
					'Type' => 'FinanceVoucher',
					'CashBookHandle' => array('Number' => $klassekladde),
					'VoucherNumber' => $bilagsnummer,
					'Amount' => $klassekladdebelob,
					'AmountDefaultCurrency' => $klassekladdebelob,
					'Currency' => 'DKK',
					'CurrencyHandle' => array('Code' => 'DKK'),
					'Text' => $klassekladdetekst,
					'Date' => $date,
					'AccountHandle' => array('Number' => $accountNumber),
					'VatAccountHandle' => array('VatCode' => $vatCode),
					'ContraAccountHandle' => array('Number' => $modkonto)
				)
			)
		);
		return $bilagsnummer;
	}
	public function getCashBooks() {
		$client=$this->connect();
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
	public function getCashBookDetails($int) {
		$client=$this->connect();
		if (!isset($this->cashbooks[$int])) {
			$result=$client->CashBook_GetData(
				array('entityHandle'=>array('Number'=>$int))
			);
			$this->cashbooks[$int]=$result->CashBook_GetDataResult;
		}
		return $this->cashbooks[$int];
	}
	public function getDebtorGroups() {
		$client=$this->connect();
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
	public function getDebtorGroupDetails($int) {
		$client=$this->connect();
		if (!isset($this->debtorgroups[$int])) {
			$result=$client->DebtorGroup_GetData(
				array('entityHandle'=>array('Number'=>$int))
			);
			$this->debtorgroups[$int]=$result->DebtorGroup_GetDataResult;
		}
		return $this->debtorgroups[$int];
	}
}
function OnlineStoreEconomics_recordTransaction($PAGEDATA, $order) {
	$details=json_decode($order['form_vals'], true);
	$email=$details['Email'];
	$name=$details['FirstName'];
	$surname=$details['Surname'];
	mail('kae@verens.com', 'test', print_r($order, true));
}
