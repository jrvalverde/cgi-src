#!/bin/bash

# Define fairly-constants
htmlinstalldir=/Services/MolBio/fasta

read line

cat <<END_HDR
Content-type: text/html

<Head><Title>Thank you!</Title></Head>
<A NAME="top"></A>
<FRAMESET ROWS="100,*" FRAMEBORDER="YES" BORDER="0" FRAMESPACING="0">
    <FRAME SRC="$htmlinstalldir/thanks.html" NAME="toc"
     MARGINHEIGHT="0" MARGINWIDTH="0"
     SCROLLING NORESIZE BORDER="YES">

    <FRAME SRC="/cgi-bin/runfasta?$line" NAME="main" BORDER="NO">
</FRAMESET></HTML>
END_HDR
 
exit
