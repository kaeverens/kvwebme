<?php require '_header.php'; ?>
<h2>Creating A Theme</h2>
<p>In WebME, website designs are packaged into "themes", which contain all the HTML templates, CSS files and images necessary for applying a design to a site's content.</p>
<p>To create a theme, you should first create a subdirectory in your CMS's <code>/ww.skins</code> directory named after your design. For this example, I will use the name <code>wood-outline</code>.</p>
<p>Within that subdirectory, you should create further subdirectories for the HTML templates, images, CSS, and any JavaScripts specific to the design:</p>
<table>
	<tr><th width="20%">directory</th><th>description</th> </tr>
	<tr><td><code>/ww.skins/wood-outline</code></td><td>the theme's main directory. should contain a screenshot named <code>screenshot.png</code></td></tr>
	<tr><td><code>/ww.skins/wood-outline/h</code></td><td>HTML templates. each file should end in <code>.html</code>, and you should have one file named <code>_default.html</code></td></tr>
	<tr><td><code>/ww.skins/wood-outline/c</code></td><td>CSS files. these files should be linked from within the HTML templates.</td></tr>
	<tr><td><code>/ww.skins/wood-outline/cs</code></td><td>alternative CSS files. If your design comes in different "flavours" (colours, etc) that the administrator can choose from, then WebME will link a selected one of these as well as the main one above.</td></tr>
	<tr><td><code>/ww.skins/wood-outline/i</code></td><td>any images specific to the design should be placed in here.</td></tr>
	<tr><td><code>/ww.skins/wood-outline/j</code></td><td>any JavaScripts for the design should be placed here.</td></tr>
</table>
<h3>HTML Templates</h3>
<p>A template is a HTML document which has certain areas defined where the site's content is displayed.</p>
<p>The first template you create should be named <code>_default.html</code>. All templates are saved in the <code>/h/</code> directory in your theme.</p>
<p>Here is an example template:</p>
<pre>
&lt;!doctype html&gt;
&lt;html&gt;
	&lt;head&gt;
		<strong>{{$METADATA}}</strong>
		&lt;link rel="stylesheet" type="text/css" href="/ww.skins/wood-outline/c/_default.css" /&gt;
	&lt;/head&gt;
	&lt;body&gt;
		&lt;div id="wrapper"&gt;
			&lt;div id="header"&gt;
				{{LOGO width="320" height="200"}}
				{{PANEL name="header"}}
			&lt;/div&gt;
			{{MENU direction="horizontal"}}
			{{PANEL name="right"}}
			{{PANEL name="left"}}
			&lt;div id="content"&gt;<strong>{{$PAGECONTENT}}</strong>&lt;/div&gt;
			&lt;br style="clear:both" /&gt;
			{{PANEL name="footer"}}
		&lt;/div&gt;
	&lt;/body&gt;
&lt;/html&gt;
</pre>
<p>You can see that interspersed in the HTML, there are a number of codes, which are used to pull in the site content.</p>
<p>The <code>{{$METADATA}}</code> and <code>{{$PAGECONTENT}}</code> codes are obligatory. The rest are optional.</p>
<p>For more information on template codes, see <a href="template-codes.php">template codes</a>.</p>
<p>The first filename should be <code>_default.html</code>, but you can name the rest anything you want, as long as they end in <code>.html</code>. It is recommended that the rest do <strong>not</strong> begin with an underscore. This is because the list of alternative templates in the WebME page admin area is alphabetical. The <code>_default.html</code> file should always be top of the list.</p>
<h3>CSS files</h3>
<p>Most designs link to one or more CSS files. These should be kept in your <code>/c/</code> directory.</p>
<p>The CSS files are not loaded automatically by WebME, as you may want to link a different file to each template. So, when you are creating your HTML template, you need to specify the CSS file you want to load.</p>
<p>The above HTML example showed this:</p>
<pre>
	&lt;head&gt;
		{{$METADATA}}
		<strong>&lt;link rel="stylesheet" type="text/css" href="/ww.skins/wood-outline/c/_default.css" /&gt;</strong>
	&lt;/head&gt;
	&lt;body&gt;
</pre>
<p>CSS files in WebME are parsed by the engine before being sent to the browser. This means we can use some codes in the CSS files as "shortcuts". See <a href="css-in-webme.php">CSS in WebME</a> for more information.</p>
<?php require '_footer.php'; ?>
