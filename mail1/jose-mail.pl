#!/usr/bin/perl -- -*-perl-*-

# ------------------------------------------------------------
# Form-mail.pl, by Reuven M. Lerner (reuven@the-tech.mit.edu).
#
# Last updated: March 14, 1994
#
# Form-mail provides a mechanism by which users of a World-
# Wide Web browser may submit comments to the webmasters
# (or anyone else) at a site.  It should be compatible with
# any CGI-compatible HTTP server.
# 
# Please read the README file that came with this distribution
# for further details.
# ------------------------------------------------------------

# ------------------------------------------------------------
# This package is Copyright 1994 by The Tech. 

# Form-mail is free software; you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by the
# Free Software Foundation; either version 2, or (at your option) any
# later version.

# Form-mail is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with Form-mail; see the file COPYING.  If not, write to the Free
# Software Foundation, 675 Mass Ave, Cambridge, MA 02139, USA.
# ------------------------------------------------------------

# Define fairly-constants

# This should match the mail program on your system.
$mailprog = '/usr/lib/sendmail';

# This should be set to the username or alias that runs your
# WWW server.
$recipient = 'jrvalverde@cnb.uam.es';

# Print out a content-type for HTTP/1.0 compatibility
print "Content-type: text/html\n\n";

# Print a title and initial heading
print "<Head><Title>Thank you</Title></Head>";
print "<Body><H1>Thank you</H1>";

# Get the input
read(STDIN, $buffer, $ENV{'CONTENT_LENGTH'});

# Split the name-value pairs
@pairs = split(/&/, $buffer);

foreach $pair (@pairs)
{
    ($name, $value) = split(/=/, $pair);

    # Un-Webify plus signs and %-encoding
    $value =~ tr/+/ /;
    $value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;

    # Stop people from using subshells to execute commands
    # Not a big deal when using sendmail, but very important
    # when using UCB mail (aka mailx).
    # $value =~ s/~!/ ~!/g; 

    # Uncomment for debugging purposes
    # print "Setting $name to $value<P>";

    $FORM{$name} = $value;
}

# If the comments are blank, then give a "blank form" response
&blank_response unless $FORM{'comments'};

# Now send mail to $recipient
$recipient = $FORM{'recipient'} if $FORM{'recipient'};

open (MAIL, "|$mailprog $recipient") || die "Can't open $mailprog!\n";
print MAIL "Reply-to: $FORM{'username'} ($FORM{'realname'})\n";
print MAIL "Subject: WWW comments (Forms submission)\n\n" unless $FORM{'subject'};
print MAIL "Subject: WWW mail: $FORM{'subject'} (Forms submission)\n\n" if $FORM{'subject'};
print MAIL "$FORM{'username'} ($FORM{'realname'}) sent the following comment\n";
print MAIL "about $FORM{'subject'}\n" if $FORM{'subject'};
print MAIL  "------------------------------------------------------------\n";
print MAIL "$FORM{'comments'}";
print MAIL "\n------------------------------------------------------------\n";
print MAIL "Server protocol: $ENV{'SERVER_PROTOCOL'}\n";
print MAIL "Remote host: $ENV{'REMOTE_HOST'}\n";
print MAIL "Remote IP address: $ENV{'REMOTE_ADDR'}\n";
close (MAIL);

# Make the person feel good for writing to us
print "Thank you for sending your comments!<P>";
print "Return to JR's <A HREF=\"/~txomsy/\">home page</A>, if you want.<P>";

# ------------------------------------------------------------
# subroutine blank_response
sub blank_response
{
    print "Your comments appear to be blank, and thus were not sent ";
    print "to Jos&eacute; R. Valverde.  Please re-enter your comments, or ";
    print "return to JR's <A HREF=\"/~txomsy\">home page</A>, if you want.<P>";
    exit;
}

