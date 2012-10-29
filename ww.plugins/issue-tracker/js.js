function addVote(id)
{
$.post('/a/p=issue-tracker/f=addVote', { "id": id},function(ret) {
	if(isNaN(ret))
	  alert(ret);
	else
	  $("#votesNumber").text(ret);
	});
}

function substractVote(id)
{
$.post('/a/p=issue-tracker/f=substractVote', { "id": id},function(ret) {
	if(isNaN(ret))
	  alert(ret);
	else
	  $("#votesNumber").text(ret);
	});
}

$(function() {
	var statii=[
		undefined, 'Open', 'Completed'
	];
	var it_edit_all=window.it_edit_all||[];
	var it_see_all=window.it_see_all||[];
	var vals={
		'projects':[],
		'types':[]
	};
	var issueId=0;
	var $wrapper=$('#issuetracker-wrapper');
	var navbar=$('<div id="issuetracker-navbar" style="text-align:right">'
		+'<select class="project"/></div>')
		.appendTo($wrapper);
	var canedit=false;
	for (var i=0;i<it_edit_all.length;++i) {
		if ($.inArray(+it_edit_all[i], userdata.groups)!=-1) {
			canedit=true;
		}
	}
	if (canedit) {
		$('<button class="edit" style="display:none;">Edit '+ITStrings.project
			+'</button>')
			.appendTo($('#issuetracker-navbar'))
			.click(function() {
				var id=+$('#issuetracker-navbar .project').val();
				$.post('/a/p=issue-tracker/f=projectGet', {
					'id': id
				}, editProject);
				return false;
			});
	}
	var $content=$('<div id="issuetracker-content"></div>').appendTo($wrapper);
	$.post('/a/p=issue-tracker/f=projectsGet', function(ret) {
		var opts=['<option value="0"> -- All -- </option>']
		var validOpts=0, lastValid=0;
		for (var i=0;i<ret.length;++i) {
			var meta=eval('('+ret[i].meta+')')||{};
			var allowed=0;
			if (!meta.groups || !meta.users) {
				allowed=1;
			}
			for (var j=0;j<it_see_all.length;++j) {
				if ($.inArray(+it_see_all[j], userdata.groups)!=-1) {
					allowed=1;
				}
			}
			if (allowed==1 || userdata.isAdmin) {
				validOpts++;
				var opt=ret[i];
				opt.meta=meta;
				opt.id=+opt.id;
				lastValid=opt.id;
				opts.push('<option value="'+opt.id+'">'+opt.name+'</option>');
				vals.projects[opt.id]=opt;
			}
		}
		if (userdata.isAdmin) {
			opts.push(
				'<option value="-1"> -- add new '+ITStrings.project+' -- </option>'
			);
		}
		$('#issuetracker-navbar .project')
			.append(opts.join(''))
			.change(function() {
				$('#issuetracker-navbar').show();
				var $this=$(this);
				var id=+$this.val()
				switch(id) {
					case -1: // {
						if (!userdata.isAdmin) {
							return alert('you\'re not an admin!');
						}
						$('#issuetracker-navbar .edit').hide();
						editProject({
							'id':0,
							'name':'new '+ITStrings.project,
							'meta':'{}',
							'parent_id':0
						});
					break; // }
					default: // {
						if (id) {
							$('#issuetracker-navbar .edit').show();
						}
						else {
							$('#issuetracker-navbar .edit').hide();
						}
						showIssues(id);
					break; // }
				}
			})
		if (!validOpts && userdata.isAdmin) {
			$('#issuetracker-navbar .project').val('-1').change();
		}
		else if (validOpts==1) {
			$('#issuetracker-navbar .project').val(lastValid).change();
		}
		else {
			var extras=document.location.toString().replace(/.*#(.*)/, '$1');
			if (extras!=document.location.toString()) {
				if (/^issue=/.test(extras)) {
					return showIssue(+extras.replace('issue=', ''));
				}
			}
			showIssues(0);
		}
	});
	$.post('/a/p=issue-tracker/f=typesGet', function(ret) {
		for (var i=0;i<ret.length;++i) {
			vals.types[+ret[i].id]=ret[i].name;
		}
	});

	
		
	function editProject(prj) {
		prj.meta=eval('('+prj.meta+')');
		if (!prj.meta) {
			prj.meta={
				'groups':[],
				'users':[]
			};
		}
		var html='<table>'
			+'<tr><th>Name</th><td><input class="name"/></td></tr>'
			+'<tr><th>Restrict&nbsp;To&nbsp;(users)</th><td class="users"></td></tr>'
			+'<tr><th>Restrict&nbsp;To&nbsp;(groups)</th><td class="groups"></td>'
			+'</tr>'
			+'</table>';
		var $table=$(html).dialog({
			'modal':true,
			'width':370,
			'close':function() {
				$table.remove();
			},
			'buttons':{
				'save':function() {
					prj.name=$table.find('.name').val();
					// { groups
					var groups=[];
					$.each(
						$('.groups>select', $table).multiselect('getChecked'),
						function(k, v) {
							groups.push($(this).attr('value'));
						}
					);
					prj.meta.groups=groups;
					// }
					// { users
					var users=[];
					$.each(
						$('.users>select', $table).multiselect('getChecked'),
						function(k, v) {
							users.push($(this).attr('value'));
						}
					);
					prj.meta.users=users;
					// }
					$.post('/a/p=issue-tracker/f=adminProjectSave', {
						'id':prj.id,
						'name':prj.name,
						'meta':prj.meta,
						'parent_id':prj.parent_id
					}, function(ret) {
						ret.id=+ret.id;
						if (prj.id==0) {
							prj.id=ret.id;
							$('<option value="'+prj.id+'">'+prj.name+'</option>')
								.insertBefore('#issuetracker-navbar .project option[value="-1"]');
						}
						else {
							$('#issuetracker-navbar .project option[value="'+ret.id+'"]')
								.text(prj.name);
						}
						vals.projects[ret.id]=prj;
						$table.remove();
						$('#issuetracker-navbar .project').val(ret.id).change();
					});
				}
			}
		});
		$.get('/a/f=adminUserNamesGet', function(ret) {
			var opts=[], users=prj.meta.users||[];
			$.each(ret, function(k, v) {
				var selected=users.indexOf(k)!=-1
					?' selected="selected"'
					:'';
				opts.push('<option value="'+k+'"'+selected+'>'+v+'</option>');
			});
			$('<select multiple="multiple">'+opts.join('')+'</select>')
				.appendTo($table.find('.users'))
				.multiselect();
		});
		$.get('/a/f=adminUserGroupsGet', function(ret) {
			var opts=[], groups=prj.meta.groups||[];
			$.each(ret, function(k, v) {
				var selected=groups.indexOf(v.id)!=-1
					?' selected="selected"'
					:'';
				opts.push('<option value="'+v.id+'"'+selected+'>'+v.name+'</option>');
			});
			$('<select multiple="multiple">'+opts.join('')+'</select>')
				.appendTo($table.find('.groups'))
				.multiselect();
		});
		$table.find('.name').val(prj.name);
	}
	function showIssues(pid) {
		$content.empty();
		var header='<label>From:<input class="date" id="issue-tracker-date-from"/></label>'
			+'<label>To:<input class="date" id="issue-tracker-date-to"/></label>';
		$(header).appendTo($content);
		$('#issue-tracker-date-from')
			.val(dateFrom.toYMD());
		$('#issue-tracker-date-to')
			.val(dateTo.toYMD());
		$('#issue-tracker-date-from, #issue-tracker-date-to')
			.datepicker({
				'dateFormat':'yy-mm-dd',
				'onClose':function() {
					$table.fnDraw(1);
				}
			});
		var table='<table style="width:100%">'
			+'<thead><tr><th>ID</th><th>Scheduled<br/>Date</th><th>Status</th>'
			+'<th>Name</th><th>Type</th>'
			+'<th>'+ITStrings.Project+'</th>'
			+'<th>Votes</th>'
			+'</tr></thead>'
			+'<tbody></tbody>'
			+'</table>';
		var params={
			"sAjaxSource": '/a/p=issue-tracker/f=issuesGetDT',
			"bProcessing":true,
			"aaSorting":[[1, "asc"]],
			aoColumns:[
				{'bVisible':false},
				null,
				null,
				null,
				null,
				null,
				null
			],
			"bJQueryUI":true,
			"bServerSide":true,
			"fnRowCallback": function(nRow, aData, iDisplayIndex) {
				$('td:nth-child(2)', nRow).text(statii[+aData[2]]);
				$('td:nth-child(4)', nRow).text(vals.types[+aData[4]]);
				$(nRow).css('cursor', 'pointer').click(function() {
					showIssue(+aData[0]);
				});
				return nRow;
			},
			"fnServerData":function(sSource, aoData, fnCallback) {
				aoData.push({
					'name':'date-from',
					'value':$('#issue-tracker-date-from').val()
				});
				aoData.push({
					'name':'date-to',
					'value':$('#issue-tracker-date-to').val()
				});
				aoData.push({
					'name':'pid',
					'value':$('#issuetracker-navbar .project').val()
				});
				$.getJSON(sSource, aoData, fnCallback);
			}
		};
		var $table=$(table).appendTo($content).dataTable(params);
		$('.dataTables_filter').css('display', 'none');
		if (userdata.isAdmin) {
			$('<button>New '+ITStrings.Issue+'</button>')
				.click(function() {
					var $table=$('<table>'
						+'<tr><th>Name</th><td><input class="name"/></td></tr>'
						+'<tr><th>'+ITStrings.Project+'</th><td><select class="project"/></td></tr>'
						+'<tr><th>Type</th><td><select class="type"/></td></tr>'
						+'</table>').dialog({
							'modal':true,
							'close':function() {
								$table.remove();
							},
							'buttons':{
								'save':function() {
									var name=$table.find('.name').val(),
										type=$table.find('.type').val();
									$.post('/a/p=issue-tracker/f=issueCreate', {
										'name':$table.find('.name').val(),
										'type_id':$table.find('.type').val(),
										'project_id':$table.find('.project').val()
									}, function(ret) {
										$table.remove();
										showIssue(ret.id);
									});
								}
							}
						});
					// { projects
					var opts=[];
					$.each(vals.projects, function(k, v) {
						if (v==undefined) {
							return;
						}
						opts.push('<option value="'+v.id+'">'+v.name+'</option>');
					});
					$table.find('.project').html(opts.join(''))
						.val($('#issuetracker-navbar .project').val());
					// }
					var opts=[];
					$.each(vals.types, function(k, v) {
						if (v==undefined) {
							return;
						}
						opts.push('<option value="'+k+'">'+v+'</option>');
					});
					$table.find('.type').html(opts.join(''));
					return false;
				})
				.appendTo($content);
		}
	}
	function showIssue(id) {
		issueId=id;
		$.post('/a/p=issue-tracker/f=issueGet', {
			'id':id
		}, function(ret) {
			if (ret.error) {
				return alert(ret.error);
			}
			$content.empty();
			var allowedEdit=0;
			for (var j=0;j<it_edit_all.length;++j) {
				if ($.inArray(+it_edit_all[j], userdata.groups)!=-1) {
					allowedEdit=1;
				}
			}
			$('#issuetracker-navbar').hide();
			var issue=ret.issue, type=ret.type;
			type.fields=eval('('+type.fields+')');
			if (type.fields===null) {
				type.fields=[];
			}
			issue.meta=eval('('+issue.meta+')');
			// { set up table HTML
			var html=
				'<table style="width:100%"><tr><th>Name</th><td class="name"></td></tr>'
				+'<tr><th>Scheduled Date</th><td class="due_date"></td></tr>'
				+'<tr style="display:none"><th>Recur every</th>'
				+'<td id="issue-tracker-recurring"></td></tr>'
				+'<tr><th>Type</th><td>'+ret.type.name+'</td></tr>';
			$.each(type.fields, function(k, v) {
				html+='<tr><th>'+v.name+'</th>';
				type.fields[k].cname='issuetracker-field-'
					+v.name.replace(/[^a-zA-Z]/g, '_');
				html+='<td class="'+type.fields[k].cname+'"></td></tr>';
			});
			html+='<tr><th>Attached Files</th><td class="files"></td></tr>';
			html+='<tr><th>Status</th><td class="status"></td></tr>';
			html+='<tr><th>Votes</th><td class="votes"><span id="votesNumber">'+issue.meta['credits']+'</span>&nbsp;&nbsp;<a href="javascript:addVote('+issue.id+')"><span style="background-image:url(\'/i/icon_plus.gif\');width:15px;height:15px;display:inline-block;">&nbsp</span></a>&nbsp;';
			html+='<a href="javascript:substractVote('+issue.id+')"><span style="background-image:url(\'/i/icon_minus.jpg\');width:15px;height:15px;display:inline-block;">&nbsp</span></a>';
			html+='</td></tr>';
			html+='</table>';
			// }
			$content.html(html);
			// { set up variables
			if (allowedEdit || userdata.isAdmin) {
				var dueDate=$('<input class="dueDate"/>')
					.val(issue.due_date||'').datepicker({
						'dateFormat':'yy-mm-dd'
					});
				var name=$('<input class="name"/>').val(issue.name);
				$.each(type.fields, function(k, v) {
					switch(v.type) {
						case 'date': // {
							var obj=$('<input class="date '+v.cname+'"/>')
								.val(issue.meta[v.name]||'').datepicker({
									'dateFormat':'yy-mm-dd'
								});
						break; // }
						case 'input': // {
							var obj=$('<input class="'+v.cname+'"/>')
								.val(issue.meta[v.name]||'');
						break; // }
						case 'textarea': // {
							var obj=$('<textarea style="width:100%;min-height:100px;" class="'+v.cname+'"/>')
								.val(issue.meta[v.name]||'');
						break; // }
						default: // {
							alert('unknown data type: '+v.type);
						break; // }
					}
					$content.find('.'+v.cname).append(obj);
				});
				var istatus=$('<select class="status">'
					+'<option value="1">Open</option>'
					+'<option value="2">Completed</option>'
					+'</select>').val(issue.status);
				// { recurring
				var $t=$('#issue-tracker-recurring');
				$('<input id="issue-tracker-recurring-multiplier" class="number"/>')
					.val(+issue.recurring_multiplier)
					.css('max-width', '80px')
					.appendTo($t);
				$('<select id="issue-tracker-recurring-type"><option value="day">Day(s)</option>'
					+'<option value="week">Week(s)</option><option value="month">Month(s)</option>'
					+'<option value="year">Year(s)</option>')
					.val(issue.recurring_type)
					.appendTo($t);
				$t.closest('tr').css('display', 'table-row');
				// }
			}
			else {
				var dueDate='<span>'+Core_dateM2H(issue.due_date)+'</span>';
				var name='<span>'+issue.name+'</span>';
				$.each(type.fields, function(k, v) {
					switch(v.type) {
						case 'date': // {
							var obj=$('<span class="date"/>')
								.text(Core_dateM2H(issue.meta[v.name]||''));
						break; // }
						case 'input': // {
							var obj=$('<span class="text-single-line"/>')
								.text(issue.meta[v.name]||'');
						break; // }
						case 'textarea': // {
							var obj=$('<pre class="text-multi-line"/>')
								.css('white-space', 'pre-wrap')
								.text(issue.meta[v.name]||'');
						break; // }
						default: // {
							alert('unknown data type: '+v.type);
						break; // }
					}
					$content.find('.'+v.cname).append(obj);
				});
				var istatus=[undefined, 'Open', 'Completed'][issue.status];
				if (+issue.recurring_multiplier) {
					var $t=$('#issue-tracker-recurring');
					$t.html(issue.recurring_multiplier+' '+issue.recurring_type+'(s)');
					$t.closest('tr').css('display', 'table-row');
				}
			}
			$content.find('.due_date').append(dueDate);
			$content.find('.name').append(name);
			$content.find('.status').append(istatus);
			// }
			// { set up files list
			var $filesWrapper=$content.find('.files');
			if (!ret.files.length) {
				$filesWrapper.append('<div>no files uploaded</div>');
			}
			else {
				var html='<ul class="files-list">';
				for (var i=0;i<ret.files.length;++i) {
					html+='<li><a href="/f/issue-tracker-files/'+id+'/'+ret.files[i]+'">'
						+htmlspecialchars(ret.files[i])+'</a></li>';
				}
				html+='</ul>';
				$(html).appendTo($filesWrapper);
			}
			if (userdata.isAdmin) {
				var $button=$('<button id="issuetracker-file">Upload</button>')
					.appendTo($filesWrapper);
				Core_uploader($button, {
					'serverScript': '/a/p=issue-tracker/f=issueFileUpload',
					'postData': {
						'id':id
					},
					'successHandler':function(file, data, response){
						$('#pte3-img').attr('src', data+'?'+Math.random());
						ret=eval('('+data+')');
						if (ret.ok) {
							alert('file uploaded');
						}
					}
				});
			}
			// }
			// { add Save buttons, etc
			if (userdata.isAdmin) {
				$('<button>Save</button>')
					.click(function() {
						var meta={};
						$.each(type.fields, function(k, v) {
							switch(v.type) {
								case 'date': // {
									meta[v.name]=$content.find('input.'+v.cname).val();
								break; // }
								case 'input': // {
									meta[v.name]=$content.find('input.'+v.cname).val();
								break; // }
								case 'textarea': // {
									meta[v.name]=$content.find('textarea.'+v.cname).val();
								break; // }
							}
						});
						$.post('/a/p=issue-tracker/f=issueSet', {
							'id':id,
							'dueDate':$content.find('input.dueDate').val(),
							'name':$content.find('input.name').val(),
							'status':$content.find('select.status').val(),
							'recurring_multiplier':$('#issue-tracker-recurring-multiplier').val(),
							'recurring_type':$('#issue-tracker-recurring-type').val(),
							'meta':meta
						}, function(ret) {
							return alert('Saved');
						});
						return false;
					})
					.appendTo($content);
			}
			$('<button>Return to '+ITStrings.Issues+' List</button>')
				.click(function() {
					$('#issuetracker-navbar .project').change();
					return false;
				})
				.appendTo($content);
			// }
			$.post('/a/p=issue-tracker/f=commentsGet', {
				'id':id
			}, showComments);
		});
	}	
	
	function showComments(ret) {
		var comments=[];
		for (var i=0;i<ret.length;++i) {
			comments.push(
				'<div class="issuetracker-comment"><table>'
				+'<tr><th>Name</th><td>'+ret[i].name+'</td></tr>'
				+'<tr><th>Date</th><td>'+Core_dateM2H(ret[i].cdate, 'datetime')
				+'</td></tr>'
				+'<tr><th>'+__('Comment')+'</th>'
				+'<td>'+htmlspecialchars(ret[i].body).replace(/\n/g, '<br/>')
				+'</td></tr>'
				+'</table><hr/></div>'
			);
		}
		$content.append(comments.join(''));
		var $addComment=$('<div class="issuetracker-addComment">'
			+'<textarea style="width:100%;height:100px;"/>'
			+'<button>Add Comment</button>'
			+'</div>').appendTo($content);
		$addComment.find('button').click(function() {
			var body=$addComment.find('textarea').val();
			if (body) {
				$.post('/a/p=issue-tracker/f=commentAdd', {
					'issue_id':issueId,
					'body':body
				}, function(ret) {
					if (ret.error) {
						return alert(ret.error);
					}
					showIssue(issueId);
				});
			}
			return false;
		});
	}
	window.dateTo=new Date();
	window.dateTo.setDate(dateTo.getDate()+7);
	window.dateFrom=new Date();
});
