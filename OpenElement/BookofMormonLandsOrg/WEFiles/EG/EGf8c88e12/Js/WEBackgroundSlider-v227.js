/*_______________________________________________
|                                                |
|    ©2013 Element Technologie - openElement     |
|________________________________________________|

100% backgound slideShow 
*/

$(function(){ EGf8c88e12.Init(); });

var EGf8c88e12 = {

	Init: function() {
		if (OEConfEGf8c88e12 === undefined) return;
		var allElements = OEConfEGf8c88e12;

		for(var ID in allElements) {
			var $el = $('#'+ID); // le tag <div> principale de l'élément
			var properties = allElements[ID]; // les propriétés de l'élément disponibles pour JS
			this.InitElement(ID, $el, properties);
		}
	},

	InitElement: function(ID, $el, properties) {
		if (properties.No_Editor && WEInfoPage.RenderMode == 'Editor') {
			return; // don't show in editor mode
		}
		if (!properties.Images.length) {
			return;
		}
		
		var wdMobile = properties.Mobile_Width;
		if (wdMobile > 0) {
			var wdScreen = $(window).width();
			if (wdScreen <= wdMobile) {
				properties.modeMobile = true;
			}
		}
		
		var ListImages = this.getListImages(properties);
		var tabbackgrounds= ListImages; //eval("["+ListImages+"]");

		var overlay = null;
		if (properties.Overlay !== 0) {
			overlay = EGf8c88e12.GetOverlayImagePath(properties.Overlay);
		}

		//preload done in OE (?)

		// Choose either page background or parent element:
		var $target = $('body');
		if (properties.Element && $('#'+properties.Element).length) {
			$target = $('#'+properties.Element);
			/* if ($target.children('.OESZ_DivContent').length) {
				$target = $target.children('.OESZ_DivContent');
			} */
			$target = $('<div class="oebs-vegas-target">').prependTo($target);
		}
		
		// Slider options ////////////////////////////////
		var options = {
			delay: 		properties.Delay,
			slides: 	tabbackgrounds, 
			preload: 	false,
			transition: false,
			timer:		false
		};
		
		if (properties.Transition) { // only if transition duration > 0
			var ttypes = [
				"random",
				"fade",
				"fade2",
				"slideLeft",
				"slideLeft2",
				"slideRight",
				"slideRight2",
				"slideUp",
				"slideUp2",
				"slideDown",
				"slideDown2",
				"zoomIn",
				"zoomIn2",
				"zoomOut",
				"zoomOut2",
				"swirlLeft",
				"swirlLeft2",
				"swirlRight",
				"swirlRight2",
				"burn",
				"burn2",
				"blur",
				"blur2",
				"flash",
				"flash2"
			];
			options['transition'] = ttypes[properties.Trans_Type];
			options['transitionDuration'] = parseInt(properties.Transition) || 1000;
			options['animationDuration'] = parseInt(properties.Transition) || "auto";
		}
		
		if (overlay !== null) {
			options['overlay'] = overlay;
			//properties.OverlayOpacity is applied below
		}
		
		if (properties.Mode_Loop > 0) { // stop slideshow at the end
			// DOES NOT WORK options['loop'] = false;
		}

		$target.vegas(options);
		$('.vegas-loading').css('background','url('+WEEdSiteCommon.LinkGetPath(OEConfSharedEGf8c88e12.Images.Loading)+') no-repeat center center'); 
		if (overlay !== null && (properties.OverlayOpacity >= 0 && properties.OverlayOpacity <= 1)) {
			$('.vegas-overlay').css('opacity', properties.OverlayOpacity);
		}

	},
	
	
	getListImages: function(properties) 
	{
		var alignVals  = ['center', 'left', 'right'];
		var valignVals = ['center', 'top', 'bottom'];

		var ListImages=[];
		for (var lineInd in properties.Images) {
			var image = {};
			var img = properties.Images[lineInd];
			
			var align  = alignVals[img['Align']] || 'center', 
				valign =  alignVals[img['Valign']] || 'center';
			
			image.src = (properties.modeMobile && WEEdSiteCommon.LinkGetPath(img['Image_Resp1'])) || WEEdSiteCommon.LinkGetPath(img['Images']);
			if (image.src) {
				image.src = image.src.replace(/\s/, '%20');
			}
			
			image.loading = properties.Loading;
			//image.fade = properties.Fade;
			image.align = alignVals[img['Align']] || 'center';
			image.valign = valignVals[img['Valign']] || 'center';
			
			// video in the slide:
			var videoSrc = null;
			if (img['Video_File'] && WEEdSiteCommon.LinkGetPath(img['Video_File'])) {
				videoSrc = WEEdSiteCommon.LinkGetPath(img['Video_File']);
			} else if (img['Video_Url'] && WEEdSiteCommon.GetLocalizableString(img['Video_Url'])) {
				videoSrc = WEEdSiteCommon.GetLocalizableString(img['Video_Url']);
			}			
			if (videoSrc) {
				image.video = { src: [videoSrc] };
				if (properties.Mode_Loop == 2 || properties.Mode_Loop == 3) { // do not loop videos
					image.video.loop = false;
				}
			}
			
			ListImages.push(image);
			
			if (WEInfoPage.RenderMode == 'Editor') {
    			break; // add only first image in editor mode
  			}

		}
		if (properties.Mode_Loop == 1 || properties.Mode_Loop == 3) { // stop slideshow at the end
			// Hack to stop the slideshow
			ListImages.push("stop");
		}
		return ListImages;
	},
	
	
	//Get Overlay image path
	GetOverlayImagePath: function(ID) {
		var image = eval('OEConfSharedEGf8c88e12.Images.Image'+ID);
		return "'"+WEEdSiteCommon.LinkGetPath(image)+"'";
	}


};





