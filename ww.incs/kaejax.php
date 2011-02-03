<?php	
if(!isset($SAJAX_INCLUDED)){
	{ # variables
		$kaejax_debug_mode=0;
		$kaejax_js_has_been_shown=0;
		$kaejax_export_list=array();
		$kaejax_request_type='POST';
		$kaejax_is_loaded=strstr($_SERVER['REQUEST_URI'],'kaejax_is_loaded');
	}	
	function kaejax_decode_unicode_url($str){
		# this code taken from here: http://php.net/urldecode
		$res='';
		$i=0;
		$max=strlen($str)-6;
		while($i<=$max){
			$character=$str[$i];
			if($character=='%'&&$str[$i+1]=='u'){
				$value=hexdec(substr($str,$i+2,4));
				$i+=6;
				if($value<0x0080)$character=chr($value);
				else if($value<0x0800)$character=chr((($value&0x07c0)>>6)|0xc0).chr(($value&0x3f)|0x80);
				else $character=chr((($value&0xf000)>>12)|0xe0).chr((($value&0x0fc0)>>6)|0x80).chr(($value&0x3f)|0x80);
			}
			else ++$i;
			$res.=$character;
		}
		return $res.substr($str, $i);
	}
	function kaejax_handle_client_request(){
		header('Content-type: text/javascript; Charset=utf-8');
		if(!isset($_POST['kaejax']))return;
		$unmangled=kaejax_decode_unicode_url(str_replace(array('%2B',"\r","\n","\t"),array('+','\r','\n','\t'),$_POST['kaejax']));
		$obj=json_decode($unmangled);
		$fs=$obj->c;
		$res=array();
		foreach($fs as $f)$res[]=call_user_func_array($f->f,$f->v);
		echo json_encode($res);
		exit;
	}
	function kaejax_export(){
		global $kaejax_export_list;
		$n=func_num_args();
		for($i=0;$i<$n;$i++)$kaejax_export_list[]=func_get_arg($i);
	}
	function kaejax_show_javascript(){
		global $kaejax_js_has_been_shown,$kaejax_is_loaded,$kaejax_export_list;
		if(!$kaejax_js_has_been_shown&&!$kaejax_is_loaded)$kaejax_js_has_been_shown=1;
		$html='kaejax_create_functions("'.preg_replace('/\&\?.*/','',$_SERVER['REQUEST_URI']).'",["'.join('","',$kaejax_export_list).'"]);';
		$kaejax_is_loaded=1;
		echo $html;
	}
	$SAJAX_INCLUDED=1;
}
