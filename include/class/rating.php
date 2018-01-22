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

class rating
{
	private $con;
	private $star;

	function __construct($connection, $star = "star.png"){
		$this->con = $connection;
		$this->star = $star;
	}

	private function hasRated($tid,$uid){
		$qry = $this->con->prepare("SELECT id FROM torrents_ratings WHERE torrent = :tid AND uid = :uid");
		$qry->bindParam(':tid', $tid, PDO::PARAM_INT);
		$qry->bindParam(':uid', $uid, PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount())
			return true;
		else
			return false;
	}

	private function getUserVote($tid,$uid){
		$qry = $this->con->prepare("SELECT rating FROM torrents_ratings WHERE torrent = :tid AND uid = :uid");
		$qry->bindParam(':tid', $tid, PDO::PARAM_INT);
		$qry->bindParam(':uid', $uid, PDO::PARAM_INT);
		$qry->execute();
		$a = $qry->Fetch(PDO::FETCH_ASSOC);
		return $a["rating"];
	}

	private function getRating($tid){
		$rating = 0;
		$sum = 0;
		$count = 0;
		$qry = $this->con->prepare("SELECT rating FROM torrents_ratings WHERE torrent = :tid");
		$qry->bindParam(':tid', $tid, PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount()){
			$rating = array();
			$data = $qry->FetchAll(PDO::FETCH_ASSOC);
			foreach($data as $row){
				$count++;
				$sum += $row["rating"];
			}
			$rating["count"] = $count;
			$rating["rating"] = round($sum/$count);
			return $rating;
		}else
			return 0;
	}

	private function getStarString($r){
		$s = "";
		for($i = 0; $i < $r; $i++)
			$s .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $this->star . "\" border=\"0\" />";
		return $s;
	}

	public function vote($tid,$uid,$rating){
		$qry = $this->con->prepare("INSERT INTO torrents_ratings (torrent, uid, rating) VALUES (:tid,:uid,:rating)");
		$qry->bindParam(':tid', $tid, PDO::PARAM_INT);
		$qry->bindParam(':uid', $uid, PDO::PARAM_INT);
		$qry->bindParam(':rating', $rating, PDO::PARAM_INT);
		$qry->execute();
	}

	public function output($tid,$uid){
		$ratings = array(5 => "Genial!!",4 => "Einfach gut",3 => "Ist O.K.",2 => "Gerade so",1 => "Finger weg!");
		$r_arr = $this->getRating($tid);
		if($r_arr["rating"] > 0 && $r_arr["count"] > 0){
			$r_pic_str = $this->getStarString($r_arr["rating"]);
			$r_str = $r_pic_str . " " . $ratings[$r_arr["rating"]] . " - " . $r_arr["count"] . " Stimme" . (($r_arr["count"] > 1) ? "n" : "");
		}else
			$r_str = "Bisher hat keiner diesen Torrent bewertet.";
		$output = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr>".
			"<td valign=\"left\" class=\"embedded\">" . $r_str . "</td></tr>";
		if($this->hasRated($tid,$uid)){
			$ownRating = $this->getUserVote($tid,$uid);
			$output .= "<tr><td align=\"left\" class=\"embedded\">Du hast mit " . $ownRating . " / 5 gestimmt.</td></tr>";
		}else{
			$options_str = "<option value=\"0\" selected=\"selected\">(Torrent bewerten)</option>";
			foreach($ratings as $k => $v){
				$options_str .= "<option value=\"" . $k . "\">" . $k . " - " . $v . "</option>";
			}
			$output .= "<tr><td align=\"left\" class=\"embedded\"><form method=\"post\" action=\"details.php?id=" . $tid . "\"><select name=\"rating\">" . $options_str . "</select><input type=\"submit\" value=\"Voten!\" /></form></td></tr>";
		}
		$output .= "</table>";
		return $output;
	}	
}
?>