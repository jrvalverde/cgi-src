#
#   Makefile for mailn CGI package
#
#   	mailn CGI is based on a previous mail interface written
#   by Benny Shomer at the EBI, and Reuven M. Lerner from The Tech.
#
#    	The scripts have been extended and modified by José R. Valverde
#   at EMBnet/CNB to be made more generic and complete.
#
#-------------------------------------------------------------
# (C) José R. Valverde
#   EMBnet/CNB
#   This is free software released under the GNU General Public
# License with NO GUARANTEES OF ANY KIND WHATSOEVER.
#   VERIFY IT CAREFULLY BEFORE ATTEMPTING ANY USE.
#   RUN AT YOUR OWN RISK.
#-------------------------------------------------------------

#       CGI-bin directory as seen by the http daemon
CGIBASEDIR=/cgi-bin

#       The real, physical, absolute pathname to the cgi-bin directory
CGIDIR=/data/www/EMBnet/cgi-bin

#   	A system-access directory to store the database building program
#   This should NOT be a web executable directory to avoid trouble
SBINDIR=/usr/local/sbin

#   	The user database file.
#   Use a *SECURE* location to keep the database. This database contains
#   usernames and personal data about human beings, hence it must be
#   protected against peepers according to current law. Every care must
#   be taken to protect it both for legal and security reasons.
#
DATABASE=/data/www/EMBnet/Security/webmail.db

#   	The mailer program
MAILPROG=/usr/lib/sendmail

#   	A default recipient
DEFREC=Network Services Administrator
DEFADDR=netadmin@es.embnet.org

#   	Local email host (where users receive mail)
#   This will be appended to the username to create the destination address
LOCALHOST=es.embnet.org

#   	A background image for the form (e.g. your logo)
BACKGROUND=/images/backgrounds/marble2back.gif

#   	Where PERL is located in your system
PERL=/usr/local/bin/perl

all: emailto.pl sendemailto.pl make_webmail_db.pl Makefile


emailto.pl: emailto.pl.orig Makefile
	sed -e 's|!/usr/bin/perl|!$(PERL)|g' emailto.pl.orig > tmp.pl
	sed -e 's|Network Services Administrator|$(DEFREC)|g' tmp.pl > emailto.pl
	sed -e 's|/images/backgrounds/marble2back.gif|$(BACKGROUND)|g' emailto.pl > tmp.pl
	sed -e 's|http:/cgi-bin|http:$(CGIBASEDIR)|g' tmp.pl > emailto.pl
	rm tmp.pl

sendemailto.pl: sendemailto.pl.orig Makefile
	sed -e 's|!/usr/bin/perl|!$(PERL)|g' sendemailto.pl.orig > tmp.pl
	sed -e 's|/usr/lib/sendmail|$(MAILPROG)|g' tmp.pl > sendemailto.pl
	sed -e 's|netadmin@cnb.uam.es|$(DEFADDR)|g' sendemailto.pl > tmp.pl
	sed -e 's|es.embnet.org|$(LOCALHOST)|g' tmp.pl > sendemailto.pl
	sed -e 's|/data/www/EMBnet/Security/webmail|$(DATABASE)|g' sendemailto.pl > tmp.pl
	mv tmp.pl sendemailto.pl

make_webmail_db.pl: make_webmail_db.pl.orig Makefile
	sed -e 's|!/usr/bin/perl|!$(PERL)|g' make_webmail_db.pl.orig > make_webmail_db.pl


install: all
	cp emailto.pl $(CGIDIR)/.
	cp sendemailto.pl $(CGIDIR)/.
	#
	# Better do this by hand if you want.
	#
	# cp make_webmail_db.pl $(SBINDIR)/.
	# $(SBINDIR)/make_webmail_db.pl > $(DATABASE)

uninstall:
	rm $(CGIDIR)/emailto.pl
	rm $(CGIDIR)/sendemailto.pl
	rm $(SBINDIR)/make_webmail_db.pl
	rm $(DATABASE)
