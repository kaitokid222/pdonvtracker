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

$a = array();
$a[] = "";
$a[] = "";
$a[] = "";
$a[] = "";
function clean_answers_array($answers){
		$c = count($answers);
		$answer_arr = array();
		$i = 0;
		while($i < $c){
			$answer_arr[$i] = NULL;
			$i++;
		}
		$empty_array = array_map(function($value){
			return $value === NULL ? "" : $value;
		}, $answer_arr);
		return $empty_array;
	}
var_dump($a);

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
		if($users[0] == "")
			$c = 0;
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
stdfoot();
?>