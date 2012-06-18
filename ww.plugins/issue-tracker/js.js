$(function() {
	var statii=[
		undefined, 'Open', 'Completed'
	];
	var vals={
		'projects':[],
		'types':[]
	};
	var $wrapper=$('#issuetracker-wrapper');
	var navbar=$('<div id="issuetracker-navbar" style="text-align:right">'
		+'<select class="project"/></div>')
		.appendTo($wrapper);
	$('<button class="edit" style="display:none;">edit project</button>')
		.appendTo($('#issuetracker-navbar'))
		.click(function() {
			var id=+$('#issuetracker-navbar .project').val();
			console.log(id);
			$.post('/a/p=issue-tracker/f=projectGet', {
				'id': id
			}, editProject);
			return false;
		});
	var $content=$('<div id="issuetracker-content"></div>').appendTo($wrapper);
	$.post('/a/p=issue-tracker/f=projectsGet', function(ret) {
		var opts=['<option value="0"> -- all -- </option>']
		var validOpts=0, lastValid=0;
		for (var i=0;i<ret.length;++i) {
			var meta=eval('('+ret[i].meta+')');
			var allowed=0;
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
			opts.push('<option value="-1"> -- add new project -- </option>');
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
							'name':'new project',
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
		else showIssues(0);
	});
	$.post('/a/p=issue-tracker/f=typesGet', function(ret) {
		for (var i=0;i<ret.length;++i) {
			vals.types[+ret[i].id]=ret[i].name;
		}
	});
	function editProject(prj) {
		prj.meta=eval('('+prj.meta+')');
		var html='<table>'
			+'<tr><th>Name</th><td><input class="name"/></td></tr>'
			+'<tr><th>Visible&nbsp;To&nbsp;(users)</th><td class="users"></td></tr>'
			+'<tr><th>Visible&nbsp;To&nbsp;(groups)</th><td class="groups"></td></tr>'
			+'</table>';
		var $table=$(html).dialog({
			'modal':true,
			'close':function() {
				$table.remove();
			},
			'buttons':{
				'save':function() {
					prj.name=$table.find('.name').val();
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
		$table.find('.name').val(prj.name);
	}
	function showIssues(pid) {
		$content.empty();
		var table='<table style="width:100%">'
			+'<thead><tr><th>Name</th><th>Type</th><th>Status</th><th>&nbsp;</th>'
			+'</tr></thead>'
			+'<tbody></tbody>'
			+'</table>';
		var params={
			"sAjaxSource": '/a/p=issue-tracker/f=issuesGetDT',
			"bProcessing":true,
			"bJQueryUI":true,
			"bServerSide":true,
			"fnRowCallback": function(nRow, aData, iDisplayIndex) {
				$('td:nth-child(2)', nRow).text(vals.types[+aData[1]]);
				$('td:nth-child(3)', nRow).text(statii[+aData[2]]);
				$(nRow).css('cursor', 'pointer').click(function() {
					showIssue(+aData[3]);
				});
				return nRow;
			},
			"fnServerData":function(sSource, aoData, fnCallback) {
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
			$('<button>New Issue</button>')
				.click(function() {
					var $table=$('<table>'
						+'<tr><th>Name</th><td><input class="name"/></td></tr>'
						+'<tr><th>Project</th><td><select class="project"/></td></tr>'
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
										issueOpen(ret.id);
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
			issue.meta=eval('('+issue.meta+')');
			// { set up table HTML
			var html='<table><tr><th>Name</th><td class="name"></td></tr>'
				+'<tr><th>Created</th><td>'+Core_dateM2H(issue.date_created)+'</td></tr>'
				+'<tr><th>Modified</th><td>'+Core_dateM2H(issue.date_modified)+'</td></tr>'
				+'<tr><th>Type</th><td>'+ret.type.name+'</td></tr>';
			$.each(type.fields, function(k, v) {
				html+='<tr><th>'+v.name+'</th>';
				type.fields[k].cname='issuetracker-field-'
					+v.name.replace(/[^a-zA-Z]/g, '_');
				html+='<td class="'+type.fields[k].cname+'"></td></tr>';
			});
			html+='<tr><th>Attached Files</th><td class="files"></td></tr>';
			html+='<tr><th>Status</th><td class="status"></td></tr>';
			html+='</table>';
			// }
			$content.html(html);
			// { set up variables
			if (allowedEdit || userdata.isAdmin) {
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
							var obj=$('<textarea class="'+v.cname+'"/>')
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
			}
			else {
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
			}
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
				$button.css('height',20)
					.uploadify({
						'swf':'/j/jquery.uploadify/uploadify.swf',
						'auto':'true',
						'checkExisting':false,
						'cancelImage':'/i/blank.gif',
						'buttonImage':'/i/choose-file.png',
						'height':20,
						'width':91,
						'uploader':'/a/p=issue-tracker/f=issueFileUpload',
						'postData':{
							'PHPSESSID':pagedata.sessid,
							'id':id
						},
						'upload_success_handler':function(file, data, response){
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
							'name':$content.find('input.name').val(),
							'status':$content.find('select.status').val(),
							'meta':meta
						}, function(ret) {
							return alert('Saved');
						});
						return false;
					})
					.appendTo($content);
			}
			$('<button>Return to Issues List</button>')
				.click(function() {
					$('#issuetracker-navbar .project').change();
					return false;
				})
				.appendTo($content);
			// }
		});
	}
});
