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
\\	polls		id			int pk
//				added		date
\\				question	varchar
//				answers		text
\\	pollanswers	pollid (polls.id)
//				answers text
\\
// dekodierte daten in $this->data
\\ $this->data all polls array
\\ $this->data[id] single poll array
// $this->data[id][id] poll id
\\ $this->data[id][added] poll datetime
// $this->data[id][question] poll question
\\ $this->data[id][answers] poll answers array
// $this->data[id][answers][i] poll answer
\\ $this->data[id][result] poll result array
// $this->data[id][result][i] poll answer result array
\\ $this->data[id][result][i][i] poll answer result userid
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
		$empty_array = $this->clean_answers_array($answers);
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
	
	public function delete_answer($poll, $user){ // eine Antwort
		foreach($this->data[$poll]['result'] as $question => $uarr){
			$k = array_search($user, $uarr);
			if($k == 0 && $k !== false) // "int (1) 0 schein auch == false zu sein aber nicht === false 
				$k = strval($k); // und "0" ist true
			if($k !== false){
				if($k == "0" && !isset($this->data[$poll]['result'][$question][1]))
					$this->data[$poll]['result'][$question][$k] = "";
				else
					unset($this->data[$poll]['result'][$question][$k]);
				break;
			}
		}
		$this->data[$poll]['result'][$question] = array_values($this->data[$poll]['result'][$question]);
		// neu indexieren. die key des nutzerid-arrays sind eh uninteressant.
		$this->update_pollanswers($poll, $this->data[$poll]['result']);
	}
	
	public function delete_answers($poll){ // alle antworten einer umfrage
		$c = $this->data[$poll]['answers'];
		$empty_array = $this->clean_answers_array($c);
		$this->update_pollanswers($poll, $empty_array);
	}

	public function add_answer($poll,$answer,$user){
		if($this->data[$poll]['result'][$answer][0] == "")
			$this->data[$poll]['result'][$answer][0] = strval($user);
		else
			$this->data[$poll]['result'][$answer][] = strval($user);
		$this->update_pollanswers($poll, $this->data[$poll]['result']);
	}

	public function edit_answer(){
	
	}
	
	private function clean_answers_array($answers){
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

	private function update_pollanswers($poll, $resultsets){
		$arr = array();
		foreach($resultsets as $q => $uarr){
			if(is_array($uarr)){
				if($uarr[0] != "" && isset($uarr[1]))
					$arr[$q] = implode(";",$uarr);
				elseif($uarr[0] != "" && !isset($uarr[1]))
					$arr[$q] = $uarr[0];
				else
					$arr[$q] = "";
			}else
				$arr[$q] = "";
		}
		$all = json_encode($arr, JSON_FORCE_OBJECT);
		$qry = $this->con->prepare('UPDATE pollanswers SET answers= :a WHERE pollid= :id');
		$qry->bindParam(':a', $all, PDO::PARAM_STR);
		$qry->bindParam(':id', $poll, PDO::PARAM_INT);
		$qry->execute();
	}
}
?>