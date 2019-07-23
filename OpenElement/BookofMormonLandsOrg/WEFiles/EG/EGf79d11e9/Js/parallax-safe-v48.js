// Polyfill for requestAnimationFrame
// via: https://gist.github.com/paulirish/1579671
(function() {
	var lastTime = 0;
	var vendors = ['ms', 'moz', 'webkit', 'o'];
	for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
	  window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
	  window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame']
		|| window[vendors[x]+'CancelRequestAnimationFrame'];
	}

	if (!window.requestAnimationFrame)
	  window.requestAnimationFrame = function(callback) { // emulation when no support
		var currTime = new Date().getTime();
		var timeToCall = Math.max(0, 16 - (currTime - lastTime));
		var id = window.setTimeout(function() { callback(currTime + timeToCall); },
		  timeToCall);
		lastTime = currTime + timeToCall;
		return id;
	  };

	if (!window.cancelAnimationFrame)
	  window.cancelAnimationFrame = function(id) {
		clearTimeout(id);
	  };
}());

$(function(){ EGf79d11e9.Init(); });

var EGf79d11e9 = {
	
	$elements: 	[], // all elements (tags) referenced by all Parallax elements on the page
	iOS: 		false, // Is iOS?
	Android: 	false, // Is Android?
	
	// Note: if update jquery sticky, check fix for absolute positioning
	
	Init: function() {
		if (typeof(OEConfEGf79d11e9) === undefined) return;
		var allElements = OEConfEGf79d11e9;

		this.iOS = /iPad|iPhone|iPod/.test(navigator.userAgent || navigator.vendor || window.opera) && !window.MSStream; // MSStream is to filter out IE11		
		this.Android = /android/i.test(navigator.userAgent);
		for(var ID in allElements) {
			var $el = $('#'+ID); // The element's main <div> tag
			var properties = allElements[ID]; // element's available properties for JS
			this.InitElement(ID, $el, properties);
		}
		
		var self = this;
		$(window).on('scroll resize', function(){
			self.requestRender(); 
		});
		$(window).on('load', function(){
			self.requestRender();
		});
		setTimeout(function(){ self.requestRender(); }, 50);
		
	},

	
	InitElement: function(ID, $el, properties) {
		var self = this;
		
		var _$elements = [], // elements (tags) referenced by this Parallax instance
			i = 0;
		
		var ids = properties.Ids ? properties.Ids.trim().split(',') : [];
		if(properties.Element!==null) { // element directly specified
			if (properties.Element.List.length !==0) {
				ids.push([properties.Element.List[0]]);
			}
		}
		for (i in ids) { // add by IDs
			var $element = $('#' + ids[i]);
			if ($element.length) {
				_$elements.push($element);
			}
		}
		
		if (properties.Custom_Class) { // add by class
			var $elementsByClass = $('.' + properties.Custom_Class.trim());
			$elementsByClass.each(function(){ _$elements.push($(this)); });
		}
		
		if (!_$elements.length) { // no elements affected
			return;
		}

		var bgSizeSpecified = (properties.Image_Width && properties.Image_Height);
		if (properties.Speed < 0) properties.Speed = 0;
		if (properties.Speed > 200) properties.Speed = 200;
		
		for (i in _$elements) {
			var $element = _$elements[i];
			$element.addClass('oepr-parallax');
			
			if (this.iOS) { // disable in iOS and remove attachment:fixed style
				$element.addClass('is-ios');
				continue;
			}
			if (this.Android) { //for Android, disable parallax but keep attachment:fixed
				//$element.addClass('is-android');
				continue;
			}
			
			$element
				// .data('bdim', [properties.Image_Width || 0, properties.Image_Height || 0])
				.data('oepr-speed', properties.Speed*0.01)
				.data('oepr-brel', bgSizeSpecified ? properties.Image_Width/properties.Image_Height : 0);


			if (!bgSizeSpecified) { // 
				var url = $element.css('background-image').replace(/url\((['"])?(.*?)\1\)/gi, '$2').split(',')[0]; // get URL from CSS ex. url("xx") => xx
				if (url) {
					var image = new Image();
					$element.data('oepr-size-img', image);
					image.onload = function(){ 
						self.calculateBgSizeInfo($element);
						self.updateEl($element);
					};
					image.src = url;
				}
			}
		}
				
		if (!this.iOS && !this.Android) {
			this.$elements = this.$elements.concat(_$elements);
		}
		
	},
	
	
	calculateBgSizeInfo: function($el) { // if artificial image is loaded and has size, use it to calculate aspect ratio of the background image
		var image = $el.data('oepr-size-img');
		if (!image) {
			return;
		}
		var iw = image.width, ih = image.height;
		if (iw && ih && iw > 32 && ih > 32) {
			var brel = iw/ih;
			$el.data('oepr-brel', brel);
			return brel;
		}
		return 0;
	},
	
	
	requestRender: function() { // on resize and scroll events
      var self = this;
      
	  if (!this.isBusy) {
        this.isBusy = true;
        window.requestAnimationFrame(function() { // do not overload the browser
          //console.log('updating');
		  self.updateAll();
          self.isBusy = false;
        });
      }
	},
	
	
	updateAll: function() {
		var $w = $(window);
		var wdim = [$w.width(), $w.height()];
		var offsetY = $w.scrollTop();

		for (var i in this.$elements) {
			this.updateEl(this.$elements[i], $w, wdim, offsetY);
		}
	},
	
	
	updateEl: function($el, $w, wdim, offsetY) {

		// window:
		$w = $w || $(window);
		wdim = wdim || [$w.width(), $w.height()];
		offsetY = (typeof offsetY === 'undefined') ? $w.scrollTop() : offsetY;
		
		var wrel = (wdim[0] > 0 && wdim[1] > 0) ? wdim[0]/wdim[1] : 1, // window aspect ratio
			wcenter = 0.5*wdim[1];
		
		// element:
		var edim = [$el.width(), $el.height()], 
			ey1 = $el.offset().top - offsetY,
			ey2 = ey1 + edim[1],
			ecenter = 0.5*(ey1 + ey2),
			erel = (edim[0] > 0 && edim[1] > 0) ? edim[0]/edim[1] : 0; // element's aspect ratio
		if (!erel) {
			return;
		}
		
		
		// background:
		
		var brel = $el.data('oepr-brel'), bspeed = $el.data('oepr-speed');
		
		if (!brel) { // check if can autodetect the size, if image is already loaded
			brel = this.calculateBgSizeInfo($el);
		}
		
		if (!brel) { // bg image size unknown - simplified calculation: background y = 0 when element is at the screen top
			var offTop = ey1;
			//var by = Math.ceil(-offTop * (1 - bspeed)); // for attachment=scroll: if speed=0 (like background-position: fixed), shift in the opposite direction as much as element top is shifted from the screen top
			var by = Math.ceil(offTop * bspeed); // for attachment=fixed: if speed=0 (like background-position: fixed), shift in the opposite direction as much as element top is shifted from the screen top
			$el.css({'background-position': 'center '+by+'px'});
		
		} else { // image size and aspect ratio are known:
			
			// background size needed to show correctly considering scroll speed multiplier:
			var bh = bspeed <= 1 ?
				Math.ceil(bspeed*edim[1] + (1.0-bspeed)*wdim[1]) // if speed 0 = fixed image (like background-position: fixed), it needs the window height; if moves with element (speed=1) it needs element's height
			  : Math.ceil(edim[1] + (wdim[1]+edim[1])*(bspeed-1.0)); //  + (wdim[1]-edim[1])*(bspeed-1.0)
			var bw = Math.ceil(bh * brel); // keep aspect ratio
			if (bw < edim[0]) { // calculated width smaller than element width, increase:
				bw = Math.ceil(edim[0]);
				bh = Math.ceil(bw / brel);
			}
			
			// background position so that bg image center is at the screen center when element's center is there:
			var by = 0.5*(wdim[1] - bh); // default background position corresponding to "center"
			var offCenter = ecenter - wcenter; // distance from element center to screen center
			var bOffCenter = offCenter * bspeed;
			by = Math.ceil(by + bOffCenter);
			
			$el.css({'background-size': bw+'px '+bh+'px', 'background-position': 'center '+by+'px'});
		}
	},
	
	
	_void: null
};

