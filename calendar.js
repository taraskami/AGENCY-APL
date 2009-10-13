/*
<LICENSE>

This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2009 by Ken Tanzer and Downtown Emergency
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
along with CHASERS.  If not, see <http://www.gnu.org/licenses/>.

For additional information, see the README.copyright file that
should be included in this distribution.

</LICENSE>
*/

var vname1='';
var calendarToday = new Date();
var calendarDay   = calendarToday.getDate();
var calendarMonth = calendarToday.getMonth();
var calendarYear  = calendarToday.getFullYear();

function calendarRestart() {
	eval("document." + vname1 + ".value = '' + (calendarMonth - 0 + 1) + '/' + calendarDay +'/' + calendarYear;");
	hideCalendar();
}

function calendarReset() {
	calendarToday = new Date();
	calendarDay   = calendarToday.getDate();
	calendarMonth = calendarToday.getMonth();
	calendarYear  = calendarToday.getFullYear();
	calendarBox.innerHTML = '';
	calendarBox.innerHTML = generateCalendar();
}

function makeCalendar( varname, loc ) {
	vname1 = varname;
	var calendarContent = generateCalendar();
	calendarBox = document.getElementById('mainCalendar');
	calendarBox.innerHTML = '';
	calendarBox.innerHTML = calendarContent;
	calendarBox.style.display = "block";
	calendarBox.style.top = getOffset(loc,'offsetTop') + 'px';
	calendarBox.style.left = getOffset(loc,'offsetLeft') + loc.offsetWidth + 'px';
}

function hideCalendar() {
	document.getElementById('mainCalendar').style.display = "none";
}
function changeDate(day) {
	calendarDay = day + '';
	calendarRestart();
}
weekDayName = new Array ('Su','M','Tu','W','Th','F','Sa');
monthName = new Array ('January','February','March','April','May','June','July','August','September','October','November','December');
daysInMonth = new Array (31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

function generateCalendar() {

	var out='';


	out += '<div style="text-align: center;">';

	out += '<span style="margin-right: 15px; font-size: 170%; font-weight: bolder;"><a title="last month" href="" onclick="javascript:lastMonth(); return false;">&larr;</a></span>';
	out += '<span><a href="" onclick="javascript:calendarReset();return false">today</a></span>';

	out += '<span style="margin-left: 15px; font-size: 170%; font-weight: bolder;"><a title="next month" href="" onclick="javascript:nextMonth(); return false;">&rarr;</a></span>';
	out += '</div>';

	calendarToday.setDate(1);
	var som=calendarToday.getDay();
	//fixme, this should use getDaysInMonth 
	var days = daysInMonth[calendarMonth];
	//calculate leap year
	if ( (calendarMonth==1) && (calendarYear%4==0) && (!(calendarYear%100==0) || (calendarYear%400==0)) ) {
		days = 29;
	}


	calendarCurrent = new Date();
	calendarCurrentDay = calendarCurrent.getDate();
	calendarCurrentMonth = calendarCurrent.getMonth();
	calendarCurrentYear = calendarCurrent.getFullYear();

	var j=0;
	var i=0;
	var row='';
	var tabs='';
	while (i < som) {
		row += '<td class="jsCalendar">&nbsp;</td>';
		i++;
		j++;
	}
	for (var i=1; i <= days; i++, j++) {
		if (j==7) {
			tabs += '<tr>' + row + '</tr>';
			row='';
			j = 0;
		}
		if (i==calendarCurrentDay && calendarMonth==calendarCurrentMonth && calendarYear==calendarCurrentYear){
			row += printDayCell(i, true);	
		} else {
			row += printDayCell(i, false);
		}
	}
	while (j<7) {
		row += '<td class="jsCalendar">&nbsp;</td>';
		j++;
	}
	tabs += '<tr>' + row + '</tr>';
	var daysOfWeek='';
	for (var k=0; k<7; k++) {
		daysOfWeek += '<th class="jsCalendar">' + weekDayName[k] + '</th>';
	}
	tabs = '<tr>' + daysOfWeek + '</tr>' + tabs;
	out += '<table cellspacing="0" cellpadding="0" class="jsCalendar">' + tabs + '</table>';
	out += '<form name="jscalendar" clase="jscalendarForm">';

	var list = '';
	for (var i=0; i < 12; i++) {
		var selected = (i==calendarMonth) ? ' SELECTED' : '';
		list += '<option value="' + i + '"' + selected + '>' + monthName[i] + '</option>';
	}
	out += '<select class="jsCalendarForm" name="month" onchange="javascript:switchMonth()">' + list + '</select>';

	var list = '';
	for (var i=1900; i < 2100; i++) {
		var selected = (i==calendarToday.getFullYear()) ? ' SELECTED' : '';
		list += '<option value="' + i + '"' + selected + '>' + i + '</option>';
	}
	out += '<select class="jsCalendarForm" name="year" onchange="javascript:switchYear()">' + list + '</select>';

	out += '</form>';
	out += '<span><a href="" onclick="javascript:document.getElementById(&quot;mainCalendar&quot;).style.display=&quot;none&quot;;return false">close</a></span>';	return out;
}


function printDayCell(day, today) {
	var aClass = (today) ? '"jsCalendar jsCalendarToday"' : '"jsCalendar"' ;	
	return '<td class= "jsCalendar"><a class=' + aClass + 'href="javascript:changeDate(' + day + ')">' + day + '</a></td>';
}

function switchMonth() {
	calendarMonth = document.jscalendar.month.options[document.jscalendar.month.selectedIndex].value;
	calendarToday.setMonth(calendarMonth);
	calendarBox.innerHTML = '';
	calendarBox.innerHTML = generateCalendar();
}

function switchYear() {
	calendarYear = document.jscalendar.year.options[document.jscalendar.year.selectedIndex].value;
	calendarToday.setFullYear(calendarYear);
	calendarBox.innerHTML = '';
	calendarBox.innerHTML = generateCalendar();
}

function nextMonth() {
	//add 1 month

	if ( calendarToday.getMonth()==11)  {		
		calendarYear = calendarToday.getFullYear() + 1;
	}

	calendarToday.setMonth(calendarToday.getMonth() + 1);
	calendarMonth = calendarToday.getMonth();


	calendarBox.innerHTML = '';
	calendarBox.innerHTML = generateCalendar();
}

function lastMonth() {

	if ( calendarToday.getMonth()==0)  {		
		calendarYear = calendarToday.getFullYear() - 1;
	}

	calendarToday.setMonth(calendarToday.getMonth() - 1);
	calendarMonth = calendarToday.getMonth();
	calendarBox.innerHTML = '';
	calendarBox.innerHTML = generateCalendar();
}

