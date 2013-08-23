<?php
/*
	Webme Mailing List Plugin v0.2
	File: admin/actions.email.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

echo '
<link rel="stylesheet" type="text/css" href="/ww.plugins/mailing-list/files'
.'/mailing-list.css"/>
<script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.v'
.'alidate/1.8/jquery.validate.min.js"></script>
<script type="text/javascript">
  $(function() {
	  $("#emailForm").validate({
		rules: {
		  	subject:{
				required:true,
				minlength:2
			},
		  	body:"required"
		},
		messages:{
	  		subject: "",
		  	body: ""
		},
		onkeyup:true
	  });
	});
</script>
';

function send_email($subject, $body, $type, $reply_to) {
	if ($subject==''||$body=='') {
		return false;
	}
	$options=dbAll('select name,value from mailing_list_options');
	foreach ($options as $option) {
		$OPT[$option['name']]=$option['value'];
	}
	$list=dbAll('select * from mailing_list where status="activated"');
	$emails='';
	$num='';
	foreach ($list as $email) {
		$emails.=$email['email'].',';
		$num++;
	}
  $headers='From: '.$OPT['email']."\r\n";
	if ($repyl_to!='') {
		$headers.='Reply-To:'.$reply_to."\r\n";
	}
	if ($type=='HTML') {
		$headers.='Content-type: text/html; charset=iso-8859-1'."\r\n";
	}
	else {
		$headers.='Content-Type: text/plain; charset=iso-8859-1'."\r\n";
	}
	if ($OPT['use_bcc']==1) {
		$headers.='Bcc: '.$emails."\r\n";
	}
	else {
		$to=$emails;
	}
	$headers.='X-Mailer: PHP '.phpversion();
  $headers.='MIME-Version: 1.0'."\n";
  mail($to, $subject, $body, $headers);
  return $num;
}

if (isset($_POST['send_mail'])) {
	$num=send_email(
		addslashes($_POST['subject']),
		addslashes($_POST['body']),
		addslashes($_POST['type']),
		addslashes($_POST['reply_to'])
	);
	if ($num==false) {
		$updated='Please do not leave the subject or body empty.';
	}
	else {
		$updated='Email sent to '.$num.' recipients.'; 
	}
}

echo '<h3>New Email</h3>';

if (isset($updated)) {
	echo '<em>'.$updated.'</em>';
}

?>
<form method="post" id="emailForm">
	<div class="mailing-list-tabs">
		<ul>
			<li><a href="#mailing-list-email-main-details">Main</a><li>
			<li><a href="#mailing-list-email-options">Options</a></li>
		</ul>
		<div id="mailing-list-email-main-details">
			<table style="width:100%">
				<tr>
					<th>Subject</th>
					<td><input name="subject" value="" style="width:85%"/>
				</td>
				<tr><th>Body</th><td colspan="2">'.ckeditor('body').'</td></tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" name="send_mail" value="Send Email"/></td>
				</tr>
			</table>
		</div>
		<div id="mailing-list-email-options">
			<table width="100%">
				<tr>
					<td>Type:</td>
					<td><select name="type">
						<option>HTML</option><option>Text</option>
					</td>
				</tr>
				<tr><td>Reply To:</td><td><input type="text" name="reply_to"/></td></tr>
			</table>
		</div>
	</div>
</form>
