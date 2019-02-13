#!/bin/sh

# Customize here: state where these programs are located

cgi-home=/data/www/EMBnet/cgi-src/grammlr

webdir=gramm.${RANDOM}.${RANDOM}
workdir=/data/www/EMBnet/tmp/$webdir
rawinlinefile=$workdir/rawinline.${RANDOM}.${RANDOM}
rawinquery=$workdir/rawinquery.${RANDOM}.${RANDOM}
inqueryfile=$workdir/inquery.${RANDOM}.${RANDOM}

#
#-------------------
#
mkdir $workdir

cd $workdir

read line

echo $line > $rawinlinefile

exit


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


cat $Receptor > receptor.pdb
cat $ligand > ligand.pdb

# We also use the same parameters and file names, hence we may as
# well just copy the template command files directly:
cp $cgi-home/*.gr   $workdir

gramm scan coord
return links to gramm.log receptor-ligand.res receptor-ligand_1-1.pdb


#
#------------------------------------------------------------------
#

echo "Content-type: text/html"
echo ""
echo '<HTML><BODY BGCOLOR="white"'
echo 'BACKGROUND="/images/mols/H2O/6h2o-b-small.gif"'
echo 'LINK="yellow" VLINK="#c0c00f">'
echo '<FONT COLOR="yellow">'

echo "<CENTER><H1>These are the results of your GRAMM query</H1></CENTER>"

echo "<HR>"


#
# Do the initial query
#
#	1. test it is not too big
#
$GETZ -c "[PDB:`cat $inqueryfile`]" > $resnofile
resno=`cat $resnofile`
if [ $resno -gt 30 ] ; then
	echo "<CENTER>"
	echo "<BR><H2>Your query is too broad: it yields $resno entries!</H2>"
	echo "<H2>Please, narrow it down and try again</H2>"
	echo "<BR><BR><A HREF=\"http://www.es.embnet.org/Services/MolBio/DisMol/\">Back</A>"
	echo "</CENTER></BODY></HTML>"
	exit
fi

if [ -z "$resno" ] ; then
	echo "<CENTER>"
	echo "<BR><H2>Your query found NO entries</H2>"
	echo "<H2>Please, try again</H2>"
	echo "<BR><BR><A HREF=\"http://www.es.embnet.org/Services/MolBio/DisMol/\">Back</A>"
	echo "</CENTER></BODY></HTML>"
	exit
fi

echo "<CENTER><H2>Your query found the following $resno entries</H2></CENTER>"

$GETZ "[PDB:`cat $inqueryfile`]" > $resultlistfile

echo "<CENTER><TABLE WIDTH=\"90%\" BORDER=\"2\">"
echo "<PRE><TT>"

cat $resultlistfile 

echo "</TT></PRE>"
echo "</TABLE></CENTER>"

echo "<P>A maximum of 10 will be shown</P>"
echo "<HR>"

echo "<CENTER><TABLE BORDER=\"2\">"
echo "<TR BGCOLOR=\"LightCyan\"><TD>VRML 1</TD><TD>VRML 2/95</TD></TR>"
#
# Create output files and send references
#
let max=0
cat $resultlistfile |\
while read line ; do
	resultfileroot=tmp/$line
	# prepare row
	echo "<TR>"
	# get entry and convert to VRML
	$GETZ -e "[$line]" > $tmppdbfile
    	# (v1)
	cat $tmppdbfile | $pdb2vrml -pdb - - > \
	    /data/www/EMBnet/${resultfileroot}.vrml
	echo "<TD><A HREF=\"http://www.es.embnet.org/${resultfileroot}.vrml\">${line}.vrml</A></TD>" 
    	# (v2)
	$molauto -cylinder -turns -nice -cpk $tmppdbfile | \
	    $molscript -vrml > /data/www/EMBnet/${resultfileroot}.wrl 2> /dev/null
    	# add URL
	echo "<TD><A HREF=\"http://www.es.embnet.org/${resultfileroot}.wrl\">${line}.wrl</A></TD>" 
	echo "</TR>"
	let max=max+1
	if [ $max = 10 ] ; then
	    break;
	fi
done

echo "</TABLE></CENTER>"

echo "<HR>"

echo "<P>If you have any trouble, please contact our"
echo '<A HREF="/cgi-bin/emailto?Bioinformatics+Administrator">Bioinformatics'
echo 'Administrator</A> <A HREF="/Copyright-CSIC.html">&copy; EMBnet/CNB</A>'
echo "</BODY>"
echo "</HTML>"

rm -r $workdir
