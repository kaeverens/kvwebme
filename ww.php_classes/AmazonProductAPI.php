<?php
 
class AmazonProductAPI {
	private $public_key	 = "YOUR AMAZON ACCESS KEY ID";
	private $private_key	= "YOUR AMAZON SECRET KEY";
	private $associate_tag  = "YOUR AMAZON ASSOCIATE TAG";
	public function __construct($public_key, $private_key, $associate_tag) {
		$this->public_key=$public_key;
		$this->private_key=$private_key;
		$this->associate_tag=$associate_tag;
	}
	private function verifyXmlResponse($response) {
		if ($response === False)
		{
			throw new Exception("Could not connect to Amazon");
		}
		else
		{
			if (isset($response->Items->Item->ItemAttributes->Title))
			{
				return ($response);
			}
			else
			{
				throw new Exception("not found in Amazon");
			}
		}
	}
	private function queryAmazon($params) {
		$region='com';
		$public_key=$this->public_key;
		$private_key=$this->private_key;
		$associate_tag=$this->associate_tag;
    $method = "GET";
    $host = "ecs.amazonaws.".$region;
    $uri = "/onca/xml";
    $params["Service"]          = "AWSECommerceService";
    $params["AWSAccessKeyId"]   = $public_key;
    $params["AssociateTag"]     = $associate_tag;
    $params["Timestamp"]        = gmdate("Y-m-d\TH:i:s\Z");
    /* The params need to be sorted by the key, as Amazon does this at
      their end and then generates the hash of the same. If the params
      are not in order then the generated hash will be different from
      Amazon thus failing the authentication process.
    */
    ksort($params);
    $canonicalized_query = array();
    foreach ($params as $param=>$value)
    {
        $param = str_replace("%7E", "~", rawurlencode($param));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $canonicalized_query[] = $param."=".$value;
    }
 
    $canonicalized_query = implode("&", $canonicalized_query);
 
    $string_to_sign = $method."\n".$host."\n".$uri."\n".
                            $canonicalized_query;
    /* calculate the signature using HMAC, SHA256 and base64-encoding */
		$signature = base64_encode(hash_hmac("sha256", 
                                  $string_to_sign, $private_key, True));
    /* encode the signature for the request */
    $signature = str_replace("%7E", "~", rawurlencode($signature));
 
    /* create request */
    $request = "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
 
    /* I prefer using CURL */
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
 
    $xml_response = curl_exec($ch);
 
    if ($xml_response === False)
    {
        return False;
    }
    else
    {
        /* parse XML and return a SimpleXML object, if you would
           rather like raw xml then just return the $xml_response.
         */
        $parsed_xml = @simplexml_load_string($xml_response);
        return ($parsed_xml === False) ? False : $parsed_xml;
    }
	}
	public function getItemByEan($ean_code, $product_type) {
		$parameters = array("Operation"	 => "ItemLookup",
							"ItemId"		=> $ean_code,
							"SearchIndex"   => 'Blended', //$product_type,
							"IdType"		=> "EAN",
							"ResponseGroup" => "Medium");
 
		$xml_response = $this->queryAmazon($parameters);
		return $this->verifyXmlResponse($xml_response);
	}
}
