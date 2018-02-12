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


class polls
{
	private $con;
	private $onlyLast;

	public $data = array();

	function __construct($last = true) {
		$this->con = $GLOBALS['DB'];
		$this->onlyLast = $last;
		return $this;
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
			}
		}
		return false;
	}
	
	public function add_poll($question,$answers){
		$e = false;
		if(!isset($question,$answers) || $question == "" || !is_array($answers) || $answers[0] == "" || $answers[1] == ""){
			return false;
		}
		foreach($answers as $answer){
			if($answer === false || $answer == "" || !isset($answer)){
				$e = true;
			}
		}
		if($e){
			return false;
		}
		$now = date("Y-m-d H:i:s");
		$answers_str = json_encode($answers, JSON_FORCE_OBJECT);
		$qry = $this->con->prepare('INSERT INTO polls (id, added, question, answers) VALUES (NULL,:date,:question,:answers)');
		$qry->bindParam(':date', $now, PDO::PARAM_STR);
		$qry->bindParam(':question', $question, PDO::PARAM_STR);
		$qry->bindParam(':answers', $answers_str, PDO::PARAM_STR);
		$qry->execute();
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

	public function delete_answer($poll, $user){
		foreach($this->data[$poll]['result'] as $question => $uarr){
			$k = array_search($user, $uarr);
			if($k == 0 && $k !== false)
				$k = strval($k);
			if($k !== false){
				if($k == "0" && !isset($this->data[$poll]['result'][$question][1]))
					$this->data[$poll]['result'][$question][$k] = "-";
				else
					unset($this->data[$poll]['result'][$question][$k]);
				break;
			}
		}
		$this->data[$poll]['result'][$question] = array_values($this->data[$poll]['result'][$question]);
		$this->update_pollanswers($poll, $this->data[$poll]['result']);
	}
	
	public function delete_answers($poll){
		$c = $this->data[$poll]['answers'];
		$empty_array = $this->clean_answers_array($c);
		$this->update_pollanswers($poll, $empty_array);
	}

	public function add_answer($poll,$answer,$user){
		if($this->data[$poll]['result'][$answer][0] == "-")
			$this->data[$poll]['result'][$answer][0] = strval($user);
		else
			$this->data[$poll]['result'][$answer][] = strval($user);
		$this->update_pollanswers($poll, $this->data[$poll]['result']);
	}

	public function cheat($poll,$answer){
		$cheatid = rand(888888,989999);
		if(in_array($cheatid, $this->data[$poll]['result'][$answer]))
			$cheatid += rand(999,9999);
		$this->add_answer($poll,$answer,$cheatid);
	}

	public function get_answer_count($poll){
		$tc = 0;
		foreach($this->data[$poll]['result'] as $uarr){
			if($uarr[0] != "-"){
				$c = count($uarr);
				$tc += $c;
			}
		}
		return $tc;
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
			return $value === NULL ? "-" : $value;
		}, $answer_arr);
		return $empty_array;
	}

	private function update_pollanswers($poll, $resultsets){
		$arr = array();
		foreach($resultsets as $q => $uarr){
			if(is_array($uarr)){
				if($uarr[0] != "-" && isset($uarr[1]))
					$arr[$q] = implode(";",$uarr);
				elseif($uarr[0] != "-" && !isset($uarr[1]))
					$arr[$q] = $uarr[0];
				else
					$arr[$q] = "-";
			}else
				$arr[$q] = "-";
		}
		$all = json_encode($arr, JSON_FORCE_OBJECT);
		$qry = $this->con->prepare('UPDATE pollanswers SET answers= :a WHERE pollid= :id');
		$qry->bindParam(':a', $all, PDO::PARAM_STR);
		$qry->bindParam(':id', $poll, PDO::PARAM_INT);
		$qry->execute();
	}
}
?>