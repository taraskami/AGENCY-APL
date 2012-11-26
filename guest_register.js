function allInputsValid() {
	if (!$('div.guestLoginUnit select').val()) {
		return false;
	}
	var date_check=/([0-9]{1,2}[\-\/]?){2}[0-9]{2,4}/;
	if ($('div.guestLoginDob input').val().match(date_check)) {
		return true;
	}
	return false;
}

$( function() {
	$('.calTodayLink').remove();
	$('.field_date').keyboard( { 
		layout: 'custom', 
		customLayout: { 'default': ['7 8 9', '4 5 6', '1 2 3', '{b} 0 /'] } ,
		usePreview: false,
		autoAccept: true,
		position: { at2: 'right center', my: 'left bottom' }
	} );

	// Show DOB after unit selected	
	$('div.guestLoginUnit select').change( function() {
		if ($(this).val()) {
			$('div.guestLoginDob').show('slow').find('input').focus();
			//if (allInputsValid()) {
				$('div.guestLoginGo').show('slow');
			//}
		} else {
			$('div.guestLoginDob').hide('slow');
		}
	});

	// Not working as change event not being triggered
	$('div.guestLoginDob input').change( function() {
		if (allInputsValid()) {
				$('div.guestLoginGo').show('slow');
		}
	});


});

