#!/usr/bin/perl

  $args = join ' ', @ARGV;
  print "Content-type: text/html\n\n";

  print "<HTML>";
  print "<BODY>";
  print "<H1> ARGUMENTS of NEW</H1>";
  print "$args <P>";
  print "<H1> Environment Variables </H1>";
  print "The following variables are present in the current environment:";
  print "<UL>";
  while (($key1,$value) = each %ENV) {
    print "<LI>$key1 = $value\n";
  }
  print "</UL>";
  print "End of environment.<BR>";
  print "<H1> Method Variables </H1>";
  print "The following method variables were also sent: <BR>"; 
  print "<UL>";
  read(STDIN, $request, $ENV{"CONTENT_LENGTH"});
  print "<HR> $request <HR>";
  @in = split(/&/, $request);

  foreach $j(0..$#in) {
    $in[$j] =~ s/\+/ /g;
    ($key, $val) = split(/=/,$in[$j]);
   
    $key =~ s/%(..)/pack("c",hex($1))/ge;
    $val =~ s/%(..)/pack("c",hex($1))/ge; 
 

    print "<LI>$key = $val\n";
  }
  print "</UL>";
  print "End of variables.";
  print "</BODY></HTML>";
  exit 0;
 
