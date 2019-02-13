#!/usr/bin/perl -- -*-perl-*-

# ------------------------------------------------------------
# Timer_Tmpl.pl, by José R. Valverde (jrvalverde@es.embnet.org).
#
# Created: May 24, 2000
# Last updated: May 24, 2000
#
# This file provides a template for time consuming CGI's so
# that when they are called the user will get a timing clock
# to keep him amused while waiting.
#
#	Why does the server return it all at once?
#
# ------------------------------------------------------------

# Define fairly-constants

use FileHandle;
#--------------------------------------------------------------------
open(STDERR, ">&STDOUT");
select(STDERR); $| = 1;     # make unbuffered
select(STDOUT); $| = 1;     # make unbuffered
STDOUT->autoflush(1);

# We'll use a multipart type document:

$boundary = "--CGI-Timer".time;
#print "HTTP/1.0 200 OK\n";
print "Content-type: multipart/x-mixed-replace;boundary=$boundary\n";

# start timer part
print "\n--$boundary\n";

# Print out a content-type 
print "Content-type: text/html\n\n";

# Print a title and initial heading
print "\<Head><Title>Thank you!</Title></Head>\n";
print "<Body BGCOLOR=\"white\">\n";
print "\<CENTER><H1>Please, wait while we process your request.</H1></CENTER>\n<BR>\n";

print "\<SCRIPT language=\"JavaScript\">\n";
print "\<!-- Timer clock\n";
print "var timerform\n";
print "speed = 1000\n";
print "function dotimer()\n{\n";
print "    today = new Date()\n";
print "    seconds = today.getSeconds()\n";
print "    minutes = today.getMinutes()\n";
print "    hours = today.getHours()\n";
print "    now = (seconds) + 60 * (minutes) + 3600 * (hours)\n";
print "    diff = now - start\n";
print "    h = Math.floor(diff / 3600)\n";
print "    m = Math.floor((diff / 3600 - h) * 60)\n";
print "    s = Math.floor((((diff / 3600 -h) * 60) - m) * 60)\n";
print "    document.timerform.timer.value = h + \':\'\n";
print "    if (m < 10) document.timerform.timer.value += \'0\'\n";
print "    document.timerform.timer.value += m + \':\'\n";
print "    if (s < 10) document.timerform.timer.value += \'0\'\n";
print "    document.timerform.timer.value += s\n";
print "    if (done == 0) window.setTimeout(\"dotimer()\", speed)\n}\n\n";
print "function stopTimer()\n{\n";
print "    done = 1\n}\n\n";
print "function startTimer()\n{\n";
print "    done = 0\n";
print "    today = new Date()\n";
print "    sseconds = today.getSeconds()\n";
print "    sminutes = today.getMinutes()\n";
print "    shours = today.getHours()\n";
print "    start = (sseconds) + 60 * (sminutes) + 3600 * (shours)\n";
print "    document.write(\'<FORM NAME=timerform><INPUT NAME=timer size=7></FORM>\')\n";
print "    dotimer()\n}\n// end -->\n</SCRIPT>\n";

print "\<H2>Your request has been running for \n";
print "\<SCRIPT language=\"JavaScript\">startTimer()</SCRIPT>\n";

print "\n--$boundary\n";

# do job
sleep 5;
# produce output

print "Content-type: text/html\n\n";

print "<H1>Thanks for waiting!</H1>\n";

print "\n--$boundary--\n";
exit;


# ------------------------------------------------------------
