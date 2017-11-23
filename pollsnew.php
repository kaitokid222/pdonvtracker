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
if (get_user_class() < UC_ADMINISTRATOR)
	stderr("Error", "Access denied.");

if (get_user_class() >= UC_MODERATOR){
    $mod_str = "<font class=middle>".
    " - [<a class=altlink href=" . $_SERVER['PHP_SELF'] . "?action=create><b>Neu</b></a>]\n".
    " - [<a class=altlink href=" . $_SERVER['PHP_SELF'] . "?action=edit&pollid=id&returnto=main><b>&Auml;ndern</b></a>]\n".
    " - [<a class=altlink href=" . $_SERVER['PHP_SELF'] . "?action=delete&pollid=id&returnto=main><b>L&ouml;schen</b></a>]".
    "</font>";
}else
	$mod_str = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
}
stdhead("Umfrageverwaltung");
//begin_frame("Umfragen", FALSE, "100%");
//begin_table(TRUE); 
$polls = new polls();
$polls->getData();
$latest = $polls->data;

foreach($latest as $p){
	$poll = $p;
}
$check = $polls->has_answered($poll['id'],$CURUSER['id']);
$tvotes = $polls->get_answer_count($poll['id']);
echo "<table cellspacing=\"5\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
	"    <tr>\n".
	"        <td valign=\"top\" width=\"50%\">\n".
	"            <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
	"                <tr class=\"tabletitle\" width=\"100%\">\n".
	"                    <td colspan=\"10\" width=\"100%\"><span class=\"normalfont\">\n".
	"                        <center><b> Aktuelle Umfrage " . $mod_str . "</b></center></span>\n".
	"                    </td>\n".
	"                </tr>\n".
	"                <tr>\n".
	"                    <td width=\"100%\" class=\"tablea\">\n".
	"                    <p align=center><b>" . $poll['question'] . "</b></p>\n";
if($check){
	echo "<center><table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n";
	foreach($poll['result'] as $questionid => $users){
		if($users[0] == "")
			$count = 0;
		else
			$count = count($users);
		if($count == 0)
			$p = 0;
		else
			$p = round($count / $tvotes * 100);

		echo "<tr>".
			"    <td nowrap align=\"left\">" . $poll['answers'][$questionid] . "&nbsp;&nbsp;</td>".
			"    <td align=\"left\">".
			"        <img src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/vote_left" . (($questionid%5)+1) . ".gif\">".
			"        <img src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/vote_middle" . (($questionid%5)+1) . ".gif\" height=9 width=" . (($p * 5)+1) .">".
			"        <img src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/vote_right" . (($questionid%5)+1) . ".gif\"> " . $p . "%".
			"    </td>".
			"</tr>\n";
	}
	
	
	echo "<p align=\"center\">Abgebene Stimmen: " . $tvotes . "</p>\n";
	echo "</table></center>\n";
}else{
	echo "<form method=\"post\" action=\"index.php?action=vote\"><center>\n";
	foreach($poll['answers'] as $aid => $a){
		echo "<input type=radio name=choice value=" . $aid . ">" . $a . "<br>\n";
	}
	echo "<br><p align=\"center\"><input type=\"submit\" value=\"'Vote!'\" class=\"btn\"></p></center>\n";
}
if ($check)
    echo "<p align=center><a href=\"polls.php\">Ältere Umfragen</a></p>\n";
echo "        </td>\n".
	"    </tr>\n".
	"</table>\n";

/*foreach($pollsnew->data as $poll){
	tr("id", $poll['id'], 1);
	tr("added", $poll['added'], 1);
	tr("frage", $poll['question'], 1);
	/*foreach ($poll['answers'] as $key => $value){
		tr("antwort " . $key, $value, 1);
	}*/
	/*foreach($poll['result'] as $questionid => $users){
		$c = count($users);
		if($users[0] == "")
			$c = 0;
		$u = implode(", ", $users);*/
		//foreach($users as $key => $value){
			// die einzelne uid
			// $uid = $value;
		//}
		//tr($poll['answers'][$questionid] . " (" . $questionid . ")", $c . " Stimmen Nutzerids : " . $u, 1);
	//}
	// endBlock
	/*tr("Optionen", "<a href=\"" . $_SERVER['PHP_SELF'] . "?delete=" . $poll['id'] . "\">Diese Umfrage löschen</a><br>
		<a href=\"" . $_SERVER['PHP_SELF'] . "?wipevotes=" . $poll['id'] . "\">Alle Antworten löschen</a>
	", 1);
	tr("", "", 1);
}*/
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
//end_table();
//end_frame();
stdfoot();
?>