#!/usr/local/bin/perl -w
# grid_session.pl
#	Control access to the Grid using sessions
#
#	New users are served with a login form to enter their auth data.
# After this data is collected, a new session is created and the unique
# session ID used to encrypt and maintain user data locally. Every access
# afterwards will notice the session is OK and proceed to do the real work.
#
#	Notes: a provision should be added to cancel/delete sessions on the
# application
#
#	Security Note: the grid passphrase is not needed once the session
# has started. It needs not be saved locally.
#
#	Security Note: This program uses SSH.sh to connect to the Grid UI,
# look into SSH.sh for more details.
#
#	Security Note: the key to encrypted data is the session ID. This is
# a weakness, as a root user with access to the session information might
# decrypt all user data. OTOH a malign root user can't be trusted not to
# tamper with any application, so...
#
#	Security Note: a solution might be to use an MD5 hash of the user
# data as the key and store it on a cookie in the user browser, keeping all
# user data locally encrypted in a traslucent database indexes by user name
# instead of session ID/key. The key is safe on the browser as it is an MD5 
# hash, and is not available to local root -unless he decides to tamper with 
# this application. Using a cookie however requires us to mimic all session-
# management implemented by CGI::Session ourselves and given so we might 
# just do without CGI::Session altogether. An attempt at an early 
# implementation is in grid_traslucent.pl
#
# Copyright 2007 Jose R. Valverde <jrvalverde@es.embnet.org>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
# 

#
use CGI qw(:standard);
use CGI::Session;
use Digest::MD5 qw(md5 md5_hex);
use Crypt::CBC;

use strict;



# Set cookie name volva
my $cookie_name = "grid-gui";

# UI host
my $h = "villon.cnb.uam.es";
# VO to use
my $vo = "biomed";

# set the name for the session cookie
CGI::Session->name("EMBOSS-GRID-UI");	# The name of the session cookie

#use vars qw( global1, global2, global3);
my $session;		# The session where we'll store all info
my $data;		# User auth data (separated by '\n')
my $key;		# MD5 hash of $data used to encrypt it and as session ID
my $cookie;		# Session cookie (set manually to be valid clusterwise)
my $ui_user;		# Username on the Grid UI node
my $ui_pass;		# Password for the Grid UI node
my $grid_passp;		# Passphrase to unlock Grid access certificate

# find out if a session already exists
$session = CGI::Session->load() or die CGI::Session->errstr();

if ( $session->is_empty ) {
	# Check if there is any form data available. If not, we need to
	# print the form.

	# if we didn't get any username from the form then we need to print out the form
	unless (param("user")) {
		print_login_form();
		exit;		# never reached
	}

	# Otherwise we need to get the data, validate it and start a new session
	init_new_session();
	# drop down to normal processing
}

# OK, we are ready to work.

# Grab user data:
retrieve_user_data($session, $ui_user, $ui_pass, $grid_passp);

print header(), start_html('EMBOSS-GUI'), h1("Welcome $ui_user"), p($ui_pass), p($grid_passp);

exit;


# ask for user authentication
# NOTE should use styles.
sub print_login_form {
    print header(), start_html("User ID"), 
	  h1("Welcome to Grid EMBOSS Explorer"),
	  h3("Please, identify yourself"),
	  p("To use EMBOSS-Explorer on the Grid you need a valid Grid \
	     identity (as proven by an approved certificate) on our \
	     Grid access point (user interface node)"),
          hr(), 
	  start_form(),
            p("Please enter your username on the UI: ", 
              textfield("user", "guest")),
            p("Please enter your password on the UI: ", 
              password_field("password", "secret")),
            p("Please enter your passphrase for the Grid: ", 
              password_field("passphrase", "very long secret passphrase")),
            submit(-NAME => "Go ahead!", -VALUE => shift),
          end_form(), 
	  hr();
    exit;
}

sub relogin_bad_input {
    my $err = shift;
    print header(),
	  start_html("User ID"), h1("Please, identify yourself"),
          hr(), 
	  start_form(),
	    p("BADINPUT:".$err.\
	      ": We have found a problem with your authentication information: \
	       either it is not valid (you may have made a mistake typing the password \
	       or the passphrase) or it is incomplete (make sure you filled in all the \
	       fields as all of them are mandatory) or it has expired or something's \
	       badly broken."),
            p("Please enter your username on the UI: ", 
              textfield("user", "guest")),
            p("Please enter your password on the UI: ", 
              password_field("password", "secret")),
            p("Please enter your passphrase for the Grid: ", 
              password_field("passphrase", "very long secret passphrase")),
            submit(-NAME => "Go ahead!", -VALUE => shift),
          end_form(), 
	  hr();
    exit;
}

sub relogin_invalid_credentials {
    print header(),
	  start_html("User ID"), h1("Please, identify yourself"),
          hr(), 
	  start_form(),
	    p("INVALIDCREDS: We have found a problem with your authentication information: \
	       we have tried to activate a Grid session for your work using the \
               data you provided but it failed. Either your data is wrong (maybe \
	       a typo in the password or passphrase) or there are communication \
	       problems or something is badly broken."),
            p("Please enter your username on the UI: ", 
              textfield("user", "guest")),
            p("Please enter your password on the UI: ", 
              password_field("password", "secret")),
            p("Please enter your passphrase for the Grid: ", 
              password_field("passphrase", "very long secret passphrase")),
            submit(-NAME => "Go ahead!", -VALUE => shift),
          end_form(), 
	  hr();
    exit;
}

sub init_new_session {
    # First try to get the user info from the filled in form
    #	ensure username is a single word
    my @fuwords = split('\b', param("user"));
    $ui_user = $fuwords[0] || relogin_bad_input("USER");
    $ui_pass = param("password") || relogin_bad_input("PASSWORD");
    $grid_passp = param("passphrase") || relogin_bad_input("PASSPHRASE");

    # verify the data is valid by initializing the Grid
    unless (initialize_grid($ui_user, $ui_pass, $grid_passp)) {
	#print "Content-type: text/text\n\nGrid initialization failed\n";
	relogin_invalid_credentials();
    }
    #print "Content-type: text/text\n\nGrid initialization succeeded\n";

    # data seems OK, go on and set up a session

    # create new session if it does not exist
    $session = new CGI::Session() or die CGI::Session->errstr;
    $session->expire("+2h");

    # first time: set session cookie
    # print $session->header;			# commented as I don't know how to widen cookie scope
    # We need to set the domain since we are on an HA balanced cluster and
    # we don't know which machine will be accessed next (and will request
    # the cookie).
    $cookie = cookie( -NAME   => $session->name(),
                      -VALUE  => $session->id(),
            	      -EXPIRES => "+2h",
	              -DOMAIN => "cnb.uam.es");

    print header( -COOKIE =>$cookie );

    # We set the data using '\n' as the field separator since we know it is
    # not allowed in usernames nor passwords/passphrases. Furthermore,
    # should we get a value tweaked to include it, it will result in an additional
    # line and since we'll only use the first three lines anyhow, any exceeding 
    # data will be ignored and all logins will fail.
    $data = $ui_user."\n".$ui_pass."\n".$grid_passp."\n(C) Jose R. Valverde, 2007";
    $key = join('',pack 'H*',$session->id());

    # Create cypher object and encrypt user info (we don't trust nobody, no sir!)
    my $cipher = Crypt::CBC->new( -key => $key,
			      -cipher => 'Blowfish'
			   );

    my $user_data = $cipher->encrypt_hex($data);

    # And now store the user data on the session database
    $session->param("data", $user_data);    

    # we are done. Let the user know and go ahead.
    #print "Content-type: text/text\n\nSession started\n".$session->id()."\n$key\n\n$data\n\n$user_data"; exit;

}

sub initialize_grid {
    my ($ui_user, $ui_pass, $grid_passp) = @_;

    open (GRID_UI, "|./SSH.sh $ui_user\@$h > .out 2>&1");
#    open (GRID_UI, 
#    "| ssh -x -t -t $ui_user\@$h /opt/glite/bin/voms-proxy-init --voms=$vo > .out 2>&1");
#
    print GRID_UI <<END_PROXY_CMD;
$ui_pass
/opt/glite/bin/voms-proxy-init --voms=$vo
$grid_passp
END_PROXY_CMD

    if (close(GRID_UI)) {
	return 1;
    } else {
	return 0;
    }
    my $st = close(GRID_UI);	# $st = 1 on success, 0 or false or undef on failure
    print "Content-type: text/text\n\nST = $st\n? = $?\n! = $!\n"; exit;
    return $?;			# $? should behave the same
}

sub retrieve_user_data {
    my $s = $_[0];
    my $key = join('', pack 'H*', $s->id());
    my $user_data = $s->param("data");

    # Create cypher object
    my $cipher = Crypt::CBC->new( -key => $key,
			          -cipher => 'Blowfish'
			   	);
    # decrypt user data
    $data = $cipher->decrypt_hex($user_data);
    # verify data has been correctly decrypted
    #print "Content-type: text/text\n\nRecovering data\n\n$key\n".$s->id()."\n$data\n\n$user_data"; exit;

    # Process encrypted user_data
    my @uinfo;
    my @uwords;

    @uinfo = split('\n', $data);
    # THESE SHOULD NEVER FAIL AS WE ONLY STORE VALIDATED DATA IN THE SESSION
    $_[1] = $uinfo[0];
    $_[2]   = $uinfo[1];
    $_[3] = $uinfo[2];

}




