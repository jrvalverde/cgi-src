#!/usr/local/bin/perl
# 
# (C) 2001 José R. Valverde jrvalverde@es.embnet.org
#

use CGI;

#$debug= 1;

$app= "/opt/structure/bin/babel"; 
$apptitle= "BABEL+DISMOL";
$applongtitle= "3D visualization of molecules";
$babel_dir='/opt/structure/babel';
$serverpath= "/data/www/EMBnet"; ## $ENV{'DOCUMENT_ROOT'};
$httptmp= "/tmp/";

$fn= "babel$$";
$inuri  	= $httptmp . $fn . ".inp";
$infile 	= $serverpath . $inuri;
$outuri 	= $httptmp . $fn . ".pdb";
$outfile 	= $serverpath . $outuri;

$iformatopts= "";
$infileopts= " $infile ";
$outfileopts= " $outfile ";
$oformatopts= " -opdb";

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
	print  "<CENTER><BR><I>Running: BABEL + DISMOL </I><BR></CENTER>\n";
	print STDERR "running: $app $iformatopts $infileopts $outfileopts $oformatopts\n" if $debug;
	
	$|= 1; # flush
	$ENV{'BABEL_DIR'} = $babel_dir;
	system ("$app $iformatopts $infileopts $oformatopts -o $outfile");
	print "<TABLE WIDTH=\"90%\" BGCOLOR=\"white\"> \n";
	print "<TR><TH>This is your molecule</TH></TR> \n";
	print "<TR><TD><STRONG><A HREF=\"$outuri\">PDB file shown</A></STRONG></TD></TR>\n";
	print "<TR><TD BGCOLOR=\"black\"><CENTER>\n";
	print "<applet codebase=\"/Services/MolBio/DisMol/\" code=\"DisMol.class\" width=\"650\" height=\"550\">";
    	print "<param name=url value=\"http://www.es.embnet.org$outuri\"></applet>";
	print "</CENTER></TD></TR>\n";
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

