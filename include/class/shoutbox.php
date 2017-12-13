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
	table shoutbox
	id|added|userid|msg|visible
*/


class shoutbox
{
	private $con;
	private $status = array();
	private $box = "";

	function __construct($connection) {
		$this->con = $connection;
	}
	
	public function insert($msg = "",$userid = 0){
		if(isset($msg, $userid) && $msg != "" && $userid != 0){
			$now = date("Y-m-d H:i:s");
			$qry = $this->con->prepare("INSERT INTO shoutbox (id, added, userid, msg, visible) VALUES (NULL,:time,:userid,:msg, '1')");
			$qry->bindParam(':time', $now, PDO::PARAM_STR);
			$qry->bindParam(':userid', $userid, PDO::PARAM_INT);
			$qry->bindParam(':msg', $msg, PDO::PARAM_STR);
			$qry->execute();
			$this->status[] = "Eintrag in die Datenbank geschrieben.";
			return $this;
		}else{
			$this->status[] = "Fehlende oder fehlerhafte Eingabedaten. Eintrag wurde nicht in die Datenbank geschrieben.";
			return $this;
		}
	}
	
	public function delete($msgid = 0){
		if(isset($msgid) && $msgid != 0){
			$qry = $this->con->prepare("DELETE FROM shoutbox WHERE id = :id");
			$qry->bindParam(':id', $msgid, PDO::PARAM_INT);
			$qry->execute();
			$this->status[] = "Eintrag aus der Datenbank gelöscht.";
			return $this;
		}else{
			$this->status[] = "Fehlende oder fehlerhafte Eingabedaten. Eintrag wurde nicht aus der Datenbank gelöscht.";
			return $this;
		}
	}
	
	public function wipe(){
		$qry = $this->con->prepare("TRUNCATE shoutbox");
		$qry->execute();
		$this->status[] = "Die Shoutbox wurde geleert.";
		return $this;
	}
	
	public function modify($msgid = 0, $newmsg = ""){
		if(isset($msgid, $newmsg) && $msgid != 0 && $newmsg != ""){
			$qry = $this->con->prepare("UPDATE shoutbox SET msg = :newmsg WHERE id = :id");
			$qry->bindParam(':newmsg', $newmsg, PDO::PARAM_STR);
			$qry->bindParam(':id', $msgid, PDO::PARAM_INT);
			$qry->execute();
			$this->status[] = "Der Eintrag wurde bearbeitet.";
			return $this;
		}else{
			$this->status[] = "Fehlende oder fehlerhafte Eingabedaten. Der Eintrag wurde nicht bearbeitet.";
			return $this;
		}
	}
	
	public function make_visible($msgid = 0){
		if(isset($msgid) && $msgid != 0){
			$qry = $this->con->prepare("UPDATE shoutbox SET visible = '1' WHERE id = :id");
			$qry->bindParam(':id', $msgid, PDO::PARAM_INT);
			$qry->execute();
			$this->status[] = "Der Eintrag ist jetzt sichtbar.";
			return $this;
		}else{
			$this->status[] = "Fehlende oder fehlerhafte Eingabedaten. Es wurde keine Datenbankoperation durchgeführt.";
			return $this;
		}
	}
	
	public function make_invisible($msgid = 0){
		if(isset($msgid) && $msgid != 0){
			$qry = $this->con->prepare("UPDATE shoutbox SET visible = '0' WHERE id = :id");
			$qry->bindParam(':id', $msgid, PDO::PARAM_INT);
			$qry->execute();
			$this->status[] = "Der Eintrag ist jetzt unsichtbar.";
			return $this;
		}else
			$this->status[] = "Fehlende oder fehlerhafte Eingabedaten. Es wurde keine Datenbankoperation durchgeführt.";
			return $this;
	}
	
	public function get_box_data($team = false){
		if($team)
			$qry = $this->con->prepare("SELECT sb.*, u.username, u.class FROM shoutbox sb LEFT JOIN users u ON u.id = sb.userid ORDER BY added DESC LIMIT 25");
		else
			$qry = $this->con->prepare("SELECT sb.*, u.username, u.class FROM shoutbox sb LEFT JOIN users u ON u.id = sb.userid WHERE sb.visible = '1' ORDER BY added DESC LIMIT 25");
		$qry->execute();
		if($qry->rowCount()){
			$data = $qry->FetchAll(PDO::FETCH_ASSOC);
			$this->box = $data;
			$this->status[] = "Die Daten wurden in eine lokale Variable geschrieben.";
			return $this;
		}else{
			$this->box = "Keine Eintr&auml;ge";
			$this->status[] = "Es wurden keine Daten gefunden.";
			return $this;
		}
	}

	public function get_box($uc){
		$uc_ref = [25,50,100];
		$b = in_array($uc, $uc_ref);
		$this->get_box_data($b);
		return $this->box;
	}

	public function get_status(){
		return $this->status;
	}
}
?>