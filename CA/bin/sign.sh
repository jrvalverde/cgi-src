#!/bin/sh

exit_on_error()
{
  if [ $? -ne 0 ] ; then
    exit 1
  fi
}

if [ $# -ne 1 ] ; then
  echo "$0: wrong number of arguments" >&2
  echo "  Usage: $0 <request identificator>" >&2
  exit 1
fi

SSL=/opt/openssl/bin
SESSIONID="$1"
DIR="/data/www/EMBnet/Security/CA/certs/$SESSIONID"

if grep SPKAC $DIR/req.raw 1> /dev/null 2>&1 ; then

#
#       Netscape stuff
#

$SSL/openssl ca -spkac $DIR/req.raw \
   -out /data/www/EMBnet/Security/CA/signed/${SESSIONID}.ucert \
   -policy policy_embnet
#   -config /usr/local/ssl/lib/ssleay.cnf.netscape 
exit_on_error

else

#
#       MSIE stuff
#

#
#       convert the certificate request from DER format to PEM
#

$SSL/openssl base64 -d < $DIR/req | $SSL/openssl req -inform der -text > $DIR/req.pem
exit_on_error

#
#       sign certificate
#

$SSL/openssl ca -gencrl -policy policy_embnet -msie_hack \
   -out $DIR/cert.signed \
   -infiles $DIR/req.pem
exit_on_error

$SSL/openssl crl2pkcs7 -certfile $DIR/cert.signed \
   -in $DIR/cert.signed -out $DIR/cert._pkcs7
exit_on_error

#
#       Take out the base64 encoded PKCS#7 signed certificate
#

/usr/local/bin/perl -ne '$a=/BEGIN /../END /;print if($a>1 && $a!~/E0/);' \
     $DIR/cert._pkcs7 > $DIR/cert.pkcs7

#
#       Build HTML page with VBS code to download the certificate to MSIE
#

(
               cat << EOF1=================================================
<HTML>
<HEAD>
<TITLE>Downloading your Signed certificate</TITLE>
</HEAD>

<BODY LANGUAGE="VBScript" ONLOAD="InstallCert">

<OBJECT CLASSID="clsid:43F8F289-7A20-11D0-8F06-00C04FC295E1"
        CODEBASE="http://www.es.embnet.org/dll/xenroll.dll"
        ID=Enroll>
</OBJECT>

<SCRIPT LANGUAGE="VBSCRIPT">
Sub InstallCert

  On Error Resume Next

  credentials = "" & _
EOF1=================================================
               /usr/local/bin/perl -npe 's/^/        "/;s/$/" & _/;' $DIR/cert.pkcs7
               cat <<EOF2=================================================
        ""
  Call Enroll.AcceptPKCS7(credentials)

  html = "" & _
"<H1>Certificate download status</H1>"            & chr(13) & chr(10)

  If err.Number <> 0 Then

    html = html & _
"There were problems downloading"                 & chr(13) & chr(10) & _
"your signed certificate to your browser."        & chr(13) & chr(10) & _
"<P>"                                             & chr(13) & chr(10) & _
"The error code is: 0x" & Hex(err)                & chr(13) & chr(10)

  else

    html = html & _
"Your signed certificate has been downloaded"     & chr(13) & chr(10) & _
"successfully to your browser."                   & chr(13) & chr(10)

  End if

  document.write(html)

End sub
</SCRIPT>

</BODY>
</HTML>
EOF2=================================================
) \
               > /data/www/EMBnet/Security/CA/signed/${SESSIONID}.html

fi

exit 0
