#!/bin/sh

upload_dir='/u/sysadmin/jr/public_html/upload/'
upload_url='http://www.es.embnet.org/~jr/upload/'

echo "Content-type: text/html"
echo ""

cd $upload_dir

# First check if we have been invoked with an RM argument
if [ $# -ne 0 ] ; then
    if [ $# -eq 2 ] ; then
    	if [ "$1" = "rm" ] ; then
	    if [ -f `basename $2` ] ; then
	    	rm `basename $2`
	    fi
    	fi
    fi
fi

cat <<ENDHEADER
<HTML>
<HEAD>
<TITLE>Upload File Manager</TITLE>
  <STYLE>

   /*  This is for Netscape 4.0+ broswsers so that the border will display.  If you want to modify the background color this is where you would do it for NS4.0+.  To modify the color for IE and NS6 do so in the style tag in the div below
   */

   .ttip {border:1px solid black;font-size:12px;layer-background-color:lightyellow;background-color:lightyellow}
  </STYLE>

<script>

//Script created by Jim Young (www.requestcode.com)
//Submitted to JavaScript Kit (http://javascriptkit.com)
//Visit http://javascriptkit.com for this script

//Set the tool tip message you want for each link here.
     var tip=new Array
     	   tip[0]='Click here to reload this page<BR> and update the file listing'
           tip[1]='Hold down right mouse button and<br> select <B>Save link as...</B>'
           tip[2]='Click here to delete this file'
           tip[3]='You may also download/see this file<br> by clicking here'
           
     function showtip(current,e,num)
        {
         if (document.layers) // Netscape 4.0+
            {
             theString="<DIV CLASS='ttip'>"+tip[num]+"</DIV>"
             document.tooltip.document.write(theString)
             document.tooltip.document.close()
             document.tooltip.left=e.pageX+14
             document.tooltip.top=e.pageY+2
             document.tooltip.visibility="show"
            }
         else
           {
            if(document.getElementById) // Netscape 6.0+ and Internet Explorer 5.0+
              {
               elm=document.getElementById("tooltip")
               elml=current
               elm.innerHTML=tip[num]
               elm.style.height=elml.style.height
               elm.style.top=parseInt(elml.offsetTop+elml.offsetHeight)
               elm.style.left=parseInt(elml.offsetLeft+elml.offsetWidth+10)
               elm.style.visibility = "visible"
              }
           }
        }
function hidetip(){
if (document.layers) // Netscape 4.0+
   {
    document.tooltip.visibility="hidden"
   }
else
  {
   if(document.getElementById) // Netscape 6.0+ and Internet Explorer 5.0+
     {
      elm.style.visibility="hidden"
     }
  } 
}
</script></HEAD>
<BODY BGCOLOR="white" BACKGROUND="/~jr/images/backgrounds/marble.jpg">
<div id="tooltip" style="position:absolute;visibility:hidden;border:1px solid black;font-size:12px;layer-background-color:lightyellow;background-color:lightyellow;padding:1px"></div>

<HR ALIGN=CENTER SIZE=8 WIDTH="75%" NOSHADE>
<CENTER><H1>Upload File Manager</H1></CENTER>
<HR ALIGN=CENTER SIZE=8 WIDTH="75%" NOSHADE>

<TABLE BGCOLOR="lightpink" ALIGN=CENTER WIDTH="80%" CELLPADDING="5">
<TR><TD><CENTER><STRONG>The following files have been uploaded to
your personal upload area</STRONG></CENTER></TD></TR>
</TABLE>
<HR ALIGN=CENTER SIZE=8 WIDTH="75%" NOSHADE>

<TABLE WIDTH="90%" ALIGN=CENTER BGCOLOR="lightblue" 
       CELLSPACING=0 CELLPADDING=5  BORDER="2">
    <TR BORDER="4"><TH>Update</TH><TH>Download</TH><TH>Delete</TH><TH>Size (kB)</TH><TH WIDTH="90%">File Name</TH></TR>
ENDHEADER


ls -1sk $upload_dir | tail +2 | while read line ; do
    size=`echo $line | sed -e 's/^ *//g' | cut -f1 -d' '`
    filnam=`echo $line | sed -e 's/^ *//g' | cut -f2 -d' '`
    cat <<ENDENTRY
    <TR>
    	<TD><A HREF="/~jr/cgi-bin/upload_mgr.cgi" onMouseover="showtip(this,event,'0')" onMouseOut="hidetip()"><IMG SRC="/~jr/images/recycler.jpg"></A></TD>
    	<TD><A HREF="${upload_url}${filnam}" onMouseover="showtip(this,event,'1')" onMouseOut="hidetip()"><IMG SRC="/~jr/images/ark.jpg"></A></TD>
    	<TD><A HREF="/~jr/cgi-bin/upload_mgr.cgi?rm+$filnam" onMouseover="showtip(this,event,'2')" onMouseOut="hidetip()"><IMG SRC="/~jr/images/shredder.jpg"></A></TD>
    	<TD ALIGN=RIGHT>$size KB</TD>
    	<TD><A HREF="${upload_url}${filnam}" onMouseover="showtip(this,event,'3')" onMouseOut="hidetip()">$filnam</A></TD>
    </TR>
ENDENTRY

done

cat << ENDFOOTER
</TABLE>
<HR ALIGN=CENTER SIZE=8 WIDTH="75%" NOSHADE>
<TABLE WIDTH="100%">
  <TR ><TD ALIGN=CENTER>
    <address>
    <!-- app info here -->
    <P>If you have any trouble, please contact our
    <A HREF="/cgi-bin/emailto?José+R.+Valverde">José R. Valverde</A></P>
    <P><A HREF="/~jr/Copyright-JR_AH.html">&copy; JR</A></P>
    </address>
  </TD></TR>
</TABLE>
</BODY></HTML>

ENDFOOTER
