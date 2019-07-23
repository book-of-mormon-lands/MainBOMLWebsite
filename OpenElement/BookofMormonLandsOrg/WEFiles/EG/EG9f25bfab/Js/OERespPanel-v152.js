$(function(){ EG9f25bfab.Init(); });

var EG9f25bfab = {

	Init: function() {
		if (typeof(OEConfEG9f25bfab) === undefined) return;
		var allElements = OEConfEG9f25bfab;

		for(var ID in allElements) {
			var $el = $('#'+ID); // The element's main <div> tag
			var properties = allElements[ID]; // element's available properties for JS
			this.InitElement(ID, $el, properties);
		}
		
		///////////////////////
		
		var self = this;
		this.onResize();
		$(window).bind('resize load', function(){
			self.onResize();
		});
	},

	InitElement: function(ID, $el, properties) {
		
		var $container = $el.find('.OECT_OuterContent');
		
		this.applyProperties($el, $container, properties); // elements that modify height etc.
		
		if (properties.Resp_In_Editor || WEInfoPage.RenderMode != 'Editor') {
			this.findAndInitInner($el, $container); // height calculations are inside
		}
		
		// examples:
		// var elWd = parseInt($el.width()); // Obtain the width of the element
		// var bgIm = $el.find('.OESZ_Zone1').css('background-image'); // obtain the background image for Style Zone 'Zone1'
		
	},
	
	
	_getValInCSSFormat: function(css, val, cssName) {
		if (!val || !val.length) {
			return;
		}		
		val = val.toLowerCase().trim();
		if (/^[0-9]+[a-z%]*$/.test(val)) {
			if (/^[0-9]+$/.test(val)) { // no units specified
				val += 'px';
			}
			css[cssName] = val;
		}
	},
	
	applyProperties: function($el, $container, properties) {
		
		var css = {};
		this._getValInCSSFormat(css, properties.Min_Top, 'padding-top');
		this._getValInCSSFormat(css, properties.Min_Bottom, 'padding-bottom');
		$container.css(css);
		
		var elConsidered = [];
		if (properties.Consider_El) {
			var ids = properties.Consider_El.replace(/[\s]+/g, '').replace(/;/g, ',').split(',');
			for (var i in ids) {
				elConsidered.push('#' + ids[i]);
			}
		}
		if (properties.Consider_Header) {
			elConsidered.push('.OESZ_XBodyHeader');
		}
		if (properties.Consider_Footer) {
			elConsidered.push('.OESZ_XBodyFooter');
		}
		if (elConsidered.length) {
			$el.data('oerp-ids-reduce-height', elConsidered);
		}

		if (properties.Fixed_Content && WEInfoPage.RenderMode != 'Editor') {
			$el.addClass('oerp-fixed-content');
			$el.find('.BaseDiv').first().addClass('oerp-fixed-content');
		}
	},
	
	/////////////////////////////////////////////////////////////////////////
	
	_elements: [],
	
	findAndInitInner: function($el, $container) {
		// find inner panel
		
		var $innerEl = $container.children('.oerp-inner-content:first');
		if (!$innerEl.length) {
			$innerEl = $container.children('.OEWEPanel:first');
			if ($innerEl.length) { // found inner Panel
				$innerEl.addClass('oerp-inner-content');
				if (WEEdToVB && WEEdToVB.GECommand) { // add custom class in OE
					WEEdToVB.GECommand('addCustomClass', {'class': 'oerp-inner-content', 'ids': $innerEl.attr('id')});
				}
			}
		}
		
		if (!$innerEl.length) { // no central element specified or auto-detected - inform user and wait for a panel to be added
			if (typeof $container.data('i-findInner') !== 'undefined') { // already waiting for panel to be added
				return null;
			}
			if (WEInfoPage.RenderMode == 'Editor') {
				this.showWarning($container, WEEdSiteCommon.GetLocalizableString(OEConfSharedEG9f25bfab.Text.Wnoinner));
			}
			
			var self = this;
			$container.data('i-findInner', setInterval(function(){ self.findAndInitInner($el, $container) }, 1000));
			return null;
		} else { // found, stop search
			if (typeof $container.data('i-findInner') !== 'undefined') {
				clearInterval($container.data('i-findInner'));
				this.hideWarnings($container);
			}
		} //////////////////////////////////////////////////////////
		
		this._elements.push($el);
		this.initAutoSize($el, $container, $innerEl);
		return $innerEl;
	},
	
	
	initAutoSize: function($el, $container, $innerEl) {
		$el = $el || $container.closest('.BaseDiv');
		$el.addClass('oerp-autosize');
		$el.data('oerp-container', $container);
		// this.calcAutoSize($el, $container, $innerEl);
	},
	
	
	calcAutoSize: function($el, $container, $innerEl) {
		// calculate top and bottmo margin on inner element so that responsive panel's height = window height
		// calculations are complicated by inner margins and min/max heights on tags
		// inner element should be centerend whenever possible even when paddings are not symmetrical
		
		var hPage = $(window).height();
		
		var hElMax = (typeof $el.data('orig-max-height') !== 'undefined' && $el.data('orig-max-height') !== null) ? 
			parseInt($el.data('orig-max-height')) : 
			parseInt($el.css('max-height')); // test units!
		if (hElMax && hElMax !== NaN && hPage > hElMax) { hPage = hElMax; }

		if ($el.data('oerp-ids-reduce-height')) { // "include" these elements into the same screen page if possible, ex. page footer
			var elConsidered = $el.data('oerp-ids-reduce-height');
			for (var ci in elConsidered) {
				var cHt = $(elConsidered[ci]).height();
				if (cHt && cHt < hPage*0.8) {
					hPage -= cHt; // leave enough height for this element to display on the same visual page
				}
			}
		}
		
		var hElMin = parseInt($el.css('min-height'));
		if (hElMin && hElMin !== NaN && hPage < hElMin) { hPage = hElMin; }
		
		// container padding (define min distances from edges for inner element):		
		var padTop 		= parseInt($container.css('padding-top')),
			padBottom 	= parseInt($container.css('padding-bottom')),
			padBoth 	= padTop + padBottom;
		var yContainer 	= $container.offset().top - $el.offset().top; // top of the container (without inner padding)
			
		for (var stepCorrection=0; stepCorrection<2; stepCorrection++) { // sometimes second recalculation is needed, for example when minimum height on one of the tags result in bad calculation
		
			var hEl = $el.outerHeight(false);
			if (parseInt(hEl) < 2) hEl = $el.children().first().outerHeight(false); // current responsive panel height ( if element has no height, take its first subtag)
			//console.log('element height: ' + hEl + ' ' + [$el.outerHeight(false), $el.children().first().outerHeight(false)]);
			
			if (!stepCorrection && parseInt(hEl) == parseInt(hPage)) { // size already Ok
				// break;
			}
			
			
			var hiContainer = $container.innerHeight(); // current container height (including hor scroll bar?)
			var hFree = hiContainer - padBoth; // current free space (height) inside the container
			//if (hEl < hFree) { hEl = hFree}; // needed when element is currently hidden
			var yFreeTop = yContainer + padTop; // start of free space relative to the responsive panel's top

			var hInnerEl = $innerEl.outerHeight(false); // current inner's element height without calculated margins
			//var hInnerEl_WithMargins = $el.outerHeight(true); // height of inner element including margins
			//var margInnerEl = hInnerEl_WithMargins - hInnerEl; // both margins of inner element

			var hMin = hEl - (hFree - hInnerEl); // minimum possible total height of the panel considering content and container zone padding (i.e. no margins on the inner element)
			
			var hDelta = hPage - hMin; // how much height should be added by calculated margins
			var hNewInnerWithMargins = hDelta + hInnerEl; // new height of inner element with margins = new container's inner height excluding padding
			if (hDelta <= 0) { // can't reduce beyond minimum height
				hDelta = 0;
				
			} else { // change outer margins, so that the content stays centered		
				// content should be centered in the parent element, not according to the minimum padding (ex. top padding 200px and bottom padding 10px should not shift the element from the center unless there's not enough space
				var yShift = 0; // (padBottom - padTop)
				var yNewTop = 0.5*(hPage - hNewInnerWithMargins); // where inner element + margins should start ideally (0.5*hPage = center of the page)
				if (yNewTop < yFreeTop) {
					yShift = (yFreeTop - yNewTop);
					yNewTop = yFreeTop;
				}
				
				var yNewFreeBottom = yFreeTop + hNewInnerWithMargins;
				var yNewBottom = yNewTop + hNewInnerWithMargins; // where inner element + margins should end ideally
				if (yNewBottom > yNewFreeBottom && yShift <= 0) {
					yShift = (yNewFreeBottom - yNewBottom); // negative
					yNewBottom = yNewFreeBottom;
				}
				
				var margHalf = 0.5*hDelta;
				if (yShift > margHalf) 	{ yShift = margHalf; } else
				if (yShift < -margHalf) { yShift = -margHalf; }
				var margTop 	= parseInt(margHalf - yShift);
				var margBottom 	= hDelta - margTop;
				$innerEl.css({'margin-top': margTop+'px', 'margin-bottom': margBottom+'px'});
			}
			//var hWindow = $(window).height();
			
			break;
		}
		
		
		// Fixed (no-scroll) Content: recalculate size:
		if ($innerEl.hasClass('oerp-fixed-content')) {
			var $content = $innerEl.children();
			var xBodyZIndex = parseInt($('#XBody').css('z-index'));
			$content.css({width: parseInt($innerEl.width())+'px', height: parseInt($innerEl.height())+'px'});
			if (xBodyZIndex) {
				$content.css('z-index', xBodyZIndex + 1);
			}
		}
		
		
	},
	
	
	onResize: function()
	{
		for (var i in this._elements) {
			var $el = this._elements[i];
			if (!$el.parent().length) { // element deleted in editor or temporarily removed from DOM?
				continue;
			}
			
			var $container = $el.data('oerp-container');
			var $innerEl = $container.children('.oerp-inner-content');
			if (!$innerEl.length) { // no inner element found
				continue;
			}
			
			this.calcAutoSize($el, $container, $innerEl);
		}
	},
	
	showWarning: function($container, text, fade)	{
		var $warning = $('<div class="oerp-warning"><div class="sub">' + text + '</div></div>').appendTo($container);
		if (fade) {
			$warning.delay(4000).fadeOut(2000);
		}
	},
	
	hideWarnings: function($container) {
		$container.children('.oerp-warning').remove();
	}

};

