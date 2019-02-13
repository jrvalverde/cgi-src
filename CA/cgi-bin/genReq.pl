#!/usr/local/bin/perl

$client = $ENV{'REMOTE_HOST'};
#
#       cf RFC1034
#
if ( $client !~ /^[a-zA-Z]+([a-zA-Z0-9-]+[a-zA-Z0-9]{1}|[a-zA-Z0-9]*)\./ ) {
  print <<EOF;
Content-type: text/html

<HTML>
<HEAD>
<TITLE>Error: bad hostname</TITLE>
</HEAD>
<BODY>
Error: $client bad hostname.
</BODY>
</HTML>
EOF

  exit 1;
}

  
$client =~ s/^([a-zA-Z0-9-]{1,}).*/\1/;
($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
$SessionId = sprintf("%02d%02d%02d%02d%02d",$mday,++$mon,$year,$hour,$min);
$SessionId = $client . "-" . $SessionId . "-$$";

$html =  <<EOF ;
Content-type: text/html

<HTML>
<HEAD>
<TITLE>Certificate request</TITLE>
</HEAD>
<BODY>

<H1>Certificate request</H1>

EOF

if ( $ENV{'HTTP_USER_AGENT'} =~ /MSIE 4/ ) {

#
#       MSIE 4.0 stuff
#

$html .=  <<EOF ;
<OBJECT CLASSID="clsid:43F8F289-7A20-11D0-8F06-00C04FC295E1"
        CODEBASE="http://www.es.embnet.org/dll/xenroll.dll"
        ID=Enroll>
</OBJECT>

<FORM NAME="CertReqForm"
      ACTION="https://www.es.embnet.org/cgi-bin/CA/treatReq.pl"
      METHOD=POST>
Your Name:                <INPUT NAME="CommonName" value=""><P>
Your Email address:       <INPUT NAME="EmailAddress" value=""><P>
Your Admistrative Unit    <INPUT NAME="OrganizationalUnit" value=""><P>
<!--- JR --->
Your Organization:        <INPUT NAME="Organization" value=""><P>
Your City:                <INPUT NAME="Locality" value=""><P>
Your State or Province:   <INPUT NAME="StateOrProvince" value=""><P>
Your Country (2 letter):  <INPUT NAME="Country" value="ES"><P>
<!--- JR --->

<INPUT TYPE="BUTTON" VALUE="Submit your certificate request"
       ONCLICK="Submit_OnClick" LANGUAGE="VBScript">
<INPUT TYPE="hidden" NAME="SessionId" VALUE="$SessionId">
<INPUT TYPE="hidden" NAME="ms_req" VALUE="">
</FORM>

<SCRIPT LANGUAGE="VBScript">

Sub Submit_OnClick

  Dim TheForm
  Set TheForm = document.CertReqForm

  szName = "C="                                           & _
           TheForm.Country.value & "; S="                 & _
           TheForm.StateOrProvince.value & "; L="         & _
	   TheForm.Locality.value & "; O="                & _ 
	   TheForm.Organization.value & "; OU="           & _
           TheForm.OrganizationalUnit.value & "; CN="     & _
           TheForm.CommonName.value                       & _
           "; 1.2.840.113549.1.9.1="                      & _
           TheForm.EmailAddress.value

  Enroll.KeySpec = 1
  Enroll.GenKeyFlags = 3
  sz10 = Enroll.CreatePKCS10(szName,"1.3.6.1.5.5.7.3.2")

  if (sz10 = Empty OR theError <> 0) Then
    sz = "The error '" & Hex(theError) & "' occurred."    & _
         chr(13) & chr(10)                                & _
         "Your credentials could not be generated."
    result = MsgBox(sz, 0, "Credentials Enrollment")

    Exit Sub
  else
    TheForm.ms_req.value = sz10
    TheForm.submit()
  end if

End Sub

</SCRIPT>

</BODY>
</HTML>
EOF

}elsif ( $ENV{'HTTP_USER_AGENT'} =~ /MSIE 3/ ) {

#
#       MSIE 3.0 stuff
#

$html .=  <<EOF ;
<OBJECT CLASSID="clsid:33BEC9E0-F78F-11cf-B782-00C04FD7BF43"
        CODEBASE="http://www.es.embnet.org/dll/certenr3.dll"
        ID=Enroll>
</OBJECT>

<FORM NAME="CertReqForm"
      ACTION="https://www.es.embnet.org/cgi-bin/CA/treatReq.pl"
      METHOD=POST>
Your Name:                <INPUT NAME="CommonName" value=""><P>
Your Email address:       <INPUT NAME="EmailAddress" value=""><P>
Your Admistrative Unit    <INPUT NAME="OrganizationalUnit" value=""><P>
<!--- JR --->
Your Organization:        <INPUT NAME="Organization" value=""><P>
Your City:                <INPUT NAME="Locality" value=""><P>
Your State or Province:   <INPUT NAME="StateOrProvince" value=""><P>
Your Country (2 letter):  <INPUT NAME="Country" value="ES"><P>
<!--- JR --->

<INPUT TYPE="BUTTON" VALUE="Submit your certificate request"
       ONCLICK="Submit_OnClick" LANGUAGE="VBScript">
<INPUT TYPE="hidden" NAME="SessionId" VALUE="$SessionId">
<INPUT TYPE="hidden" NAME="ms_req" VALUE="">
</FORM>

<SCRIPT LANGUAGE="VBScript">

Sub Submit_OnClick

  Dim TheForm
  Set TheForm = Document.CertReqForm

  SessionId     = TheForm.SessionId.value
  reqHardware   = FALSE

  szName        = "C="                                           & _
                  TheForm.Country.value & "; S="                 & _
		  TheForm.StateOrProvince.value & "; L="         & _
		  TheForm.Locality.value & "; O="                & _
		  TheForm.Organization.value & "; OU="           & _
                  TheForm.OrganizationalUnit.value & "; CN="     & _
                  TheForm.CommonName.value                       & _
                  "; 1.2.840.113549.1.9.1="                      & _
                  TheForm.EmailAddress.value

  szPurpose          = "ClientAuth"
  doAcceptanceUINow  = FALSE
  doOnline           = TRUE
  keySpec            = 1

  sz10 = Enroll.GenerateKeyPair(SessionId,         _
                                reqHardware,       _
                                szName,            _
                                0,                 _
                                szPurpose,         _
                                doAcceptanceUINow, _
                                doOnline,          _
                                keySpec)

  if (sz10 = Empty OR theError <> 0) Then
    sz = "The error '" & Hex(theError) & "' occurred."    & _
         chr(13) & chr(10)                                & _
         "Your credentials could not be generated."
    result = MsgBox(sz, 0, "Credentials Enrollment")

            Exit Sub
  else
    TheForm.ms_req.value = sz10
    TheForm.submit()
  end if

End Sub

</SCRIPT>

</BODY>
</HTML>
EOF

}else{

#
#       Netscape stuff
#

$html .=  <<EOF ;
<FORM ACTION="https://www.es.embnet.org/cgi-bin/CA/treatReq.pl"
      METHOD="POST">
<KEYGEN NAME="mykey"><P>
Your Name:                <INPUT NAME="CommonName" value=""><P>
Your Email address:       <INPUT NAME="EmailAddress" value=""><P>
Your Admistrative Unit    <INPUT NAME="OrganizationalUnit" value=""><P>
<!--- JR --->
Your Organization:        <INPUT NAME="Organization" value=""><P>
Your City:                <INPUT NAME="Locality" value=""><P>
Your State or Province:   <INPUT NAME="StateOrProvince" value=""><P>
Your Country (2 letter):  <INPUT NAME="Country" value="ES"><P>
<!--- JR --->

<INPUT TYPE="HIDDEN" NAME="SessionId" VALUE=$SessionId>
<INPUT TYPE="SUBMIT" VALUE="Submit your certificate request">

</FORM>

</BODY>
</HTML>
EOF

}

print $html ;

__END__

