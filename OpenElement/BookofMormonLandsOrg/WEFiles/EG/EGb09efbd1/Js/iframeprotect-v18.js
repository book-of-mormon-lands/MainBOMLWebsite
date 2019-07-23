$(function(){ EGb09efbd1.Init(); });

var EGb09efbd1 = {

	Init: function() {
		if (typeof(OEConfEGb09efbd1) === undefined) return;
		var allElements = OEConfEGb09efbd1;

		for(var ID in allElements) {
			var $el = $('#'+ID); // le tag <div> principale de l'élément
			var properties = allElements[ID]; // les propriétés de l'élément disponibles pour JS
			this.InitElement(ID, $el, properties);
		}
		
		this.initHandlers();
	},

	InitElement: function(ID, $el, properties) {
		
		var _$elements = [], // elements (tags) affected
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
		
		if (!_$elements.length) { // no elements - search all iframes on the page
			$('iframe').closest('.BaseDiv').each(function(){ _$elements.push($(this)); });
			if (!_$elements.length) {
				return;
			}
		}
		
		for (i in _$elements) {
			var $protect = _$elements[i];
			if ($protect.data('oeip-on')) { continue; } // element already processed
			$protect.data('oeip-on', true);
			var $iframes = []; // all iframes inside this tag, including the tag itself
			if ($protect.prop("tagName").toLowerCase() == 'iframe') { // the tag itself is an iframe
				$iframes.push($protect);
			} else {
				$protect.find('iframe').each(function(){ $iframes.push($(this)); });
			}
			if ($iframes.length) {
				
				for (var ii in $iframes) {
					var $iframe = $iframes[ii];
					if ($iframe.data('oeip-on')) { continue; } //  already processed
					$iframe.data('oeip-on', true);
					this.wrapIframe($iframe);
				}
			}
		}		
	},
	
	
	wrapIframe: function($iframe) {
		$iframe.wrap("<div class='oeip-wrapper'></div>");
	},
	
	
	initHandlers: function() {
		$('body').on('click', '.oeip-wrapper', function(){
			$(this).find('iframe').addClass('clicked')});
		$('body').on('mouseleave', '.oeip-wrapper', function(){
			$(this).find('iframe').removeClass('clicked')});
	}

};