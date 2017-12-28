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
include("includes/lilac-conf.php");

$dsn = $conf['datasources']['lilac']['connection']['dsn'];
$dbuser = $conf['datasources']['lilac']['connection']['user'];
$dbpassword = $conf['datasources']['lilac']['connection']['password'];
$bdd = new PDO($dsn, $dbuser, $dbpassword);





	$sql = $bdd->query("SELECT * FROM nagios_command WHERE id = ".$_GET['id']."");

	$sql->setFetchMode(PDO::FETCH_BOTH);// Mode par défaut (tableau)

	$help = $sql->fetch();

	if(isset($_GET['id']) && isset($_GET['action'])) {

		if ($_GET['action'] == "help") {

				if ($help['help']==""){ echo 'No help for this command. You can, configure on the "Nagios commands" page.'; } else {
					echo $help['help'];
				}


		} else {

			if ($_GET['action'] == "line") {

			echo $help['line'];
		} else {
			echo "Action interdite";
		}

	}







} else {
	header("Location: welcome.php");
	die();
}










		?>
