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

class tupload
{
	private $con;
	private $user;
	private $fname;
	private $torrent_file;
	private $torrent_descr;
	private $torrent_type;
	private $torrent_name;
	private $uploaderrors_pic = array();

	function __construct($connection) {
		$this->con = $connection;
	}
	
	public function setUser($cu){
		$this->user = $cu;
	}
	
	public function canUpload(){
		if($this->user["allowupload"] != "yes")
			return false;
		return true;
	}
	
	public function isUploader(){
		if($this->user["class"] < 10)
			return false;
		return true;
	}
	
	public function checkForm(){
		foreach(explode(":", "descr:type:name") as $v){
			if(!isset($_POST[$v]))
				return false;
			else
				$this->torrent_{$v} = $_POST[$v];
		}
		if(!isset($_FILES["file"]))
			return false;
		else
			$this->torrent_file = $_FILES["file"];
		return true;
	}
	
	public function checkMetafile($file){
		$f = $file;
		if(get_magic_quotes_gpc())
			$fname = stripslashes($f["name"]);
		else
			$fname = $f["name"];
		if(empty($fname))
			return false;

		if(!preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $fname))
			return false;
		$this->fname = $fname;
	
		$tmpname = $f["tmp_name"];
		if(!is_uploaded_file($tmpname))
			return false;
		return true;
	}
	
	public function get_fname(){
		return $this->fname;
	}
	
	public function checkMetafileSize($fz){
		if($fz > $GLOBALS["MAX_TORRENT_SIZE"])
			return false;

		if($fz == 0)
			return false;
		return true;
	}
	
	public function checknfofileSize($fz){
		if($fz == 0)
			return false;
		if($fz > 65535)
			return false;
		return true;
	}
	
	public function checkTorrentDescr($descr){
		if(strlen($descr) > 20000)
			return false;
		if(trim($descr) == "")
			return false;
		return true;
	}
	
	private function reset_uploaderrors_pic($picnum){
		unset($this->uploaderrors_pic[$picnum]);
	}

	public function get_uploaderrors_pic(){
		if(isset($this->uploaderrors_pic[1]) || isset($this->uploaderrors_pic[2]))
			return $this->uploaderrors_pic;
		else
			return false;
	}

	public function torrent_image_upload($file, $id, $picnum){
		if(!isset($file) || $file["size"] < 1){
			$this->uploaderrors_pic[$picnum][] = "Es wurden keine Daten von " . $file["name"] . " empfangen!";
			return false;
		}
			
		if($file["size"] > $GLOBALS["MAX_UPLOAD_FILESIZE"]){
			$this->uploaderrors_pic[$picnum][] = "Die Bilddatei ".$file["name"]." ist zu gro&szlig (max. " . (0+$GLOBALS["MAX_UPLOAD_FILESIZE"]) . ")!";
			return false;
		}

		$it = exif_imagetype($file["tmp_name"]);
		if($it != IMAGETYPE_GIF && $it != IMAGETYPE_JPEG && $it != IMAGETYPE_PNG){
			$this->uploaderrors_pic[$picnum][] = "Sorry, die hochgeladene Datei '".$file["name"]."' konnte nicht als g&uuml;ltige Bilddatei verifiziert werden.";
			return false;
		}

		$i = strrpos($file["name"], ".");
		if($i !== false){
			$ext = strtolower(substr($file["name"], $i));
			if(($it == IMAGETYPE_GIF  && $ext != ".gif") || ($it == IMAGETYPE_JPEG && $ext != ".jpg") || ($it == IMAGETYPE_PNG  && $ext != ".png")){
				$this->uploaderrors_pic[$picnum][] = "Ung&uuml;tige Dateinamenerweiterung: <b>" . $ext . "</b>";
				return false;
			}
			//$filename .= $ext;
		}else{
			$this->uploaderrors_pic[$picnum][] = "Die Datei ".$file["name"]." besitzt keine Dateinamenerweiterung.";
			return false;
		}
		$img = resize_image($file["name"], $file["tmp_name"], $GLOBALS["BITBUCKET_DIR"] . "/t-" . $id . "-" . $picnum . ".jpg", 100);    
		if($img === false){
			$this->uploaderrors_pic[$picnum][] = "Das Bild ".$file["name"]." konnte nicht verkleinert werden.";
			return false;
		}
		$ret = imagejpeg($img, $GLOBALS["BITBUCKET_DIR"] . "/f-" . $id . "-" . $picnum . ".jpg", 85);
		imagedestroy($img);
		if(!$ret){
			$this->uploaderrors_pic[$picnum][] = "Die Originalversion des Bildes ".$file["name"]." konnte nicht auf dem Server gespeichert werden - bitte SysOp benachrichtigen!";
			return false;
		}else{
			//tr_status("ok");
			$this->reset_uploaderrors_pic($picnum);
			return true;
		}
	}
}
?>