#!/usr/local/bin/perl
# predator.cgi
#
# ------------------------------------------------------------
# predator.pl, by Jos� R. Valverde (jrvalverde@es.embnet.org).
#
# Created: February 6th, 2002
# Last updated: February 6th, 2002
#
# PREDATOR.CGI provides a simplified mechanism to run PREDATOR
# over the web.
# ------------------------------------------------------------
# This package is Copyright 2002 by Jos� R. Valverde
#
# $Id: predator.cgi,v 1.1 2002/02/07 07:44:07 netadmin Exp $
# $Log: predator.cgi,v $
# Revision 1.1  2002/02/07 07:44:07  netadmin
# Initial revision
#
# 

use CGI;

#$debug= 1;

$apptitle=     "PREDATOR";
$applongtitle= "Prediction of Protein Secondary Structure";
$predator_dir=    "/opt/structure/predator";
$predator=       "/opt/structure/bin/predator";
$serverpath=   "/data/www/EMBnet";  	## $ENV{'DOCUMENT_ROOT'};
$httptmp=      "/tmp/";  	    	## inside serverpath

$maintainer =  "netadmin\@es.embnet.org";

$dir            ="predator$$";
$workdir        = $serverpath . $httptmp . $dir ;
$fn             = "predator";
#input file
$inuri  	= $httptmp . $dir . "/" . $fn . ".in";
$infile 	= $serverpath . $inuri;
#output file
$outuri 	= $httptmp . $dir . "/"  . $fn . ".out";
$outfile 	= $serverpath . $outuri;


$infileopts= " $infile ";
$outfileopts= " -f$outfile ";
$useropts = "";

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


sub do_work {
	my($query) = @_;
	my(@values,$val,$key);
	my $havein = 0;
	my $havemail = 0;

    	# 1. Create working dir and cd to it
	die "<H1>ERROR, HORROR: cannot create neccessary temporary files</H1>\n" 
    	unless mkdir $workdir, 0777;
	chdir $workdir or die 
	"<H1>ERROR, HORROR: cannot access needed temporary files</H1>\n";

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
		## Takes precedence over selected file.
   	  	$val =~ tr/\r/\n/;
   	  	open(INF,">$infile");
				print INF $val; 
				close(INF);
   	  	$havein= 1;
   	  	}
   	  elsif ($key =~ /^oformat/ && $val) {
   	  	if ( $val =~ /l/ ) { 
		    $useropts .= " -l ";
		    }
   	  	}
   	  elsif ($key =~ /^predict/ && $val) {
   	  	if ( $val =~ /a/ ) {
		    $useropts .= " -a ";
		    }
   	  	}
	} # foreach $key
		
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
    	} # end DEBUG
}


sub runApp {
	local($useropts)= @_;

	$|= 1; # flush


	print  "<CENTER><BR><STRONG>Running: PREDATOR</STRONG><BR></CENTER>\n";
	print <<ENDDONE1;
	&nbsp;<BR>
	<TABLE WIDTH="80%" BGCOLOR="white" ALIGN="CENTER" BORDER="2" 
        CELLSPACING="1" CELLPADDING="5" >
	<TR><TD>
	Your job has been started in our server. Please be patient.</STRONG></TT>
	</TD></TR></TABLE>
ENDDONE1
	#
	print "<PRE>\n";
	$ENV{'PRE_DIR'} = $predator_dir;
	#print "Running: $predator -h $useropts -f$outfile $infile";
	system ("$predator -h $useropts -f$outfile $infile");
    	print "\n</PRE>";
	
	&print_report;

    	# WE DON'T DELETE THE FILES. A CRON JOB WILL DO IT ONE DAY LATER

}

sub print_report {
    print "<CENTER><H2>Here are your results</H2></CENTER>";
    print "<TABLE BORDER=\"2\">";
    if (-f $infile) {
	print "<TR><TD><I>Your input data was</I> </TD><TD><A HREF=\"$inuri\">INPUT</A></TD></TR>\n";
    }
    if (-f $outfile) {
	print "<TR><TD><I>Your results are</I> </TD><TD><A HREF=\"$outuri\">OUTPUT</A></TD></TR>\n";
    }
    print "</TABLE>";
    print "<BR><HR>";
    print "<CENTER><H3>Your results will be kept for one day and deleted afterwards</H3></CENTER>";
    print "<BR>";
}

sub print_tail {
   print <<END;
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

sub blank_response {
    print "<H2>Some fields appear to be blank or incorrect, and thus your\n";
    print "job has <B>not</B> been processed.";
    print "Please, go back and re-enter your data.</H2>";
    exit;
}


