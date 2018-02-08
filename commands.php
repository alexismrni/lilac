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
Lilac Index Page, Displays Menu, and Statistics
*/
include_once('includes/config.inc');

// EoN_Actions
EoN_Actions_Process("Command");

include("includes/lilac-conf.php");
$dsn = $conf['datasources']['lilac']['connection']['dsn'];
$dbuser = $conf['datasources']['lilac']['connection']['user'];
$dbpassword = $conf['datasources']['lilac']['connection']['password'];
$bdd = new PDO($dsn, $dbuser, $dbpassword);

if(isset($_GET['command_id'])) {
	$command = NagiosCommandPeer::retrieveByPK($_GET['command_id']);
	if(!$command) {
		header("Location: welcome.php");
		die();
	}
}
	if(isset($command)) {
$sql = $bdd->query("SELECT * FROM nagios_command WHERE id = ".$_GET['command_id']."");
$sql->setFetchMode(PDO::FETCH_BOTH);// Mode par dÃ©faut (tableau)
$help = $sql->fetch();
}



// Action Handlers
if(isset($_GET['request'])) {
		if($_GET['request'] == "delete") {
			$command->delete();
			$success = "Command Deleted";
			unset($command);
		}
}

if(isset($_POST['request'])) {
	// Load Up The Session Data


	if($_POST['request'] == 'add_command') {
		// Error check for required fields
		if($_POST['command_manage']['command_name']=='' or $_POST['command_manage']['command_line']=='') {
			$error = "You must provide a command name and a command line.";
			$_GET['command_add'] = 1;
		}
		else {
			// Check for pre-existing command with same name
			if($lilac->command_exists($_POST['command_manage']['command_name'])) {
				$error = "A command with that name already exists!";
				$_GET['command_add'] = 1;
			}
			else {
				// All is well for error checking, add the command into the db.
				$lilac->add_command($_POST['command_manage']);

				if($_POST['cmdtypehelp'] == "none"){ $typehelp = "0"; $posthelp = $help['help'];}
				if($_POST['cmdtypehelp'] == "cmd"){ $typehelp = "1"; $posthelp = $_POST['command_man_cmd'];}
				if($_POST['cmdtypehelp'] == "text"){ $typehelp = "2"; $posthelp = $_POST['command_man_help'];}


				$vowels = array("..", "~", "$", "%", "*", "&", ";", ">", "<", "!", "?", "(", "{", "[", "\"", "\'", "\`", "`", "|");
				$posthelpparsed = str_replace($vowels, "", $posthelp);

				$bdd->query("UPDATE nagios_command SET help = '".$posthelpparsed."', typehelp = '".$typehelp."' WHERE id = '".$_GET['command_id']."'");
				// Remove session data
				unset($command);
				$success = "Command added.";
			}
		}
	}
	else if($_POST['request'] == 'modify_command') {
		$c = new Criteria();
		$c->add(NagiosCommandPeer::NAME, $_POST['command_manage']['command_name']);
		$c->setIgnoreCase(true);
		$c->add(NagiosCommandPeer::ID, $command->getId(), "!=");

		$duplicate = NagiosCommandPeer::doSelectOne($c);

		if($_POST['command_manage']['command_name']=='' or $_POST['command_manage']['command_line']=='') {
			$error = "You must provide a command name and a command line.";
			$_GET['command_add'] = 1;
		}
		else if($duplicate && $command->getId() != $duplicate->getId()) {
			$error = "A command with that name already exists!";
		}
		else {
			// All is well for error checking, modify the command.
			$command->updateFromArray($_POST['command_manage']);
			$command->save();
			if($_POST['cmdtypehelp'] == "none"){ $typehelp = "0"; $posthelp = $help['help'];}
			if($_POST['cmdtypehelp'] == "cmd"){ $typehelp = "1"; $posthelp = $_POST['command_man_cmd'];}
			if($_POST['cmdtypehelp'] == "text"){ $typehelp = "2"; $posthelp = $_POST['command_man_help'];}
			$vowels = array("..", "~", "$", "%", "*", "&", ";", ">", "<", "!", "?", "(", "{", "[", "\"", "\'", "\`", "`", "|");
			$posthelpparsed = str_replace($vowels, "", $posthelp);

			$bdd->query("UPDATE nagios_command SET help = '".$posthelpparsed."', typehelp = '".$typehelp."' WHERE id = '".$_GET['command_id']."'");
			$success = "Command modified.";
			unset($command);
		}
	}
}




// Get list of commands
$lilac->return_command_list($command_list);
$numOfCommands = count($command_list);

require_once('NagiosResource.php');

$resourceCfg = NagiosResourcePeer::doSelectOne(new Criteria());
if(!$resourceCfg) {
	$resourceCfg = new NagiosResource();
	$resourceCfg->save();
}

print_header("Nagios Command Editor");
?>

			<?php
	if(isset($command) || isset($_GET['command_add'])) {
		if(isset($command)) {

			print_window_header("Modify A Command", "100%");
			?>		<form name="command_form" method="post" action="commands.php?command_id=<?php echo $command->getId();?>"><?php

		}
		else {
			print_window_header("Add A Command", "100%");
			?>		<form name="command_form" method="post" action="commands.php"><?php
		}
		?>

			<?php
				if(isset($command))	{
					?>
					<input type="hidden" name="request" value="modify_command" />
					<input type="hidden" name="command_id" value="<?php echo $command->getId();?>">
					<?php
				}
				else {
					?>
					<input type="hidden" name="request" value="add_command" />
					<?php
				}
			?>
			<b>Command Name:</b><br />
			<input type="text" size="40" name="command_manage[command_name]" value="<?php echo isset($command) ? $command->getName() : '';?>">
			<?php echo $lilac->element_desc("command_name", "nagios_commands_desc"); ?><br />
			<br />
			<b>Command Line:</b><br />
			<input type="text" size="100" name="command_manage[command_line]" value="<?php echo isset($command) ? htmlentities($command->getLine()) : '';?>">
			<?php echo $lilac->element_desc("command_line", "nagios_commands_desc"); ?><br />
			<br />
			<b>Command Description:</b><br />
			<input type="text" size="100" name="command_manage[command_desc]" value="<?php echo isset($command) ? $command->getDescription(): '';?>">
			<?php echo $lilac->element_desc("command_desc", "nagios_commands_desc"); ?><br />
			<br />
			<b>Command Type Help:</b><br />
			<select id="cmdtypehelp" name="cmdtypehelp">
	  	<option value="none" <?php if(isset($command)){ if($help['typehelp'] == "0"){ echo "selected";} } ?>>None</option>
	  	<option value="cmd" <?php if(isset($command)){ if($help['typehelp'] == "1"){ echo "selected";} } ?> >Command line</option>
	  	<option value="text" <?php if(isset($command)){ if($help['typehelp'] == "2"){ echo "selected";} } ?>>Text</option>

			</select>
			<?php echo $lilac->element_desc("command_select_help", "nagios_commands_desc"); ?><br />
			<br /><div class="textarea" style=" display: none;">
				<b>Command Help:</b><br />
				<div class="srveonplugins" style=" display: none;"><?php echo $resourceCfg->getUser1();?>/ <input type="text" size="100" name="command_man_cmd" class="command_man_cmd" value="<?php echo isset($command) ? $help['help']: ''; ?>"><?php echo $lilac->element_desc("command_cmd_help", "nagios_commands_desc"); ?>
					<br /><button type="button" class="btn btn-warning" name="runcmd" id="runcmd"><i class="fa fa-terminal" aria-hidden="true"></i> Tester la commande</button>
						<div class="outputcmd" style=" display: none;"><div class="outputcmd2"></div><textarea id="runcmd" name="runcmd" rows="10" cols="110" readonly></textarea></div>

				</div>
				<div class="textareahelp" style=" display: none;"><textarea id="command_man_help" class="command_man_help" name="command_man_help"
   		rows="10" cols="50"><?php echo isset($command) ? $help['help']: ''; ?></textarea><?php echo $lilac->element_desc("command_help", "nagios_commands_desc"); ?></div></div>
			<br />
			<br />
			<br />
			<?php
				if(isset($command)) {
					?>
					<a class="btn btn-danger" href="commands.php?command_id=<?php echo $command->getId();?>&request=delete">Delete</a> <input class="btn btn-primary" type="submit" value="Modify Command" /> <a class="btn btn-default" href="commands.php">Cancel</a>
					<?php
				}
				else {
					?>
					<input class="btn btn-primary" type="submit" value="Create Command" /> <a class="btn btn-default" href="commands.php">Cancel</a>
					<?php
				}
			?>
			<br /><br />
		<?php
		print_window_footer();
	}
	else {
		print_window_header("Nagios Commands", "100%");
		?>
		<a class="sublink btn btn-success" href="commands.php?command_add=1">Add A New Command</a><br />
		<?php
		if($numOfCommands) {
			?>
			<br />
			<form name="EoN_Actions_Form" method="post">
		        <?php echo EoN_Actions("Command");?>
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Command Name</td>
			<td>Command Description</td>
			<td align="center"><a href="#" onClick="checkUncheckAll('EoN_Actions_Checks_Command');">ALL</a></td>
			</tr>
			<?php
			for($counter = 0; $counter < $numOfCommands; $counter++) {
				if($counter % 2) {
					?>
					<tr class="altRow1" id="line<?php echo $counter?>">
					<?php
				}
				else {
					?>
					<tr class="altRow2" id="line<?php echo $counter?>">
					<?php
				}
				?>
				<td height="20" class="altLeft" onclick="checkLine('line<?php echo $counter?>','check<?php echo $counter?>');">&nbsp;<a href="commands.php?command_id=<?php echo $command_list[$counter]->getId();?>"><?php echo $command_list[$counter]->getName();?></a></td>
				<td height="20" class="altLeft" onclick="checkLine('line<?php echo $counter?>','check<?php echo $counter?>');">&nbsp;<?php echo $command_list[$counter]->getDescription();?></td>
				<td align="center"><input type="checkbox" id="check<?php echo $counter?>" class="checkbox" name="EoN_Actions_Checks_Command[]" value="<?php echo $command_list[$counter]->getId();?>" onclick="checkBox('line<?php echo $counter?>','check<?php echo $counter?>');"></td>
				</tr>
				<?php
			}
			?>
			</table>
			</form>
			<?php

		}
		else {
			?>
			<br />
			<div class="statusmsg">No Commands Exist</div>

			<?php
		}
		print_window_footer();
	}

print_footer();
?>
