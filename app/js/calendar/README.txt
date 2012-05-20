Calendar Project
================
Alan Chandler <alan@chandlerfamily.org.uk>

This project is a Javascript Calendar, using the "mootools" javascript framework.  The overall
package contains two classes

- Calendar.Single which provides a single calendar selection, and
- Calendar.Multiple which provides for a number of calendars which can have constraints on the gap between them

This README and other associated documentation is in need of an update.  The original code, and the MANUAL.html file were
copied from another project (see COPYING.txt for information) and the code was partially rewritten to provide the
Calendar.Multiple option and to add selection of times down to the nearest five minutes for use in another project

The key difference from the original is that the Calendar wraps a `<input>` tag of type hidden which contains a time and date
as a Unix Timestamp (ie seconds from 1st Jan 1970), displaying the calendar in the users local time (according to browser settings)
but maintaining the value in the original input field, so when a form is submitted it can be used to update a database.

As the Calender wraps the `<input>` element, it also provides a `<div>` with the result of any changes displayed, along with an icon that can be used to open the calendar (for user selection of a date).

Calendar.Single
--------------- 

The basic call for a single calendar is

-----------------------------
new Calendar.Single(input,options);
-----------------------------

where +input+ is the id of an html `<input>` element and +options+ is a literal object containing additional optional parameters.

Calendar.Multiple
-----------------

This is called so :-

----------------------------
new Calendar.Multiple(inputs,options);
----------------------------

where +inputs+ is an array of `<input>` elements and +options+ is a literal object containing additional optional parameters.


