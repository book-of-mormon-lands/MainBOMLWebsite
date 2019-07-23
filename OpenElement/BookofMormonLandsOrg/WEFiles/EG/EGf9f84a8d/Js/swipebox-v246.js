$(function(){ EGf9f84a8d.Init(); });

var EGf9f84a8d = {

	galleries: {},
	
	Init: function() {
		if (typeof(OEConfEGf9f84a8d) === undefined) return;
		var allElements = OEConfEGf9f84a8d;

		for(var ID in allElements) {
			var $el = $('#'+ID); // The element's main <div> tag
			var properties = allElements[ID]; // element's available properties for JS
			this.InitElement(ID, $el, properties);
		}
		
		if (WEInfoPage.RenderMode != 'Editor') {
			this.apply(); 
		}

	},

	InitElement: function(ID, $el, properties) {
		
		// tmp? // fix bug in element HTML code generation - remove end spaces and line breaks
		/*
		$el.find('a, img').each(function(){
			var $tag = $(this);
			var atts = {rel: null, href: null, src: null, title: null, alt: null};
			for (var attr in atts) {
				var val = $tag.attr(attr);
				if (val && val.trim() !== val) {
					$tag.attr(attr, val.trim());
				}
			}
		});
		*/
		
		var imgURL = $el.find('img').attr('src');
		$el.find('.oesi-img-bg').css('background-image', 'url("'+imgURL+'")');
		
		var name = properties.Gallery_Name;
		if (name) {
			if (!this.galleries[name]) {
				this.galleries[name] = $el;
			} else {
				this.galleries[name] = this.galleries[name].add($el);
			}
		}
		
		// fix mouseover behavior on internal zones (inside of non-zone tags)
		var $forceOver = $(this).find('.oesi-apply-onover');
		if (WEInfoPage.RenderMode == 'Editor') {
			$forceOver = $forceOver.not('.OESZ'); /* attempt to fix the behavior in editor, but it does not seem to work*/
		}
		$forceOver.off('mouseenter mouseleave');
		$el.on('mouseenter', function(){ $(this).addClass('OE_Over').find('.oesi-apply-onover').addClass('OE_Over')});
		$el.on('mouseleave', function(){ $(this).removeClass('OE_Over').find('.oesi-apply-onover').removeClass('OE_Over')});
		
		if (properties.Title_Visibility == 2) {
			$el.find('.oesi-thumb-title').addClass('always-visible').removeClass('oesi-transition-all'); // title always visible on the thumb
		}
		
		if (properties.Zoom_White_Bg) {
			$el.find('.oesi-swipebox').data('oesiZoomWhite', true);
		}
		
		//buttons:
		//
		//
		//if (WEInfoPage.RenderMode == 'Editor') { // make buttons visible in editor (otherwise they don't appear on mouseover as in browser)
			// $(".oesi-buttons").addClass('OE_Over').find('oesi-apply-onover').addClass('OE_Over');
  		//}
		
		if (properties.Default_Icons) { // default button style activated
			$el.find('.oesi-button').addClass('oesi-default-style');
			// preload images:
			var defaultBGImages = [
				WEEdSiteCommon.LinkGetPath(OEConfSharedEGf9f84a8d.Images.Zoom0),
				WEEdSiteCommon.LinkGetPath(OEConfSharedEGf9f84a8d.Images.Zoom1),
				WEEdSiteCommon.LinkGetPath(OEConfSharedEGf9f84a8d.Images.Link0),
				WEEdSiteCommon.LinkGetPath(OEConfSharedEGf9f84a8d.Images.Link1)
			];
			for (var i_img in defaultBGImages) {
				(new Image()).src =defaultBGImages[i_img];
			}
		}
		
  		$el.find('.oesi-click-zoom, .oesi-thumb-title').click(function(ev){
			// transfer click on text area etc.
			$el.find('.oesi-swipebox').click();
			ev.stopPropagation();
			return false;
		});
		
		// remove spacing between inline-block buttons:
		var $buttons = $el.find('.oesi-button');
		if ($buttons.length == 2) { // both zoom and link buttons present
			$buttons.eq(1).detach().insertAfter($buttons.eq(0));
		}


	},
	
	apply: function() {
		
		// override background images:
		var urlIcons = WEEdSiteCommon.LinkGetPath(OEConfSharedEGf9f84a8d.Images.Icons);
		var urlIcons_ZoomWhite = WEEdSiteCommon.LinkGetPath(OEConfSharedEGf9f84a8d.Images.Icons_Black);
		var urlLoader = WEEdSiteCommon.LinkGetPath(OEConfSharedEGf9f84a8d.Images.Loader);
		var cssHead = 	'#swipebox-slider .slide-loading { background-image: url("'+urlLoader+'"); }';
		cssHead += 		'#swipebox-prev, #swipebox-next, #swipebox-close { background-image: url("'+urlIcons+'"); }';
		cssHead += 		'.oesi-zoom-white #swipebox-prev, .oesi-zoom-white #swipebox-next, .oesi-zoom-white #swipebox-close { background-image: url("'+urlIcons_ZoomWhite+'"); }';
		this.addStyleToHeader(cssHead);
		
		for (var name in this.galleries) {
			var $gallery = this.galleries[name].find('a.oesi-swipebox');
			$gallery.swipebox({useSVG : false, classZoom: $gallery.data('oesiZoomWhite') ? 'oesi-zoom-white' : ''});
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

