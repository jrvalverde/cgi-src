<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *  Utility functions
 *
 *  PHP version 4 and up
 *
 * LICENSE:
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package	RBGMDB
 * @author 	José R. Valverde <jrvalverde@acm.org>
 * @copyright 	José R. Valverde <jrvalverde@acm.org>
 * @license	c/lgpl.txt
 * @version	$Id$
 * @link	http://www.es.embnet.org/Services/MolBio/rbgmdb/
 * @see		utils.php config.inc
 * @since	File available since Release 1.0
 */


/**
 * show an error and terminate
 */
function show_error()
{
    if (mysql_error())
    	die("Error" . mysql_errno() . " : " . mysql_error());
    else
    	die("Could not connect to DBMS");
}

/**
 * secure user data by escaping characters and cutting the string
 */
function clean($input, $maxlength)
{
    $input = substr($input, 0, $maxlength);
    $input = EscapeShellCmd($input);
    return ( $input );
}

/**
 * check an e-mail address for validity
 *
 * Simple version
 */
function check_e_mail($email) {
    if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/" , $email)){
    	list($username,$domain)=split('@',$email);
	// check if the domain has an MX record in DNS
    	if(!getmxrr ($domain,$mxhosts)){
    	    return false;
    	}
    	return true;
    }
    return false;
}

/**
 * check an e-mail address for validity
 * more complex version
 */
function check_email($email) {
    // checks proper syntax
    if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/" , $email)) {
    	// gets domain name
    	list($username,$domain)=split('@',$email);
    	// check for its MX records in the DNS and
	// attempts a socket connection to mail server
	//  NOTE: if the mail server is not available, yet valid,
	//  we may reject a valid e-mail address!
	$mxhosts = array();
    	if(!getmxrr($domain, $mxhosts)) {
    	    // no mx records, we have to check domain
    	    if (!fsockopen($domain,25,$errno,$errstr,30)) {
    	    	return false;
    	    } else {
    	    	return true;
    	    }
    	} else {
    	    // mx records found
    	    foreach ($mxhosts as $host) {
    	    	if (fsockopen($host,25,$errno,$errstr,30)) {
    	    	    return true;
    	    	}
    	    }
    	    return false;
    	}
	
    	if(!fsockopen($domain,25,$errno,$errstr,30)) {
    	    return false;
    	}
    	return true;
    }
    return false;
}


/**
 * show a copyright notice in an output HTML page
 */
function show_copyright()
{
    echo "\n<hr>\n" .
         "<table>\n<tr>\n" .
         "<td align=\"left\">Data compilation &copy; Angel Pesta&ntilde;a, 2005</td>\n" .
         "<td align=\"right\">Please cite (manuscript in preparation) to reference this database</td>\n" .
        "</tr>\n</table>\n";
}

/**
 *  Notify the user's browser of the kind of data we are about to send
 *
 * We use a special header to indicate the type of report we are generating.
 *
 *  @param string $format   the interchange format of the report
 */
function show_export_header($format)
{
    header("Content-type: text/$format\nContent-Disposition: inline; filename=report.$format\n");
}

/**
 * Show page header
 *
 * We use a special header with the style and JavaScrip code needed to
 * enhance the user experience.
 */
function show_header()
{
    echo "<head>\n";
    echo "\t<title>RBGMDB Query results</title>\n";
    $showtips = file_get_contents("js/showtips_inline.js");
    echo $showtips;
    echo "</head>\n";
}

/**
 * show an HTML footer for a typical result page
 */
function show_footer()
{
    echo "</body></html>";
}

?>
