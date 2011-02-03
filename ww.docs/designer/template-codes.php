<?php require '_header.php'; ?>
		<h2>Template Codes</h2>
		<p>Following is a list of common codes used by the WebME engine.</p>
		<table>
			<tr><th>Code</th><th>Description</th></tr>
			<tr><th><code>{{$PAGECONTENT}}</code></th><td>This is the content of the page; the "body" of the page. This changes depending on the page you are on.</td></tr>
			<tr><th><code>{{$WEBSITE_TITLE}}</code></th><td>The website title. This is site-wide, and is set in the Site Options part of the admin.</td></tr>
			<tr><th><code>{{$WEBSITE_SUBTITLE}}</code></th><td>The website sub-title, if it has one. This is site-wide, and is set in the Site Options part of the admin.</td></tr>
		</table>
		<p>And here is a list of common functions. These are different to codes, in that they can include parameters to adjust what is printed to the HTML.</p>
		<table>
			<tr><th>Function</th><th>Description</th><th>Parameters</th><th>Examples</th></tr>
			<tr><th><code>{{LOGO}}</code></th><td>Prints out the site logo to the screen, resizing if necessary.</td><td><code>width</code>: the maximum width to be shown. defaults to 64<br /><code>height</code>: the maximum height to be shown. defaults to 64</td><td><code>{{LOGO&nbsp;height=64&nbsp;width=98}}</code><br /><code>{{LOGO}}</code></td></tr>
			<tr><th><code>{{MENU}}</code></th><td>Prints the menu to the HTML, including classes as appropriate.</td><td><code>mode</code>: the style of menu. choose from 'accordion', 'two-tier' and 'default'. defaults to 'default'<br /><code>preopen</code>: open up the menu to the current page. no values necessary<br /><code>direction</code>: how the menu is drawn. choose from 'horizontal' or 'vertical'. defaults to 'horizontal'.<br /><code>close</code>: whether to allow submenus to be closed. choose from 'yes' or 'no'. defaults to 'yes'.<br /><code>parent</code>: what is the root page of the menu. enter a page's name. defaults to the top-level.<br /><code>nodropdowns</code>: use this if you don't want submenus to appear.</td> <td><code>{{MENU}}</code><br /><code>{{MENU mode="accordion"&nbsp;direction="vertical"}}</code><br /><code>{{MENU direction="vertical"&nbsp;preopen="yes"}}</code><br /><code>{{MENU close="no"}}</code><br /><code>{{MENU parent="/parent/page"}}</code><br /><code>{{MENU nodropdowns="yes"}}</code></td></tr>
			<tr><th>{{PANEL}}</code></td><td>Creates a panel, which can contain Widgets</td><td><code>name</code>: the name of the panel. it's common to name it after the location on the page, such as "left" or "right" or "footer" or "header"</td><td><code>{{PANEL name="right"}}</code></td></tr>
		</table>
<?php require '_footer.php'; ?>
