#!/usr/local/bin/perl
# analyze.cgi
#
# ------------------------------------------------------------
# analyze.pl, by Jos� R. Valverde (jrvalverde@es.embnet.org).
#
# Created: September 4, 2001
# Last updated: September 4, 2001
#
# ANALYZE provides a simplified mechanism to run TINKER ANALYZE
# over the web.
# It doesn't support keywords in the configuration file, but does
# the basic job and should suffice for most initial users.
# ------------------------------------------------------------
# This package is Copyright 2001 by Jos� R. Valverde
#
# $Id: analyze.cgi,v 1.3 2002/02/06 12:20:03 netadmin Exp $
# $Log: analyze.cgi,v $
# Revision 1.3  2002/02/06 12:20:03  netadmin
# Fixed mail/wapmail [j]
#
# Revision 1.2  2002/02/06 11:47:32  netadmin
# Fixed wap mailing [j]
#
# Revision 1.1  2001/09/04 13:37:05  netadmin
# Initial revision
#

use CGI;

#$debug= 1;

$babel=        "/opt/structure/bin/babel"; 
$apptitle=     "BABEL+TINKER";
$applongtitle= "Molecular Analysis";
$babel_dir=    "/opt/structure/babel";
$tinker=       "/opt/structure/tinker";
$analyze=      $tinker . "/bin/analyze";
$pdbxyz=       $tinker . "/bin/pdbxyz";
$xyzpdb=       $tinker . "/bin/xyzpdb";
$params=       $tinker . "/params";
$mailprog=     'usr/lib/sendmail';
$wapprog=      '/usr/sbin/Mail';
$serverpath=   "/data/www/EMBnet";  	## $ENV{'DOCUMENT_ROOT'};
$httptmp=      "/tmp/";  	    	## inside serverpath

$maintainer =  "netadmin\@es.embnet.org";

$dir            ="tinker$$";
#$dir            ="tinker";
$workdir        =$serverpath . $httptmp . $dir ;
$fn             = "tinker";
$inuri  	= $httptmp . $dir . "/" . $fn . ".inp";
$infile 	= $serverpath . $inuri;
$brkuri 	= $httptmp . $dir . "/"  . $fn . ".brk";
$brkfile 	= $serverpath . $brkuri;
$xyzuri 	= $httptmp . $dir . "/"  . $fn . ".xyz";
$xyzfile 	= $serverpath . $xyzuri;
$loguri         = $httptmp . $dir . "/"  . $fn . ".log";
$logfile        = $serverpath . $loguri;
$resuri         = $httptmp . $dir . "/"  . $fn . ".xyz_2";
$resfile        = $serverpath . $resuri;
$pdburi         = $httptmp . $dir . "/"  . $fn . ".pdb";
$pdbfile        = $serverpath . $pdburi;
$sequri         = $httptmp . $dir . "/"  . $fn . ".seq";
$seqfile        = $serverpath . $sequri;


$iformatopts= "";
$infileopts= " $infile ";
$outfileopts= " $outfile ";
$oformatopts= " -opdb";     # Now, BABEL rendering to TINKER is defectuous
$useropts = "";
$energy = "";
$eatom = "";
$elarge = "";
$details = "";
$inertia = "";
$moment = "";
$ffpar = "";
$param = "amber";
$job = "";
$havewap = 0;

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
   	  	$val =~ tr/\r/\n/;
   	  	open(INF,">$infile");
				print INF $val; 
				close(INF);
   	  	$havein= 1;
   	  	}
   	  elsif ($key =~ /^informat/ && $val) {
   	  	$iformatopts .= " -i" . $val;
   	  	}
	  elsif ($key =~ /^forcefield/ && $val) {
	    	$ff = $val;
    	    	}
	  elsif ($key =~ /^energy/ && $val) {
	    	$energy = $val;
	    	}
	  elsif ($key =~ /^eatom/ && $val) {
	    	$eatom = $val;
	    	}
	  elsif ($key =~ /^elarge/ && $val) {
	    	$elarge = $val;
	    	}
	  elsif ($key =~ /^details/ && $val) {
	    	$details = $val;
	    	}
	  elsif ($key =~ /^inertia/ && $val) {
	    	$inertia = $val;
	    	}
	  elsif ($key =~ /^moment/ && $val) {
	    	$moment = $val;
	    	}
	  elsif ($key =~ /^ffpar/ && $val) {
	    	$ffpar = $val;
	    	}
	  elsif ($key =~ /^email/ && $val) {
	    	$email = $val;
		$havemail = 1;
	    	}
	  elsif ($key =~ /^wapmail/ && $val) {
	    	$wapmail = $val;
		$havewap = 1 unless ( $wapmail =~ /^$/ );
	    	}
	  elsif ($key =~ /^job/ && $val) {
	    	$job = $val;
		$job =~ s/\"/_/g;
	    	}
	} # foreach $key
		
    	&badaddress unless $havemail;
        if ($havemail == 1) {
	    # Test for correct email
	    &badaddress unless $email =~ /.*@.*\..*/;
	    $remotehost = $email;
	    $remotehost =~ s/.*@//g;
    	    &badaddress unless gethostbyname($remotehost);
	}
	else {
	    &blank_response;
	}
	if ($havewap) {
	    &badwap unless $wapmail =~ /.*@.*\..*/;
	    $remotehost = $wapmail;
	    $remotehost =~ s/.*@//g;
    	    &badwap unless gethostbyname($remotehost);
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
    	} # end DEBUG
}


sub runApp {
	local($useropts)= @_;

	$|= 1; # flush

	# fork a process to do the work and mail the results.
	if ( $tinker_pid = fork) {
	    # Create tinker.key file  
	    open(KEYFILE, ">tinker.key");
    	    print KEYFILE "parameters /opt/structure/tinker/params/$ff.prm\n";
	    close(KEYFILE);
	    
	    # Start job file
	    open (JOB, ">tinker.job");
	    print JOB "# ANALYZE for $email\n";
	    print JOB "cd $workdir\n";

	    # Convert to TINKER format if needed
	    #
	    #	For some reason BABEL gives problems converting to TINKER
	    # directly: convert first to PDB and then to TINKER.
	    #
	    if ( $iformatopts =~ / -itinker$/ ) {
	        system("cp $infile $xyzfile");
	    } else {
		if ( $iformatopts =~ / -ipdb$/ ) {
	    	    # no need to use babel
	     	    system("cp $infile $brkfile");
		} else {
	    	    $ENV{'BABEL_DIR'} = $babel_dir;
	            system ("$babel $iformatopts $infileopts $oformatopts $brkfile");
   		}

		# Convert PDB to TINKER XYZ format
		open (IN, ">pdbxyz.in");
		print IN "\n";	   # if >1 structure, accept default
		close IN;
		print JOB "$pdbxyz $brkfile < pdbxyz.in > $logfile 2>&1\n";
 	    }
	    
	    # Analyze structure
	    open (IN, ">analyze.in");
	    print IN "$xyzfile\n";
	    print IN "$energy $eatom $elarge $details $inertia $moment $ffpar\n\n\n";
	    close IN;
	    print JOB "nice $analyze < analyze.in >> $logfile 2>&1\n";
	    
    	    # mail results
	    open (IN, ">mail.header");
	    print IN "Reply-To: $maintainer\n";
	    print IN "Subject: ### Your Tinker analyze job $job\n";
	    print IN "\n";
	    close IN;
	    
    	    print JOB "cat mail.header $logfile | ";
	    print JOB "sed -e 's/^Subject: ###/Subject: \[LOG\]/g' | $mailprog  $email\n";

    	    # WAP notify (if requested)
	    if ($havewap) {
	    	open (IN, ">wap.msg");
	    	#print IN "Reply-To: $maintainer\n";
	    	#print IN "Subject: Analyze $job\n\n";
	    	print IN "\n\nYour job $job has finished.\n";
	    	close IN;
	    
	    	print JOB "$wapprog -s analyze $wapmail < wap.msg\n";
    	    }
	    print JOB "cd ..\n";
	    print JOB "rm -rf $workdir\n";
	    close JOB;
	    exec("at -f ./tinker.job now");
	    
	    $|=1;
	    exit;
	}
	else {
	    print  "<CENTER><BR><STRONG>Running: BABEL + PDBXYZ + ANALYZE</STRONG><BR></CENTER>\n";
	    print <<ENDDONE1;
	    &nbsp;<BR>
	    <TABLE WIDTH="80%" BGCOLOR="white" ALIGN="CENTER" BORDER="2" 
            CELLSPACING="1" CELLPADDING="5" >
	    <TR><TD>
	    Your job has been started in our server. The results will be
            mailed to you at <TT><STRONG>&lt;$email&gt;</STRONG></TT>
	    </TD>
ENDDONE1
	    if ($havewap) {
	      print <<ENDDONE2;
	      <TD>
	      You specified a WAP mobile address:, a short notice will be
	      sent to <TT><STRONG>&lt;$wapmail&gt;</STRONG></TT> when the 
	      job is finished.
	      </TD>
ENDDONE2
    	    }
	    print "</TR></TABLE>\n&nbsp;<BR>\n";
	    print "<CENTER><H2>Thanks for using our service</H2></CENTER>\n";
	}
		
	## unlink $infile;
}

sub mailfile {
    local($file) = @_;
    open (MAIL, "|$mailprog $email") || die "ANALYZE: Can\'t open $mailprog!\n";
    print MAIL "Reply-to: $email\n";
    print MAIL "Subject: TINKER Analyze results\n\n";
    
    open (THEFILE, "<$file");
    while (<THEFILE>) {
    	$line = $_;
	print MAIL $line;
    }
    close THEFILE;
    close MAIL ;
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


sub badaddress {
    print "<H2>Sorry, your job cold not be\n";
    print "processed: your address seems to be incorrect. Please, review\n";
    print "your address or contact your system administrator\n";
    print "and try again</H2>\n";
    exit;
}

sub badwap {
    print "<H2>Sorry, your job cold not be\n";
    print "processed: your WAP mail address seems to be incorrect. Please, review\n";
    print "your address or contact your telco provider\n";
    print "and try again</H2>\n";
    exit;
}
