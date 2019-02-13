#!/usr/local/bin/perl
# t-coffee.cgi

use CGI;

#$debug= 1;

$app= "/opt/evolution/bin/t_coffee"; ##"/usr/local/bin/clustalw";
$apptitle= "T-Coffee";
$applongtitle= "T-Coffee Multiple Sequence Alignments";
$treeappurl= "/cgi-bin/treeprint.cgi";
$dburl= "http://iubio.bio.indiana.edu/.bin/genbankq.html";
$serverpath= "/data/www/EMBnet"; ## $ENV{'DOCUMENT_ROOT'};
$httptmp= "/tmp/";

$ENV{'PATH'}= "/bin:/usr/bin:/opt/evolution/bin";

$maxload = 2;   # system load level to stop running me
$sysloadaverage = "/usr/ucb/uptime";

$fn= "t-coffee$$";
$inuri  	= $httptmp . $fn . ".inseq";
$infile 	= $serverpath . $inuri;
$outuri 	= $httptmp . $fn . ".aln";
$outfile 	= $serverpath . $outuri;
$phyuri 	= $httptmp . $fn . ".phylip";
$phyfile 	= $serverpath . $phyuri;
$treeuri	= $httptmp . $fn . ".dnd";
$treefile	= $serverpath . $treeuri;

$scouri 	= $httptmp . $fn . ".score_html";
$scofile 	= $serverpath . $scouri;
$scouriOK 	= $httptmp . $fn . ".html";
$scofileOK 	= $serverpath . $scouriOK;

$spdfuri 	= $httptmp . $fn . ".score_pdf";
$spdffile 	= $serverpath . $spdfuri;

$resuri 	= $httptmp . $fn . ".txt";
$resfile 	= $serverpath . $resuri;

$defopts= " -output=clustalw,phylip,score_html,score_pdf ";
$fileopts= " -infile=$infile";
$useropts= "";

$query = new CGI;

print $query->header;
print $query->start_html($apptitle);
print "<CENTER><H2>$applongtitle</H2></CENTER>\n";

if (&loadOkay()) {
	if (!$query->param || $query->param('Action') eq 'Form') { &print_prompt($query); }
	else { &do_work($query); }
	&print_tail;
	}
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
    	print "<CENTER><TABLE WIDTH=\"80%\" BGCOLOR=\"LightPink\"><TR><TD><CENTER><STRONG>PLEASE WAIT: MAY TAKE A LONG TIME TO RUN</STRONG></CENTER></TD></TR></TABLE></CENTER>\n";
	print  "<BR><I>running: $apptitle </I><BR>\n";
	print  " <I>options: $defopts $useropts</I><BR>\n";
	print STDERR "running: $app $defopts $fileopts $useropts\n" if $debug;
	print "running: $app $defopts $fileopts $useropts\n" if $debug;
	
	$|= 1; # flush
	#system ("$app $defopts $fileopts $useropts > $resfile 2>&1");
	printf("\n<PRE>\n");
	chdir($serverpath.$httptmp);
	system("$app $infile  $defopts > $resfile 2>&1");
	printf("\n<PRE>\n");

	print "<P><B>Results</B> \n";
	print "<TABLE> \n";
	if (-f $outfile) {
		print "<TR><TD><I>Alignment</I> </TD><TD><A HREF=\"$outuri\">$outuri</A></TD></TR>\n";
		}
	if (-f $phyfile) {
		print "<TR><TD><I>Alignment (Phylip)</I> </TD><TD><A HREF=\"$phyuri\">$phyuri</A></TD></TR>\n";
		}
	if (-f $scofile) {
	    	system("mv $scofile $scofileOK");
		print "<TR><TD><I>Score</I> </TD><TD><A HREF=\"$scouriOK\">$scouriOK</A></TD></TR>\n";
		}
	if (-f $spdffile) {
		print "<TR><TD><I>Score</I> </TD><TD><A HREF=\"$spdfuri\">$spdfuri</A></TD></TR>\n";
		}
	if (-f $treefile) {
	  my $drurl= $treeappurl . '?form=1&data=' . &getHttpServerUrl() . $treeuri;
	 	## my $drurl= $treeappurl . '?form=1&data=' . $treefile;
	 	print "<TR><TD><I>Tree drawing</I></TD><TD><A HREF=\"$drurl\">$drurl</A> </TD></TR> \n";
	 	print "<TR><TD><I>Tree data</I> </TD><TD><A HREF=\"$treeuri\">$treeuri</A> </TD></TR> \n";
	 	}
	print "<TR><TD><I>Program output</I> </TD><TD><A HREF=\"$resuri\">$resuri</A> </TD></TR> \n";
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
   	  	## need to check input for URL or Accession nums in place of bioseq data
   	  	open(INF,">$infile");
				while (<$val>) { tr/\r/\n/; print INF $_;  $havein= 1; }
				close(INF);
   	  	}
   	  elsif ($key =~ /^indata/ && $val && !$havein ) {
   	  	## need to check input for URL or Accession nums in place of bioseq data
   	  	$val =~ tr/\r/\n/;
   	  	open(INF,">$infile");
				print INF $val; 
				close(INF);
   	  	$havein= 1;
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
<CITE>
<PRE>
T-Coffee: A novel method for multiple sequence alignments.
C.Notredame, D. Higgins, J. Heringa,Journal of Molecular Biology,Vol 302, pp205-217,2000

COFFEE: A New Objective Function For Multiple Sequence Alignmnent.
C. Notredame, L. Holme and D.G. Higgins,Bioinformatics,Vol 14 (5) 407-422,1998
</PRE>
</CITE>
<BR>
END
}


sub loadOkay {
  $_ = `$sysloadaverage`;
  if (/load average: ([\d\.]+), ([\d\.]+), ([\d\.]+)/) {
    $load1= $1;  $load5= $2;  $load15= $3;
    if ($load1 > $maxload) {
		  print <<TEOF
This server is too busy now.  Please try later.<br>
[ $load1 (1-min), $load5 (5-min), $load15 (15-min) ] <br>
TEOF
;
 	  return 0;
 	  }
  }
  return 1;
}
