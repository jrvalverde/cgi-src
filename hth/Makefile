#
# Makefile for HTH and it's WWW interface.
#
#   HTH is (C) by its authors
#   The WWW interface is (C) José R. Valverde
#   and is hereby placed in the public domain
#
#       CGI-bin directory as seen by the http daemon
CGIBASEDIR=/cgi-bin

#       The real, physical, absolute pathname to the cgi-bin directory
CGIDIR=/data/www/EMBnet/cgi-bin

#       Where the html form is to be stored.
HTFORMDIR=/data/www/EMBnet/Services/MolBio

#   How do you want the executable installed
HTH=/opt/molbio/bin/hth

# How do you have ReadSeq installed
READSEQ=/opt/molbio/bin/readseq

# Maintainer email contact address in HTML form
EMAIL=/cgi-bin/emailto?Bioinformatics+Administrator

CC=cc
CFLAGS= -O
LDFLAGS= 

all: hth


hth: hth.c hth.html.orig hth.cgi.orig Makefile
	@ sed -e 's|/cgi-bin|$(CGIBASEDIR)|g' hth.html.orig > hth.html
	@ sed -e 's|/opt/molbio/bin/hth|$(HTH)|g;s|/opt/molbio/bin/readseq|$(READSEQ)|g;s|/cgi-bin/emailto?Bioinformatics+Administrator|$(EMAIL)|g' hth.cgi.orig > hth.cgi
	@ $(CC) $(CFLAGS) -o hth hth.c $(LDFLAGS)
	@ if [ ! -x $(READSEQ) ] ; then echo "You need to install READSEQ!" ; fi

install: hth
	cp hth $(HTH)
	cp hth.cgi $(CGIDIR)
	cp hth.html $(HTFORMDIR)

