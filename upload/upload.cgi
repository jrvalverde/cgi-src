#!/usr/local/bin/perl
# upload.cgi
#
# ------------------------------------------------------------
# upload.pl, by José R. Valverde (jrvalverde@es.embnet.org).
#
# Created: August 30, 2001
# Last updated: September 6, 2001
#
# UPLOAD provides a WWW interface to upload files for a 
# specific user in this server.
# ------------------------------------------------------------
# This package is Copyright 2001 by José R. Valverde
#
# $Id$
# $Log$


use CGI;

$debug = 0;

$app= "upload"; 
$apptitle= "UPLOAD";
$applongtitle= "Upload a file for JR";

$myuid = 60001;     # WWW server runs with this UID
$mask = 0022;	    # use this if chown UID is allowed
#$mask = 0007;	    # and this if only chown GID is permitted

# XXX JR XXX Next variables may be determined on the fly 
#   	     from the password file
$username = 'jr';
$mailhost = 'es.embnet.org';
($name,$passwd,$uid,$gid,$quota,
$comment,$gcos,$homedir,$shell) = getpwnam($username)
                          or die "$username not in passwd file";
# Where to store the file in the server filesystem
$serverpath = "$homedir/public_html/upload/";
# How to find the file as a URL
$httptmp = "http://www.es.embnet.org/~$name/upload/";
# Destination user UID,GID
$destuid = $uid;	    # if chown UID is not allowed use $myuid instead
$destgid = $gid;
$destmail = "$name\@$mailhost";
$mgruri = 'http://www.es.embnet.org/~jr/cgi-bin/upload_mgr.cgi';


######## START PROCESSING ########

$query = new CGI;

print $query->header;
print $query->start_html($apptitle);
print "<CENTER><H2>$applongtitle</H2></CENTER>\n";

&do_work($query);
&print_tail;

print $query->end_html;

exit;

        #####################################################
        #   	S U B R O U T I N E         C O D E 	    #
        #####################################################


sub getHttpServerUrl  {
	my $host= $ENV{"SERVER_NAME"};
	my $port= $ENV{"SERVER_PORT"};
	if ($port==80 || $port==0) { return "http://" . $host; }
	else { return "http://" . $host . ':' . $port; }
}


sub do_work {
	my($query) = @_;
	my(@values,$val,$key);
	my $havein= 0;

 	foreach $key ($query->param) {
  	    
  	    $val = $query->param($key); ## @values =  

   	    if ($key =~ /^infile/ && $val) {
		## The user selected a local input file
		umask $mask;
	    	$filename = $query->param('infile');
		#$dir = "$$";
		## XXX JR XXX Note: permissions are set to allow sender to 
		##   access the file. Otherwise, use 0750.
		#die "<H1>ERROR, HORROR: cannot create neccessary temporary files</H1>\n" 
    	    	#    unless mkdir $serverpath . $dir, 0775;
    	    	#$fn = $dir . "/" . $filename;
    	    	$dir = "";
		$fn = $dir . "" . $filename;
    	    	$inuri = $httptmp . $fn;
    	    	$infile = $serverpath . $fn;
   		open(INF,">$infile");
		while (<$val>) { 
		    print INF $_;  
		    $havein= 1; 
		}
		close(INF);
		# change group ownership to that of target user
		#chown $destuid, $destgid, $serverpath . $dir;
		chown $destuid, $destgid, $infile;
   	    }
  	}

	if ($havein) { 
	    &notify();
	    &say_ok();
	}
	else { 
    	    &say_retry();
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
    # print getpwnam() return data
    print "<BR><B>name:</B> $name<BR>\n";
    print "<B>passwd:</B> $passwd<BR>\n";
    print "<B>uid:</B> $uid<BR>\n";
    print "<B>gid:</B> $gid<BR>\n";
    print "<B>quota:</B> $quota<BR>\n";
    print "<B>comment:</B> $comment<BR>\n";
    print "<B>gcos:</B> $gcos<BR>\n";
    print "<B>homedir:</B> $homedir<BR>\n";
    print "<B>shell:</B> $shell<BR>\n";

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

sub notify {
    $mailprog = '/usr/lib/sendmail';
    open (MAIL, "|$mailprog $destmail") || die "Can't open $mailprog!\n";

    print MAIL "Reply-to: netadmin\@es.embnet.org\n";
    print MAIL "Subject: Upload of file $filename\n\n";

    #print MAIL "$FORM{'username'} ($FORM{'realname'}) sent the following comment\n";
    print MAIL  "------------------------------------------------------------\n";
    print MAIL "A new file has been stored in your web account.\n";
    print MAIL "The original file name was $filename\n";
    print MAIL "To retrieve it use the following URL:\n";
    print MAIL "\n\t$inuri\n";
    print MAIL "\nor access your Upload Manager at URL:\n";
    print MAIL "\n\t$mgruri\n";
    print MAIL "\n------------------------------------------------------------\n";
    print MAIL "Server protocol: $ENV{'SERVER_PROTOCOL'}\n";
    print MAIL "Remote host: $ENV{'REMOTE_HOST'}\n";
    print MAIL "Remote IP address: $ENV{'REMOTE_ADDR'}\n";
    print MAIL "Remote user: $ENV{'REMOTE_USER'}\n";
    close (MAIL);

    return;
}


sub say_ok {
    print "\n<CENTER>\n<P>Upload completed successfully</P>\n";
    print "\n<P>This is the file you uploaded:\n";
    print "\n<A HREF=\"$inuri\">$filename</A><\P>\n";
    print "\n<P>JR has been notified by e-mail.</P>\n";
}

sub say_retry {
    print "<CENTER><H3>No input data found</H3></CENTER>\n";
    print "<CENTER><H3>Please try again</H3></CENTER>\n";
}

sub print_tail {
   print <<END;
<HR>
<TABLE WIDTH="100%">
  <TR ><TD ALIGN=CENTER>
    <address>
    <!-- app info here -->
    <P>If you have any trouble, please contact 
    <A HREF="/cgi-bin/emailto?José+R.+Valverde">Jos&eacute; R. Valverde</A></P>
    <P><A HREF="/~jr/Copyright-JR_AH.html">&copy; Jos&eacute; R. Valverde</A>
  </TD></TR>
</TABLE>

END
}

