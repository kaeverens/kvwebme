/*global __,adminVars*/
$(function(){
	function pageAddNode(name,id,pid){
		var pel=null;
		var $jstree=$('#pages-wrapper');
		if (pid) {
			pel='#page_'+pid;
		}
		else{
			pel='#pages-wrapper';
		}
		$jstree.jstree(
			'create',
			pel,
			'last',
			{'attr':{'id':'page_'+id},'data':name},
			function(){
				$jstree.jstree('deselect_all');
				$jstree.jstree('select_node','#page_'+id);
			},
			true
		);
	}
	function pageNew(node) {
		var pid=node[0]?node[0].id.replace(/.*_/,''):0;
		$('<table id="newpage-dialog">'+
			'<tr><th>'+__('Name')+'</th><td><input name="name"/></td></tr>'+
			'<tr><th>'+__('Page Type')+'</th><td><select name="type">'+
			// TODO: translation needed
			'<option value="0">normal</option></select></td></tr>'+
			'</table>'
		).dialog({
			modal:true,
			close:function(){
				$('#newpage-dialog').remove();
			},
			buttons:{
				// TODO: translation needed
				'Create Page': function() {
					var name=$('#newpage-dialog input[name="name"]').val();
					if (name==='') {
						// TODO: translation needed
						return alert('Name must be provided');
					}
					$.post('/a/f=adminPageEdit', {
						'parent':pid,
						'name':name,
						'type':$('#newpage-dialog select[name="type"]').val()
					}, function(ret) {
						if (ret.error) {
							alert(ret.error);
						}
						else{
						pageAddNode(ret.alias, ret.id, ret.pid);
						pageOpen(ret.id);
						}
					});
					$(this).dialog('close');
				},
				// TODO: translation needed
				'Cancel': function() {
					$(this).dialog('close');
				}
			}
		});
		$('#newpage-dialog select[name=type]')
			.remoteselectoptions({url:'/a/f=adminPageTypesList'});
		return false;
	}
	function pageOpen(id) {
		var $rw=$('#reports-wrapper');
		var $pfw=$('#page-form-wrapper');
		if (!$pfw.length) {
			$pfw=$('<iframe id="page-form-wrapper" name="page-form-wrapper" '+
				' src="about:blank"></iframe>')
				.insertAfter($rw);
		}
		$pfw.attr('src', '/ww.admin/pages/form.php?id='+id);
		$rw.css('display', 'none');
	}
	function reportsSave() {
		clearTimeout(window.reportOrdTimer);
		window.reportOrdTimer=setTimeout(function() {
			var vis=[];
			var $portlets=$('.portlet');
			if (!$portlets.length) {
				return;
			}
			$portlets.each(function() {
				var $this=$(this);
				var $content=$('.portlet-content', $this);
				var func=$this.data('func'),
					open=$content.css('display')=='block'?1:0,
					width=$content.width(), height=$content.height();
				vis.push([func, open, width, height]);
			});
			var visJSON=JSON.stringify(vis);
			$.post('/a/f=adminAdminVarsSave', {
				'name':'dashboardReportsOrder',
				'val':visJSON
			});
			adminVars.dashboardReportsOrder=visJSON;
		}, 1000);
	}
	function reportsShow(funcname, name, open, width, height) {
		$(
			'<div class="portlet" data-func="'+funcname+'" data-open="'+(open?1:0)+'">'+
			'<div class="portlet-header">'+name+'</div>'+
			'<div class="portlet-content"'+
			' style="width:'+width+'px;height:'+height+'px"/>'+
			'</div>'
		).appendTo('#reports-wrapper');
	}
	$.jstree._themes='/j/jstree/themes/';
	$('#pages-wrapper')
		.jstree({
			'contextmenu': {
				'items': {
					'rename':false,
					'ccp':false,
					'create' : {
						// TODO: translation needed
						'label'	: 'Create Page',
						'visible'	: function (NODE, TREE_OBJ) {
							if (NODE.length != 1) {
								return 0;
							}
							return TREE_OBJ.check('creatable', NODE);
						},
						'action':pageNew,
						'separator_after' : true
					},
					'remove' : {
						// TODO: translation needed
						'label'	: 'Delete Page',
						'visible':function (NODE, TREE_OBJ) {
							if (NODE.length != 1) {
								return 0;
							}
							return TREE_OBJ.check('deletable', NODE);
						},
						'action':function(node){
							// TODO: translation needed
							if (!confirm('Are you sure you want to delete this page?')) {
								return;
							}
							$.post('/a/f=adminPageDelete/id='+node[0].id.replace(/.*_/, ''), function(ret){
								if(ret.error) {
									alert(ret.error);
								}
								else{
									if (node.find('li').length) {
										document.location=document.location.toString();
									}
									else {
										$('#pages-wrapper').jstree('remove', node);
									}
								}
							});
						},
						'separator_after' : true
					},
					'copy' : {
						// TODO: translation needed
						'label'	: 'Copy Page',
						'visible'	: function () {
							return true;
						},
						'action':function(node) {
							$.post('/a/f=adminPageCopy', {
								'id':node[0].id.replace(/.*_/,'')
							}, function(ret){
								pageAddNode(ret.name, ret.id, ret.pid);
								pageOpen(ret.id);
							}, 'json');
						}
					},
					'view' : {
						// TODO: translation needed
						'label' : 'View Page',
						'action':function(node) {
							window.open(
								'/?pageid='+node[0].id.replace(/.*_/,''),
								'_blank'
							);
						}
					}
				}
			},
			'dnd': {
				'drag_target': false,
				'drop_target': false
			},
			'json_data' : {
				'ajax' : {
					'url' : '/a/f=adminPageChildnodes',
					'data' : function (n) {
						return { id : n.attr ? n.attr('id') : 0 };
					}
				},
				'progressive_render' : true,
				'progressive_unload' : true
			},
			'plugins': [
				'themes', 'json_data', 'ui', 'crrm', 'contextmenu', 'dnd'
			]
		})
		.bind('move_node.jstree',function(e, ref){
			var data=ref.args[0];
			var node=data.o[0];
			setTimeout(function(){
				var p=node.parentNode.parentNode;
				var nodes=$(p).find('>ul>li');
				if (p.tagName=='DIV') {
					p=-1;
				}
				var new_order=[];
				for (var i=0;i<nodes.length;++i) {
					new_order.push(nodes[i].id.replace(/.*_/,''));
				}
				$.post('/a/f=adminPageMove', {
					'id':node.id.replace(/.*_/,''),
					'parent_id':(p==-1?0:p.id.replace(/.*_/,'')),
					'order':new_order
				});
			},1);
		});
	var div=$('<div><i>right-click for options</i><br /><br /></div>');
	$('<button>'+__('Add main page')+'</button>')
		.click(pageNew)
		.appendTo(div);
	div.appendTo('div.sub-nav');
	$('#pages-wrapper a').live('click',function(e){
		var node=e.target.parentNode;
		pageOpen(node.id.replace(/.*_/,''));
		$('#pages-wrapper').jstree('select_node',node);
	});
	$('<div class="resize-bar-w"/>')
		.css('cursor','e-resize')
		.draggable({
			helper:function(){
				return document.createElement('span');
			},
			start:function(e){
				this.offsetStart=e.pageX;
				this.hasLeftOffsetStart=parseInt(
					$('div.pages_iframe').css('left')
				);
				this.menuWidthStart=parseInt(
					$(this).closest('div.sub-nav').css('width')
				);
			},
			drag:function(e){
				var offset=e.pageX-this.offsetStart;
				$(this).closest('div.sub-nav').css('width', this.menuWidthStart+offset);
				$('div.pages_iframe').css('left', this.hasLeftOffsetStart+offset);
			},
			stop:function(){
			}
		})
		.appendTo('div.sub-nav');
	if (/\?id=/.test(document.location.toString())) {
		pageOpen(document.location.toString().replace(/.*\?id=/, ''));
	}
	else {
		// { show list of reports
		var reports_ordered=[];
		if (adminVars.dashboardReportsOrder!==undefined) {
			reports_ordered=eval('('+adminVars.dashboardReportsOrder+')');
		}
		var available_reports=[
			// TODO: translation needed
			['visitorStats', 'Visitor Stats'],
			['popularPages', 'Popular Pages']
		];
		var repAvail, i;
		for (i=0;i<reports_ordered.length;++i) {
			var repOrd=reports_ordered[i];
			for (var j=0;j<available_reports.length;++j) {
				repAvail=available_reports[j];
				if (repAvail[0]==repOrd[0]) {
					repAvail[2]=1;
					reportsShow(repOrd[0], repAvail[1], repOrd[1], repOrd[2], repOrd[3]);
					break;
				}
			}
		}
		for (i=0;i<available_reports.length;++i) {
			repAvail=available_reports[i];
			if (repAvail[2]===undefined) {
				reportsShow(repAvail[0], repAvail[1], 0, 200, 200);
			}
		}
		$('#reports-wrapper').sortable({
			'update':reportsSave
		});
		$('.portlet')
			.addClass( 'ui-widget ui-widget-content ui-helper-clearfix ui-corner-all' )
			.find( '.portlet-header' )
			.addClass( 'ui-widget-header ui-corner-all' )
			.prepend( '<span class="ui-icon ui-icon-minusthick"></span>')
			.end()
			.find( '.portlet-content' );
		$( '.portlet-header .ui-icon' )
			.click(function() {
				var $this=$(this);
				var $parent=$this.closest('.portlet');
				var $content=$parent.find('.portlet-content');
				$this
					.toggleClass( 'ui-icon-minusthick' )
					.toggleClass( 'ui-icon-plusthick' );
				$content
					.toggle();
				if ($content.css('display')=='block') {
					eval('Reports_'+$parent.data('func'))($content);
				}
				else {
					$this.empty();
				}
				reportsSave();
			});
		$('.portlet[data-open=0] .portlet-header .ui-icon').click();
		$('.portlet[data-open=1] .portlet-header .ui-icon').click().click();
		$('.portlet-content').resizable({
			'minWidth':200,
			'minHeight':200,
			'stop':function() {
				reportsSave();
				var $content=$(this);
				var $parent=$content.closest('.portlet');
				eval('Reports_'+$parent.data('func'))($content);
			}
		});
		// }
	}
});

function Reports_popularPages($el) {
	$.post('/a/f=adminReportsPopularPages', function(ret) {
		var table='<table style="width:100%" class="report-two-column">'+
			'<thead><tr>'+
			'<th colspan="2">'+__('Today')+'</th>'+
			'<th colspan="2">'+__('7 Days')+'</th>'+
			'<th colspan="2">'+__('31 Days')+'</th>'+
			'</tr></thead>'+
			'<tbody>';
		for (var i=0;i<50;++i) {
			var day=ret.day[i]||false, week=ret.week[i]||false,
				month=ret.month[i]||false;
			if (!day&&!week&&!month) {
				continue;
			}
			table+='<tr>'+
				'<td class="amt day">'+(day?day.amt:'')+'</td>'+
				'<td class="page day">'+(day?day.page:'')+'</td>'+
				'<td class="amt week">'+(week?week.amt:'')+'</td>'+
				'<td class="page week">'+(week?week.page:'')+'</td>'+
				'<td class="amt month">'+(month?month.amt:'')+'</td>'+
				'<td class="page month">'+(month?month.page:'')+'</td>'+
				'</tr>';
		}
		table+='</tbody></table>';
		$el.find('>table').remove();
		$el.css('overflow-y', 'auto').append(table);
	});
}
function Reports_visitorStats($el) {
	var $content=$el;
	function update() {
		var from=$('#reports-visitors-from').val(),
			to=$('#reports-visitors-to').val();
		if (!from) {
			var d=new Date();
			$('<table class="wide smalltext"><tr>'+
				'<th>'+__('From')+'</th>'+
				'<td><input class="date" id="reports-visitors-from"/></td>'+
				'<th>'+__('To')+'</th>'+
				'<td><input class="date" id="reports-visitors-to"/></td>'+
				'</tr></table>'+
				'<div id="reports-visitors-chart" style="position:absolute;left:0;'+
				'bottom:0;right:0;top:25px"/>'
			).appendTo($content);
			to=$('#reports-visitors-to')
				.val(d.toYMD())
				.datepicker({
					'dateFormat':'yy-mm-dd',
					'onSelect':function() {
						Reports_visitorStats($content);
					}
				})
				.val();
			d.setDate(d.getDate()-31);
			from=$('#reports-visitors-from')
				.val(d.toYMD())
				.datepicker({
					'dateFormat':'yy-mm-dd',
					'onSelect':function() {
						Reports_visitorStats($content);
					}
				})
				.val();
		}
		$.post('/a/f=adminReportsVisitorStats', {
			'from':from,
			'to':to
		}, function(ret) {
			var line1=[];
			$.each(ret, function(key, val) {
				line1.push([key, val]);
			});
			$('#reports-visitors-chart').empty();
			if (line1.length<2) {
				return $('#reports-visitors-chart')
					// TODO: translation needed
					.html('Not enough data to create a chart');
			}
			$.jqplot('reports-visitors-chart', [line1], {
				'axes':{
					'xaxis': {
						'renderer':$.jqplot.DateAxisRenderer
					},
					'yaxis': {
						'min':0
					}
				},
				'series':[
					{
						'lineWidth':1,
						'color':'#f00',
						'markerOptions': {
							show:false
						}
					}
				]
			});
		});
	}
	if ($.jqplot) {
		update();
	}
	else {
		$.cachedScript(
			'/j/jquery.jqplot/jquery.jqplot.min.js',
			function() {
				$.cachedScript(
					'/j/jquery.jqplot/jqplot.dateAxisRenderer.min.js',
					function() {
						Reports_visitorStats($content);
					}
				);
			}
		);
	}
}
