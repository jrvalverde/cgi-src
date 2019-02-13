// return true if a string contains only whitespace characters
function isblank(s)
{
for (var i = 0; i < s.length; i++) {
	var c = s.charAt(i);
	if ((c != ' ') && (c != '\n') && (c != '\t') && (c != ''))
		return false;
}
return true;
}

// Perform form verification. Invoke it from the onsubmit event handler.
// The handler should return whatever value this function returns.
function verify(f)
{
var msg;
var empty_fields = "";
var errors = "";

// loop through the form elements looking for all Text and
// Textarea elements that don't have an 'optional' property
// defined. Then check for fields that are empty and make a
// list of them. Also, if any of these have a 'min' or 'max'
// property defined, verify that they are numbers and in the
// right range. If the element has a 'numeric' property
// defined, verify that it is a number, but don't check its
// range. Put together error messages for fields that are
// wrong.
for (var i = 0; i < f.length; i++) {
	var e = f.elements[i];
	if (((e.type == "text") || (e.type == "textarea")) &
	    ! e.optional) {
		// first check if the field is empty
		if ((e.value == null) ||
		    (e.value == "") ||
		    isblank(e.value)) {
			empty_fields += "\n        " + e.name;
			continue
		}

		// now check for fields that are supposed to be numeric
		if (e.numeric || (e.min != null) || (e.max != null)) {
			var v = parseFloat(e.value);
			if (isNaN(v) ||
			    ((e.min != null) && (v < e.min)) ||
			    ((e.max != null) && (v > e.max))) {
				errors += "- The field " + e.name + " must be a number";
				if (e.min != null)									errors += " that is greater than " + e.min;
				if ((e.max != null) && (e.min != null))
					errors += " and less than " + e.max;
				else if (e.max != null)
					errors += " that is less than " + e.max;
				errors += ".\n";
			}
		}
	}
}

// Now if there were any errors, display the messages, and
// return false to prevent the form from being submitted.
// Otherwise return true;
if (! empty_fields && !errors) return true;

msg = "------------------------------------------------------------\n\n";
msg += "The form was not submitted because of the following error(s).\n";
msg += "Please correct these error(s) and resubmit.\n";
msg += "------------------------------------------------------------\n\n";

if (empty_fields) {
	msg += "- The following required fields are empty:"
	     + empty_fields + "\n";
	if (errors) msg += "\n";
}
msg += errors;
alert(msg);
return false;
}
