#!/bin/sh
#
# (C) 2001 José R. Valverde, EMBnet/CNB, jrvalverde@es.embnet.org
#

echo "Content-type: text/html"
echo ""

cat << END
<html><head><title>BABEL+DISMOL</title></head>
<body>
<CENTER><H2>3D Visualization of molecules</H2></CENTER>
<CENTER><BR><I>Running: BABEL + DISMOL </I><BR></CENTER>
<TABLE WIDTH="90%" BGCOLOR="white"> 
<TR><TH>This is your molecule</TH></TR> 
<TR><TD><STRONG><A HREF="/cgi-src/DisMol/aspirin.pdb">PDB file shown</A></STRONG></TD></TR>
<TR><TD BGCOLOR="black"><CENTER>
<applet codebase="/Services/MolBio/DisMol/" code="DisMol.class" width="650" height="550">
<param name=url value="http://www.es.embnet.org/cgi-src/DisMol/aspirin.pdb"></applet></CENTER></TD></TR>
</TABLE> 
<P>  
<P>
<HR>

<TABLE WIDTH="100%">
  <TR ><TD ALIGN=CENTER>
    <P>If you have any trouble, please contact our
    <A HREF="/cgi-bin/emailto?Bioinformatics+Administrator">Bioinformatics
    Administrator</A></P>
    <P><A HREF="/Copyright-CSIC.html">&copy; EMBnet/CNB</A></P>
  </TD></TR>
</TABLE>
</body></html>

END
