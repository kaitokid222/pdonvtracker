<?php

/*
// +--------------------------------------------------------------------------+
// | Project:    pdonvtracker - NetVision BitTorrent Tracker 2017             |
// +--------------------------------------------------------------------------+
// | This file is part of pdonvtracker. NVTracker is based on BTSource,       |
// | originally by RedBeard of TorrentBits, extensively modified by           |
// | Gartenzwerg.                                                             |
// +--------------------------------------------------------------------------+
// | Obige Zeilen dürfen nicht entfernt werden!    Do not remove above lines! |
// +--------------------------------------------------------------------------+
 */

require "include/bittorrent.php";
userlogin();
loggedinorreturn();
$polls = new polls(false);
$polls->getData();
//if (get_user_class() < UC_ADMINISTRATOR)
//	stderr("Error", "Access denied.");

if(isset($_GET['action']))
	$action = $_GET['action'];
else
	$action = "view";

switch ($action) {
	case "view":
		echo "";
		break;
	case "edit":
		echo "";
		break;
	case "vote":
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			if(isset($_POST["choice"], $_POST["userid"], $_POST["pollid"])){
				if(!$polls->has_answered($_POST["pollid"],$_POST["userid"])){
					$polls->add_answer($_POST["pollid"],$_POST["choice"],$_POST["userid"]);
					header("Refresh:0; url=". $_SERVER['PHP_SELF'] . "");
				}else
					stderr("Error", "Du hast schon abgestimmt!");
			}else
				stderr("Error", "Es scheinen Daten zu fehlen!");
		}else
			stderr("Error", "Diese Seite darf so nicht aufgerufen werden!");
		break;
	case "revoke":
		if(isset($_GET['pollid'])){
			if($polls->has_answered($_GET['pollid'],$CURUSER['id'])){
				$polls->delete_answer($_GET['pollid'],$CURUSER['id']);
				header("Refresh:0; url=". $_SERVER['PHP_SELF'] . "");
			}else
				stderr("Error", "Du hast keine Stimme abgegeben, die du zurück ziehen könntest!");
		}else
			stderr("Error", "Es scheinen Daten zu fehlen!");
		break;
	case "":
		echo "";
		break;
	case "":
		echo "";
		break;
}

if ($_SERVER["REQUEST_METHOD"] == "POST"){
	
}
stdhead("Umfrageverwaltung");
if($action == "view"){
	begin_frame("Umfragen", FALSE, "100%");
	begin_table(TRUE); 
	$all_polls = $polls->data;
	foreach($all_polls as $poll){
		$tvotes = $polls->get_answer_count($poll['id']);
		echo "<table cellspacing=\"5\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
			"    <tr>\n".
			"        <td valign=\"top\" width=\"50%\">\n".
			"            <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
			"                <tr class=\"tabletitle\" width=\"100%\">\n".
			"                    <td colspan=\"10\" width=\"100%\"><span class=\"normalfont\">\n".
			"                        <b> Umfrage Nr. " . $poll['id'] . " - " . date("Y-m-d",strtotime($poll['added'])) . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($poll["added"]))) . " ago)" . "</b></span>\n".
			"                    </td>\n".
			"                </tr>\n".
			"                <tr>\n".
			"                    <td width=\"100%\" class=\"tablea\">\n".
			"                    <p><b>Frage: " . $poll['question'] . "</b></p>\n".
			"                    </td>".
			"                </tr>".
			"                <tr>\n".
			"                    <td width=\"100%\" class=\"tablea\">\n".
			"";
		foreach($poll['answers'] as $k => $a){
			if($poll['result'][$k][0] != "")
				$c = count($poll['result'][$k]);
			else
				$c = 0;

			if($tvotes != 0)
				$p = round($c / $tvotes * 100);
			else
				$p = 0;

			echo "                    <p><b>" . $a . " - " . $c . " Stimmen - " . $p . "%</b></p>\n";
		}
		echo "                    </td>".
			"                </tr>".
			"                <tr>\n".
			"                    <td width=\"100%\" class=\"tablea\">\n".
			"                    <p><b>Optionen:<br>".
			"                        <a href=\"" . $_SERVER['PHP_SELF'] . "?delete=" . $poll['id'] . "\">Diese Umfrage löschen</a><br>\n".
			"                        <a href=\"" . $_SERVER['PHP_SELF'] . "?wipevotes=" . $poll['id'] . "\">Alle Antworten löschen</a><br>\n".
			"                        <a href=\"" . $_SERVER['PHP_SELF'] . "?edit=" . $poll['id'] . "\">Diese Umfrage bearbeiten</a\n>".
			"                    </b></p>\n".
			"                    </td>\n".
			"                </tr>\n".
			"            </table>\n".
			"        </td>\n".
			"    </tr>\n".
			"</table>\n";
	}
		// EXPERIMENT!
		////////
		/*$frage = "neuste testfrage 3 antworten";
		$antwort = array();
		for ($x = 0; $x <= 2; $x++) {
			$antwort[] = "NEUE Antwort Nr. " . $x;
		} 
		$pollsnew->add_poll($frage,$antwort);*/
		
		//$pollsnew->add_answer(19,1,4);
		//$pollsnew->add_answer(20,0,2);
		//$pollsnew->add_answer(20,0,3);
		//$pollsnew->delete_answer(20,3);
		
		////////
	end_table();
	end_frame();
}
stdfoot();
?>