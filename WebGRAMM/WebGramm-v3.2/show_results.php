<?
// $Id: show_results.php,v 1.1 2003/05/13 13:41:39 root Exp $
// $Log: show_results.php,v $
// Revision 1.1  2003/05/13 13:41:39  root
// Initial revision
//
// We need to know
// Our server name
$server="http://www.es.embnet.org";
//
//	Where are we
$http_root="/data/www/EMBnet";
//
//	Location of auxiliary servlets
//	Servlets accept a URL parameter and return contents of the
//	specified type
$pdb2vrml1="/cgi-src/pdb_servlets/pdb2vrml1.php";
$pdb2vrml2="/cgi-src/pdb_servlets/pdb2vrml2.php";
$pdb2ps="/cgi-src/pdb_servlets/pdb2ps.php";
//$pdb2png="/cgi-src/pdb_servlets/pdb2png.php";

$file_path=$_SERVER['SCRIPT_NAME'];
$path= dirname ($file_path);

echo "<HTML><HEAD><TITLE>WebGramm results</TITLE>";

$job=basename( dirname(__FILE__) );

$file="./rpar.gr";
$date=date( "j F Y H:i", filemtime("$file") );

//The results of file_exists function are cached.
clearstatcache();
	
//variables to control gramm parameters
$from=$_GET["from"];
$to=$_GET["to"];

if (isset($from) && isset($to))
	if ( ($from != "") && ($from < $to) && ($from >0) && ($from < 999) && ($to != "")  && ($to > 1)  && ($to <= 1000) )
	    //With correct 'from' and 'to' values we run gramm
	    if ( ! file_exists("./started"))
	    	run_gramm_coord($from, $to);


if ( file_exists("./done") )
{
	unlink("./started");
	echo "<META http-equiv=\"Expires\" content=\"" . date("D, j M Y H:m:s T") . "\">";
	echo "</HEAD><BODY BGCOLOR=\"white\" BACKGROUND=\"./6h2o-w-small.gif\">";
	
	$current_dir = ".";
  	$dir = dir($current_dir);
	
	echo "<center><h1>WebGramm. Gramm results:<br></h1><center><hr>";
	echo "<center><table border=\"2\" bgcolor=\"lightblue\">";
	
	$dir->rewind();
	while ($file = $dir->read())
  	{
		if ($file !="." && $file!=".." && $file!="index.php" && $file!="done")
		{
			if ( $file=="receptor.pdb" )
			{
				echo "<tr>";
				echo "<td>Structure of your receptor molecule.</td>"; 
				echo "<td><a href=./$file>$file</a></td>";
				echo "<td><a href=\"$pdb2vrml1?url=$server$path/$file\">VRML1</a></td>";
				echo "<td><a href=\"$pdb2vrml2?url=$server$path/$file\">VRML2</a></td>";
				echo "<td><a href=\"$pdb2ps?url=$server$path/$file\">PS</a></td>";
				echo "</tr>";	
			}
		}
	}
	$dir->rewind();
	while ($file = $dir->read())
  	{
		if ($file !="." && $file!=".." && $file!="index.php" && $file!="done")
		{
			if ( $file=="ligand.pdb" )
			{
				echo "<tr>";
				echo "<td>Structure of your ligand molecule.</td>"; 
				echo "<td><a href=./$file>$file</a></td>";
				echo "<td><a href=\"$pdb2vrml1?url=$server$path/$file\">VRML1</a></td>";
				echo "<td><a href=\"$pdb2vrml2?url=$server$path/$file\">VRML2</a></td>";
				echo "<td><a href=\"$pdb2ps?url=$server$path/$file\">PS</a></td>";
				echo "</tr>";	
			}
		}
	}
	$dir->rewind();
	while ($file = $dir->read())
  	{
		if ($file !="." && $file!=".." && $file!="index.php" && $file!="done")
		{
			if ( $file=="rpar.gr" )
			{
				echo "<tr>";
				echo "<td>Parameters used for the docking procedure</td>"; 
				echo "<td colspan=\"4\"><a href=./$file>$file</a></td>";
				echo "</tr>";	
			}
		}
	}
	$dir->rewind();
	while ($file = $dir->read())
  	{
		if ($file !="." && $file!=".." && $file!="index.php" && $file!="done")
		{
			if ( $file=="rmol.gr" )
			{
				echo "<tr>";
				echo "<td>Description of the molecules.</td>"; 
				echo "<td colspan=\"4\"><a href=./$file>$file</a></td>";
				echo "</tr>";	
			}
		}
	}
	$dir->rewind();
	while ($file = $dir->read())
  	{
		if ($file !="." && $file!=".." && $file!="index.php" && $file!="done")
		{
			if ( $file=="gramm.log" )
			{
				echo "<tr>";
				echo "<td>Log output produced by GRAMM</td>"; 
				echo "<td colspan=\"4\"><a href=./$file>$file</a></td>";
				echo "</tr>";	
			}
		}
	}
	$dir->rewind();
	while ($file = $dir->read())
  	{
		if ($file !="." && $file!=".." && $file!="index.php" && $file!="done")
		{
			if ( $file=="wlist.gr" )
			{
				echo "<tr>";
				echo "<td>Config file to set the results</td>"; 
				echo "<td colspan=\"4\"><a href=./$file>$file</a></td>";
				echo "</tr>";	
			}
		}
	}
	$dir->rewind();
	while ($file = $dir->read())
  	{
		if ($file !="." && $file!=".." && $file!="index.php" && $file!="done")
		{
			if ( $file=="receptor-ligand.res" )
			{
				echo "<tr>";
				echo "<td>Listing of the 1000 best scoring docks</td>"; 
				echo "<td colspan=\"4\"><a href=./$file>$file</a></td>";
				echo "</tr>";	
			}
		}
	}
	
	$dir->rewind();
	while ($file = $dir->read())
  	{
		if ($file !="." && $file!=".." && $file!="index.php" && $file!="done")
		{
			if ( ereg("^receptor-ligand_[0-9]+.pdb$", $file))
			{
				$filename = basename($file, ".pdb");
				echo "<tr>";
				echo "<td>Structure of the receptor-ligand complex</td>"; 
				echo "<td><a href=\"$filename.pdb\">$filename</a></td>";
				echo "<td><a href=\"$pdb2vrml1?url=$server$path/$file\">VRML1</a></td>";
				echo "<td><a href=\"$pdb2vrml2?url=$server$path/$file\">VRML2</a></td>";
				echo "<td><a href=\"$pdb2ps?url=$server$path/$file\">PS</a></td>";
				echo "</tr>";	
			}
		}
	}
	
	echo "</table></center>";
  	echo "<hr><br>";
	$dir->close();
	
	if ( !isset($from) && !isset($to) )
	{
	    	echo "<table border=2><tr>";
            	echo "<td><p><a href=\"$server$path/index.php?from=11&to=20\">Next</a> ten receptor-ligand complexes.";
		echo "</tr></table>";
        }else
	{   
	    //Validating values
	    if ( ($from == "") || ($from > $to) || ($from <0) || ($from > 991)|| ($to == "")  ||($to <0)  || ($to > 1000) )
	    {
	    	echo "<CENTER><H1>ERROR: invalid first and/or last match numbers!</H1></CENTER>";
		exit;
	    }
	    
	    if ( $from=="1" )
	    {
    	    	echo "<table border=2><tr>";
            	echo "<td><p><a href=\"$server$path/index.php?from=11&to=20\">Next</a> ten receptor-ligand complexes.";
		echo "</tr></table>";
	    }elseif ($to=="1000" )
	    {	
	    	echo "<table border=2><tr>";
            	echo "<td><p><a href=\"$server$path/index.php?from=991&to=1000\">Prev</a> ten receptor-ligand complexes.";
		echo "</tr></table>";
            }else
	    {	//trick for the gramm parameters
	    	$from_next=$from + 10;
		$to_next=$to + 10;
		$from_prev=$from - 10;
		$to_prev=$to - 10;
		
	    	echo "<table border=2>";
		echo "<tr><td colspan=2 width=100%>Receptor-ligand complexes</td></tr>";
		echo "<tr>";
		echo "<td width=50%><p><a href=\"$server$path/index.php?from=$from_prev&to=$to_prev\"><center>Previous</center></a></td>";
		echo "<td width=50%><p><a href=\"$server$path/index.php?from=$from_next&to=$to_next\"><center>Next</center></a></td>";
		echo "</tr>";
		echo "</table>";
		echo "<BR><P>Note: you may need to reload this page if does not display correctly</P>";
    	    }
	}
	
    	

}else
{   	
	if ( !isset($from) && !isset($to) || ($from=="" && $to==""))
	{
	    echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"20; URL=/tmp/$job/index.php\">";
	}else
	{
	    echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"20; URL=/tmp/$job/index.php?from=$from&to=$to\">";
	}
	echo "</HEAD><BODY BGCOLOR=\"white\">";
	echo "<script language=	\"JavaScript1.2\">";
	echo "<!--";
	echo "var bookmarkurl=\"/tmp/$job\"";
	echo "var bookmarktitle=\"$job Results\"";
	echo "function bookmark()";
	echo "{";
	echo "   window.external.AddFavorite(bookmarkurl,bookmarktitle)";
	echo "}";
	echo "//-->";
	echo "</script>";


	echo "<CENTER><p><H2>WebGramm. Gramm results.</H2><p><br><br>";

        echo "<TABLE BORDER=\"0\"><TR><TD><IMG SRC=\"Rodin_Penseur.jpg\"></TD><TD ALIGN=\"center\">";

	if ( !isset($from) && !isset($to) || ($from=="" && $to==""))
	{
            echo "<H3><A HREF=\"/tmp/$job/index.php\" style=\"color:blue; text-decoration:none\">Reload</A> this page to update your job status</H3>";
	} else {
            echo "<H3><A HREF=\"/tmp/$job/index.php?from=$from&to=$to\" style=\"color:blue; text-decoration:none\">Reload</A> this page to update your job status</H3>";
	}
	echo "<H3>or</H3>";
	echo "<H3><a href=\"javascript:bookmark();\" style=\"color:blue; text-decoration:none\">Bookmark</A> this page to access it later</H3>";
	echo "<TABLE border=2 align=center>";
	echo "<tr>";
	echo "<td VALIGN=\"top\" ALIGN=\"center\" COLSPAN=\"6\"><b>Your Gramm Job is running.</b></td>";
	echo "</tr>";
	echo "<tr>";

	echo "<td BGCOLOR=\"#cccccc\" VALIGN=\"top\" ALIGN=\"center\"><b>Gramm Job number: </b></td>";
	echo "<td BGCOLOR=\"LightBlue\" VALIGN=\"top\" ALIGN=\"center\"><b> $job </b></td>";
	
	echo "<td BGCOLOR=\"#cccccc\" VALIGN=\"top\" ALIGN=\"center\"><b>Status: </b></td>";
	$status="Running"; $color="LightGreen";
	echo "<td BGCOLOR=\"$color\" VALIGN=\"top\" ALIGN=\"center\" BGCOLOR=\"$color\"><b> $status </b></td>";

	echo "<td BGCOLOR=\"#cccccc\" VALIGN=\"top\" ALIGN=\"center\"><b>Start Date: </b></td>";
	echo "<td BGCOLOR=\"LightBlue\" VALIGN=\"top\" ALIGN=\"center\"><b> $date </b></td>";

	echo "</tr>";	
	echo "</TABLE>";

        echo "</TR></TABLE></CENTER>";

	echo "<BR><BR><CENTER><STRONG>Note:</STRONG> This page updates itself automatically every 20 seconds</CENTER>";
}

echo "</BODY></HTML>";
/////////////////////////////// SUBROUTINES ////////////////////////////////

function run_gramm_coord($from , $to)
{   

    $fd = fopen("./wlist.gr", "w");

    fwrite( $fd, "# File_of_predictions   First_match   Last_match   separate/joint  +init_lig\n" );
    fwrite( $fd, "# ----------------------------------------------------------------------------\n" );
    fwrite( $fd, "receptor-ligand.res	$from	$to	separ	no\n" );	

    fclose($fd);
    
    $started = fopen("./started", "w");
    fclose($started);	
    //executing gramm
    exec("rm -f receptor-ligand_*");
    exec("rm -f done");			
    exec("./gramm.sh coord");
}

?>
