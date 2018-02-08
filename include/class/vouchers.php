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

class voucher
{
	protected $today;
	protected $id;
	protected $code;
	protected $date;
	protected $value;
	protected $users_str;
	protected $con;

	function __construct($code = ""){
		$this->today = date("Y-m-d");
		$this->con = $GLOBALS['DB'];
		if($code != ""){
			$this->code = $code;
			$this->loadVoucher($this->code);
		}
	}

    /**
     * fügt einen neuen vouchercode in die datenbank ein
     * 
     * @param str $code  
     * @param str $date  
     * @param int $value  
     * @param bool $load
     * 
     * @return bool
     */
	public function create($code = "", $date = "", $value = 0, $load = false){
		if($code == "")
			$code = $this->randomString();
		else{
			if(strlen($code) < 20)
				$code = $code . $this->randomString((20-strlen($code)));
		}
		if($date == "")
			$date = $this->today;
		if($value == 0)
			$value = $this->defaultValue();
		else
			$value = $this->makeMegaByte($value);
		$str = $this->emptyJSON();
		$qry = $this->con->prepare("INSERT INTO vouchers (id, code, date, value, users) VALUES (NULL,:code,:date,:value,:users)");
		$qry->bindParam(':code', $code, PDO::PARAM_STR);
		$qry->bindParam(':date', $date, PDO::PARAM_STR);
		$qry->bindParam(':value', $value, PDO::PARAM_INT);
		$qry->bindParam(':users', $str, PDO::PARAM_STR);
		$qry->execute();
		if($qry->rowCount()){
			if($load !== false){
				$this->code = $code;
				if($this->loadVoucher($this->code) === false)
					return false;
			}
			return true;
		}else
			return false;
	}

	public function resetUsers(){
		$this->users_str = $this->emptyJSON();
		if($this->saveVoucher() === true)
			return true;
		return false;
	}
		
    /**
     * benutzt einen voucher
     * 
     * @param int $userid 
     * 
     * @return bool
     */
	public function useVoucher($userid){
		$users_arr = $this->makeUserArray();
		if($this->canUse($users_arr,$userid) === true){
			$a = $this->insertUser($users_arr, $userid);
			$this->makeUsersString($a);
			$this->giveReward($userid);
			if($this->saveVoucher() === true)
				return true;
			return false;
		}else
			return false;
	}

    /**
     * löscht einen voucher aus der datenbank
     * 
     * @return bool
     */
	public function deleteVoucher(){
		$qry = $this->con->prepare("DELETE FROM vouchers WHERE code = :code");
		$qry->bindParam(':code', $this->code, PDO::PARAM_STR);
		$qry->execute();
		if($qry->rowCount())
			return true;
		return false;
	}

    /**
     * gibt ein "schönes" array mit userids zurück
     * 
     * @return arr
     */
	public function getPrettyUsers(){
		return $this->makeUserArray();
	}

	/**
     * speichert änderungen am voucher
     * 
     * @return bool
     */
	public function saveVoucher(){
		if($this->date == "")
			$this->date = $this->today;
		if($this->value == 0)
			$this->value = $this->defaultValue();
		$qry = $this->con->prepare("UPDATE vouchers SET date = :date, value = :value, users = :users WHERE code = :code");
		$qry->bindParam(':code', $this->code, PDO::PARAM_STR);
		$qry->bindParam(':date', $this->date, PDO::PARAM_STR);
		$qry->bindParam(':value', $this->value, PDO::PARAM_INT);
		$qry->bindParam(':users', $this->users_str, PDO::PARAM_STR);
		$qry->execute();
		if($qry->rowCount())
			return true;
		return false;
	}

	/**
     * Prüft, ob für heute oder einem anderen tag schon ein voucher existiert und gibt ihn ggf zurück
     * 
	 * @param str $date
	 *
     * @return bool
     */
	public function todaysVoucher($date = ""){
		if($date == "")
			$date = $this->today;
		$qry = $this->con->prepare("SELECT code FROM vouchers WHERE date = :date");
		$qry->bindParam(':date', $date, PDO::PARAM_STR);
		$qry->execute();
		if($qry->rowCount()){
			$row = $qry->Fetch(PDO::FETCH_ASSOC);
			$this->code = $row["code"];
			$this->loadVoucher($this->code);
			return true;
		}else
			return false;
	}

    /**
     * prüft ob ein user den voucher nutzen kann
     * 
     * @param arr $uarr 
     * @param int $userid 
     * 
     * @return bool
     */
	private function canUse($uarr, $userid){
		if(in_array($userid, $uarr))
			return false;
		if($this->date != "0000-00-00" && $this->date != $this->today)
			return false;
		return true;
	}

    /**
     * liefert den standartwert für einen voucher (500 MB)
     * 
     * @return int
     */
	private function defaultValue(){
		return 500 * 1024 * 1024;
	}

    /**
     * liefert einen leeren jsonstring für einen voucher
     * 
     * @return str
     */
	private function emptyJSON(){
		$arr["users"] = "nan";
		return json_encode($arr, JSON_FORCE_OBJECT);
	}

	
    /**
     * schreibt den wert des vouchers gut
     * 
     * @param int $userid 
     * 
     * @return bool
     */
	private function giveReward($userid){
		$qry = $this->con->prepare("UPDATE users SET uploaded = uploaded + :value WHERE id = :uid");
		$qry->bindParam(':value', $this->value, PDO::PARAM_INT);
		$qry->bindParam(':uid', $userid, PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount())
			return true;
		return false;
	}

    /**
     * Liefert eine zufällige Zeichenkette der länge $len aus zahlen und buchstaben zurück
     * 
     * @param int $len  
     * 
     * @return str
     */
	private function randomString($len = 20){
		$zeichen = array('A','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9');
		$s = "";
		for($i = 0; $i < $len; $i++)
			$s .= $zeichen[rand(0,count($zeichen)-1)];
		return $s;
	}

    /**
     * multipliziert den gewünschten wert zu MB
     * 
     * @param int $mb  
	 *
     * @return int
     */
	private function makeMegaByte($mb){
		return $mb * 1024 * 1024;
	}

    /**
     * wandelt den jsonstring in ein einfaches array um
     * 
     * @return arr
     */
	private function makeUserArray(){
		$o = array();
		$uarr = json_decode($this->users_str, true);
		if($uarr["users"] == "nan")
			$o[] = "nan";
		else{
			$nuarr = explode(";", $uarr["users"]);
			foreach($nuarr as $userid)
				$o[] = $userid;
		}
		return $o;
	}

    /**
     * wandelt das userarray in einen jsonstring um
     * 
     * @param arr $arr 
     */
	private function makeUsersString($arr){
		$narr["users"] = "";
		if($arr[0] == "nan")
			$narr["users"] = "nan";
		elseif($arr[0] != "nan" && !isset($arr[1]))
			$narr["users"] = $arr[0];
		else
			$narr["users"] = implode(";", $arr);
		$this->users_str = json_encode($narr, JSON_FORCE_OBJECT);
	}

    /**
     * fügt die userid des benutzers ein und liefert das erweiterte array zurück
     * 
     * @param arr $arr 
     * @param int $userid 
	 *
	 * @return arr
     */
	private function insertUser($arr, $userid){
		if($arr[0] == "nan")
			$arr[0] = $userid;
		elseif($arr[0] != "nan")
			$arr[] = $userid;
		return $arr;
	}

	/**
     * lädt den gewünschten voucher
     * 
     * @param str $code 
     * 
     * @return bool
     */
	private function loadVoucher($code){
		$qry = $this->con->prepare("SELECT id, date, value, users FROM vouchers WHERE code = :code");
		$qry->bindParam(':code', $code, PDO::PARAM_STR);
		$qry->execute();
		if($qry->rowCount()){
			$row = $qry->Fetch(PDO::FETCH_ASSOC);
			$this->id = $row["id"];
			$this->date = $row["date"];
			$this->value = $row["value"];
			$this->users_str = $row["users"];
			return true;
		}
		return false;
	}
	
    /**
     * liefert eigenschaft des objekts zurück (magic)
     * 
     * @param mixed $name 
     * 
     * @return mixed
     */
	public function __get($name){
		return $this->{$name};
	}

	/**
     * ändert eigenschaft des objekts (magic)
     * 
     * @param mixed $name 
     * @param mixed $value 
     */
	public function __set($name, $value){
		$this->{$name} = $value;
	}
}
?>