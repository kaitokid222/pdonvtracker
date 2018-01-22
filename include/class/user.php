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

class user
{
	protected $id;
	protected $username;
	function __construct() {
	}
	
	
	
	public static function addUser($wantusername,$wantpasshash,$passkey,$secret,$editsecret = "",$email = "",$status = "confirmed",$stylesheet = 1,$dt = ""){
		if($email == "")
			$email = "system@" . $GLOBALS["SITENAME"];
		if($dt == "")
			$dt = date("Y-m-d H:i:s");
		$qry = $GLOBALS['DB']->prepare("INSERT INTO users (username, passhash, passkey, secret, editsecret, email, status, stylesheet, added) VALUES (:username, :passhash, :passkey, :secret, :editsecret, :email, :status, :stylesheet, :dt)");
		$qry->bindParam(':username', $wantusername, PDO::PARAM_STR);
		$qry->bindParam(':passhash', $wantpasshash, PDO::PARAM_STR);
		$qry->bindParam(':passkey', $passkey, PDO::PARAM_STR);
		$qry->bindParam(':secret', $secret, PDO::PARAM_STR);
		$qry->bindParam(':editsecret', $editsecret, PDO::PARAM_STR);
		$qry->bindParam(':email', $email, PDO::PARAM_STR);
		$qry->bindParam(':status', $status, PDO::PARAM_STR);
		$qry->bindParam(':stylesheet', $stylesheet, PDO::PARAM_INT);
		$qry->bindParam(':dt', $dt, PDO::PARAM_STR);
		$qry->execute();
		if($qry->rowCount())
			return $GLOBALS['DB']->lastInsertId();
		else
			return false;
	}

	public static function refreshSession($uid){
		session_unset();
		$sql = "SELECT * FROM `users` WHERE `id` = :uid";
		$qry = $GLOBALS['DB']->prepare($sql);
		$qry->bindParam(':uid', $uid, PDO::PARAM_INT);
		$qry->execute();
		$_SESSION["userdata"] = $qry->Fetch(PDO::FETCH_ASSOC);
	}

	/*public static function hasToAcceptRules($uid,$page){
		if($GLOBALS["CURUSER"]["accept_rules"] == "no" && !preg_match("/(my|rules|faq|logout|delacct)\\.php$/", $_SERVER["PHP_SELF"])) {
			header("Location: rules.php?accept_rules");
	}*/

	
}
?>