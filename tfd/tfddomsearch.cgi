#!/bin/sh

# Customize here: state where these programs are located

TFDSearch='/opt/molbio/prosearch/tfddomsearch'
ReadSeq='/opt/molbio/bin/readseq'

#
#-------------------
#

echo "Content-type: text/html"
echo ""
echo '<HTML><BODY BGCOLOR="white">'

echo "<CENTER><H1>Thank you for using TFDSearch</H1></CENTER>"

read line

rawinlinefile=/tmp/rawinline.${RANDOM}.${RANDOM}
rawinseqfile=/tmp/rawinseq.${RANDOM}.${RANDOM}
inseqfile=/tmp/inseq.${RANDOM}.${RANDOM}
plainseqfile=/tmp/plainseq.${RANDOM}.${RANDOM}
outputfile=/tmp/output.${RANDOM}.${RANDOM}

echo $line > $rawinlinefile
echo ""

cat $rawinlinefile | cut -d'=' -f2 | sed -e 's/\+/ /g' > $rawinseqfile
echo ""

cat $rawinseqfile | sed -e 's/%0D%0A/\
/g' | sed -e 's/%0D/\
/g' | sed -e 's/%0A/\
/g' | sed -e 's/%09/	/g' |\
sed -e 's/%21/\!/g' |\
sed -e 's/%3B/\;/g' |\
sed -e 's/%23/\#/g' |\
sed -e 's/%2C/\,/g' |\
sed -e 's/%2F/\//g' |\
sed -e 's/%5C/\\/g' |\
sed -e 's/%7C/\|/g' |\
sed -e 's/%28/\(/g' |\
sed -e 's/%29/\)/g' |\
sed -e 's/%5B/\[/g' |\
sed -e 's/%5D/\]/g' |\
sed -e 's/%3A/\:/g' |\
sed -e 's/%3E/\>/g' |\
sed -e 's/%3C/\>/g' |\
sed -e 's/%..//g' > $inseqfile

$ReadSeq -f=13 $inseqfile -o=$plainseqfile

echo "<H2>The results of your analysis are</H2>"

echo "<PRE><TT>"

$TFDSearch $plainseqfile 

echo "</TT></PRE>"

echo "<H2>To obtain more details search by TFD domain name on the SRS server</H2>"

echo "<H2>The sequence you submitted was:</H2>"

echo "<TABLE BORDER=2><TR><TD><PRE><TT>"
cat $inseqfile
echo "</TT></PRE></TD></TR></TABLE>"

echo ""

echo "<H2>The sequence used for the analysis is</H2>"
echo "<TABLE BORDER=2><TR><TD><PRE><TT>"
cat $plainseqfile
echo "</TT></PRE></TD></TR></TABLE>"

echo "<BR>"
echo "<HR>"
echo "<P>If you have any trouble, please contact our"
echo '<A HREF="/cgi-bin/emailto?Bioinformatics+Administrator">Bioinformatics'
echo 'Administrator</A> <A HREF="/Copyright-CSIC.html">&copy; EMBnet/CNB</A>'
echo "</BODY>"
echo "</HTML>"

rm $rawinlinefile $rawinseqfile $inseqfile $plainseqfile $outputfile
