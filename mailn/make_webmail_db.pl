#!/usr/local/bin/perl -- -*-perl-*-

#open (DB, ">/data/www/common/webmail/webmail.db");
open (DB, ">-");
print DB "# Users of web mail form.\n";
print DB "#\n";
print DB "#Uname    Show web?     Real name\n";
print DB "#-------- --------- ------------------------------------------------\n";

open (PASSWD, "</etc/passwd");
while (<PASSWD>) {
   next if /^#/;
   @fields = split(":", $_);
   $uname = $fields[0];
   @gecos = split(",", $fields[4]);
   $realname = $gecos[0];
   print DB "$uname:";
   print DB "	" unless length "$uname:" > 7;
   print DB "	N:	$realname\n";
}
exit;
