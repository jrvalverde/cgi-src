#!/bin/sh

umask 022

cd /data/www/EMBnet/Members
rm -f index.html

tmpfile='/tmp/netadmin.members'
rm -f $tmpfile
touch $tmpfile
listfile='/tmp/netadmin.members.list'
rm -f $listfile
touch $listfile

# Print header part 1
cat > $tmpfile << ENDHEADER1
<HTML>
<HEAD>
    <TITLE>EMBnet/CNB member pages</TITLE>
</HEAD>
<BODY BGCOLOR="white" BACKGROUND="/images/backgrounds/EMBnetCNB.gif"
      LINK="Blue" VLINK="DarkBlue">
<CENTER>
ENDHEADER1

# Change logo according to season:
#   A season is assumed to last 1/4 year = 365/4 = 91.25 days
#   spring  	> 81
#   summer  	> 172
#   fall    	> 263
#   winter  	> 355
day=`date +%j`

if [ $day  -ge 355 ] ; then 
cat >> $tmpfile << ENDWINTER
    <IMG SRC="/images/logos/EMBnetCNB/logo-frosty-tx.gif" ALT="EMBnet/CNB" WIDTH="95%">
ENDWINTER
elif [ $day -ge 263 ] ; then
cat >> $tmpfile << ENDFALL
    <IMG SRC="/images/logos/EMBnetCNB/logo-sspace.gif" ALT="EMBnet/CNB" WIDTH="95%">
ENDFALL
elif [ $day -ge 172 ] ; then 
cat >> $tmpfile << ENDSUMMER
    <IMG SRC="/images/logos/EMBnetCNB/logo-coolm-tx.gif" ALT="EMBnet/CNB" WIDTH="95%">
ENDSUMMER
elif [ $day -ge 172 ] ; then 
cat >> $tmpfile << ENDSPRING
    <IMG SRC="/images/logos/EMBnetCNB/logo-sburst-tx.gif" ALT="EMBnet/CNB" WIDTH="95%">
ENDSPRING
else
cat >> $tmpfile << ENDWINTER2
    <IMG SRC="/images/logos/EMBnetCNB/logo-frosty-tx.gif" ALT="EMBnet/CNB" WIDTH="95%">
ENDWINTER2
fi

# Continue header
cat >> $tmpfile << ENDHEADER2
<BR>
<H1>Welcome to our Member Pages</H1>
</CENTER>

<TABLE BORDER="3">
<TR><TD BGCOLOR="lightpink">We provide hosting of web pages for all EMBnet/CNB 
members. This service encompasses both, <STRONG>private user pages</STRONG> and 
<STRONG>common group pages</STRONG>.</TD></TR>

<TR><TD BGCOLOR="lightyellow">No illegal, commercial or advertising activity 
is to be allowed</TD></TR>

<TR><TD BGCOLOR="lightpink">Users are ultimately resposible for the contents 
of their pages.</TD></TR>

<TR><TD BGCOLOR="lightyellow">Institutional or official web pages from groups 
belonging to CSIC must comply with <A HREF="http://www.csic.es/normaweb/">
CSIC rules</A>.</TD></TR>

<TR><TD BGCOLOR="lightpink">This page is automatically generated. If you
would rather have supervised, scientific content pages,
 <A HREF="/Services/web.html">see this one</A>.</TD></TR>
</TABLE>

<BIG>
<P>These are all member pages currently available at our host.</P>

<UL>
ENDHEADER2




#-------------------------------------------------------------------------
# Print (unsorted) list of available pages
#

for i in /u/*
{
#    echo $i
    cd $i 
    for j in * 
    {
    	if [ -d $i/$j/public_html ] ; then
#	    echo $i/$j/public_html
    	    cd $i/$j/public_html
	    if [ -f index.html ] ; then
#	    	echo $i/$j/public_html/index.html
    	    	# Verify there is a valid index.html page
		diff -q $i/$j/public_html/index.html \
		    /usr/local/etc/skel/public_html/index.html > /dev/null 2>&1 
		if [ $? = 1 ] ; then
    	    	    # if there is a valid home page
#		    echo "$i/$j/public_html/index.html"
		    name=`grep "^$j\:" /etc/passwd | cut -d: -f5 | cut -d, -f1`
		    user=`echo $name | sed -e 's/ /_/g'`
		    user=`echo $user | tr "¡…Õ”⁄·ÈÌÛ˙—Ò-" "AEIOUaeiouNn_"`
		    user=`echo $user | tr -d "\(\)\." `
		    if [ ! -e /data/www/EMBnet/Members/$user ] ; then 
		    	echo "Creating link for $i/$j/public_html to /data/www/EMBnet/Members/$user"
		    	ln -s $i/$j/public_html /data/www/EMBnet/Members/$user
    	    	    fi
		    echo "    <LI><A HREF=\"/Members/$user\">$name</A></LI>" >> $listfile
#		else
#		    # if there isn't, make sure there is no link either
#		    if [ -l /data/www/EMBnet/Members/$user ] ; then
#		    	echo "Removing now empty /data/www/EMBnet/Members/$user"
#			rm /data/www/EMBnet/Members/$user
#		    fi
		fi
	    fi
	fi
    }
}

# Sort and append to page
#
cat $listfile | sort >> $tmpfile
rm $listfile


# end the page
cat >> $tmpfile << ENDFOOTER
</UL>
</BIG>

</BODY>
<HR>

<A HREF="/cgi-bin/emailto?Web+Master">webmaster@www.es.embnet.org</A>
<P><STRONG><A HREF="/Copyright-CSIC.html">&copy; EMBnet/CNB.</A></STRONG></P> 

</HTML>

ENDFOOTER

mv $tmpfile /data/www/EMBnet/Members/index.html
