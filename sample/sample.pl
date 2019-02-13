#!/usr/local/bin/perl -- -*-perl-*-
# That should have been the location of your perl5 executable!
# E.g. on OpenBSD
#!/usr/bin/perl -- -*-perl-*-


# READSEQ
$readseqprog = '/opt/molbio/bin/readseq';

# The program to run
$program = '/opt/molbio/segmentadorcillo_de_mi_alma';

# The mailer program
$mailprog = '/usr/lib/sendmail';

# The htpasswd program
$htpassword = '/usr/local/bin/htpasswd';

# Base name for internal files
$intmpbase = '/tmp';

# System base name for externally visible files
$extmpbase = '/data/www/EMBnet/tmp';
# HTMLized base name for externally visible documents
$htmldir = 'http://www.es.embnet.org/tmp';

# Background logo to use in WWW page
$htmlbglogo = '/images/backgrounds/MY_LOGO.gif';

# This should be set to the address (in URL form) and name of the
# local bioinformatics administrator.
$contact_url = 'mailto:God@Heaven.Sky';
$contact_addr = 'Bioinformatics Administrator';

# This should be the URL to your Copytight page
$Copyright_URL = 'http://www.es.embnet.org/Copyright.html';

# This is the name of your site
$my_site = 'MY FAMOUS PLACE';

#open(STDERR, ">&STDOUT");
select(STDERR); $| = 1;     # make unbuffered
select(STDOUT); $| = 1;

# Get the input 
#--------------
# Read one line from stdin
read(STDIN, $buffer, $ENV{'CONTENT_LENGTH'});
# Split the name-value pairs
@pairs = split(/&/, $buffer);

# Alternately, get enrionment variable QUERY_STRING
#@pairs = split(/&/, $ENV{'QUERY_STRING'});

foreach $pair (@pairs)
{
    ($name, $value) = split(/=/, $pair);

    # Un-Webify plus signs and %-encoding
    $value =~ tr/+/ /;
    $value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;

    # Stop people from using subshells to execute commands
    # Not a big deal when using sendmail, but very important
    # when using UCB mail (aka mailx).
     $value =~ s/~!/ ~!/g; 

    # Uncomment for debugging purposes
    # print "Setting $name to $value<P>";

    $FORM{$name} = $value;
}
# Now, $FORM is an array of form values indexed by field names

# Start printing ASAP. This way the browser will start transfer soon
# and the user will notice his browser getting data.
# This is needed so we can print error messages in HTML format
#
# Print out a content-type for HTTP/1.0 compatibility
print "Content-type: text/html\n\n";
# Print out page header
print "<HTML>\n<HEAD><TITLE>YOUR results</TITLE></HEAD>\n";
print "<BODY BGCOLOR=\"white\" BACKGROUND=\"$htmlbglogo\">\n";


# Check required fields are present and valid
&nosequence unless $FORM{'sequence'};

&wrong_level unless $FORM{'confidence'};

&wrong_level unless (($FORM{'confidence'} == 100) ||
    ($FORM{'confidence'} == 99) ||
    ($FORM{'confidence'} == 98) ||
    ($FORM{'confidence'} == 97) ||
    ($FORM{'confidence'} == 96) ||
    ($FORM{'confidence'} == 95) ||
    ($FORM{'confidence'} == 90) ||
    ($FORM{'confidence'} == 85) ||
    ($FORM{'confidence'} == 80));
    
# e-mail address
&badaddress unless $FORM{'username'};
&badaddress unless $FORM{'username'} =~ /.*@.*\..*/;
$remotehost = $FORM{'username'};
$remotehost =~ s/.*@//g;
$uname = $FORM{'username'};
$uname =~ s/@.*//g;
&badaddress unless gethostbyname($remotehost);

# Print calling parameters (some of them are TTY text).
# This way the user can see what he's actually done

print "<CENTER><H1><FONT COLOR=\"gray\">Thanks for using this program</FONT></H1></CENTER>\n";

print "\n</FONT>\n<H2>Results will be mailed to $FORM{'username'}</H2>\n";

# OK, let's go and do the real thing interactively.
# Create a temporary housekeeping directory.
umask 0077;
srand;
$intmpdir = sprintf "%s.%d.%d", "$intmpbase/zsegment", $$, int(rand(30000));
die "<H1>ERROR, HORROR: cannot create neccessary temporary files</H1>\n" 
unless mkdir $intmpdir, 0700;

# Create a temporary user visible directory.
umask 0077;
$extmpdirname = sprintf "%s.%d.%d", "zsegment", $$, int(rand(30000));
$extmpdir = sprintf "%s/%s", $extmpbase, $extmpdirname;
die "<H1>ERROR, HORROR: cannot create neccessary temporary files</H1>\n" 
unless mkdir $extmpdir, 0700;

# Create temporary index.html file
chdir $extmpdir or die "<H1>ERROR, HORROR: cannot access needed output dir</H1>\n";
open (INDEX_HTML, ">index.html");
print INDEX_HTML <<END_INDEX;
<HTML>
<HEAD><TITLE>Please, wait</TITLE></HEAD>
<BODY BGCOLOR="white" BACKGROUND="$htmlbglogo">
<H1>Please, wait</H1>
<H2>Your data is still being processed. Wait a few minutes and reload
this page again to see your results</H2>
</BODY>
</HTML>
END_INDEX
close(INDEX_HTML);

# Create access control files
open (HTACCESS, ">.htaccess");
print HTACCESS<<ENDHTACCESS;
AuthType Basic
AuthName $uname
AuthUserFile  $extmpdir/.htpasswd
<Limit GET POST>
order deny,allow
require valid-user
</Limit>
ENDHTACCESS
close(HTACCESS);
$password = int(rand(99999999));
system "$htpassword -b -c $extmpdir/.htpasswd $uname $password";
# Create the sequence input file
chdir $intmpdir or die "<H1>ERROR, HORROR: cannot access needed temporary files</H1>\n";

open (INFILE, "|$readseqprog -p -fgenbank -a -o=inputfile");
print INFILE $FORM{'sequence'};
print INFILE "\n";
close (INFILE);
#	Show the user
print "<H2>Search sequence (in GenBank format) will be:</H2>\n";
print "<FONT COLOR=\"Magenta\"><PRE><TT>\n";
open (SEQ, "inputfile");
while (<SEQ>) {
	print "$_";
}
print "</TT></PRE></FONT>";

# Create command file
open CMDFILE, ">cmdfile";
print CMDFILE<<ENDCMD;
#!/bin/sh
cd $extmpdir
echo "" > outputfile
echo "These are the results of your SEGMENT job with" >> outputfile
cat $intmpdir/inputfile >> outputfile
echo "" >> outputfile
$program inputfile $FORM{'confidence'} >> outputfile 2>&1
echo "" >> outputfile
echo "You can see your results on" >> outputfile
echo "	$htmldir/$extmpdirname" >> outputfile
echo "identifying yourself as" >> outputfile
echo "	Username: $uname" >> outputfile
echo "	Password: $password" >> outputfile
echo "" >> outputfile
cat outputfile | $mailprog $FORM{'username'}
cd /
/bin/rm -rf $intmpdir
ENDCMD
close CMDFILE;

# This could be used instead of "batch" in IRIX systems
#system "at -f $tmpdir/cmdfile -q b now";
# queue 'c' is defined as c.5j15n90w in /usr/lib/cron/queuedefs
#	i.e. 5 simultaneous jobs with a nice value of 15
# Alternately, in OpenBSD systems, you may use
system "at -f $intmpdir/cmdfile now";

print "<P>You can see your results on</P>\n";
print "<P><CENTER><A HREF=\"$htmldir/$extmpdirname\">$my_site</A></CENTER></P>\n";
print "<P>identifying yourself as</P>\n";
print "<P>Username: $uname</P>\n";
print "<P>Password: $password</P>\n";


print "</BODY>\n";
print "<HR>\n";
print "<CENTER><TABLE BORDER=0 WIDTH=90%><TR>\n";
print "<TD><A HREF=\"$Copyright_URL\">\&copy; $my_site</A></TD>\n";
print "<TD ALIGN=RIGHT><A HREF=\"$contact_url\"><EM>$contact_addr</EM></A></TD>\n";
print "</TR></TABLE></CENTER>\n";
print "</HTML>\n";

exit;

#--------------------------------------------------------------------
# Subroutines
#--------------------------------------------------------------------

sub badaddress {
    print <<END_BADADDRMSG;
    <H2>Sorry, your request could not be processed:
    your address seems to be incorrect. Please, review
    your address or contact your system administrator
    and try again</H2>
END_BADADDRMSG
    exit;
}

sub wrong_level {
    print <<END_WRONG_LEVEL;
    <H2>Sorry, you did not specify a valid confidence level</H2>
    <p>Please, go back to the form and fill it correctly.</P>
END_WRONG_LEVEL
    exit;
}

sub nosequence {
    print <<ENDNOSEQUENCE;
<H2>Sorry, you did not specify a probe DNA sequence. Please, make sure
you are correctly filling the form.</H2>
ENDNOSEQUENCE
    exit;
}
