<?php

/**
 * admin/wizard/product-types.php, KV-Webme Online Store Plugin
 *
 * lists different product types
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

// { product types
$types=array(
// name					=>			 type
	'Default'			=>			'default',
	'Books'				=>			'books',
	'Clothes'			=>			'clothes',
	'Software'		=>			'software',
);
// }

// { default type
$default_single='
<table>
	<tbody>
		<tr>
			<td rowspan="3" style="vertical-align:top;">
				{{PRODUCTS_IMAGE}}{{PRODUCTS_IMAGES}}</td>
			<td>
				<h1>
					{{$_name}}</h1>
				<p>
					<b>Price:</b>{{PRODUCTS_FULL_PRICE}}</p>
			</td>
		</tr>
		<tr>
			<td>
				{{$description}}</td>
		</tr>
		<tr>
			<td>
				{{PRODUCTS_BUTTON_ADD_MANY_TO_CART}}</td>
		</tr>
	</tbody>
</table>
';
$default_multi='
<table style="width:150px¨">
	<tbody>
		<tr>
			<td>
				{{PRODUCTS_IMAGE}}</td>
		</tr>
		<tr>
			<td>
				<h3 style="width:150px">
					<a href="{{PRODUCTS_LINK}}">{{$_name}}</a></h3>
			</td>
		</tr>
		<tr>
			<td>
				<a href="{{PRODUCTS_LINK}}">Read More...</a></td>
		</tr>
		<tr>
			<td>
				<b>Price:</b> {{PRODUCTS_FULL_PRICE}} {{PRODUCTS_BUTTON_ADD_TO_CART}}</td>
		</tr>
	</tbody>
</table>
';

$default=array(
	'fields'=>array(
		'Description' => 'textarea',
	),
	'single'=>$default_single,
	'multi'=>$default_multi,
);
// }

// { clothes type
$clothes=array(
	'fields'=>array(
		'Description' => 'textarea',
		'Color' => 'inputbox',
		'Sizes' => 'inputbox',
		'For'	=> array(
			'Men',
			'Women',
		),
	),
	'single'=>'
		<table>
			<tbody>
				<tr>
					<td rowspan="3" style="vertical-align:top;">
						{{PRODUCTS_IMAGE}}{{PRODUCTS_IMAGES}}</td>
					<td>
						<h1>
							{{$_name}}</h1>
						<p>
							<b>Price:</b> {{PRODUCTS_FULL_PRICE}}</p>
						<p>
							<b>Color:</b> {{$color}}</p>
						<p>
							<b>Sizes:</b> {{$sizes}}</p>
						<p>
							For <b>{{$gender}}</b></p>
					</td>
				</tr>
				<tr>
					<td>
						{{$description}}</td>
				</tr>
				<tr>
					<td>
						{{PRODUCTS_BUTTON_ADD_MANY_TO_CART}}</td>
				</tr>
			</tbody>
		</table>
		<h2>
			Related</h2>
		<p>{{PRODUCTS_RELATED}}</p>
	',
	'multi'=>$default_multi,
);
// }

// { books type
$books=array(
	'fields'=>array(
		'ISBN' => 'inputbox',
		'Ebook ISBN'=>'inputbox',
		'Author'=>'inputbox',
		'Description' => 'textarea',
	),
	'single'=>'
		<table>
			<tbody>
				<tr>
					<td rowspan="3" style="vertical-align:top;">
						{{PRODUCTS_IMAGE}}{{PRODUCTS_IMAGES}}</td>
					<td>
						<h1>
							{{$_name}}</h1>
						<p>
							<b>Price:</b> {{PRODUCTS_FULL_PRICE}}</p>
						<p>
							<b>Author:</b> {{$author}}</p>
						<p>
							<b>ISBN:</b> {{$isbn}}</p>
						<p>
							<b>Ebook ISBN:</b> {{$ebookisbn}}</p>
					</td>
				</tr>
				<tr>
					<td>
						{{$description}}</td>
				</tr>
				<tr>
					<td>
						{{PRODUCTS_BUTTON_ADD_MANY_TO_CART}}</td>
				</tr>
			</tbody>
		</table>
		<h2>
			Related</h2>
		<p>{{PRODUCTS_RELATED}}</p>
	',
	'multi'=>$default_multi,

);
// }

// { software type
$software=array(
	'fields'=>array(
		'Version' => 'inputbox',
		'Description' => 'textarea',
		'Platform' => 'inputbox',
	),
	'single'=>'
		<table>
			<tbody>
				<tr>
					<td rowspan="3" style="vertical-align:top;">
						{{PRODUCTS_IMAGE}}{{PRODUCTS_IMAGES}}</td>
					<td>
						<h1>
							{{$_name}}</h1>
						<p>
							<b>Price:</b> {{PRODUCTS_FULL_PRICE}}</p>
						<p>
							<b>Version:</b> {{$version}}</p>
						<p>
							<b>Platform:</b> {{$platform}}</p>
					</td>
				</tr>
				<tr>
					<td>
						{{$description}}</td>
				</tr>
				<tr>
					<td>
						{{PRODUCTS_BUTTON_ADD_MANY_TO_CART}}</td>
				</tr>
			</tbody>
		</table>
		<h2>
			Related</h2>
		<p>{{PRODUCTS_RELATED}}</p>
	',
	'multi'=>$default_multi,
);
// }

?>
