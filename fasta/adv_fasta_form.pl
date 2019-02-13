#!/usr/local/bin/perl -- -*-perl-*-
#
#----------------------------------------------------------------------
# FASTA form
#
#   Generate a fasta form using FASTLIBS as the template to build
# the choice list of available databases.
#   This form calls on 'runfasta' to do the job.
#
#   (C) José R. Valverde, 29-may-2000
#
#   $Log: fasta_form.pl,v $
#   Revision 1.1  2000/05/29 15:13:12  netadmin
#   Initial revision
#
#---------------------------------------------------------------------

# The real disk location of your $FASTLIBS file
$fastlibs = '/data/gen/fastadb/FASTLIBS';

# The location (from the WWW server's perspective) of the 'runfasta' cgi
$fasta_cgi = '/cgi-bin/runfasta';

# A contact address in your organization to report problems
$contact_addr = 'Bioinformatics Administrator';
#contact_url = 'mailto:genadmin@es.embnet.org';
$contact_url = '/cgi-bin/emailto?Bioinformatics+Administrator';

# A background logo/image for the form
$htmlbglogo = '/images/backgrounds/EMBnetCNB.gif';

print <<END_FIRST_PART;
Content-type: text/html

<HTML>
<HEAD>
	<TITLE>FASTA</TITLE>
</HEAD>

<BODY BGCOLOR="white" BACKGROUND="$htmlbglogo">

<CENTER><H1>FASTA scanner</H1></CENTER>

<H2>This form allows you to scan sequence databases with a probe sequence
of your choice in four easy steps.</H2>

<P><B>Step 1.</B> Enter or paste your sequence in any format below.</P>

<!-- For interactive jobs FORM METHOD="POST" ACTION="/cgi-bin/fasta.sh"-->
<FORM METHOD="POST" ACTION="$fasta_cgi">

<CENTER>
<TABLE>
<TR>
  <TD><CENTER>
  <b>Sequence type:</b>
  <input type="radio" name="seqtype" value="p" checked>Protein 
  <input type="radio" name="seqtype" value="n">Nucleic Acid
  </CENTER></TD>
</TR>
<TR>
  <TD><CENTER>
  <TEXTAREA NAME="sequence" ROWS=10 COLS=60></TEXTAREA>
  </CENTER></TD>
</TR>
</TABLE>
</CENTER>

<P><B>Step 2.</B> Select the database to search</P>

<CENTER>
<TABLE BORDER="2" WIDTH="90%">
<TR>
  <TD COLSPAN="2">
  <CENTER><B>Available databases</B></CENTER>
  </TD>
</TR>
<TR>
  <TD><CENTER><B>Amino Acid sequences</B></CENTER></TD>
  <TD><CENTER><B>Nucleic Acid sequences</B></CENTER></TD>
</TR>
<TR>
<TR>
  <TD VALIGN="top">
END_FIRST_PART

# Now process the aminoacid databases to select

open (LIBS, "<$fastlibs");
while (<LIBS>) {
    if ( /\$0/ ) {
        @parts = split(/\$0/, $_);
        $dbname = $parts[0];
	$val = substr($parts[1],0,1);
	print "    <INPUT TYPE=\"radio\" NAME=\"db\" VALUE=\"$val\">$dbname<BR>\n";
    }
}

# prepare a new column
print "  </TD>\n  <TD VALIGN=\"top\">\n";

# Go for te nucleotide databases

open (LIBS, "<$fastlibs");
while (<LIBS>) {
    if ( /\$1/ ) {
        @parts = split(/\$1/, $_);
        $dbname = $parts[0];
	$val = substr($parts[1],0,1);
	print "    <INPUT TYPE=\"radio\" NAME=\"db\" VALUE=\"$val\">$dbname<BR>\n";
    }
}

# and finalize the form

print <<END_LAST_PART;
  </TD>
</TR>
</TABLE>
</CENTER>
<P>&nbsp;</P>

<P><B>Step 3.</B> Enter your e-mail address below:</P>

<P>E-mail: <INPUT NAME="username" SIZE="60" VALUE="your_name\@your.site.net"></P>

<P><B>Step 4.</B> Press the <I>Submit</I> button to send your request
or <I>Reset</I> to clean this form and start all over again.</P>

<Input TYPE="submit" VALUE="Submit">

<Input TYPE="reset" VALUE="Reset">

</FORM>

<HR>

<P>
<CENTER><TABLE WIDTH="90%" BORDER="0"><TR>
<TD><A HREF="http://www.es.embnet.org/Copyright-CSIC.html">&copy; EMBnet/CNB</A></TD>
<TD ALIGN="RIGHT"><A HREF=\"$contact_url\"><EM>$contact_addr</EM></A></TD>
</TR></TABLE></CENTER>

</BODY>
</HTML>

END_LAST_PART

exit;
