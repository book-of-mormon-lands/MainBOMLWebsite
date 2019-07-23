$(function(){ EG2e52cdc0.Init(); });

var EG2e52cdc0 = {

	Init: function() {
		if (typeof(OEConfEG2e52cdc0) === undefined) return;
		var allElements = OEConfEG2e52cdc0;

		for(var ID in allElements) {
			var $el = $('#'+ID); // le tag <div> principale de l'élément
			var properties = allElements[ID]; // les propriétés de l'élément disponibles pour JS
			this.InitElement(ID, $el, properties);
		}
	},

	InitElement: function(ID, $el, properties) {
		
		var sliderID  = (properties.Slider!==null && properties.Slider.List.length !==0) ? properties.Slider.List[0] : null;
		var triggerID = (properties.Trigger!==null && properties.Trigger.List.length !==0) ? properties.Trigger.List[0] : null;
		var keepOverStyle = properties.Keep_Over_Style;
		
		var $slider = $('#'+(sliderID || '-')), $trigger = $('#'+(triggerID || '-'));
		if (!$slider.length || !$trigger.length) {
			return;
		}
		
		// extra options:
		var $autoScrollTarget = !properties.Auto_Scroll ? null : // if autoscroll on open, scroll to either the slider itself or a specified element
			((properties.Auto_Scroll_Target!==null && properties.Auto_Scroll_Target.List.length !==0) ? $('#'+properties.Auto_Scroll_Target.List[0]) : $slider);
		
		var speed = properties.Speed || 500;
		
		var extraOpeners = (properties.Extra_Open  || '').replace(/[\s]+/g, '');
		if (extraOpeners) {	extraOpeners = '#' + extraOpeners.replace(/,/g, ',#'); }
		var extraClosers = (properties.Extra_Close || '').replace(/[\s]+/g, '');
		if (extraClosers) {	extraClosers = '#' + extraClosers.replace(/,/g, ',#'); }
		

		// if element has original max height, keep it in data (used in Responsive Panel):
		var maxHeight = $slider.css('max-height');
		if (maxHeight) {
			$slider.data('orig-max-height', maxHeight);
		}
		 
		// set parent controller, classes and initial state:
		var triggerInitClass = !properties.Open ? '' : (' oeso-open' + (keepOverStyle ? ' OE_Over' : ''));
		$trigger.data('so-controller', $el).addClass('oeso-trigger' + triggerInitClass).data('keep-over', keepOverStyle);
		$slider.data('so-controller', $el ).addClass('oeso-slider oeso-no-anim'  + (properties.Open ? ' oeso-open' : '')).data('delay', speed);
		$el.data('trigger', $trigger).data('slider', $slider);

		
		// set groups:
		var groups = (properties.Group || '').toLowerCase().replace(/[\s]+/g, ' ').replace(/[^a-z\s\-_]/g, '-').trim();
		if (groups) {
			$slider.data('groups', groups).addClass('oeso-g-'+groups.replace(/[\s]/g, ' .'))
				.data('keep-open', properties.Group_Keep_Open);
		}
		
		
		function toggleSlide($slider, on, noCloseGroup) { /////////////////////////////////////////
			var nowOpen = $slider.hasClass('oeso-open');
			if (nowOpen == on) {
				return; // no change of state
			}
			if (!on && !noCloseGroup && $slider.data('keep-open')) {
				return; // keep current slide open unless group's another slider opens
			}
				
			var $controller = $slider.data('so-controller');
			if (!$controller) { // this slider is not yet initialised
				return;
			}
			var $trigger = $controller.data('trigger');

			// start animation and finalize it at the end:
			if (!on) {
				$slider.css('max-height', parseInt($slider.outerHeight(false))+'px');
			} else { // when opening, try to estimate the height by child's height (works for panels)
				var childHeight = ($slider.children().length == 1) ? $slider.children().first().outerHeight(false) : null;
				//console.log(' * child height ' + childHeight);
				if (childHeight && childHeight > 20) {
					$slider.css('max-height', parseInt(childHeight)+'px');
					//console.log('set child height ' + childHeight);
				}
			}
			$slider.addClass('oeso-start-anim');
			clearTimeout($slider.data('tid') || null);
			clearTimeout($slider.data('tid-autoscroll') || null);
			//console.log('start anim: ' + $slider.css('max-height'));
			
			$slider.removeClass('oeso-no-anim');
			function launchAnim() {
				$slider.toggleClass( 'oeso-open', on);
				$trigger.toggleClass('oeso-open' + ($trigger.data('keep-over') ? ' OE_Over' : ''), on);
				
				$slider.data('tid', // stop animation later
					setTimeout(function(){ 
						/*if ($slider.height() > 1000) {
							return;
						}*/
						$slider.addClass('oeso-no-anim').removeClass('oeso-start-anim').css('max-height', ''); /*console.log('end anim: ' + $slider.css('max-height'));*/ 
						setTimeout(function(){ 
							$(window).trigger('resize'); 
							//console.log('after child height: ' + $slider.children().first().outerHeight(false)) ;
						}, 1);
					}, $slider.data('delay'))
				);
				
				if (on && !noCloseGroup && $autoScrollTarget) { // scroll to slider or another element (only if not recursive call)
					$slider.data('tid-autoscroll', // run autoscroll (not immediately)
						setTimeout(function(){ 
								$('html, body').stop().animate({scrollTop: $autoScrollTarget.offset().top}, speed);
						}, $slider.data('groups') ? $slider.data('delay') : $slider.data('delay')*0.125) // if groups enables, wait till animation ends; otherwise wait just a little
					);
				}
			}
			var iid = setInterval(function(){
				if (!$slider.hasClass('oeso-no-anim')) { // tru to avoid bug in certain browsers
					clearInterval(iid);
					launchAnim();
				} else {
					console.log('wait class remove');
				}
			}, 10);
			
			
			// close other sliders of the group:
			if (!noCloseGroup && $slider.data('groups')) {
				var split = $slider.data('groups').split(' ');
				for (var i in split) {
					var group = split[i];
					var $otherSlides = $('.oeso-g-'+group + '.oeso-slider:not(#'+$slider.attr('id')+')');
					$otherSlides.each(function(){ toggleSlide($(this), false, true); });
				}
			}
			
		} ///////////////////////////////////////////////////////////////////////////
		
		
		$trigger.on('click', function(){
			var nowOpen = $slider.hasClass('oeso-open');
			toggleSlide($slider, !nowOpen);
		});
		
		if (keepOverStyle) {
			$('body').on('mouseleave', '#'+triggerID+'.oeso-open', function(){ // keep OE_Over class on activated triggers
				var $t = $(this).addClass('OE_Over');
				setTimeout(function(){ if ($t.hasClass('oeso-open')) $t.addClass('OE_Over'); }, 1);
			});
		}
						   
		if (extraOpeners) {
			$('body').on('click', extraOpeners, function(){ // extra open triggers
				if (!$slider.hasClass('oeso-open')) {
					toggleSlide($slider, true);
				}
			});
		}
		if (extraClosers) {
			$('body').on('click', extraClosers, function(){ // extra close triggers
				if ($slider.hasClass('oeso-open')) {
					toggleSlide($slider, false);
				}
			});
		}
		
		// add animation speed style if different from default:
		if (speed != 500) {
			var s = (speed*0.001); // 250 => 0.25
			/*
			this.addStyleToHeader("#"+sliderID+".oeso-slider:not(.oeso-open):not(.oeso-no-anim) {"
								 + "-webkit-transition: max-height "+s+"s ease-out;"
								 + "transition: max-height "+s+"s ease-out;"
								 + "}"
								 +"#"+sliderID+".oeso-slider.oeso-open:not(.oeso-no-anim) {"
								 + "-webkit-transition: max-height "+s+"s ease-in;"
								 + "transition: max-height "+s+"s ease-in;"
								 + "}"
			);
			*/
			this.addStyleToHeader("#"+sliderID+".oeso-slider:not(.oeso-no-anim) {"
								 + "-webkit-transition: max-height "+s+"s ease;"
								 + "transition: max-height "+s+"s ease;"
								 + "}"
			);
		}
		
	},
	
	
	addStyleToHeader: function(code) {
		try {
			var style = document.createElement('style');
			style.type = 'text/css';
			style.innerHTML = code;
			document.getElementsByTagName('head')[0].appendChild(style);
		} catch (ex) {}
	}	

};

