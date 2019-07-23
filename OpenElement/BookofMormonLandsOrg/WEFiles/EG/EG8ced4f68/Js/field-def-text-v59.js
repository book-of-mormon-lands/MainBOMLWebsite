$(function(){ EG8ced4f68.Init(); });

var EG8ced4f68 = {

	Init: function() {
		if (typeof(OEConfEG8ced4f68) === undefined) return;
		var allElements = OEConfEG8ced4f68;

		for(var ID in allElements) {
			var $el = $('#'+ID); // The element's main <div> tag
			var properties = allElements[ID]; // element's available properties for JS
			this.InitElement(ID, $el, properties);
			
			break; //process  only one element!
		}
	},

	InitElement: function(ID, $el, properties) {
		
		/*
		var $style = $el.find('.OESZ_Default_Style').first();
		var el_color = $el.css('color');
		var color = $style.css('color');
		if (el_color == color) { // test if color is inherited from element
			$el.css('color', (el_color == '#444444') ? 'black' : '#444444');
			el_color = $el.css('color');
			color = $style.css('color');
			if (el_color == color) {
				color = null;
			}
		}
		*/
		if (properties.Apply_Style) {
			var color 	= $el.css('color') || null;
			var bg 		= $el.css('background') || null;
			// var font_size = $style.css('font-size');
			if (color || bg) { // add CSS class with corresponding styles
				color 	= color ? ("color: " + color + "; ") : '';
				bg 		= bg ? ("background: " + bg + "; ") : '';
				this.addStyleToHeader(" .oedf-default-style { " + color + bg + "} </style>");
			}
		}
		
		var selector = (properties.Custom_Class ? ('.'+properties.Custom_Class) : '') + ' :input';
		
		$(selector).each(function(){
			var $this = $(this), val = $this.val();
			if (val 
			 && (($this.prop('tagName') && $this.prop('tagName').toLowerCase() === 'textarea') || $this.attr('type') === 'text')) 
			{
				$this.data('defaultText', val).addClass('oedf-default-style defaultText');
				
				$this.focus(function(){ // on field focus & start editing
					$(this).removeClass('oedf-default-style defaultText');
					var defaultText = $(this).data('defaultText'); // texte de depart
					if ($(this).val() === defaultText && defaultText.length) {
						$(this).val(''); // clear if contains default text
					}
				});
				
				$this.blur(function(){ // when the field loses focus
					var defaultText = $(this).data('defaultText') || ""; // texte de depart
					if (defaultText && !$(this).val()) {
						$(this).val(defaultText).addClass('oedf-default-style defaultText'); // if the field is empty, put back the default text
					}
				});
				
			}			
		});
		
		
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

