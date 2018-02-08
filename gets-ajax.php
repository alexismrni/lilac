<?php
/*
Lilac - A Nagios Configuration Tool
Copyright (C) 2007 Taylor Dondich

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/*
This page is required to get (in ajax) the command you need when you want to add a service on a host.
*/
require_once('includes/config.inc');
include("includes/lilac-conf.php");

$dsn = $conf['datasources']['lilac']['connection']['dsn'];
$dbuser = $conf['datasources']['lilac']['connection']['user'];
$dbpassword = $conf['datasources']['lilac']['connection']['password'];
$bdd = new PDO($dsn, $dbuser, $dbpassword);

require_once('NagiosResource.php');


$resourceCfg = NagiosResourcePeer::doSelectOne(new Criteria());
if(!$resourceCfg) {
	$resourceCfg = new NagiosResource();
	$resourceCfg->save();
}



	$sql = $bdd->query("SELECT * FROM nagios_command WHERE id = ".$_GET['id']."");

	$sql->setFetchMode(PDO::FETCH_BOTH);// Mode par dÃ©faut (tableau)

	$help = $sql->fetch();

	if(isset($_GET['id']) && isset($_GET['action'])) {

		if ($_GET['action'] == "help") {

				if ($help['help']==""){

					echo 'No help for this command. Use the "Nagios commands" page to configure.';

					} else {

							if ($help['typehelp']=="0"){
								//None
								echo 'Help is disabled. Use the "Nagios commands" page to configure.';
							}
							if ($help['typehelp']=="1"){
								//Command Line help
								$vowels = array("..", "~", "$", "%", "*", "&", ";", ">", "<", "!", "?", "(", "{", "[", "\"", "\'", "\`", "`", "|");
								$posthelpparsed = str_replace($vowels, "", $help['help']);
								$user1 = $resourceCfg->getUser1();
								system(''.$user1.'/'.$posthelpparsed.'', $retval);
								if ($retval == "127") { echo "Error with your help command: probably because your command doesn't exist."; } else {
									if ($retval == "126") { echo "Error with your help command: probably because there is a space before your command or you don't have the rights to run your script."; } else { echo $retval; }
									 }
							}
							if ($help['typehelp']=="2"){
								//Text Help
									echo $help['help'];
							}


					}


		} else {

			if ($_GET['action'] == "line") {

			echo $help['line'];
		} else {

				if ($_GET['action'] == "helptest") {

					$vowels = array("..", "~", "$", "%", "*", "&", ";", ">", "<", "!", "?", "(", "{", "[", "\"", "\'", "\`", "`", "|");
					$posthelpparsed = str_replace($vowels, "", $_GET['cmd']);
					$user1 = $resourceCfg->getUser1();
					system(''.$user1.'/'.$posthelpparsed.'', $retval);
					if ($retval == "127") { echo "Error with your help command: probably because your command doesn't exist."; } else {
						if ($retval == "126") { echo "Error with your help command: probably because there is a space before your command or you don't have the rights to run your script."; } else { echo $retval; }
						 }
				} else {
						echo "Forbidden";
				}
		}

	}


} else {
	header("Location: welcome.php");
	die();
}

		?>
