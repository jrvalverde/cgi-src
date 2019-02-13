#!/usr/local/bin/perl -- -*-perl-*-

# ------------------------------------------------------------
# mail interface by benny shomer.
#
# Last updated: November 14, 1994
#
# based on Reuven Lerner's script. This pl script provides the 
# ability to use only one send-mail script for all the users.
# 
# The personal home page html script of each user should contain
# the appropriate fields for interaction with this script. See mine.
# 
#	Modified by J.R.Valverde to suit *my* personal needs! 09-Dec-1998
#	Modified by J.R.Valverde to suit any user! 13-Dec-1999
#
# ------------------------------------------------------------

# set default recipient (possibly webmaster@site)
$recipientname = 'Network Services Administrator';

# Print out a content-type for HTTP/1.0 compatibility
print "Content-type: text/html\n\n";

# Get the input
#read(STDIN, $buffer, $ENV{'CONTENT_LENGTH'});
#
# Split the name-value pairs
#@pairs = split(/&/, $buffer);
#
#foreach $pair (@pairs)
#{
#    ($name, $value) = split(/=/, $pair);
#
#    # Un-Webify plus signs and %-encoding
#    $value =~ tr/+/ /;
#    $value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
#
#    # Stop people from using subshells to execute commands
#    # Not a big deal when using sendmail, but very important
#    # when using UCB mail (aka mailx).
#     $value =~ s/~!/ ~!/g; 
#
#    # Uncomment for debugging purposes
#    # print "Setting $name to $value<P>";
#
#    $FORM{$name} = $value;
#}
#
#$recipientname = $FORM{'recipientname'} if $FORM{'recipientname'};

$recipientname = $ENV{'QUERY_STRING'} if $ENV{'QUERY_STRING'};

$recipientname =~ tr/+/ /;
$recipientname =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;

#print "<Head><Title>Send mail to $FORM{'recipientname'}.</Title></Head>\n";
print "<Head><Title>Send mail to $recipientname.</Title></Head>\n";

# NOTE: this is highly personalized! XXX JR XXX
#print "<Body><H1>Send mail to $FORM{'recipientname'}.</H1>\n";
print "<Body BACKGROUND=\"/images/backgrounds/marble2back.gif\">\n";
print "<H1>Send mail to $recipientname.</H1>\n";
print "<hr>\n";
print "<P><B>Compose your message and select the \"Submit\" button when you are ready.\n";
print "Note that this page only works if your browser supports forms.</B></P>\n";
print "<hr>";
print "<Form method=POST action=\"http:/cgi-bin/sendemailto.pl\">\n";
print "<P><B>Enter your E-mail address:</B> <INPUT NAME=\"username\"></P>\n"; 
print "<P><B>Enter your name:</B> <INPUT NAME=\"realname\"></P>\n"; 
print "<P><B>Subject (optional):</B> ";
print "<INPUT NAME=\"subject\" SIZE = 40></P>\n";
print "<P><B>Enter your comments below:</B></p>\n";
print "<TEXTAREA NAME=\"comments\" ROWS=20 COLS=60></TEXTAREA><P>\n";
#print "<INPUT TYPE=\"hidden\" NAME=\"recipient\" VALUE=\"$FORM{'recipient'}\">\n";
print "<INPUT TYPE=\"hidden\" NAME=\"recipientname\" VALUE=\"$recipientname\">\n";

print "<Input TYPE=\"submit\" VALUE=\"Submit\">\n";
print "<Input TYPE=\"reset\" VALUE=\"Reset\"><p>\n";
print "</Form>\n";
print "</Body>\n";

