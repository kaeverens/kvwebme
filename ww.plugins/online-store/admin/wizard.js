$(function(){
	Wizard.init();	
	$('.toggle').live('click',function(){
		var id=$(this).attr('id').replace(" ","-");
		$('#'+id+'-toggle').toggle();
	});
	$('.next-link').live('click',function(){
		Wizard.submit();
	});
	$('.back-link').live('click',function(){
		Wizard.animatePrevious();
	});
	$('.preview-invoice').live('click',function(){
		var inv=$(this).attr('id');
		$('#preview-dialog')
			.html(
				'<img src="/ww.plugins/online-store/admin/wizard/sample-invoice-'
				+inv+'.png"/>'
			)
			.attr('title',(inv==1)?'Standard':'Business')		
			.dialog({
				modal:true,
				width:'950px',
				buttons:{
					Close:function(){
						$(this).dialog('close');
					}
				}
			});
	});
	$('select[name="wizard-products-type"]').live('change',function(){
		var type=$(this).val();
		var html='<table>'
			+ '<tr>'
				+ '<th>'+__('Single View Template:')+'</th>'
				+ '<td><button mode="single" class="preview-template-mode" id="'
					+type+'">'
					+ __('Preview')+'</button></td>'
			+ '</tr>'
			+ '<tr>'
				+ '<th>'+__('Multi View Template:')+'</th>'
				+ '<td><button mode="multi" class="preview-template-mode" id="'
					+type+'">'
					+ __('Preview')+'</button></td>'
			+ '</tr>'
		+ '</table>';
		$('#preview-template').html(html);
	});
	$('.preview-template-mode').live('click',function(){
		var type=$(this).attr('id');
		var multi=$(this).attr('mode');
		$('#preview-dialog')
			.html(
				'<img src="/ww.plugins/online-store/admin/wizard/type-screenshots/'
				+type+'-'+multi+'.png"/>'
			)
			.attr('title',multi)		
			.dialog({
				modal:true,
				width:'600px',
				buttons:{
					Close:function(){
						$(this).dialog('close');
					}
				}
			});
	});
});

var Wizard={

	stage:0,
	queryString:{},
	vars:{},

	init:function(){
		this.stage=1;
		this.nextStep();
	},

	submit:function(){
		if(this.validate()){
			++this.stage;
			this.nextStep();
		}
		return false;
	},

	validate:function(){
		this.queryString={};
		switch(this.stage){
			case 1:
				var name=$('input[name="wizard-name"]').val();
				if(name==""){
					$('#error').html(__('Name is required'));
					return false;
				}
				this.queryString={
					'wizard-name':name
				};
			break;
			case 2:
			case 3:
				// loop through inputs, textareas and selects and
				// get their values
				$('#step_'+this.stage+' input, #step_'+this.stage+' textarea,'
					+'#step_'+this.stage+' select')
					.each(function(){
					if($(this).attr('type')=='checkbox'){
						var val=0;
						if ($(this).is(':checked')) {
							val=1;
						}
						Wizard.queryString[$(this).attr('name')]=val;
						return;
					}
					Wizard.queryString[$(this).attr('name')]=$(this).val();
				});
			break;
			case 4:
				var type=$('select[name="wizard-products-type"]').val();
				this.queryString['wizard-products-type']=type;
			break;
		}
		$('#error').html('');
		return true;
	},

	nextStep:function(){
		$.extend( this.vars, this.queryString );
		var rand=Math.floor(Math.random()*111111);
		$('#step_'+(this.stage-1)).find('input,button,textarea').attr('disabled', true);
		$.post('/ww.plugins/online-store/admin/wizard/step'+this.stage
			+'.php?rand='+rand,
			Wizard.queryString,
			function(result){
				Wizard.animateNext(result);
				Wizard.nextScript();
			}
		);
	},

	animateNext:function(html){
		var container=$('<div class="register-container" id="step_'+this.stage
		+'">'+html+'</div>').css({'left':'0'});
		$('#step_'+(this.stage-1)).css({'left':'-850px'});
		$('#slider')
			.append(container)
			.css({'left':'850px'})
			.animate(
				{'left':'0'},
				750,
				function(){
					$('#step_'+(Wizard.stage-1)).css({'display':'none'});
				}
			);
	},

	animatePrevious:function(){
		$('#step_'+(this.stage-1)).find('input,button,textarea').attr('disabled', false);
		$('#step_'+this.stage).css({'left':'850px'});
		$('#slider')
			.prepend(
				$('#step_'+(this.stage-1)).css({'left':'0','display':'block'})
			)
			.css({'left':'-850px'})
			.animate(
				{'left':'0'},
				750,
				function(){
					$('#step_'+(Wizard.stage+1)).remove();
				}
			);
			--this.stage;
			Wizard.nextScript();
	},

	// to be executed as each section loads
	nextScript:function(){
		switch(this.stage){
			case 2:
				$('#redirect-after-payment').remoteselectoptions({url:"/a/f=adminPageParentsList"});
			break;
		}
		$('#register-progress').slideBackground('#register-progress li:eq('+(this.stage-1)+')');
	}
};

(function($){
	$.fn.slideBackground=function(target, options) {
		var opts=$.extend({},$.fn.slideBackground.defaults, options);
		return this.each(function(){
			var $this=$(this);
			var o = $.meta ? $.extend({}, opts, $this.data()) : opts;
			var $target=$(target), pos=$target.position();
			if (!this.slideBackground) {
				$(this).css({'position':'relative'});
				$(this).find('>*').css({'position':'relative','z-index':2});
				this.slideBackground=$('<div style="position:absolute;'
					+'background:'+o.border+';z-index:0'
					+';width:1px;height:1px'
					+';"/>');
				$this.prepend(this.slideBackground)
					.attr('has-background-slider', true);
			}
			this.slideBackground.animate({
				'width':$target.outerWidth(),
				'height':$target.outerHeight(),
				'top':$target[0].offsetTop,
				'left':$target[0].offsetLeft
			});
		});
	};
	$.fn.slideBackground.defaults={
		'border':'#ff0'
	};
})(jQuery);
