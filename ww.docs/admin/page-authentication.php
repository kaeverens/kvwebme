<?php require '_header.php'; ?>
		<h2>Page Authentication</h2>

		<p>Authentication for pages can be done in two ways:</p>
		<ol>
			<li><a href="#protection-by-password">Password-protection</a>: Add a password to the page, such that only people that know the password can view the contents.</li>
			<li><a href="#protection-by-group">Protection by Group</a>: Protect a page such that it can only be seen by readers that are logged in and members of a specified group.</li>
		</ol>
		<p>In both cases, you will need to install the Privacy plugin.</p>
		<a name="protection-by-password"></a>
		<h3>Password-protecting a page</h3>
		<p>We start off by creating a page in the admin area. In this case, we will use the page Services:</p>
		<a href="page-authentication/sc1.png"><img src="page-authentication/sc1-small.png" /></a>
		<p>Note the tabs along the top - "Common Details" and "Advanced Options". In this image, we have not yet installed the Privacy plugin.</p>
		<p>Save the page, install the Privacy plugin, then go back to the page, and you will have a third tab to click. Click it:</p>
		<a href="page-authentication/sc2.png"><img src="page-authentication/sc2-small.png" /></a>
		<p>If nothing is ticked or filled in on this tab, then the page is readable by everytone.</p>
		<p>If anything is ticked or filled in on this page, then the page is protected and can only be accessed by someone who either knows the right password (if it's filled in), or is a member of the right group (if they're logged in and a group is ticked).</p>
		<p>Fill the Password input box in with a password. It will appear as plain text:</p>
		<img src="page-authentication/sc3.png" />
		<p>The reason that a plain-text password is used instead of the normal password field where everything changes to ********, is that you are logged in as an admin while working on this, and there's no reason why you should not be able to read this password.</p>
		<p>Now, scroll down and click "Update Page Details" to save the page. The page is now protected.</p>
		<p>To test this, open up a new browser. You are logged in as an admin in your current browser, so the test will be biased if you don't use a different browser.</p>
		<p>In the new browser, go to the page you have just protected:</p>
		<a href="page-authentication/sc4.png"><img src="page-authentication/sc4-small.png" /></a>
		<p>You can see that the page has been protected, and invites you to either enter a password, or log in.</p>
		<p>If you enter the password ("kaepass" in this case) and click Submit, then the page is revealed to you.</p>
		<a href="page-authentication/sc5.png"><img src="page-authentication/sc5-small.png" /></a>
		<p>Sorted!</p>
		<a name="protection-by-group"></a>
		<h3>Protecting a page using Groups</h3>
		<p>Groups are a way to have a number of separate user accounts all have the same rights.</p>
		<p>To protect a page by group, you should first make sure you have at least one user account which has the group you want to protect it by.</p>
		<p>For example, let's say that you want the page to be only visible to users that are members of the ServiceProviders group.</p>
		<p>In this case, you should create a test user in the admin area (Site Options &gt; Users), and click the + sign in Groups to add a new group:</p>
		<a href="page-authentication/sc6.png"><img src="page-authentication/sc6-small.png" /></a>
		<p>Make sure you set Active to Yes, and then click Save to create the user.</p>
		<p>Next, you need to make sure you have a page for users to log in with.</p>
		<p>If you don't already have a login page, create a page of type "privacy" under the protected page:</p>
		<a href="page-authentication/sc7.png"><img src="page-authentication/sc7-small.png" /></a>
		<p>Click the Options tab. Make sure that the Redirect on Login is set to the protected page (Services in this case).</p>
		<a href="page-authentication/sc8.png"><img src="page-authentication/sc8-small.png" /></a>
		<p>If you want people to register themselves, change Registration Type to Email-verified. Otherwise, you will need to verify the users yourself and activate them.</p>
		<p>You can have new registrants automatically added to the right group by ticking the right box in "Add New Users To". Do <i>not</i> add users to the "administrators" group unless you want them having access to your admin panel.</p>
		<p>If you do not want people to register themselves, then change Visibility to just "Login form".</p>
		<p>Ok - now go to the page you want to protect, and click the Privacy tab.</p>
		<a href="page-authentication/sc9.png"><img src="page-authentication/sc9-small.png" /></a>
		<p>Tick the checkbox of the group or groups that you want to allow access to.</p>
		<p>If you don't want people getting in using a password, then leave the password box blank.</p>
		<p>Save the page, and you're ready to test.</p>
		<p>Open the page on the site's frontend in a fresh browser:
		<p>You will get the same "permission denied" message as described earlier:</p>
		<a href="page-authentication/sc4.png"><img src="page-authentication/sc4-small.png" /></a>
		<p>Click the "click here to log in" link.</p>
		<a href="page-authentication/sc10.png"><img src="page-authentication/sc10-small.png" /></a>
		<p>Fill that in with your user's details, and you will then be brought back to the right place.</p>
		<a href="page-authentication/sc5.png"><img src="page-authentication/sc5-small.png" /></a>
		<p>All done!</p>
<?php require '_footer.php'; ?>
