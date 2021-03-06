#!/bin/sh

# Customize here: state where these programs are located

GETZ='/opt/srs/bin/irix64/getz'

#
#-------------------
#

echo "Content-type: text/html"
echo ""
echo '<HTML><BODY BGCOLOR="white"'
echo 'BACKGROUND="/images/mols/H2O/6h2o-b-small.gif"'
echo 'LINK="yellow" VLINK="#c0c00f">'
echo '<FONT COLOR="yellow">'

echo "<CENTER><H1>These are the results of your PDB query</H1></CENTER>"

echo "<HR>"

read line

webdir=tmp/dismol.${RANDOM}.${RANDOM}
workdir=/data/www/EMBnet/$webdir
rawinlinefile=$workdir/rawinline.${RANDOM}.${RANDOM}
rawinquery=$workdir/rawinquery.${RANDOM}.${RANDOM}
inqueryfile=$workdir/inquery.${RANDOM}.${RANDOM}
resnofile=$workdir/resno.${RANDOM}.${RANDOM}
resultlistfile=$workdir/resultlist.${RANDOM}.${RANDOM}
outputfile=$workdir/output.${RANDOM}.${RANDOM}

mkdir $workdir

echo $line > $rawinlinefile

cat $rawinlinefile | cut -d'=' -f2 | sed -e 's/\+/ /g' > $rawinquery

echo ""

cat $rawinquery |\
sed -e 's/%0D//g' |\
sed -e 's/%0A//g' |\
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
sed -e 's/%..//g' > $inqueryfile

echo ""

echo "<CENTER><H2>The query you submitted was <EM>\""
cat $inqueryfile
echo "\"</EM></CENTER></H2>"

#
# Do the initial query
#
#	1. test it is not too big
#
#$GETZ -c "[PDB:`cat $inqueryfile`]" > $resnofile
ls -1 /data/gen/pdb/*`cat $inqueryfile`* > $resultlistfile
resno=`cat $resultlistfile | wc -l`
if [ $resno -gt 30 ] ; then
	echo "<CENTER>"
	echo "<BR><H2>Your query is too broad: it yields $resno entries!</H2>"
	echo "<H2>Please, narrow it down and try again</H2>"
	echo "<BR><BR><A HREF=\"/Services/MolBio/DisMol/\">Back</A>"
	echo "</CENTER></BODY></HTML>"
	exit
fi

if [ -z "$resno" ] ; then
	echo "<CENTER>"
	echo "<BR><H2>Your query found NO entries</H2>"
	echo "<H2>Please, try again</H2>"
	echo "<BR><BR><A HREF=\"/Services/MolBio/DisMol/\">Back</A>"
	echo "</CENTER></BODY></HTML>"
	exit
fi

echo "<CENTER><H2>Your query found the following $resno entries</H2></CENTER>"

#$GETZ "[PDB:`cat $inqueryfile`]" > $resultlistfile


echo "<CENTER><TABLE WIDTH=\"90%\" BORDER=\"2\">"
echo "<PRE><TT>"

cat $resultlistfile 

echo "</TT></PRE>"
echo "</TABLE></CENTER>"

echo "<P>A maximum of 10 will be shown</P>"
echo "<HR>"

echo "<applet codebase=\"/Services/MolBio/DisMol/\" code=\"DisMol.class\"" 
echo "width=\"650\" height=\"550\">"
/bin/echo -n "<param name=url value=\""
#
# Create output files and send references
#
let max=0
cat $resultlistfile |\
while read line ; do
	resultfile=tmp/dismol/`basename $line`
	#$GETZ -e "[$line]" > /data/www/EMBnet/$resultfile
	ln -s $line /data/www/EMBnet/$resultfile
	/bin/echo -n "http://${SERVER_NAME}/${resultfile}"
	let max=max+1
	if [ $max = 10 ] ; then
	    break;
	else
	    echo ";"
	fi
done
echo "\"></applet>"
echo ""

echo "<P>If you have any trouble, please contact our"
echo '<A HREF="/cgi-bin/emailto?Bioinformatics+Administrator">Bioinformatics'
echo 'Administrator</A> <A HREF="/Copyright-CSIC.html">&copy; EMBnet/CNB</A>'
echo "</BODY>"
echo "</HTML>"

rm -r $workdir
