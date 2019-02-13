#!/usr/bin/perl -w

print "Content-type: text/text\n\n";

open (GUI, "|ssh -x -t -t jr\@villon /bin/ls>.out 2>&1 ");
print GUI<<END;
xxxxxxxx
END
my $st = close(GUI);
print "res= $? and $!\n";
print "st=".$st."\n";
exit;
