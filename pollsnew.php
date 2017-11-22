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

if ($_SERVER["REQUEST_METHOD"] == "POST"){
}
stdhead("Umfrageverwaltung");
begin_frame("Umfragen", FALSE, "100%");
begin_table(TRUE); 
$pollsnew = new polls(false);
$pollsnew->getData();
//$test = $pollsnew->has_answered(14,111);
//$arr = var_dump($pollsnew->data);
foreach($pollsnew->data as $poll){
	tr("id", $poll['id'], 1);
	tr("added", $poll['added'], 1);
	tr("frage", $poll['question'], 1);
	/*foreach ($poll['answers'] as $key => $value){
		tr("antwort " . $key, $value, 1);
	}*/
	foreach($poll['result'] as $questionid => $users){
		$c = count($users);
		$u = implode(", ", $users);
		//foreach($users as $key => $value){
			// die einzelne uid
			// $uid = $value;
		//}
		tr($poll['answers'][$questionid] . " (" . $questionid . ")", $c . " Stimmen Nutzerids : " . $u, 1);
	}
	// endBlock
	tr("Optionen", "<a href=\"" . $_SERVER['PHP_SELF'] . "?delete=" . $poll['id'] . "\">Diese Umfrage löschen</a><br>
		<a href=\"" . $_SERVER['PHP_SELF'] . "?wipevotes=" . $poll['id'] . "\">Alle Antworten löschen</a>
	", 1);
	tr("", "", 1);
}
	// EXPERIMENT!
	////////
	$frage = "neuste testfrage 10 antworten";
	/*$antwort = array();
	for ($x = 0; $x <= 9; $x++) {
		$antwort[] = "NEUE Antwort Nr. " . $x;
	} 
	$pollsnew->add_poll($frage,$antwort);*/
	/*$pollsnew->add_answer(18,0,1);
	$pollsnew->add_answer(18,0,2);
	$pollsnew->add_answer(18,0,3);
	$pollsnew->add_answer(18,0,4);
	$pollsnew->add_answer(18,0,5);
	$pollsnew->add_answer(18,1,6);
	$pollsnew->add_answer(18,1,7);
	$pollsnew->add_answer(18,1,8);
	$pollsnew->add_answer(18,1,9);
	$pollsnew->add_answer(18,1,10);
	$pollsnew->add_answer(18,2,11);
	$pollsnew->add_answer(18,2,12);
	$pollsnew->add_answer(18,2,13);
	$pollsnew->add_answer(18,3,14);
	$pollsnew->add_answer(18,3,15);
	$pollsnew->add_answer(18,3,16);*/
	
	////////
end_table();
end_frame();
stdfoot();
?>