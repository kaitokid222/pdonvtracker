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

class db
{
	private $con;
	public $data = array();

	function __construct($dsn = array()) {
		if(!isset($dsn, $dsn[0], $dsn[1], $dsn[2]))
			die("dsn fehlerhaft");
		$this->con = new PDO($dsn[0], $dsn[1], $dsn[2]);
		$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		$this->con->query('SET NAMES utf8');
	}
	
	public function getPDO(){
		return $this->con;
	}
	
	public function row_count($table,$condition = '1=1'){
		$sql = "SELECT COUNT(*) FROM " . $table . " WHERE " . $condition;
		$qry = $this->con->prepare($sql);
		$qry->execute();
		return $qry->fetchColumn(0);
	}
}
/*
public function get_Assoc($sql, $params=array()){
      $stmt = $this->prepare($sql);
      $params = is_array($params) ? $params : array($params);
      $stmt->execute($params);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/
?>