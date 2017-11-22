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
\\						(json-string {"0":"Antwort 1","1":"Antwort 2"})
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
	
	public $tmp = array();
	

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
				foreach($pretty_data[$row['id']]['result'] as &$answer_str){
					$tmp = explode(";",$answer_str);
					$answer_str = array();
					$answer_str = $tmp;
				}
			}
			$this->data = $pretty_data;
		}
	}

	public function has_answered($poll,$id){
		foreach($this->data[$poll]['result'] as $arr){
			if(in_array($id, $arr)){
				return true;
				break;
			}
		}
		return false;
	}
	
	public function add_poll($question,$answers){
		if(!isset($question,$answers) || $question == "" || !is_array($answers) || $answers[0] == "" || $answers[1] == "")
			return false;
		$now = date("Y-m-d H:i:s");
		$answers_str = json_encode($answers, JSON_FORCE_OBJECT);
		$qry = $this->con->prepare('INSERT INTO polls (id, added, question, answers) VALUES (NULL,:date,:question,:answers)');
		$qry->bindParam(':date', $now, PDO::PARAM_STR);
		$qry->bindParam(':question', $question, PDO::PARAM_STR);
		$qry->bindParam(':answers', $answers_str, PDO::PARAM_STR);
		$qry->execute();
		// struktur sichern
		// bestimmt nicht die schönste art.
		// GOOGLE insert into multiple tables in one query
		$pollid = $this->con->lastInsertId();
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
		$answers_str = json_encode($empty_array, JSON_FORCE_OBJECT);
		$qry = $this->con->prepare('INSERT INTO pollanswers (pollid, answers) VALUES (:pollid,:answers)');
		$qry->bindParam(':pollid', $pollid, PDO::PARAM_INT);
		$qry->bindParam(':answers', $answers_str, PDO::PARAM_STR);
		$qry->execute();
		return true;
	}

	public function delete_poll($pollid){
		$qry = $this->con->prepare('DELETE polls, pollanswers FROM polls INNER JOIN pollanswers ON pollanswers.pollid = polls.id WHERE polls.id = :id');
		$qry->bindParam(':id', $pollid, PDO::PARAM_INT);
		$qry->execute();
	}
	
	public function edit_poll(){
	
	}
	
	public function delete_answer(){ // eine Antwort
	
	}
	
	public function delete_answers(){ // alle antworten einer umfrage
	
	}

	public function add_answer(){
		
	}

	public function edit_answer(){
	
	}
}
?>