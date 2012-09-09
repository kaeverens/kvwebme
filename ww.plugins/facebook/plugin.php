<?php
/**
	* definition file for FaceBook plugin
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
	'name' => 'FaceBook',
	'description' => 'add various FaceBook widgets to your site',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/facebook/admin/widget.php',
			'js_include' =>'/ww.plugins/facebook/admin/widget.js'
		)
	),
	'frontend' => array(
		'widget' => 'FaceBook_widgetShow'
	)
);
// }

/**
	* returns a HTML string to show the FaceBook widget
	*
	* @param object $vars plugin parameters
	*
	* @return string
	*/
function FaceBook_widgetShow($vars=null) {
	global $PAGEDATA;
	switch(@$vars->what_to_show) {
		case 'like-gateway': // {
			require_once SCRIPTBASE.'/ww.external/facebook/facebook.php';
			$config=array(
				'appId'=>$vars->app_id,
				'secret'=>$vars->app_secret
			);
			$facebook=new Facebook($config);
			// { add js sdk
			$html='<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : "'.$vars->app_id.'",
      channelUrl : "//'.$_REQUEST['HTTP_HOST'].'/channel.html",
      status     : true,
      cookie     : true,
      xfbml      : true
    });
  };
  (function(d){
     var js, id = "facebook-jssdk", ref = d.getElementsByTagName("script")[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement("script"); js.id = id; js.async = true;
     js.src = "//connect.facebook.net/en_US/all.js";
     ref.parentNode.insertBefore(js, ref);
   }(document));
</script>';
			// }
			$uid=$facebook->getUser();
			if ($uid==0) { // not logged in
				echo '<a href="'
					.$facebook->getLoginUrl(
						array('scope'=>'publish_stream')
					)
					.'">'.$vars->click_message.'</a>';
			}
			else {
				$facebook->api(
					'/'.$uid.'/feed',
					'post',
					array('message' => $vars->wall_message
					)
				);
				$html=$vars->thankyou_message;
				$gs=dbAll(
					'select * from users_groups where groups_id=1', 'user_accounts_id'
				);
				$emails=array_keys(
					dbAll(
						'select email from user_accounts where id in ('
						.join(',', array_keys($gs)).')', 'email'
					)
				);
				$details=$facebook->api('/me', 'GET');
				Core_mail(
					join(', ', $emails),
					'['.$_SERVER['HTTP_HOST'].'] Facebook post',
					'<p>A customer has clicked the Like gateway on your website,'
					.' posting to their wall.</p><p>Their details are:</p><ul>'
					.'<li>Name: '.$details['name'].'</li>'
					.'<li>Gender: '.$details['gender'].'</li>'
					.'<li>Facebook Link: '.$details['link'].'</li>'
					.'</ul>'
					.'<p>this is an automated email; please do not reply to it.</p>',
					'no-reply@'.$_SERVER['HTTP_HOST']
				);
			}
			echo $html;
		break; // }
		default: // {
			if (!isset($vars->show_faces)) {
				$vars->show_faces='1';
			}
			$show_faces=$vars->show_faces;
			if (!isset($vars->layout)) {
				$vars->layout='standard';
			}
			switch ($vars->layout) {
				case 'standard': // {
					$w=225;
					$h=$show_faces=='1'?80:35;
				break; // }
				case 'button_count': // {
					$w=90;
					$h=20;
				break; // }
				default: // {
					$vars->layout='box_count';
					$w=55;
					$h=65; //}
			}
		return '<iframe src="http://www.facebook.com/widgets/like.php?href='
			.urlencode('http://'.$_SERVER['HTTP_HOST'].$PAGEDATA->getRelativeURL())
			.'&layout='.$vars->layout.'&show_faces='.$show_faces
			.'" scrolling="no" frameborder="0"'
			.' style="border:none;width:'.$w.'px;height:'.$h.'px"></iframe>';
	}
}
