<?php

// { Mailinglists_subscribe

/**
	* subscribe to a mailinglist
	*
	* @return status
	*/
function Mailinglists_subscribe() {
	$list=(int)$_REQUEST['list'];
	$email=$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return array('error'=>__('Not an email address'));
	}
	$sql='select * from mailinglists_lists';
	if ($list) {
		$sql.=' where id='.$list;
	}
	$list=dbRow($sql);
	if (!$list) {
		return array('error'=>__('No such mailing list'));
	}
	$listMeta=json_decode($list['meta'], true);
	switch ($listMeta['engine']) {
		case 'Ubivox': // {
			$apiusername=$listMeta['ubivox-apiusername'];
			$apipassword=$listMeta['ubivox-apipassword'];
			$listId=preg_replace('/\|.*/', '', $listMeta['ubivox-list']);
			$response=Mailinglists_xmlrpcClient(
				$apiusername,
				$apipassword,
				xmlrpc_encode_request(
					'ubivox.create_subscription',
					array($email, array($listId), true)
				)
			);
			$data=xmlrpc_decode(trim($response));
		break; // }
		default: // {
			$apikey=$listMeta['mailchimp-apikey'];
			require_once dirname(__FILE__).'/MCAPI.class.php';
			$api=new MCAPI($apikey);
			$data=$api->lists();
			$api->listSubscribe(
				preg_replace('/\|.*/', '', $listMeta['mailchimp-list']),
				$email
			);
			if ($api->errorCode) {
				return array(
					'error'=>$api->errorCode,
					'message'=>$api->errorMessage
				);
			}
		// }
	}
	return array('ok'=>true);
}

// }
