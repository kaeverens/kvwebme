<?php

/*   _             _      _ _           _     _ 
  __| | __ _ _ __ (_) ___| | |__   __ _| |__ | |
 / _` |/ _` | '_ \| |/ _ \ | '_ \ / _` | '_ \| |
| (_| | (_| | | | | |  __/ | |_) | (_| | | | | |
 \__,_|\__,_|_| |_|_|\___|_|_.__/ \__,_|_| |_|_|
  © Copyright Daniel Bahl 2011 - www.danielbahl.dk
*/

// ************
// Husk at indsætte dit brugernavn, kodeord og aftalenr. under her
// ************ 

    // Variabler: Login-data e-conomics 
    $agreementNumber = "123456";  
    $username = "demokonto";  
    $password = "dithemmeligepassword";  

    // Variabler: URL Variabler 
    $wsdlUrl = 'https://www.e-conomic.com/secure/api1/EconomicWebservice.asmx?WSDL'; 


	/* 
	 * Daniel Bahl's Economics Script 
	 * Copyright 2010 - Daniel Bahl 
	 * Soap your hands up, then wash in hot water! 
	 * 
	 * Systemkrav: 
	 * Testet med PHP 5.2 og 5.3 
	 * Kraever 5.0.1+ 
	 * 
	 * Bruger SoapClient::SoapClient â€” SoapClient constructor 
	 * http://php.net/manual/en/soapclient.soapclient.php 
	 *  
	 * Kraever PHP SOAP extension 
	 * http://www.php.net/manual/en/soap.installation.php 
	 * 
	 */ 
 
		/*
		 Eksempel:
		 
		// Inkluderer min toolbox Libary fil
			
			include("dbEconomics.php");
			$Regnskabsprogram = new dbEconomics();
		
		// Indsæt data i klassekladden
		
			// $konto, $modkonto, $momskode, $klassekladdetekst, $klassekladdebelob
			$TestStreng = $Regnskabsprogram -> Klassekladde(1010, 5820, "U25","Testpostering","-10.00");
		
		*/
 

class dbEconomics {

  public function Klassekladde($konto, $modkonto, $momskode, $klassekladdetekst, $klassekladdebelob) {
  
  	global $agreementNumber, $username, $password, $wsdlUrl;
  
    /* 
     * 
     * I mit eksempel under her oensker jeg at oprette en 
     * postering i min klassekladde. 
     * 
     * =================================================== 
     * DETTE SKAL MULIGVIS RETTES IND TIL JERES KONTOPLAN 
     *  Eksemplet bruger E-conomics' Standard Kontoplan 
     * =================================================== 
     * 
     * Posteringen i dette eksempel skal bestaa af: 
     * 
     * kredittering @ konto 1010 
     * (Salg varer/ydelser inkl. moms) 
     * Beloeb -10.00 (jeg har solgt for 10 kr.) 
     * 
     * debitering @ modkonto 5820 
     * (Bankkonto) 
     * 10.00 DKK (samme beloeb, indsat paa min konto) 
     * 
     */ 


    // Data til klassekladden (Defineres af funktionen)
    	//$konto = "1010"; // Salg af varer/ydelser m/moms 
   		//$modkonto = "5820"; // Bankkonto 
    	//$momskode = "U25"; // I25 eller U25 
    	//$klassekladdetekst = "Testpostering"; 
    	//$klassekladdebelob = "-10.00"; 

    // Hvilken klassekladde oensker du at oprette en postering i? 
    $klassekladde = 1; 

    // Dato for postering i formatet 2010-12-24T18:31:00' 
    $bilagsdato = date("Y-m-d\TH:i:s"); 

		// Opsætter fejlhåndtering:

		try {
		
		    // Opretter en forbindelse til overstaaende Webserivce WSDL Url: 
		    $client = new SoapClient($wsdlUrl, array("trace" => 1, "exceptions" => 1)); 
		
		    // Sender Brugernavn og Kodeord over HTTPS (Krypteret forbindelse) 
		    $client->Connect(array( 
		        'agreementNumber' => $agreementNumber, 
		        'userName' => $username, 
		        'password' => $password)); 
		
		 } catch (Exception $e) {
		 
		 	echo "Der opstod en fejl i kommunikationen med e-conomics.dk API-server";
		 	die();
		 	
		 	// Du kan evt. sætte denne catch til at sende dig en e-mail, så du ved, hvornår en
		 	// bogføring er gået galt. Efter test igennem 1 år nu, har jeg kun oplevet éet udfald
		 	// på e-conomics.dk API system.
		 
		 }


    // Opretter vores kassekladde-postering 

    $PartOne = $client->CashBookEntry_CreateFinanceVoucher(array( 
        'cashBookHandle' => array('Number' => $klassekladde), 
        'accountHandle' => array('Number' => $konto), 
        'contraAccountHandle' => array('Number' => $modkonto) 
    )); 

    // Finder de to unikke ID-numre vores klasseklasse har faaet 

    $id1 = $PartOne->CashBookEntry_CreateFinanceVoucherResult->Id1; 
    $id2 = $PartOne->CashBookEntry_CreateFinanceVoucherResult->Id2; 

    // Finder bilagsnummer vores klassekladde har faaet ud fra vores ID-nr. 

    $PartOneAndAHalf = $client->CashBookEntry_GetVoucherNumber(array( 

        'cashBookEntryHandle' => array( 

            'Id1' => $id1, 
            'Id2' => $id2 

        ) 
    )); 

    // Definerer variablen 'bilagsnummer' med vores unikke bilagsnummer 

    $bilagsnummer = $PartOneAndAHalf->CashBookEntry_GetVoucherNumberResult; 

    // Opdaterer vores postering med alle de noedvendige data: 

    $PartTwo = $client->CashBookEntry_UpdateFromData(array( 

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
            'Date' => $bilagsdato, 
            'AccountHandle' => array('Number' => $konto), 
            'VatAccountHandle' => array('VatCode' => $momskode), 
            'ContraAccountHandle' => array('Number' => $modkonto) 

        ))); 

    // Log ind paa din www.e-conomic.dk konto og tjek Bogfoering -> Daglig 
    
    // Retunerer Bilagsnummeret, så vi har det, hvis vi vil gemme det i en database eller lign.
    
     return $bilagsnummer;
 }
 
 public function TilknytPDF($bilagsnummer,$pdffil) {
 
	global $agreementNumber, $username, $password, $wsdlUrl;
 
     // Hvilken klassekladde oensker du at oprette en postering i? 
    $klassekladde = 1; 

    // Dato for postering i formatet 2010-12-24T18:31:00' 
    $bilagsdato = date("Y-m-d\TH:i:s"); 

	// Opsætter fejlhåndtering:

	try {

    // Opretter en forbindelse til overstaaende Webserivce WSDL Url: 
    $client = new SoapClient($wsdlUrl, array("trace" => 1, "exceptions" => 1)); 

    // Sender Brugernavn og Kodeord over HTTPS (Krypteret forbindelse) 
    $client->Connect(array( 
        'agreementNumber' => $agreementNumber, 
        'userName' => $username, 
        'password' => $password)); 

	 } catch (Exception $e) {
 
 	echo "Der opstod en fejl i kommunikationen med e-conomics.dk API-server";
 	die();
 	
 	// Du kan evt. sætte denne catch til at sende dig en e-mail, så du ved, hvornår en
 	// bogføring er gået galt. Efter test igennem 1 år nu, har jeg kun oplevet éet udfald
 	// på e-conomics.dk API system.
 
	 }


 	$encoded = file_get_contents($pdffil);
	 // Nogle PDF-filer skal encodeds først, brug understående, hvis det ikke virker:
	 //$encoded = base64_encode($pdffil);
 
	$uploadresult = $client->CashBook_RegisterPdfVoucher(array("data"=>$encoded, "voucherNumber"=>$bilagsnummer, "entryDate"=>$bilagsdato));
 
 }
 
// Afslutter Class()
}
    
?>