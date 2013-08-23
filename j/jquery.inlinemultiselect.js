/**
 * inlinemultiselect
 * @version 1.1
 * @requires jQuery v1.2.6
 * 
 * Copyright (c) 2009 Peter Edwards
 * Examples and docs at: http://code.google.com/p/inlinemultiselect/
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * based on original code by Kae Verens
 * http://verens.com/archives/2005/04/27/son-of-multiselect/
 */

/**
 *
 * @description Create multiple selection lists within text to alter the text by adding/removing items from a list
 * 
 * @example $('select').inlinemultiselect();
 * @desc Create a simple widget to add items from a select list to some inline text.
 *
 * @param Object settings An object literal containing key/value pairs to provide optional settings.
 * 
 * @option String separator (optional) 			  A string used to separate selected items in the list 
 * 												  Default value: ", "
 * 
 * @option String endSeparator (optional) 		  A string used to separate the last two items in the list 
 * 												  Default value: " or "
 *
 * @option Object separatorSpecial (optional) 	  An object containing strings used to separate selected items in lists with the list ID used as a key 
 * 												  Default value: {}
 * 
 * @option Object endSeparatorSpecial (optional)  A object containing strings used to separate the last two items in lists with the list ID used as a key 
 * 												  Default value: {}
 *
 * @option Boolean showAllLink (optional)		 Whether to show the link at the top of the popup to select all options
 *												Default value: true
 *
 * @option Boolean showNoneLink (optional)		Whether to show the link at the top of the popup to deselect all options
 *												Default value: true
 *
 * @option Boolean showResetLink (optional)	   Whether to show the link at the top of the popup to reset all options
 *												Default value: true
 *
 * @option Object triggerPopup (optional)		 Either text or HTML to act as the trigger to show the popup.
 *												This has two values - one for choosing an option when no options are selected
 *												(with the key 'empty'), another for when at least one item is selected
 *												(with the key 'nonempty'), and one for disabled select lists (with the key
 *												'disabled'). . Values can include images by using HTML. 
 *												Default value: {'empty':"[Choose...]",'nonempty':"[Change...]",'disabled':"[Editing disabled]"}
 *
 * @option String wrapperClass (optional)		 CSS class for the container (<div>) around the options and controls
 *												Default value: 'selectWrapper'
 *											  
 * @option String wrapperClassDisabled (optional) CSS class for the container (<div>) around the options and controls
 *												Default value: 'selectWrapperDisabled'
 *											  
 * @option String controlsClass (optional)		CSS class for the links (all, none, reset and close) at the top of each container
 *												Default value: 'selectControl'
 *											  
 * @option String listWrapperClass (optional)	 CSS class for the container (<div>) around the list of options
 *												Default value: 'listWrapper'
 *											  
 * @option String checkboxClass (optional)		CSS class for the checkboxes in the list
 *												Default value: 'listBox'
 *											  
 * @option String checkedClass (optional)		 CSS class for the label of the checkbox when it is checked
 *												Default value: 'boxChecked'
 *											  
 * @option String uncheckedClass (optional)	   CSS class for the label of the checkbox when it is unchecked 
 *												Default value: 'boxUnchecked'
 *											  
 * @option String disabledClass (optional)		CSS class for the label of the checkbox when it is disabled
 *												Default value: 'boxDisabled'
 *		  
 * @option String hoverClass (optional)		   CSS class for the label of the checkbox when the mouse is hovering over it (must be unchecked and enabled)
 *												Default value: 'boxHover'
 *		  
 * @option Object maxSelected (optional)		  An object with properties named after the IDs of any multiple selects you wish to limit
 *												the number of choices for. For example, if one of your multiple selects has an ID "limitIt"
 *												and you want to limit the number of selections to two, you would pass the option:
 *												{'maxSelected':{'limitIt': 2}}
 *											   
 * @option Function changeCallback(optional)	  A callback function to be executed when the state of a checkbox is changed. The function is called
 *												within the context of the option which is being changed (so this refers to the checkbox object), and 
 *												receives an array of objects representing all of the checkboxes in the group as an argument.
 *												The objects representing checkboxes have the following properties: id (auto-generated ID of the new
 *												checkbox element), title (originally the text of the <option> element), value (originally the value 
 *												of the <option> element), name (originally, the name of the multiple <select>), and checked 
 *												(boolean value representing the checked state).
 *												Default value: null
 *
 * @option Function onClose (optional)			A callback function which is triggered when the selectbox is closed. This is useful for running a
 *												function only after all selections have been completed. If you want to run a function after each tickbox
 *												is ticked, use changeCallback instead.
 *												Default value: null
 *											  
 * @option Boolean showConsoleLog (optional)	  Whether to log the setup of each multiple select list to the console. This will
 *												not work where console is undefined and should be safe to set to true if this is the case.
 *												Default value: true
 * 
 * @type jQuery
 *
 * @name inlinemultiselect
 * 
 * @cat Plugins/Widgets/inlineMultiSelect
 * 
 * @author Peter Edwards <tech@e-2.org>
 */
  
(function($) {
	$.fn.inlinemultiselect = function(options){
		/* merge passed options with defaults */
		var options = $.extend({
			separator: ", ",
			separatorSpecial: {},
			endSeparator: "or",
			endSeparatorSpecial: {},
			showAllLink: true,
			showNoneLink: true,
			showResetLink: true,
			triggerPopup: {'empty':"[Choose...]",'nonempty':"[Change...]",'disabled':"[Editing disabled]"},
			wrapperClass:'selectWrapper',
			wrapperClassDisabled:'selectWrapperDisabled',
			controlsClass:'selectControl',
			listWrapperClass:'listWrapper',
			checkboxClass:'listBox',
			checkedClass:'boxChecked',
			uncheckedClass:'boxUnchecked',
			disabledClass:'boxDisabled',
			hoverClass:'boxHover',
			maxSelected: {},
			changeCallback: null,
			onClose: null,
			showConsoleLog: true
		},options||{});
		/* log to console */
		var logToConsole = function(msg) {
				return;
		};
		return this.each(function(){
			/* this only makes sense on multiple select lists! */
			if (this.type === 'select-multiple') {
				/* get select name and remove trailing [] if present */
				var msname = $(this).attr('name').replace(/\[\]$/,'');

				/* set separator values */
				var s = options.separatorSpecial[msname]? options.separatorSpecial[msname]: options.separator;
				var es = options.endSeparatorSpecial[msname]? options.endSeparatorSpecial[msname]: options.endSeparator;


				/* wrapper */
				var wc = (this.disabled)? options.wrapperClassDisabled: options.wrapperClass;
				var wrapper = $('<div id="'+msname+'"></div>').addClass(wc);
				/* controls */
				var controls = $('<div/>').appendTo(wrapper)
				/* setup scrolling container for options */
				var optionContainer = $('<div/>').addClass(options.listWrapperClass).appendTo(wrapper);
				/* setup list for options */
				var selectList = $('<ul/>').appendTo(optionContainer);
				/* close link */
				$('<a href="#">close</a>').addClass(options.controlsClass).click(function(){
						$(this).parents('.selectWrapper').hide();
						this.blur();
						/* execute callback function */
						if (options.onClose) {
							/* collect checkbox elements for close callback */
							var checkboxes = [];
							$(this).parents('.'+options.wrapperClass).find(':checkbox').each(function(){
								checkboxes.push({'id':$(this).attr("id"),'name':$(this).attr("name"),'value':$(this).attr("value"),'title':$(this).attr("title"),'checked':this.checked});
							});
							options.onClose.call(this, checkboxes);
				}
						return false;
					}).appendTo(controls);
				/* reset link */
				if (options.showResetLink) {
					$('<a href="#">reset</a>').addClass(options.controlsClass).click(function(){
						var checkboxgroup = $(this).parents('.'+options.wrapperClass).find(':checkbox');
						checkboxgroup.each(function(){this.checked=this.defaultChecked;});
						checkboxgroup.each(function(){$(this).trigger('change');});
						this.blur();
						return false;
					}).appendTo(controls);
				}
				/* select all link */
				if (options.showAllLink) {
					$('<a href="#">all</a>').addClass(options.controlsClass).click(function(){
						var checkboxgroup = $(this).parents('.'+options.wrapperClass).find(':checkbox');
						var groupID = $($(this).parents('.'+options.wrapperClass).get(0)).attr('id');
						if (options.maxSelected[groupID] && options.maxSelected[groupID] < checkboxgroup.length) {
							alert("Sorry, there is a limit of "+options.maxSelected[groupID]+" possible selection(s) from this list");
						} else {
							checkboxgroup.each(function(){if (!this.disabled) {this.checked=true;}});
							checkboxgroup.each(function(){$(this).trigger('change');});
						}
						this.blur();
  						return false;
					}).appendTo(controls);
				}
				/* select none link */
				if (options.showNoneLink) {
					$('<a href="#">none</a>').addClass(options.controlsClass).click(function(){
						$(this).parents('.'+options.wrapperClass).find(':checkbox').each(function(){this.checked=false;$(this).trigger('change');});
						this.blur();
						return false;
					}).appendTo(controls);
				}
				/* helper function to get selected options as string with separators */
				var formatOptionStr = function(existing, sep, endSep){
					var selectedStr = '';
					if (existing.length == 1) {
						selectedStr = existing[0]+' ';
					} else if (existing.length == 2) {
						selectedStr = existing.join(' ' + endSep + ' ') + ' ';
					} else {
						for (i = 0; i < existing.length; i++) {
							if (i == (existing.length - 1)) {
								selectedStr += ' ' + endSep + ' ';
							} else if (i > 0) {
								selectedStr += sep;
							}
							selectedStr += existing[i];
						}
						selectedStr += " ";
					}
					return selectedStr;
				};
				/* construct options inline and provide a link to change them */
				var disabledStr = '';
				var existingChoices = [];
				$('option',this).each(function(){
					if (this.selected) {
						existingChoices[existingChoices.length] = $(this).text();
					}
				});
				var openLink = null;
				if (this.disabled) {
					if (options.triggerPopup.disabled !== '') {
						var openLink = $('<span class="selectDisabled">'+options.triggerPopup.disabled+'</span>');
					}
				} else {
					if (existingChoices.length) {
						var openLink = $('<a href="#" id="'+msname+'link">'+options.triggerPopup.nonempty+'</a>');
					} else {
						var openLink = $('<a href="#" id="'+msname+'link">'+options.triggerPopup.empty+'</a>');
					}
				}
				/* make sure we have something to click first */
				if (openLink && !this.disabled) {
					openLink.click(function(e){
						if ($(this).hasClass('selectDisabled')) {
							return false;
						}
						$('.'+options.wrapperClass).hide();
						/* adjust coordinates of wrapper so it fits within the viewport */
						var wrapperX = (e.pageX > ($(window).width() - $('#'+msname).width()))? ($(window).width() - ($('#'+msname).width()+5)): e.pageX;
						wrapperX = wrapperX < 0? 0: wrapperX;
						var wrapperY = (e.pageY > (($(window).height() + $(document).scrollTop()) - $('#'+msname).height()))? (($(window).height() + $(document).scrollTop()) - ($('#'+msname).height()+5)): e.pageY;
						wrapperY = wrapperY < 0? 0: wrapperY;
						$('#'+msname).css({'top':wrapperY,'left':wrapperX}).show();
						return false;
					});
				}
				var openStr = $('<span/>').append($('<strong id="'+msname+'choices">'+disabledStr+formatOptionStr(existingChoices,s,es)+' </strong>')).append(openLink);
				/* go through options and construct checkboxes */
				$('option',this).each(function(idx){
					var value = $(this).attr('value');
					var text = $(this).text();

					var isSelected = $(this).attr('selected') == true? ' checked="checked"' : '';
					var label = $('<label/>');
					var checkbox = $('<input type="checkbox" id="'+msname+idx+'"/>').attr({name:msname+'[]',value:$(this).attr('value'),title:$(this).text()}).addClass(options.checkboxClass);
					if (this.selected) {
						checkbox.attr({checked:'checked',defaultChecked:true});
						label.addClass(options.checkedClass);
					} else {
						label.addClass(options.uncheckedClass);
					}
					if (this.disabled) {
						checkbox.attr({disabled:'disabled'});
						label.addClass(options.disabledClass);
					}
					checkbox.bind("click change", function(){
						var w = $(this).parents('.inlineSelectList').get(0);
						var defaultCheckbox = false;
						var existingChoices = [];
						var disabledStr = '';
						$(this).parents('.'+options.wrapperClass).find(':checkbox').each(function(){
							if (this.checked) {
								existingChoices[existingChoices.length] = $(this).attr("title");
							}
						});
						/* see if we are at the maximum for this optionSet */
						if (options.maxSelected[msname] && existingChoices.length > options.maxSelected[msname]) {
							alert("Sorry, you cannot choose any more.");
							this.checked = false;
							return false;
						}
						if (this.checked) {
							$(this).parents('label').removeClass(options.uncheckedClass+" "+options.hoverClass).addClass(options.checkedClass);
						} else {
							$(this).parents('label').removeClass(options.checkedClass+" "+options.hoverClass).addClass(options.uncheckedClass);
						}
						var selectedChoicesStr = '';
						if (existingChoices.length) {
							$('#'+msname+'link').html(options.triggerPopup.nonempty);
						} else {
							$('#'+msname+'link').html(options.triggerPopup.empty);
						}
						$('#'+msname+'choices').text(formatOptionStr(existingChoices,s,es));
						/* execute callback function */
						if (options.changeCallback) {
							/* collect checkbox elements for change callback */
							var checkboxes = [];
							$(this).parents('.'+options.wrapperClass).find(':checkbox').each(function(){
								checkboxes.push({'id':$(this).attr("id"),'name':$(this).attr("name"),'value':$(this).attr("value"),'title':$(this).attr("title"),'checked':this.checked});
							});
							options.changeCallback.call(this, checkboxes);
						}
					});
					label.html($(this).text()).prepend(checkbox).hover(
						function() {
							var cbx = $(this).children(':checkbox').get(0);
							if (cbx && !cbx.checked && !cbx.disabled) {
								$(this).addClass(options.hoverClass);
							} else {
								$(this).removeClass(options.hoverClass);	
							}
						},
						function() {
							$(this).removeClass(options.hoverClass);
						});
					$('<li/>').append(label).appendTo(selectList);

				});
				$(wrapper).hide();
				$(document.body).append(wrapper);
				$(this).before(openStr);

				$(this).remove();

			} else {

			}
		});
	}
})(jQuery);
