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

require_once(dirname(__FILE__) . "/include/bittorrent.php");
userlogin();
$shoutbox = new shoutbox($GLOBALS['DB']);
//loggedinorreturn();

if(isset($_REQUEST["ajax"]))
	$ajax = (isset($_GET["ajax"]) ? htmlentities($_GET["ajax"]) : htmlentities($_POST["ajax"]));
else
	$ajax = 0;

if(isset($_REQUEST["hid"]))
	$hid = (isset($_GET["history"]) ? intval($_GET["hid"]) : intval($_POST["hid"]));
else
	$hid = 0;

if(isset($_REQUEST["view"]))
	$view = (isset($_GET["history"]) ? intval($_GET["view"]) : intval($_POST["view"]));
else
	$view = 0;

if(isset($_REQUEST["del"]))
	$del = (isset($_GET["history"]) ? intval($_GET["del"]) : intval($_POST["del"]));
else
	$del = 0;

if($hid != 0 && $ajax == "yes"){
	$shoutbox->make_invisible($hid);
    die;
}

if($view != 0 && $ajax == "yes"){
	$shoutbox->make_visible($view);
    die;
}

if($del != 0 && $ajax == "yes"){
	$shoutbox->delete($del);
    die;
}

if(isset($_POST["message"])){
	//$message = utf2html(trim($_POST["message"]));
	$message = trim($_POST["message"]);
	$message = str_replace(array("&#228;","&#246;","&#252;","&#196;","&#214;","&#220;","&#223;"),
							array("&auml;","&ouml;","&uuml;","&Auml;","&Ouml;","&Uuml;","&szlig;"),
							$message);
	$message = trim($message);
	if($message != ""){
		$user    = "<font class=\"" . get_class_color($CURUSER["class"]) . "\">" . htmlentities($CURUSER["username"]) . "</font>";
		$shoutbox->insert($message, $CURUSER["id"]);
	}
}
/*
$append = "(visible = 'yes' AND private_id = 0) OR (visible = 'yes' AND (private_id = " . $CURUSER["id"] . " OR user_id = " . $CURUSER["id"] . "))";

if (!isset($_GET["history"]))
{
    $sql = "SELECT * FROM shoutbox WHERE " . $append . " ORDER BY time DESC LIMIT 30";
}
else
{
    $sql = "SELECT * FROM shoutbox ORDER BY time DESC LIMIT 100";
}
*/
$result = $shoutbox->get_box(get_user_class());
//$result = mysql_query($sql);

$loading  = "  <div id=\"loading-layer\" class=\"sb_loading\">\n";
$loading .= "    <div style=\"font-weight: bold; color: #000000;\" id=\"loading-layer-text\">Aktualisiere. Bitte warten...</div>\n";
$loading .= "    <br />\n";
$loading .= "    <img src=\"" . $BASEURL . "/pic/loading.gif\" border=\"0\" />\n";
$loading .= "  </div>\n";

$menu = "\n<div id=\"popup\" style=\"display:none;\"></div>\n\n";

if ($result){
	if (get_user_class() >= UC_MODERATOR){
		print ($loading);
	}

    print($menu);

    $out = "";

    $popup_pic = $GLOBALS["PIC_BASE_URL"] . "menu_open.gif";

    $history = ( isset($_GET["history"])                ? "1" : "0" );
    $hid     = ( (get_user_class() >= UC_MODERATOR)     ? "1" : "0" );
    $usr     = ( (get_user_class() >= UC_MODERATOR)     ? "1" : "0" );
    $del     = ( (get_user_class() >= UC_ADMINISTRATOR) ? "1" : "0" );
    $pm      = "1";

    //while($row = mysql_fetch_array($result))
	if(is_array($result)){
		foreach($result as $row){
			$sb_opt = "'"   . $row["id"] .         // PostID
					"','" . $row["userid"] .    // UserID des Schreibers
					"','" . $hid .               // kann Post ausblenden
					"','" . $row["visible"] .    // Post versteckt oder sichtbar
					"','" . $del .               // kann Post löschen
					"','" . $history .           // Ansicht History oder Normal
					"','" . $pm .                // Kann PM in SB schreiben
					"','" . $usr .               // Link zum User-Profil
					"'";

			if ( $history && ($row["visible"] == 0) ){
			  $start = "<i>";
			  $ende  = "</i>";
			}else{
			  $start = "";
			  $ende  = "";
			}

			$user = "<span class=\"" . get_class_color($row["class"]) . "\">" . $row["username"] . "</span>";

			$line = "  <div style=\"width: 99%; vertical-align: text-top; margin: 5px;\">\n" .
					"    <div style=\"float: left; width: 15px;\"><img class=\"button\" src=\"" . $popup_pic .  "\" id=\"menu_" . $row["id"] . "\" onClick=\"popup_sb(" . $sb_opt . ");\" /></div>\n" .
					"    <div style=\"float: left; width: 85px;\">" . date("H:i:s",strtotime($row['added'])) . "</div>\n" .
					"    <div style=\"float: left; width: 105px;\">" . $user . "</div>\n" .
					"    <div><table><tr><td>" . str_replace("<br /><br />", "", format_comment($row["msg"])) . "</td></tr></table></div>\n" .
					"  </div>\n";

			$line = str_replace("<br /></td>", "</td>", $line);

			if ($line != ""){
				$out .= $line;
			}
		}
	}else{
		$out = $result;
	}
	print($out);
}
?>