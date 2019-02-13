#!/usr/bin/perl -- -*-perl-*-

# ------------------------------------------------------------
# ProtPars.pl, by José R. Valverde (jrvalverde@es.embnet.org).
#
# Created: January 13, 2000
# Last updated: January 13, 2000
#
# ProtPars provides a mechanism by which users of a World-
# Wide Web browser may submit jobs to PHYLIP protpars.
# It should be compatible with any CGI-compatible HTTP server.
# 
# Please read the README file that came with this distribution
# for further details.
# ------------------------------------------------------------

# ------------------------------------------------------------
# This package is Copyright 2000 by José R. Valverde

# ProtPars is free software; you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by the
# Free Software Foundation; either version 2, or (at your option) any
# later version.

# ProtPars is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with ProtPars; see the file COPYING.  If not, write to the Free
# Software Foundation, 675 Mass Ave, Cambridge, MA 02139, USA.
# ------------------------------------------------------------

# Define fairly-constants

# This should match the ProtPars program on your system.
$protparsprog = '/opt/evolution/phylip/bin/protpars';

# This should match the READSEQ program on your system.
$readseqprog = '/opt/molbio/bin/readseq';

# This should be set to the address (in URL form) and name of the
# local bioinformatics administrator.
#$mailaddr = 'mailto:genadmin@es.embnet.org';
$mailaddr = '/cgi-bin/emailto?Bioinformatics+Administrator';
$contactaddr = 'Bioinformatics Administrator';

# This should be set to the background image to use (your logo perhaps?)
$bkgimage='/images/backgrounds/EMBnetCNB.gif';

# This is the directory where temporary files are to be created
$systmp='/data/www/EMBnet/tmp';

#--------------------------------------------------------------------
open(STDERR, ">&STDOUT");
select(STDERR); $| = 1;     # make unbuffered
select(STDOUT); $| = 1;     # make unbuffered

# Print out a content-type for HTTP/1.0 compatibility
print "Content-type: text/html\n\n";

# Print a title and initial heading
print "<Head><Title>Thank you!</Title></Head>\n";
print "<Body BGCOLOR=\"white\" BACKGROUND=\"$bkgimage\">\n";
print "<H1>Thank you for using PROTPARS</H1>\n<BR>\n";

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
    # $value =~ s/~!/ ~!/g; 

    # Uncomment for debugging purposes
    # print "Setting $name to $value<P>";

    $FORM{$name} = $value;
}

# If the important fields are blank, then give a "blank form" response
&blank_response unless $FORM{'alignment'};

# First create the input file from the alignment
umask 0077;
#   1. mkdir /tmp/tmpdir$$
srand;
$tmpdir = sprintf "%s/%s.%d.%d", $systmp, "xprotpars", $$, int(rand(30000));
die "<center><H1>ERROR, HORROR: cannot create neccessary temporary files</H1>\
$tmpdir</center>\n" unless mkdir $tmpdir, 0777;

#   2. cd to /tmp/tmpdir$$

chdir $tmpdir or die "<H1>ERROR, HORROR: cannot access needed temporary files</H1>\n";

#   3. create infile
print "<H2>";	    # In case something goes wrong
open (INFILE, "|$readseqprog -p -fphylip -a -o=infile");
print INFILE $FORM{'alignment'};
close (INFILE);
print "</H2>\n";
# Now $? contains readseq exit status
# We should check the number of sequences in infile before going on.
$noseqs = &checkalignment;
if ($noseqs == 0) {
    print "<H2>Error: there are no sequences in your alignment</H2>\n";
    print "<H2>Please, review your alignment data and try again</H2>\n";
    exit; 
}
else {
    print "Your alignment contains $noseqs sequences\n";
}

#   4. now run the program
#print  "<P>Running PROTPARS...";
open (PROTPARS, "|$protparsprog >$tmpdir/ppoutput") || die "<H1>Can't run PROTPARS</H1>\n";
#open (PROTPARS, "|$protparsprog") || die "<H1>Can't run PROTPARS</H1>\n";

#Note: this is not standard behaviour XXX - CNB - XXX
#print PROTPARS "infile\n";
#Note: this is not standard behaviour XXX - CNB - XXX
#print PROTPARS "outfile\n";

print PROTPARS "U\n" if $FORM{'sbt'} =~ /n/i;	# Search for best tree?
#optionJ -> always use input order

if ($FORM{'or'}) {
    $outgroup = int($FORM{'or'});
    if ($outgroup != 0) {
    	if ($outgroup > $noseqs) {
	    print "<P>WARNING: Outgroup number is greater than the number of species</P>\n";
	    printf "<P>(%d > %d) NOT using outgroup</P>\n", $outgroup, $noseqs;
    	}
	else {
	printf PROTPARS "O\n%s\n", $outgroup;
    	    printf "<P>Using sequence number %d as outgroup</P>", $outgroup;
	}
    }
}

if ($FORM{'tp'} && $FORM{'tp'} > 1) {
    printf PROTPARS "T\n%s\n", $FORM{'tp'};
    printf "<P>Using Threshold Parsimony, counting steps up to %s per site</P>\n", $FORM{'tp'};
}
else {
    print "<P>Using Ordinary Parsimony</P>\n";
}
#optionM -> we don't do multiple data sets
#optionI -> we have converted to interleaved format
#option0 -> we are always ANSI
print PROTPARS "1\n";	    	    	    	# print out data at start of run
print PROTPARS "2\n";	    	    	    	# do not print indications of progress
print PROTPARS "3\n" if  $FORM{'pot'} =~ /n/i;	# Print out tree?
print PROTPARS "4\n" if  $FORM{'poss'} =~ /y/i; # Print out steps at each site?
print PROTPARS "5\n" if  $FORM{'psant'} =~ /y/i;# Print sequences at all nodes?
#option6 -> we shall always produce tree

print PROTPARS "Y\n";

#Note: this is not standard behaviour XXX - CNB - XXX
#print PROTPARS "treefile\n";

print "<hr><br />\
<center><h2>Please, wait while we analyze your data</h3></center>\n";
print "<p>Parsimony analysis of phylogenies may be a computationally \
intensive task. It may take several minutes to complete your analysis \
depending on the size of your data set and the parameters selected.</p>\
<p>Please, do also take notice that if your data set is too large then\
you may not be able to see the results at all as your web browser may\
timeout while waiting for the results (i.e. your web browser may get bored\ 
waiting for your analysis to finish and give up).</p>\n<hr>\n";

close (PROTPARS);   # wait for PROTPARS to finish
# Now $? contains protpars exit status
#print " done</P>\n";


# open OUTFILE and TREEFILE and print them to stdout
print "<H2>Here are your results:</H2>\n\n";

print "<H3><A NAME=\"#outfile\">Parsimony report</A></H3>\n";
print "<TABLE BORDER=\"3\"><TR><TD BGCOLOR=\"LightYellow\"><PRE><TT>\n";
&printoutput;
print "</TT></PRE></TD></TR></TABLE>";
print "<BR>\n";
print "<H3><A NAME=\"#treefile\">Trees found</A></H3>\n";
print "<TABLE BORDER=\"3\"><TR><TD BGCOLOR=\"LightGreen\"><PRE><TT>\n";
&printtrees;
print "</TT></PRE></TD></TR></TABLE>";

# Remove all files
unlink "$tmpdir/infile", "$tmpdir/ppoutput", "$tmpdir/outfile", "$tmpdir/outtree";
chdir "..";
rmdir $tmpdir ; #or die "<H2>Cannot remove $tmpdir!</H2>\n";

# print out closing remarks
print "<H3>To see the above tree copy it now, go to \n";
print "<A HREF=\"http://www.es.embnet.org/Doc/phylodendron/treeprint-form.html\">Phylodendron\n";
print "TreePrint</A> and paste it there.</H3>\n";

print "</UL></BODY>\n";
print "<HR>\n";
print "<CENTER><TABLE BORDER=0 WIDTH=90%><TR>\n";
print "<TD><A HREF=\"http://www.es.embnet.org/Copyright-CSIC.es.html\">\&copy; EMBnet/CNB</A></TD>\n";
print "<TD ALIGN=RIGHT><A HREF=\"$mailaddr\"><EM>$contactaddr</EM></A></TD>\n";
print "</TR></TABLE></CENTER>\n";
print "</HTML>\n";

exit;


# ------------------------------------------------------------
# subroutine blank_response
sub blank_response
{
    print "<H2>Sorry, you did not enter any alignment and therefore your job\n";
    print "<STRONG>NOT</STRONG> been sent.  Please go back and fill in the form again or\n";
    print "return to the <A HREF=\"/Services/training/smc99/\">home page</A>, if you want.</H2>\n";
    print "\n<BR><BR>\n";
    print "<P>The data you sent was:</P><PRE>\n";
    print "<HR>\n";
    print "<B>Search for best tree:</B>         [$FORM{'sbt'}]\n";
    print "<B>Print out tree:</B>               [$FORM{'pot'}]\n";
    print "<B>Print steps at each site:</B>     [$FORM{'poss'}]\n";
    print "<B>Print sequences at all nodes:</B> [$FORM{'psant'}]\n";
    print "<B>Alignment:</B>\n";
    print "<HR>\n";
    print "$FORM{'alignment'}";
    print "\n<HR>\n";
    print "</PRE></BODY>\n</HTML>\n";
    exit;
}

# ------------------------------------------------------------
# subroutine checkalignment
sub checkalignment
{
    open(ALIGNMENT, "<$tmpdir/infile") or 
    	die "<H2>Error: this format is unknown to me.</H2>";
    if (-z ALIGNMENT) {
#    	print "<H2>Error: no sequences found in your alignment</H2>\n";
	return 0;
    }
    # First line must contain the number of species
    $_ = <ALIGNMENT>;
    @fields = split " ";
#    print "<P>Your input alignment contains $fields[0] sequences.<P>\n";
    if ($fields[0] == 0) {
#    	print "<H2>Error: there are no sequences in your alignment</H2>\n";
    }
    close(ALIGNMENT);
    return $fields[0];
}

# ------------------------------------------------------------
# subroutine printoutput
sub printoutput
{
    open(OUTFILE, "<$tmpdir/outfile") or die "<H2>No output was generated!!!</H2>\n";
    while (<OUTFILE>) {
    	print "$_";
    }
    close(OUTFILE);
    
}

# ------------------------------------------------------------
# subroutine printtrees
sub printtrees
{
    open(TREEFILE, "<$tmpdir/outtree") or die "<H2>No trees were generated!!!</H2>\n";
    while (<TREEFILE>) {
    	print "$_";
    }
    close(TREEFILE);
}

