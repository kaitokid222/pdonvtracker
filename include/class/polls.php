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

/*
\\	polls	id			int pk
//			added		date
\\			question	varchar
//			answers		text
\\						(json-string {0:"Antwort 1",1:"Antwort 2"})
\\						$string = json_encode($array);
//						$array = json_decode($string, true);
*/

/*
\\	pollanswers	pollid (polls.id)
//				answers text
\\						(json-string {polls.answers.id:users.ids,polls.answers.id:users.ids})
*/

class polls
{
	private $con;
	private $onlyLast;

	public $data = array();
	

	public function __construct($last = true) {
		$this->con = $GLOBALS['DB'];
		$this->onlyLast = $last;
	}

	public function getData(){
		if($this->onlyLast)
			$qry = $this->con->prepare('SELECT polls.*, pollanswers.answers as result FROM polls LEFT JOIN pollanswers ON polls.id = pollanswers.pollid ORDER BY added DESC LIMIT 1');
		else
			$qry = $this->con->prepare('SELECT polls.*, pollanswers.answers as result FROM polls LEFT JOIN pollanswers ON polls.id = pollanswers.pollid ORDER BY added DESC');
		$qry->execute();
		if($qry->rowCount() > 0){
			$alldata = $qry->FetchAll();
			$pretty_data = array();
			foreach($alldata as $row){
				$pretty_data[$row['id']]['id'] = $row['id'];
				$pretty_data[$row['id']]['added'] = $row['added'];
				$pretty_data[$row['id']]['question'] = $row['question'];
				$pretty_data[$row['id']]['answers'] = json_decode($row['answers'], true);
				$pretty_data[$row['id']]['result'] = json_decode($row['result'], true);
			}
			$this->data = $pretty_data;
		}
	}	
}
?>