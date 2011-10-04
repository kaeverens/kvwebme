<?php
echo Core_adminSideMenu(array(
	'Dashboard'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=dashboard',
	'Send Message'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=send-message',
	'Addressbooks'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=addressbooks',
	'Subscribers'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=subscribers'
),$_url);
