#!/usr/local/bin/perl
# babel.cgi
#
# ------------------------------------------------------------
# babel.pl, by José R. Valverde (jrvalverde@es.embnet.org).
#
# Created: August 30, 2001
# Last updated: September 6, 2001
#
# BABEL provides a WWW interface to the BABEL 3D structure
# file format conversion program. It does not provide the
# full functionality, but may with time ;-)
# ------------------------------------------------------------
# This package is Copyright 2001 by José R. Valverde
#
# $Id$
# $Log$

use CGI;

#$debug= 1;

$app= "/opt/structure/bin/babel"; 
$apptitle= "BABEL";
$applongtitle= "Babel version 1.6 Copyright (C) 1992-1996";
$babel_dir='/opt/structure/babel';
$pdbviewappurl= "/cgi-bin/treeprint.cgi";
$dburl= "http://srs.es.embnet.org";
$serverpath= "/data/www/EMBnet"; ## $ENV{'DOCUMENT_ROOT'};
$httptmp= "/tmp/";

$fn= "babel$$";
$inuri  	= $httptmp . $fn . ".instr";
$infile 	= $serverpath . $inuri;
$outuri 	= $httptmp . $fn . ".inp";
$outfile 	= $serverpath . $outuri;
$pdbviewuri	= $httptmp . $fn . ".dnd";
$pdbviewfile	= $serverpath . $pdbviewuri;
$resuri 	= $httptmp . $fn . ".txt";
$resfile 	= $serverpath . $resuri;

$iformatopts= "";
$infileopts= " $infile ";
$outfileopts= " $outfile ";
$oformatopts= "";

$query = new CGI;

print $query->header;
print $query->start_html($apptitle);
print "<CENTER><H2>$applongtitle</H2></CENTER>\n";

&do_work($query);
&print_tail;

print $query->end_html;

exit;
##/////////////////////////////////////////////

sub getHttpServerUrl  {
	my $host= $ENV{"SERVER_NAME"};
	my $port= $ENV{"SERVER_PORT"};
	if ($port==80 || $port==0) { return "http://" . $host; }
	else { return "http://" . $host . ':' . $port; }
}

sub runApp {
	local($useropts)= @_;
	print  "<CENTER><BR><I>Running: $apptitle </I><BR></CENTER>\n";
	print STDERR "running: $app $iformatopts $infileopts $outfileopts $oformatopts\n" if $debug;
	
	$|= 1; # flush
	## system ("$app $iformatopts $infileopts $oformatopts $outfileopts > $resfile");
	$ENV{'BABEL_DIR'} = $babel_dir;
	##system ("$app $iformatopts $infileopts $oformatopts -o $outfile");
	
	print "<TABLE WIDTH=\"90%\" BGCOLOR=\"white\"> \n";
	print "<TR><TH>Converted output file</TH></TR> \n";
	##print "<TR><TD><STRONG>To download results, shift-click <A HREF=\"$outuri\">here</A></STRONG></TD></TR>\n";
	print "<TR><TD><TT><PRE> \n";
	##system("printenv") if $debug;
	##print "$app $iformatopts $infileopts $oformatopts\n" if $debug;
	system ("$app $iformatopts $infileopts $oformatopts ");
	system ("cat $outfile");
	print "</PRE></TT></TD></TR>\n";
	print "</TABLE> \n";
	print "<P>  \n";
		
	## unlink $infile;
}


sub do_work {
	my($query) = @_;
	my(@values,$val,$key);
	my $havein= 0;

 	foreach $key ($query->param) {
  	    
  	  $val = $query->param($key); ## @values =  
   	  $useropts .= " " . $val if ($val =~ /^-/);
   	  
   	  if ($key =~ /^infile/ && $val) {
	        ## The user selected a local input file
   	  	open(INF,">$infile");
				while (<$val>) { tr/\r/\n/; print INF $_;  $havein= 1; }
				close(INF);
   	  	}
   	  elsif ($key =~ /^indata/ && $val && !$havein ) {
   	  	## The user entered the data directly
   	  	$val =~ tr/\r/\n/;
   	  	open(INF,">$infile");
				print INF $val; 
				close(INF);
   	  	$havein= 1;
   	  	}
   	  elsif ($key =~ /^informat/) {
   	  	$iformatopts .= " -i" . $val;
   	  	}
   	  elsif ($key =~ /^outformat/) {
   	  	$oformatopts .= " -o" . $val;
   	  	}
   	  	
  		}

	if ($havein) { &runApp($useropts); }
	else { 
 		print "<H3>No input data found</H3>";
		}

	print "<P>\n";
	
	return if (!$debug);

 	print "<H2>Here are the current settings in this form</H2>";
 	foreach $key ($query->param) {
		print "<STRONG>$key</STRONG> -> ";
		@values = $query->param($key);
		print join(", ",@values),"<BR>\n";
		}

	$filename = $query->param('infile');
	if ($filename) {
		print "uploaded file '$filename'<br>\n";
		print "info<br>\n";

	  my %info= %{$query->uploadInfo($filename)};
    foreach $key (keys %info) {
      print "<B>$key</B> -> ";
      my $val = $info{$key};
      print "$val<BR>\n";
			}
		## $type = $query->uploadInfo($filename)->{'Content-Type'};
		## unless ($type eq 'text/html') { die "HTML FILES ONLY!"; }
			
		# Read a text file and print it out
		# print "Contents: <pre>\n";
		# while (<$filename>) { print; }
		# print "</pre>\n";
	
		## open (OUTFILE,">>/usr/local/web/users/feedback");
		## while ($bytesread=read($filename,$buffer,1024)) {  print OUTFILE $buffer; }
		}
}



sub print_tail {
   print <<END;
<HR>
<CENTER>
              <BOLD>Babel version 1.6 Copyright (C) 1992-1996</BOLD><BR>
                                  by<BR>
                      <EM>Pat Walters and Matt Stahl</EM><BR>

                   <TT>babel\@mercury.aichem.arizona.edu</TT><BR>
</CENTER>
<BR>
<HR>

<TABLE WIDTH="100%">
  <TR ><TD ALIGN=CENTER>
    <P>If you have any trouble, please contact our
    <A HREF="/cgi-bin/emailto?Bioinformatics+Administrator">Bioinformatics
    Administrator</A></P>
    <P><A HREF="/Copyright-CSIC.html">&copy; EMBnet/CNB</A></P>
  </TD></TR>
</TABLE>
END
}

