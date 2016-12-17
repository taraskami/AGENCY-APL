/*
<LICENSE>

This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2012 by Ken Tanzer and Downtown Emergency
Service Center (DESC).

All rights reserved.

For more information about AGENCY, see http://agency-software.org/
For more information about DESC, see http://www.desc.org/.

AGENCY is free software: you can redistribute it and/or modify
it under the terms of version 3 of the GNU General Public License
as published by the Free Software Foundation.

AGENCY is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with AGENCY.  If not, see <http://www.gnu.org/licenses/>.

For additional information, see the README.copyright file that
should be included in this distribution.

</LICENSE>
*/

/* This file contains all the custom code for jquery and AGENCY.
   For now.  It may make more sense to break it up into separate
   files later.
 */

/* Datepicker for date fields */
$(function() {
	var today=' <a href=# class="calTodayLink fancyLink">today</a>';
	$(".field_date").after(today);
	$(".calTodayLink").click( function(event) {
		event.preventDefault();
		var tmp=new Date();
		var tmp2=1+tmp.getMonth()+'/'+tmp.getDate()+'/'+tmp.getFullYear();
		$(event.target).prev().prev().val(tmp2).change();
	});

	$('.field_date').datepick( {
			showOnFocus: false, 
    		showTrigger: '<img src="images/calendar.png" alt="Popup" class="calButton"/>',
			monthsToShow: 2,
			firstDay: 1,
			yearRange: '-100:+10'
	});
});

/* TimePicker for time fields */
$(function() {
	var clock_button='<img src="images/clock.gif" class="clockButton" />';
	var now=' <a href=# class="calNowLink fancyLink">now</a>';
	$(".field_time").after(now).after(clock_button);
	$(".clockButton").clockpick( {
		starthour: 0,
		endhour: 23,
		minutedivisions: 12
	},
	function( ntime ) {
		$(this).prev().val(ntime);
	});

	$(".calNowLink").click( function(event) {
		event.preventDefault();
		var tmp=new Date();
		var hours=tmp.getHours();
		if ( hours >= 12 ) {
			var ampm = "pm";
			hours = hours - 12;
		} else {
			var ampm = "am";
		}
		var min = tmp.getMinutes();
		if (min < 10) {
			min = '0' + min;
		}
		var tmp2= hours + ':' + min + ' ' + ampm;
		$(event.target).prev().prev().val(tmp2);
	});
});

/* Highlight errors and selected checkboxes on Engine forms */
$(function() {
	$(".engineFormError").closest("tr").addClass("engineFormError"); 
	$(".engineFormError").closest("tr").click( function() { 
		$(this).find(".engineFormError").removeClass("engineFormError");
		$(this).removeClass("engineFormError");
	});
	$(".engineForm input:checkbox:checked").closest('tr').addClass("engineFormSelected");

	$(".engineForm input:checkbox").change( function() {
		$(this).closest('tr').toggleClass('engineFormSelected');
	});
});

/* For multi forms, check the "selected" box if another input is entered for that row */
$(function() {
	$('table.multiForm input').click( function() {
		if ( ($(this).val() || $(this).prop('checked')) ) {	
			var target=$(this).closest('tr');
			var target2=$(target).closest('table').closest('tr');
			// If its an extra info row, need to get out of table and go up a row
			if ($(target2).hasClass('AdditionalInformation')) {
				target=$(target2).prev();
			}
			target=$(target).find('input').first();
			if ( (!$(target).is($(this))) && ($(target).prop('checked')===false) ) {
				$(target).click();
			}
		}
	});
});


/* Hide & Toggle minor engine messages */
$(function() {
	$(".engineMessageResultDetail").hide().before('<a href="#" class="toggleLink">details...</a>');
	$(".engineMessageResultDetail").prev().click( function(event) {
		event.preventDefault();
		$(this).next().toggle();
	});
});

/* Hide & Toggle minor messages */
$(function() {
	//$(".hiddenDetail").hide().before('<a href="#" class="toggleLink">details...</a>');
	$(".hiddenDetail").each( function() {
		var lab = $(this).children("[name=toggleLabel]");
		if (lab.val()==undefined) {
			if ( $(this).hasClass("sqlCode") ) {
				var text = "Show SQL...";
			} else if ( $(this).hasClass("configFile") ) {
				var text = "Show Configuration...";
			} else {
				var text = "details...";
			}
		} else {
			var text = lab.val();
		}
		$(this).hide().before('<a href="#" class="toggleLink fancyLink">'+text+'</a>');
	});

	$(document).on( 'click', '.toggleLink', function(event) {
		event.preventDefault();
		$(this).next().toggle();
	});
	$(".hiddenDetailShow").show();
});

/*
 * Log text, show as row in table
   Should be genericized to hiddenEngineText
   */

$(function() {
	$(".hiddenLogText").each( function() { 
		var row=$(this).closest('tr');
		var cols=$(row).children('td').length-3;
		$(row).after( '<tr><td></td><td></td><td class="revealedLogText" colspan="' + cols +'">'
			+ '<div class="revealedLogText">'
			+ $(this).html()
			+ '</div>'
			+ '</td></tr>');
		$(this).remove();
	});
});

$(function() {
	$(".listObjectLog").first().before('&nbsp;<a href="#" class="toggleLogText">Show/hide text in place</a>');
	$(".toggleLogText").click( function(event) {
		event.preventDefault();
		$("td.revealedLogText").toggle();
	});
});
$(function() {
	// move staff alerts to command bar
	//$("#engineStaffAlertForm").wrap("<td></td>").after($("#agencyTopLoginBox"));
	//$("#staffAlertContainer").hide();
//	$("#staffAlertContainer").wrap("<td></td>").insertAfter("#agencyTopLoginBox");
//	$("#agencyTopLoginBox").parent().after("test").wrap("<td></td>"));
});

$(function() {
/* To remove test warning, click on red spacer cells */
	$(document).on('click','.topNavSpacerTest', function() {
		$(".agencyTestWarningBox").remove();
		$(".topNavSpacerTest").addClass('topNavSpacer').removeClass('topNavSpacerTest');
	});
});

$(function() {
	/* Elastic text boxes */
	$(".engineTextarea").elastic();
});

$(function() {
/* Process autocomplete options */
	$(".autoComplete").each( function() {
		var opts=eval($(this).html());
		$(this).prev().autocomplete( { source: opts } );
	});

/* QuickSearch autocomplete */
	$("#QuickSearchText").autocomplete( {
		source: function( request, response ) {
			request.type = $("#QuickSearchType").val();
			lastXhr = $.getJSON( "live_search.php", request, function( data, status, xhr ) {
				if ( xhr === lastXhr ) {
					response( data );
				}
			});
		},
		minLength: $("#QuickSearchAutocompleteMinLength").val()
	});

/* TEST Client Form autocomplete */
	$(".enterClient").autocomplete( {
		source: function( request, response ) {
			//request.type = $("#QuickSearchType").val();
			request.type = 'client';
			lastXhr = $.getJSON( "live_search.php", request, function( data, status, xhr ) {
				if ( xhr === lastXhr ) {
					response( data );
				}
			});
		},
		//minLength: $("#QuickSearchAutocompleteMinLength").val()
		minLength: 2
	});

/* Test Entry Client trim autocomplete result down to ID */
	$("form.enterClient input:first").focus();
	$("form.enterClient").submit( function (e) {
		var id_repl = /^.* \(([0-9]+)\)$/;
		var search_val=$(this).find("input:first").val();
		var m;
		m = id_repl.exec(search_val);
		if ( m[1] ) {
			$(this).find("input:first").val( m[1] );
		}
	});

/* Trim autocomplete result down to ID */
	$("form.QuickSearchForm").submit( function (e) {
		var id_repl = /^.* \(([0-9]+)\)$/;
		var search_val=$("#QuickSearchText").val();
		var m;
		m = id_repl.exec(search_val);
		if ( m[1] ) {
			$("#QuickSearchText").val( m[1] );
		}
	});
/* Toggle Advanced Controls */
	$("#advancedControlButton").click( function(e) { e.preventDefault(); $(".advancedControl").toggle(); } );
});

$(function() {
/* Toggle visitor form on entry page */
	$("#enterVisitorLink").click( function (event) {
		event.preventDefault();
		$("#enterVisitorForm").toggle("slow");
	});

$( function() {
  // Webcam stuff here

  $('.photoDialogSelectorLink').click( function(e) {
		e.preventDefault();
		var title = $('.photoDialog').find('.photoDialogTitle').html();
		$('.photoDialog').dialog( {title: title, width: 'auto',height: 'auto', position: { my: "center top", at: "center top", of: window }}).show();
//		$('.photoDialog').show();
		var onFailSoHard = function(e) {
			console.log('Reeeejected!', e);
		};

		navigator.getMedia = ( navigator.getUserMedia ||
                       navigator.webkitGetUserMedia ||
                       navigator.mozGetUserMedia ||
                       navigator.msGetUserMedia);

//navigator.getMedia (constraints, success, error);

  // Not showing vendor prefixes.
	//navigator.getUserMedia({video: true, audio: true}, function(localMediaStream) {
	navigator.getMedia({video: true, audio: false}, function(localMediaStream) {
		var myVideo = document.querySelector('video.videoStream');
		myVideo.src = window.URL.createObjectURL(localMediaStream);
  
  	// Note: onloadedmetadata doesn't fire in Chrome when using it with getUserMedia.
  	// See crbug.com/110938.
		myVideo.onloadedmetadata = function(e) {
			// Ready to go. Do some stuff.
		};
	}, onFailSoHard);
	});

	$('video.videoStream').click( function(e) {
		e.preventDefault();
		var myVideo = document.querySelector('video.videoStream');
		var canvas = document.querySelector('canvas.imageCapture');
		var context = canvas.getContext('2d');
		$(this).closest('.photoDialog').find('.imageSendContainer').show();
		canvas.width=myVideo.videoWidth;
		canvas.height=myVideo.videoHeight;
		context.drawImage(myVideo, 0, 0,myVideo.videoWidth,myVideo.videoHeight);
		console.log('W: ' + myVideo.videoWidth+', H: ' + myVideo.videoHeight);
	});

	$('canvas.imageCapture').click( function(e) {
		e.preventDefault();
		var canvas = document.querySelector('canvas.imageCapture');
		var form = $(this).closest('form');
		$(form).find('input[name=photo_data]').val(canvas.toDataURL('image/jpeg'));
		$(form).submit();
	});

	// Submit form when upload file selected
	$('.imageCaptureUpload').change( function() { 
		if ($(this).val() != "") {
			$(this).closest('form').submit();
		}
	});

	// Upload dialog is clunky and hidden.  A cleaner button triggers it.
	$('.imageCaptureUploadButton').click( function(e) {
		e.preventDefault();
		$(this).closest('div').find('.imageCaptureUpload').click();
	});
  });

});

$( function() {
	$(".formWipeoutLink").click( function (e) {
		e.preventDefault();
		var varname = $(this).next().html();
		$('input[name=' + varname + ']').val('');
	});

	$(document).on('click','.engineValueUnsetLink', function(e) {
		e.preventDefault();
		$(this).closest('.engineValueContainer').find('.engineValue').val(null);
		$(this).closest('.engineValueContainer').find('.engineValueLabel').remove();
		$(this).closest('.engineValueContainer').find('.objectPickerToForm').show();
		$(this).closest('.engineValueContainer').find('.objectPickerToggleLink').hide();	
		$(this).hide();
	});

	// This replaces the doChallengeResponse function for password fields
	$(document).on('submit','form.doChallengeResponse',function() {
		$(this).find('input.passwordField').each( function() {
			$(this).val( MD5($(this).val()));
		} );
	});
});

/* Floating Headers */

// For multi-forms
$(function() {
    $('table.multiForm').floatThead();
});

/* Togglle blank rows on engine forms */
$(function() {

		if ( $('tr.engineValueRowBlank').length > 0 ) {
			function headerDisplay() {
				$('tr.engineRowBefore').each( function() {
					var last_r=$(this).nextUntil("tr.engineRowBefore","tr.engineRowAfter").last();
					var rows=$(this).nextUntil('tr.engineRowBefore').filter('.engineValueRow');
					if ( $(rows).filter(':visible').length == 0) {
						$(this).hide();
						$(last_r).hide();
					} else {
						$(this).show();
						$(last_r).show();
					}
				});
			}

			headerDisplay();
			$('a.engineRowBlankToggle').click( function(e) {
				e.preventDefault();
				$('tr.engineValueRowBlank').toggle();
				headerDisplay();
			});
		} else {
			$('a.engineRowBlankToggle').hide();	
		}
	
});

