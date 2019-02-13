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
SESSIONID=$1
DIR="/data/www/EMBnet/Security/CA/certs/$SESSIONID"

if grep SPKAC $DIR/req.raw 1> /dev/null 2>&1 ; then

#
#       Netscape stuff
#

  /bin/cat $DIR/req.raw

else

#
#       MSIE stuff
#

#
#       convert the certificate request from DER format to PEM
#

  $SSL/openssl base64 -d < $DIR/req | $SSL/req -inform der -text

fi

exit 0

