<?php
/* 
 */
	// Retrieve URL from params
	// If not, return a Web page with usage description
	// Use CURL to get PDB file contents
	// if not, output error
	//
	// (C) EMBnet/CNB 13/05/2003
	//	See http://www.es.embnet.org/Copyright-CSIC.html
	//
	// AUTHOR: José R. Valverde (EMBnet/CNB) jrvalverde@es.embnet.org
	//
    	// $Id: babel_client.php,v 1.3 2005/10/07 13:26:56 netadmin Exp $
	// $Log: babel_client.php,v $
	// Revision 1.3  2005/10/07 13:26:56  netadmin
	// Modified for WebServices
	// Modified pdb2png to use raster3d in addition to molscript (molscript alone
	// required a valid DISPLAY)
	//
	// Revision 1.2  2005/01/21 15:35:54  netadmin
	// Fixed minor bugs... and installed pdb2vrml on the server
	//
	// Revision 1.1.1.2  2005/01/21 14:35:22  netadmin
	// Added WS to pdb2vrml1
	//
	//
 
// Pull in the NuSOAP code
require_once('lib/nusoap.php');

/////////////////////// GLOBAL VARIABLES //////////////////////////////////

// Configuration section
$server_url = 'http://eris.cnb.uam.es/cgi-src/pdb_servlets/babel-ws.php?wsdl';
	
// set to 0 for no debug output, or select a debug level
$debug=0;


////////////////////////////////// START /////////////////////////////////

    if ( $debug == 1 ) echo "<HTML><BODY><H1>babel</H1>";

    // be nice: provide our own source code to others if requested
    $dnld=$_GET["download"];
    if (isset($dnld)) {
	header("Content-type: text/plain");
	header("Content-Disposition: inline; filename=babel.php");
    	$source = get_server_source($server_url);
	print $source;
	exit;
    }
    
    // provide bibliography references if requested
    $dnld=$_GET["bibliography"];
    if (isset($dnld)) {
	header("Content-type: text/plain");
	header("Content-Disposition: inline; filename=babel.txt");
    	$refs = get_server_references($server_url);
	// XXX JR XXX $source is an array, should be processed properly.
	// plus, we should add our own refs. here too.
    	foreach ($refs as $cite)
	    print "$cite\n";
	exit;
    }


    // produce usage info
    $use=$_GET["usage"];
    if (isset($use)) {
    	$use = get_server_usage($server_url);
	print $use;
	exit;
    }


    // get format options
    $iformat=$_GET["iformat"];
    $oformat=$_GET["oformat"];
    if ( (! isset($iformat)) || (! isset($oformat))) {
    	$use = get_server_usage($server_url);
	print $use;
	echo "<P>Please specify format options</P></BODY></HTML>";
	exit;
    }

    // get URL to convert into "./structure.in"
    // Retrieve URL value
    $data_url = $_GET["url"];
    if ( ! isset($data_url) ) 
    {
    	// if not set print usage notice
	$use = get_server_usage($server_url);
	print $use;
	echo "<BR><P>Please, submit the URL of the PDB file to process.</P>\n</BODY>\n</HTML>";
    	exit;
    }
    if ( $debug == 1 ) echo "<H1>Called with URL=$data_url</H1>";
    
    $converted = convert_url($server_url, $data_url, $iformat, $oformat);
    header("Content-type: chemical/x-$oformat");
    header("Content-Disposition: inline; filename=structure.$oformat");
    print $converted;    


exit;


/////////////////////////////// SUBROUTINES ////////////////////////////////

function get_server_usage($server_url)
{
    // Create the client instance
    $client = new soapclient($server_url, true);

    // Check for an error
    $err = $client->getError();
    if ($err) {
	// Display the error
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	// At this point, you know the call that follows will fail
    }

    if ($debug == 1) echo '<h2>Creating client proxy</h2>';
    // Create the proxy
    $proxy = $client->getProxy();

    // Call the SOAP method
    #$result = $proxy->source_code();
    $result = $proxy->usage();
    #$result = $proxy->bibliography();

    // Check for a fault
    if ($proxy->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
    } else {
	// Check for errors
	$err = $proxy->getError();
	if ($err) {
            // Display the error
            echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
    	    if ($debug == 1) {
            	// Display the result
            	echo '<h2>Result</h2><pre>';
            	print_r($result);
	    	echo '</pre>';
	    }
	    return $result;
	}
    }

    if ($debug == 1) {
	// Display the request and response
	echo '<h2>Request</h2>';
	echo '<pre>' . htmlspecialchars($proxy->request, ENT_QUOTES) . '</pre>';
	echo '<h2>Response</h2>';
	echo '<pre>' . htmlspecialchars($proxy->response, ENT_QUOTES) . '</pre>';
	// Display the debug messages
	echo '<h2>Debug</h2>';
	echo '<pre>' . htmlspecialchars($proxy->debug_str, ENT_QUOTES) . '</pre>';
    }
}

function get_server_source($server_url)
{
    // Create the client instance
    $client = new soapclient($server_url, true);

    // Check for an error
    $err = $client->getError();
    if ($err) {
	// Display the error
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	// At this point, you know the call that follows will fail
    }

    if ($debug == 1) echo '<h2>Creating client proxy</h2>';
    // Create the proxy
    $proxy = $client->getProxy();

    // Call the SOAP method
    $result = $proxy->source_code();
    #$result = $proxy->usage();
    #$result = $proxy->bibliography();

    // Check for a fault
    if ($proxy->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
    } else {
	// Check for errors
	$err = $proxy->getError();
	if ($err) {
            // Display the error
            echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
            if ($debug == 1) {
	    	// Display the result
            	echo '<h2>Result</h2><pre>';
            	print_r($result);
	    	echo '</pre>';
	    }
	    return $result;
	}
    }

    if ($debug == 1) {
	// Display the request and response
	echo '<h2>Request</h2>';
	echo '<pre>' . htmlspecialchars($proxy->request, ENT_QUOTES) . '</pre>';
	echo '<h2>Response</h2>';
	echo '<pre>' . htmlspecialchars($proxy->response, ENT_QUOTES) . '</pre>';
	// Display the debug messages
	echo '<h2>Debug</h2>';
	echo '<pre>' . htmlspecialchars($proxy->debug_str, ENT_QUOTES) . '</pre>';
    }
}

function get_server_references($server_url)
{
    // Create the client instance
    $client = new soapclient($server_url, true);

    // Check for an error
    $err = $client->getError();
    if ($err) {
	// Display the error
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	// At this point, you know the call that follows will fail
    }

    if ($debug == 1) echo '<h2>Creating client proxy</h2>';
    // Create the proxy
    $proxy = $client->getProxy();

    // Call the SOAP method
    #$result = $proxy->source_code();
    #$result = $proxy->usage();
    $result = $proxy->bibliography();

    // Check for a fault
    if ($proxy->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
    } else {
	// Check for errors
	$err = $proxy->getError();
	if ($err) {
            // Display the error
            echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
	    if ($debug == 1) {
            	// Display the result
            	echo '<h2>Result</h2><pre>';
            	print_r($result);
	    	echo '</pre>';
    	    }
	    return $result;
	}
    }

    if ($debug == 1) {
	// Display the request and response
	echo '<h2>Request</h2>';
	echo '<pre>' . htmlspecialchars($proxy->request, ENT_QUOTES) . '</pre>';
	echo '<h2>Response</h2>';
	echo '<pre>' . htmlspecialchars($proxy->response, ENT_QUOTES) . '</pre>';
	// Display the debug messages
	echo '<h2>Debug</h2>';
	echo '<pre>' . htmlspecialchars($proxy->debug_str, ENT_QUOTES) . '</pre>';
    }
}

function convert_url($server_url, $url, $from, $to)
{
    global $debug;
    
    // Create the client instance
    $client = new soapclient($server_url, true);

    // Check for an error
    $err = $client->getError();
    if ($err) {
	// Display the error
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	// At this point, you know the call that follows will fail
    }

    if ($debug == 1) echo '<h2>Creating client proxy</h2>';
    // Create the proxy
    $proxy = $client->getProxy();

    // Call the SOAP method
    $params = array(
    	'struct3Durl' => $url, 
	'inputFormat' => $from,
	'outputFormat' => $to);
    $result = $proxy->convert_url($params);

    // Check for a fault
    if ($proxy->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
    } else {
	// Check for errors
	$err = $proxy->getError();
	if ($err) {
            // Display the error
            echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
	    if ($debug == 1) {
            	// Display the result
            	echo '<h2>Result</h2><pre>';
            	print_r($result);
	    	echo '</pre>';
	    }
	    return $result;
	}
    }

    if ($debug == 1) {
	// Display the request and response
	echo '<h2>Request</h2>';
	echo '<pre>' . htmlspecialchars($proxy->request, ENT_QUOTES) . '</pre>';
	echo '<h2>Response</h2>';
	echo '<pre>' . htmlspecialchars($proxy->response, ENT_QUOTES) . '</pre>';
	// Display the debug messages
	echo '<h2>Debug</h2>';
	echo '<pre>' . htmlspecialchars($proxy->debug_str, ENT_QUOTES) . '</pre>';
    }
}
?>
