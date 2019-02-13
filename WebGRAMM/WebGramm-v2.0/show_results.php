<?
//$Id$
//$Log$

echo "<HTML><HEAD><TITLE>WebGramm results</TITLE>";

$job=basename( dirname(__FILE__) );

$file="./rpar.gr";
$date=date( "j F Y H:i", filemtime("$file") );

if ( file_exists("./done") )
{ 
	echo "<META http-equiv=\"Expires\" content=\"" . date("D, j M Y H:m:s T") . "\">";
	echo "</HEAD><BODY BGCOLOR=\"white\">";
	$current_dir = ".";
  	$dir = dir($current_dir);

	echo "<center><h1>WebGramm. Gramm results:<br></h1><center><hr>";
	echo "<center><table border=2>";
	
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
				echo "<td><a href=\"receptor.vrml\">VRML1</a></td>";
				echo "<td><a href=\"receptor.wrl\">VRML2</a></td>";
				echo "<td><a href=\"receptor.ps\">PS</a></td>";
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
				echo "<td><a href=\"ligand.vrml\">VRML1</a></td>";
				echo "<td><a href=\"ligand.wrl\">VRML2</a></td>";
				echo "<td><a href=\"ligand.ps\">PS</a></td>";
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
			if ( ereg("^receptor-ligand+_[0-9]+.pdb$", $file))
			{
				$filename = basename($file, ".pdb");
				echo "<tr>";
				echo "<td>Structure of the receptor-ligand complex</td>"; 
				echo "<td><a href=\"$filename.pdb\">$filename</a></td>";
				echo "<td><a href=\"$filename.vrml\">VRML1</a></td>";
				echo "<td><a href=\"$filename.wrl\">VRML2</a></td>";
				echo "<td><a href=\"$filename.ps\">PS</a></td>";
				echo "</tr>";	
			}
		}
	}
	
	echo "</table></center>";
  	echo "<hr><br>";
	$dir->close();
	
	//Generating the Buttons to browse the receptor-ligand complex results
	read_wlist();
	
	//variables to control gramm parameters
	$from=$_GET["from"];
	$to=$_GET["to"];
	/*
	if ($param!="" && $param!="")
	{   
	    run_gramm($param, $first_match, $last_match);
	}
	*/
	switch ($first_match) 
	{
    	    case 1:
	    	echo "<table border=2><tr>";
            	echo "<td><p><a href=\"./index.php?param=add\">Next </a> ten receptor-ligand complexes.";
		echo "</tr></table>";
            	break;
    	    case 990:
    	    	echo "<table border=2><tr>";
            	echo "<td><p><a href=\"./index.php?param=sub\">Previous </a> ten receptor-ligand complexes.";
		echo "</tr></table>";
		break;
    	    default:
            	echo "<table border=2>";
		echo "<tr><td colspan=2>Receptor-ligand complexes</td></tr>";
		echo "<tr>";
		echo "<td width=50%><p><a href=\"./index.php?from=$prev_from+to=$prev_to\"><center>Previous</center></a></td>";
		echo "<td width=50%><p><a href=\"./index.php?from=$next_from+to=$next_to"><center>Next</center></a></td>";
		echo "</tr>";
		echo "</table>";
    	}

}else
{
	echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"20; URL=/tmp/$job/\">";
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

        echo "<H3><A HREF=\"/tmp/$job\" style=\"color:blue; text-decoration:none\">Reload</A> this page to update your job status</H3>";
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

//
//Checking the match parameters
//
function read_wlist()
{   	
global $first_match;
global $last_match;
$wlist="./wlist.gr";
    if ( file_exists("$wlist") )
    {   
    	    $fd = fopen($wlist, "r");
	    while (!feof ($fd))
	    {   	
       	    $buffer = fgets($fd, 4096);
	    	if (ereg( "^receptor-ligand.res", $buffer))
	    	{
		    list($a, $first_match, $last_match, $c, $d) = sscanf($buffer,"%s %d %d %s %s");
			
	    	}
			
       	    }
	    fclose($fd);
		
    }		
}

//
//Modifiying wlist.gr, cleaning the directory and then run gramm 'coord'
//
function run_gramm($param, $first_match, $last_match)
{   
$fd = fopen("./wlist.gr", "r");
$fd_copy = fopen("./wlist_copy.gr", "w");
exec("rm -f receptor-ligand_*");

		while (!feof ($fd))
		{   	
			// find the record we wish to fix and update it
       			$buffer = fgets($fd, 4096);
			
			
			if (($first_match > $last_match) 
			 || ($first_match <0) || ($first_match > 1000)
			 || ($last_match <0)  || ($last_match > 1000) ){
				echo "<CENTER><H1>ERROR: invalid first and/or last match numbers!</H1></CENTER>";
				echo "</BODY></HTML>";
				exit;
			}
			
			//modifying the file
			if (ereg( "^receptor-ligand.res", $buffer))
			{
				if ( $param=="add" && $last_match < 990)
				{   
				    $new_first_match = $first_match + 10;
				    $new_last_match = $last_match + 10;
				}
				if ( $param=="sub" && $first_match > 10 )
				{   
				    $new_first_match = $first_match - 10;
				    $new_last_match = $last_match - 10;
				}
				$new_values="receptor-ligand.res\t$new_first_match\t$new_last_match\tsepar\tno\n";
				fputs($fd_copy, $new_values);
			}
			else
			{	
				fputs($fd_copy, $buffer);
			}
       			
		}

		fclose($fd);
		fclose($fd_copy);
		rename("./wlist_copy.gr", "./wlist.gr");

//executing gramm		
putenv("gramm_param=coord");		
exec("./gramm.sh");

}

?>
