/*
<LICENSE>
 
This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2012 by Ken Tanzer and Downtown Emergency
Service Center (DESC).

All rights reserved.

For more information about AGENCY, see http://agency.sourceforge.net/
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

//FIXME:  This code could be nicer and cleaner

/* Client Selector */
function qs_object( searchText, obj ) {
		var curPage = window.location.pathname;
		//var args = { QuickSearch: searchText, MyClients: myClient, select_to_url: curPage };
		var args = { QuickSearch: searchText, QuickSearchObject: obj, MyClients: false, select_to_url: curPage };
		return args;
};	

/* Object Picker */
function addSelectedObject( object ) {
	// Should have id, label, obj, canRemove, refType

	// Check if exists
	var dupe=false;
	$(".selectedObject").each( function() {
		data=$(this).data('selectedObject');
		if ( (object.object==data.object) && (object.id==data.id) ) {
			dupe = true;
		}
	});
	if (dupe) {
		return false;
	}
	/* FIXME: this is an ugly hack, and also won't work for donor or other flavors */
 	if (object.object=='client') {
		var lab = '<a href=client_display.php?id=' + object.id +' class=' + object.object + 'Link>'+object.label+'</a>';
	} else {
		var lab = '<a href=display.php?control[action]=view&control[object]=' + object.object + '&control[id]='
			+ object.id +' class=' + object.object + 'Link>'+object.label + '</a>';
	};
	var val_remove = object.canRemove ? ' <a href=# class=selectedObjectRemove>(remove)</a>' : '';
	var text = $('<span class="selectedObject"><li>' + lab + val_remove + "</li></span>");
	text.data('selectedObject',object);
	if (object.object=='info_additional') {
		$("#infoAdditionalContainer").show().append(text).data("selectedObject",object);
	} else {
		$("#objectReferenceContainerReference" + (object.refType=='from' ? 'From' : 'To')).show().append(text).data("selectedObject",object);
		$("#objectReferenceContainer").show();
	}
}

$(function() {
	$(".objectPickerSubmit").live("click", function(event) {
		event.preventDefault();
		var method =$(event.target).closest('div').find("[name=objectPickerMethod]").val();
		if (!method) {
			method = $(event.target.row).find("[name=objectPickerMethod]").val();
		}
		switch (method) {
			case 'Pick':
				var obj_name = $(event.target).closest('div').find("[name=objectPickerObject]").val();
				var obj_id = $(event.target).closest('div').find("[name=objectPickerPickList]").val();
				var obj_text = $(event.target).closest('div').find("[name=objectPickerPickList] :selected").text();
				addSelectedObject( { id: obj_id, label: obj_text, object: obj_name, canRemove: true, refType: 'to' });
				break;
			case 'searchResult':
				var obj_name = $(event.target).closest('tr').find("[name=objectPickerObject]").val();
				var obj_id = $(event.target).closest('tr').find("[name=objectPickerId]").val();
				var obj_text = $(event.target).closest('tr').find("[name=objectPickerLabel]").val();
				addSelectedObject( { id: obj_id, label: obj_text, object: obj_name, canRemove: true, refType: 'to' });
				break;
			case 'Search':
				var search_text = $(event.target).closest('div').find("[name=objectPickerSearchText]").val();
				var obj = $(event.target).closest('div').find("[name=objectPickerObject]").val();
				var args = qs_object(search_text,obj);
				$.ajax({
					method: "get",
					url: "ajax_selector.php",
					data: args,
					beforeSend: function(){$("#page_loading").show("fast");},
					complete: function(){ $("#page_loading").hide("fast");}, 
					success: function(html){
						$("#aj_client_selector_my_clients").val(false);
						$("#ajClientSearchResults").html(html); 
						var button = '<td><button type="button" class="objectPickerSubmit">SELECT</button></td>';
						$("#ajClientSearchResults tr.generalData2,tr.generalData1").each( function(i) {
							$(this).children("td:eq(1)").html(button);
						});
						$("#ajClientSearchResults").show(); 
						var tab=$("#ajClientSearchResults table");
						}
				});
		}
	});
});

/*
 * Object Reference Container & Additional Information
 */

$(function() {
	var section_to='<div id=objectReferenceContainerReferenceTo><h2>Refers to</h2></div>';
	var section_from='<div id=objectReferenceContainerReferenceFrom><h2>Referenced By</h2></div>';
	$("#objectReferenceContainer").append(section_to+section_from).draggable().hide();
	$("#objectReferenceContainer div").hide();
	$("#infoAdditionalContainer").append("<h2>Additional Information</h2>").draggable().hide();
	var closeButton = '<a id="objectSelectorHideLink" class="fancyLink">close</a>';
	$("#objectSelectorForm").tabs().draggable().hide().append(closeButton);
	addPreSelectedObjects();
	$("#objectSelectorShowLink").click( function(event) {
		event.preventDefault();
		$("#objectSelectorForm").show();
		$(this).hide();
	} );
	$("#objectSelectorHideLink").click( function(event) {
		event.preventDefault();
		$("#objectSelectorForm").hide();
		$("#ajClientSearchResults").hide();
		$("#objectSelectorShowLink").show();
	});
	$('.selectedObjectRemove').live( 'click', function(event) {
		event.preventDefault();
		data=$(this).closest('span').data('selectedObject');
		if ( ($(this).closest('div').find('span')).length==1) {
			$(this).closest('div').hide();
		}
		if (data.canRemove) {
			$(this).closest('span').remove();
			if ($("#objectReferenceContainer span").length==0) {
				$("#objectReferenceContainer").hide();
			}
		}
	});


	/* On submit, add references to form */
	$("form").submit( function(event) {
		$(".selectedObject").each( function() {
			var data='<input type="hidden" name="selectedObject[]" value = "'+encodeURIComponent(JSON.stringify($(this).data('selectedObject')))+'">';
		$('#preSelectedObjects').before(data);
		});
	});

});

/* Add pre-selected objects from server to form */
function addPreSelectedObjects() {
	$("#preSelectedObjects > div").each( function() {
		addSelectedObject( jQuery.parseJSON( $(this).html()));
	});
};

	
