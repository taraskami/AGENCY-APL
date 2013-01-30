function inputsValidate() {
	var unit = $('div.guestLoginUnit');
	var secret = $('div.guestLoginSecret');

	if (! ((unit.length==1) && secret.length==1)) {
		// Stop if we're on the wrong page
		return true;
	}

	var goButton = $('div.guestLoginGo');
	//var secred_regex=/([0-9]{1,2}[\-\/]?){2}[0-9]{2,4}/; // for a full dob
	var secret_regex = /^([0-9]{4})$/; // 4 year DOB

	var our_form = $(goButton).closest('form');

	var unit_val = $(unit).find('input').val();
	var secret_val = $(secret).find('input').val();

	var check_unit = (jQuery.inArray(unit_val, inputsValidate.validUnits) > -1);
	var check_secret = (secret_val !== undefined) && secret_val.match(secret_regex);
	if ( (check_unit) && check_secret) {
		var loading = $('<img></img>').attr('src','images/loading.gif');
		$(unit).find('input').removeAttr('disabled');
		$(unit).after(loading);
		$(our_form).submit();
		return true;
	} else if (check_unit) {
		var keyboard=$(unit).find('input').keyboard().getkeyboard();
		keyboard.destroy();
//		$(unit).find('input').attr('disabled','disabled');
		$(secret).show().find('input').focus();
		return false;
	} 
}

$( function() {

	inputsValidate.validUnits= jQuery.parseJSON( $( '#housingUnitCodes' ).html() );

  $('div.guestLoginUnit input').focus( function() {
		inputsValidate();
		var inp = $('div.guestLoginUnit').find('input');
		if ( ($(inp).val() !== undefined) && jQuery.inArray($(inp).val(), inputsValidate.validUnits) > -1) { 
			$(inp).val('');
		}
	});

	$('.calTodayLink').remove();
	var params = {
		alwaysOpen: false,
		stayOpen: false,
		layout: 'custom', 
		customLayout: { 'default': ['7 8 9', '4 5 6', '1 2 3', '{b} 0 /'] } ,
		usePreview: false,
		autoAccept: true,
		position: { at2: 'right center', my: 'left center' }
	};
// Remove the comment block for an onscreen keyboard
/*
	$('div.guestLoginSecret input').keyboard( params );
	$('div.guestLoginUnit input').keyboard( params );
*/
	$('div.guestLoginUnit input').focus();
	var watch = setInterval( function() {
		if (inputsValidate()) {
			clearInterval(watch);
		} 
		} ,100);
});

