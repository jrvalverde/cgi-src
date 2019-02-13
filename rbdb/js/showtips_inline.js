<!--- Add the following to the <HEAD> section of your page: --->

  <STYLE>

   /*  This is for Netscape 4.0+ broswsers so that the border will display. 
If you want to modify the background color this is where you would do it for
NS4.0+.  To modify the color for IE and NS6 do so in the style tag in the
div below
   */

   .ttip {border:1px solid black;font-size:12px;layer-background-color:lightyellow;background-color:lightyellow}
  </style>

<script>

//Script created by Jim Young (www.requestcode.com)
//Submitted to JavaScript Kit (http://javascriptkit.com)
//Visit http://javascriptkit.com for this script

           
     function showtip(current,e,tip)
        {
         if (document.layers) // Netscape 4.0+
            {
             theString="<DIV CLASS='ttip'>"+tip+"</DIV>"
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
               elm.innerHTML=tip
               elm.style.height=elml.style.height
               elm.style.top=parseInt(elml.offsetTop+elml.offsetHeight*3)
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
</script>

<!--- E.g.: Use the below HTML code for the <BODY> section. 

<div id="tooltip" style="position:absolute;visibility:hidden;border:1px solid black;font-size:12px;layer-background-color:lightyellow;background-color:lightyellow;padding:1px"></div>

<a href="http://www.es.embnet.org" 
    onMouseover="showtip(this,event,'Our home site')"
    onMouseOut="hidetip()">EMBnet/CNB</a>
--->
