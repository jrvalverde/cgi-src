#!/usr/local/bin/perl

require "/data/www/EMBnet/cgi-bin/cgi-lib.pl";

if (&ReadParse(*input)) {
  $sessionId = $input{"SessionId"};
}else{
  print &PrintHeader;
  print "CGI error: unrecognized format\n" ;
  exit 1 ;
}

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

if ( $ENV{'HTTP_USER_AGENT'} =~ /MSIE/ ) {
  $certfile = "$sessionId.html";
}elsif ( $ENV{'HTTP_USER_AGENT'} =~ /Mozilla/ ) {
  $certfile = "$sessionId.ucert";
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

if ( -r "/data/www/EMBnet/Security/CA/signed/$certfile" ) {
  print <<EOF;
Location: https://www.es.embnet.org/Security/CA/signed/$certfile

EOF
}else{
  print <<EOF;
Content-Type: text/html

<HTML><HEAD>
<TITLE>Your certificate request $sessionId</TITLE>
</HEAD>

<BODY>

<P>
<H1>Your certificate request</H1>

Your request number is: <STRONG>$sessionId</STRONG>.

<P>
Once the Certification Authority has told you everything is ok, you
may retrieve your signed certificate by returning to <EM>this</EM>
page. So write down it's <STRONG>URL</STRONG>:

<PRE>
     <A HREF="https://www.es.embnet.org/cgi-bin/CA/loadCert.pl?SessionId=$sessionId">https://www.es.embnet.org/cgi-bin/CA/loadCert.pl?SessionId=$sessionId</A>
</PRE>
or <EM>bookmark</EM> it to load it again later.

</BODY>
</HTML>
EOF

EOF
}

__END__

