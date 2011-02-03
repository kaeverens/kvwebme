<?php
function send_mail($emailaddress, $fromaddress, $emailsubject, $body, $attachments=false){
  $eol="\n";
  $mime_boundary=md5(time());
	$mime_boundary2='1_'.$mime_boundary;
	$now=time();
	if(!$fromaddress)$fromaddress='no@email.supplied';
 
  # Common Headers
  $headers = 'From: <'.$fromaddress.'>'.$eol;
  $headers .= 'Reply-To: <'.$fromaddress.'>'.$eol;
  $headers .= 'Return-Path: <'.$fromaddress.'>'.$eol;    // these two to set reply address
  $headers .= "Message-ID: <".$now." php@".$_SERVER['SERVER_NAME'].">".$eol;
  $headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

  # Boundry for marking the split & Multitype Headers
  $headers .= 'MIME-Version: 1.0'.$eol;
  $headers .= "Content-Type: multipart/related; boundary=\"".$mime_boundary."\"".$eol.$eol;

  $msg = "--".$mime_boundary.$eol;
 
  # Setup for text OR html
  $msg .= "Content-Type: multipart/alternative; boundary=\"".$mime_boundary2."\"".$eol.$eol;
 
  # Text Version
  $msg .= "--".$mime_boundary2.$eol;
  $msg .= "Content-Type: text/plain; charset=utf-8".$eol;
  $msg .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
  $msg .= strip_tags(str_replace("<br>", "\n", $body)).$eol.$eol;
 
  # HTML Version
  $msg .= "--".$mime_boundary2.$eol;
  $msg .= "Content-Type: text/html; charset=utf-8".$eol;
  $msg .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
  $msg .= $body.$eol.$eol;

	# finish message boundary
	$msg.="--".$mime_boundary2."--".$eol.$eol;
 
	$files=array();
	if(is_array($attachments)){
		foreach($attachments as $f){
			$files[]=array('file'=>$f['tmp_name'],'name'=>$f['name'],'content_type'=>$f['type']);
		}
	}
  if(count($files))
  {

    for($i=0; $i < count($files); $i++)
    {
      if (is_file($files[$i]["file"]))
      {  
        # File for Attachment
        $file_name = $files[$i]['name'];
       
        $handle=fopen($files[$i]["file"], 'rb');
        $f_contents=fread($handle, filesize($files[$i]["file"]));
        $f_contents=chunk_split(base64_encode($f_contents));    //Encode The Data For Transition using base64_encode();
        fclose($handle);
       
        # Attachment
        $msg .= "--".$mime_boundary.$eol;
        $msg .= "Content-Type: ".$files[$i]["content_type"]."; name=\"".$file_name."\"".$eol;
        $msg .= "Content-Transfer-Encoding: base64".$eol;
        $msg .= "Content-Disposition: attachment; filename=\"".$file_name."\"".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
        $msg .= $f_contents.$eol.$eol;
       
      }
    }
  }
 
  # Finished
  $msg .= "--".$mime_boundary."--".$eol.$eol;  // finish with two eol's for better security. see Injection.
   
  # SEND THE EMAIL
  ini_set('sendmail_from',$fromaddress);  // the INI lines are to force the From Address to be used !
  mail($emailaddress, $emailsubject, $msg, $headers);
  ini_restore('sendmail_from');
}
