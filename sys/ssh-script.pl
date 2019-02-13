#!/usr/bin/perl -w

print "Content-type: text/text\n\n";

open (GUI, "|./SSH.sh jr\@villon >.out 2>&1 ");
print GUI<<END;
xxxxxxx
voms-proxy-init --voms=biomed
yyyyyyyyyyyyyyyyyyyyyyyyyyyyy
END
my $st = close(GUI);
print "res= $? and $!\n";
print "st=".$st."\n";
exit;
