#	Makefile for cookie programs and database.
#
#	Where the database file will be installed
DBDIR=/data/www/EMBnet/Services/production

#	CGI-bin directory as seen by the http daemon
CGIBASEDIR=/cgi-bin

#	The real, physical, absolute pathname to the cgi-bin directory
CGIDIR=/data/www/EMBnet/cgi-bin

#	Where html documents are stored.
HTDOCDIR=/data/www/EMBnet

#	Where the gif backgrounds will be as seen by the http daemon
#   	NOTE: use a directory specific to this CGI if you want to be
#   	able to run 'make uninstall' later!
GIFBASEDIR=/images/backgrounds/totd

#	The absolute pathname of the gif directory for backgrounds
#   	NOTE: use a directory specific to this CGI if you want to be
#   	able to run 'make uninstall' later!
GIFDIR=$(HTDOCDIR)$(GIFBASEDIR)

CC=cc
CFLAGS= -O
LDFLAGS= -lm

all: totd


totd: totd.c rnd.o
	sed -e 's|/usr/local/lib|$(DBDIR)|g;s|/bkgs|$(GIFBASEDIR)|g;s|ACTION="/cgi-bin/|ACTION="$(CGIBASEDIR)/"|g' totd.c > tmp.c
	$(CC) $(CFLAGS) -o totd tmp.c rnd.o $(LDFLAGS)
	rm tmp.c

rnd.o: rnd.c
	$(CC) $(CFLAGS) -c rnd.c

installbin: totd
	mv totd $(CGIDIR)/.

install: totd
	mv totd $(CGIDIR)/.
	cp totd.file.sample $(DBDIR)/totd.file
	mkdirhier $(GIFDIR)
	cp bkgs/* $(GIFDIR)/.

clean:
	rm -f core *.o totd

uninstall:
	rm $(CGIDIR)/totd
	rm $(DBDIR)/totd.file
	rm -r $(GIFDIR)
