#!/usr/local/bin/perl

require "/data/www/EMBnet/cgi-bin/cgi-lib.pl";

if ( $ENV{'HTTP_USER_AGENT'} =~ /MSIE 4/ ) {
  $browser = "msie" ;
  $msversion = 4;
}elsif ( $ENV{'HTTP_USER_AGENT'} =~ /MSIE 3/ ) {
  $browser = "msie" ;
  $msversion = 3;
}elsif ( $ENV{'HTTP_USER_AGENT'} =~ /Mozilla/ ) {
  $browser = "netscape" ;
}else{
  print <<EOF;
Content-type: text/html

<HTML><HEAD>
<TITLE>Unknown browser</TITLE>
</HEAD>
<BODY>
<H1>Unknown browser</H1>
Sorry, I don't know how to treat a certificate request comming from
your type of browser ("$ENV{'HTTP_USER_AGENT'}").

</BODY></HTML>
EOF
}

if (&ReadParse(*input)) {
  $sessionId = $input{"SessionId"};
#
#       cf RFC1034
#
  if ( $sessionId !~
     /^[a-zA-Z]+([a-zA-Z0-9-]+[a-zA-Z0-9]{1}|[a-zA-Z0-9]*)-[0-9]+-[0-9]+$/ ) {

    print <<EOF;
Content-type: text/html

<HTML>
<HEAD>
<TITLE>Error: bad request identificator</TITLE>
</HEAD>
<BODY>
Error: $sessionId bad request identificator.
</BODY>
</HTML>
EOF

    exit 1;
  }

  if ( $browser eq "netscape" ) {
    $key = $input{"mykey"} ;
    $key =~ s/\n//g ;
    $emailAddress = $input{"EmailAddress"} ;
    $commonName = $input{"CommonName"} ;
    $unit = $input{"OrganizationalUnit"};
    # JR
    $organization = $input{"Organization"};
    $locality = $input{"Locality"};
    $state = $input{"StateOrProvince"};
    $country = $input{"Country"};
    # JR
    mkdir("/data/www/EMBnet/Security/CA/certs/$sessionId",0777) ;
    chmod 0777, "/data/www/EMBnet/Security/CA/certs/$sessionId" ;
    open(CERT,">/data/www/EMBnet/Security/CA/certs/$sessionId/req.raw") ;
    print CERT "C=$country\n" ;
    print CERT "ST=$state\n" ;
    print CERT "L=$locality\n" ;
    print CERT "O=$organization\n" ;
    print CERT "OU=$unit\n" ;
    print CERT "CN=$commonName\n" ;
    print CERT "Email=$emailAddress\n" ;
    print CERT "SPKAC=$key\n" ;
    close(CERT) ;
  }else{
#
#       MSIE
#
    $req = $input{'ms_req'} ;
    mkdir("/data/www/EMBnet/Security/CA/certs/$sessionId",0777) ;
    chmod 0777, "/data/www/EMBnet/Security/CA/certs/$sessionId" ;
    open(CERT,">/data/www/EMBnet/Security/CA/certs/$sessionId/req.raw.$msversion") ;
    print CERT $req ;
    close(CERT) ;
    $req =~ s/\r//g ;
    $req =~ s/([^\n]{72,72})\n([^\n]{1,71})\n([^\n]{1,71})$/$1\n$2$3/ ;
    open(CERT,">/data/www/EMBnet/Security/CA/certs/$sessionId/req") ;
    print CERT "$req\n" ;
    close(CERT) ;
  }

} else {
  print &PrintHeader;
  print "CGI error: unrecognized format\n" ;
  exit 1 ;
}

print <<EOF;
Location: https://www.es.embnet.org/cgi-bin/CA/loadCert.pl?SessionId=$sessionId

EOF

__END__

