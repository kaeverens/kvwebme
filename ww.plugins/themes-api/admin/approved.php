<?php
/**
	* shows all approved themes in the repository, and allows
	* them to be downloaded, deleted and marked for re-moderation
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<h2>'.__('Approved Themes').'</h2>';

/**
 * no themes in db
 */
if (count($themes) == 0) {
	die(__('Themes database empty!'));
}

/**
 * add themes awaiting moderation to the $moderation array
 */
$approved = array();
for ($i = 0; $i < count($themes); ++$i) {
	if ($themes[ $i ][ 'moderated' ] == 'yes') {
		array_push($approved, $themes[ $i ]);
	}
}

if (count($approved)==0) {
	die(__('No themes approved'));
}

/**
 * write javascript and add it to caching scheme
 */
$script='$(".delete").click(function(){var theme_id=$(this).attr("id");'
	.'var user_id=$(this).attr("userid");var hash=Math.floor(Math.random()*1001);'
	.'var dataString="theme_id="+theme_id+"&user_id="+user_id;var $this=$(this);'
	.'$.ajax({type:"POST",data:dataString,url:"/ww.plugins/themes-api/admin/d'
	.'elete-theme.php?hash="+hash,success:function(html){if(html=="ok")'
	.'$this.parent().parent().fadeOut("slow");else alert("'
	.addslashes(__('There was an error deleting the file,please try again'))
	.'");}});});$(".unapprove").click(function(){var theme_id=$(this).attr("id");'
	.'var user_id=$(this).attr("userid");var hash=Math.floor(Math.random()*1001);'
	.'var dataString="theme_id="+theme_id+"&user_id="+user_id;var $this=$(this);'
	.'$.ajax({type:"POST",data:dataString,url:"/ww.plugins/themes-api/admin/u'
	.'napprove-theme.php?hash="+hash,success:function(html){if(html=="ok")'
	.'$this.parent().parent().fadeOut("slow");else alert("'
	.addslashes(__('There was an error approving the file. Please try again.'))
	.'");}});});';
WW_addInlineScript($script);

echo '
<table>
	<tr>
		<th>'.__('Name').'</th>
		<th>'.__('Version').'</th>
		<th>'.__('Description').'</th>
		<th>'.__('Download').'</th>
		<th>'.__('Submit Date').'</th>
		<th>'.__('Author').'</th>
		<th>'.__('Unapprove').'</th>
		<th>'.__('Delete').'</th>
	</tr>
';

/**
 * print themes in table
 */
foreach ($approved as $theme) {
	$author = dbOne(
		'select name from user_accounts where id=' . $theme[ 'author' ],
		'name'
	);
	$d_name = $theme[ 'id' ] . '/' . $theme[ 'id' ] . '.zip';
	echo '<tr>'
		.'<td>' . $theme[ 'name' ] . '</td>'
		.'<td>' . $theme[ 'version' ] . '</td>'
		.'<td>' . substr($theme[ 'description' ], 0, 30) . '...</td>'
		.'<td><a href="/ww.plugins/themes-api/api.php?download=true&id='
		. $theme[ 'id' ] . '">' . $d_name . '</a></td>'
		.'<td>' . $theme[ 'last_updated' ] . '</td>'
		.'<td><a href="' . $theme[ 'author_url' ] . '">' . $author . '</a></td>'
		.'<td><a id="' . $theme[ 'id' ] . '" userid="' . $theme[ 'author' ]
		. '" href="#" class="unapprove">[-]</a></td>'
		.'<td><a id="' . $theme[ 'id' ] . '" userid="' . $theme[ 'author' ]
		. '" href="#" class="delete">[x]</a></td>';
}

echo '</table>';

?>
