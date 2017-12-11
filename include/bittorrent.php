<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);  
/*
// +--------------------------------------------------------------------------+
// | Project:    NVTracker - NetVision BitTorrent Tracker                     |
// +--------------------------------------------------------------------------+
// | This file is part of NVTracker. NVTracker is based on BTSource,          |
// | originally by RedBeard of TorrentBits, extensively modified by           |
// | Gartenzwerg.                                                             |
// |                                                                          |
// | NVTracker is free software; you can redistribute it and/or modify        |
// | it under the terms of the GNU General Public License as published by     |
// | the Free Software Foundation; either version 2 of the License, or        |
// | (at your option) any later version.                                      |
// |                                                                          |
// | NVTracker is distributed in the hope that it will be useful,             |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with NVTracker; if not, write to the Free Software Foundation,     |
// | Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            |
// +--------------------------------------------------------------------------+
// | Obige Zeilen dürfen nicht entfernt werden!    Do not remove above lines! |
// +--------------------------------------------------------------------------+
*/

function local_user()
{
    global $_SERVER;

    return $_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"];
} 

if (!file_exists("include/secrets.php") || !file_exists("include/config.php"))
    die("<html><head><title>FEHLER</title></head><body><p>Der Tracker wurde noch nicht konfiguriert.</p>
        <p><a href=\"inst/install.php\">Zum Installationsscript</a></p></body></html>");

require_once("include/secrets.php");
require_once("include/config.php");
require_once("include/cleanup.php");


require_once("include/shoutcast.php");


require_once("include/class/db.php");
$database = new db($dsn);
$GLOBALS['DB'] = $database->getPDO();

require_once("include/class/polls.php");

function set_last_access($id){
	$latime = date("Y-m-d H:i:s");
	$qry = $GLOBALS['DB']->prepare('UPDATE users SET last_access= :date WHERE id= :id');
	$qry->bindParam(':date', $latime, PDO::PARAM_STR);
	$qry->bindParam(':id', $GLOBALS["CURUSER"]["id"], PDO::PARAM_INT);
	$qry->execute();
}
	
/**
 * *** validip/getip courtesy of manolete <manolete@myway.com> ***
 */
// IP Validation
function validip($ip)
{
    if (!empty($ip) && ip2long($ip) != -1) {
        // reserved IANA IPv4 addresses
        // http://www.iana.org/assignments/ipv4-address-space
        $reserved_ips = array (
            array('0.0.0.0', '2.255.255.255'),
            array('10.0.0.0', '10.255.255.255'),
            array('127.0.0.0', '127.255.255.255'),
            array('169.254.0.0', '169.254.255.255'),
            array('172.16.0.0', '172.31.255.255'),
            array('192.0.2.0', '192.0.2.255'),
            array('192.168.0.0', '192.168.255.255'),
            array('255.255.255.0', '255.255.255.255')
            );

        foreach ($reserved_ips as $r) {
            $min = ip2long($r[0]);
            $max = ip2long($r[1]);
            if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
        } 
        return true;
    } else return false;
} 
// Patched function to detect REAL IP address if it's valid
function getip()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
} 

function dbconn($autoclean = false)
{
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db, $_SERVER;

    if (!@mysql_connect($mysql_host, $mysql_user, $mysql_pass)) {
        switch (mysql_errno()) {
            case 1040:
            case 2002:
                if ($_SERVER["REQUEST_METHOD"] == "GET")
                    die("<html><head><meta http-equiv=refresh content=\"5 " . $_SERVER["REQUEST_URI"] . "\"></head>
                         <body><table border=0 width=100% height=100%><tr><td><h3 align=center>Die Serverlast
                         ist momentan zu hoch. Versuche es erneut, bitte warten...
                         </h3></td></tr></table></body></html>");
                else
                    die("Zu viele Benutzer. Bitte benutze den Aktualisieren-Button Deines Browsers,
                         um es erneut zu versuchen.");
            default:
                die("[" . mysql_errno() . "] dbconn: mysql_connect: " . mysql_error());
        } 
    } 
    mysql_select_db($mysql_db)
    or die('dbconn: mysql_select_db: ' + mysql_error());

    userlogin();

    if ($autoclean)
        register_shutdown_function("autoclean");
} 

function userlogin()
{
    global $SITE_ONLINE;
    unset($GLOBALS["CURUSER"]);

    $ip = getip();
    /*$nip = ip2long($ip);
    $res = mysql_query("SELECT * FROM bans WHERE " . $nip . " >= first AND " . $nip . " <= last") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0) {
        header("HTTP/1.0 403 Forbidden");
        print("<html><body><h1>403 Forbidden</h1>Unauthorized IP address.</body></html>\n");
        die;
    } */

    session_start();

    if (!$SITE_ONLINE || (!isset($_SESSION["userdata"]) && (empty($_COOKIE["uid"]) || empty($_COOKIE["pass"]))))
        return;

    if (isset($_SESSION["userdata"])) {
		$qry = $GLOBALS['DB']->prepare('SELECT COUNT(*) AS `cnt` FROM `users` WHERE `id` = :id AND `enabled`= yes AND `status` = confirmed');
		$qry->bindParam(':id', $GLOBALS["CURUSER"]["id"], PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount() > 0){
			$enabled = $qry->FetchAll();
			if ($enabled["cnt"] != 1 || $_SESSION["userdata"]["ip"] != $ip) {
				session_unset();
				session_destroy();
				return;
			} 
		}
        $GLOBALS["CURUSER"] = $_SESSION["userdata"];
    } else {
        // Keine Session aktiv, login via Cookie
        $id = 0 + $_COOKIE["uid"];
        if (!$id || strlen($_COOKIE["pass"]) != 32)
            return;
			
		$qry = $GLOBALS['DB']->prepare('SELECT * FROM users WHERE id = :id AND enabled= yes AND status = confirmed');
		$qry->bindParam(':id', $id, PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount() > 0){
			$row = $qry->FetchAll();
		}else{
			return;
		}
		$sec = hash_pad($row["secret"]);
        if ($_COOKIE["pass"] !== $row["passhash"])
            return;

        $row['ip'] = $ip;
        $GLOBALS["CURUSER"] = $row;
        $_SESSION["userdata"] = $row;

        if (isset($_COOKIE["passhash"])) {
			$qry = $GLOBALS['DB']->prepare('SELECT * FROM `accounts` WHERE `userid`= :id AND `chash`= :chash');
			$qry->bindParam(':id', $GLOBALS["CURUSER"]["id"], PDO::PARAM_INT);
			$qry->bindParam(':chash', $_COOKIE["passhash"], PDO::PARAM_STR);
			$qry->execute();
			if($qry->rowCount() > 0){
				//$row = $qry->FetchAll();
				set_last_access($GLOBALS["CURUSER"]["id"]);
			}else{
				$qry = $GLOBALS['DB']->prepare('SELECT * FROM `accounts` WHERE `chash`= :chash');
				$qry->bindParam(':chash', $_COOKIE["passhash"], PDO::PARAM_STR);
				$qry->execute();
				if($qry->rowCount() > 0){
					$data = $qry->fetchAll();
                    $baduser = $data["baduser"];
                }else{
					$baduser = 0;
				}
				$latime = date("Y-m-d H:i:s");
				$qry = $GLOBALS['DB']->prepare('INSERT INTO `accounts` (`userid`,`chash`,`lastaccess`,`username`,`email`,`baduser`) VALUES (:id, :chash, :date, :un, :em, :bu');
				$qry->bindParam(':id', $row["id"], PDO::PARAM_INT);
				$qry->bindParam(':chash', $_COOKIE["passhash"], PDO::PARAM_STR);
				$qry->bindParam(':date', $latime, PDO::PARAM_STR);
				$qry->bindParam(':un', $row["username"], PDO::PARAM_STR);
				$qry->bindParam(':em', $row["email"], PDO::PARAM_STR);
				$qry->bindParam(':bu', $baduser, PDO::PARAM_STR);
				$qry->execute();
            } 
        } else {
			$qry = $GLOBALS['DB']->prepare('SELECT * FROM `accounts` WHERE `userid`= :id');
			$qry->bindParam(':id', $GLOBALS["CURUSER"]["id"], PDO::PARAM_INT);
			$qry->execute();
			if($qry->rowCount() > 0){
			    set_last_access($GLOBALS["CURUSER"]["id"]);
				$data = $qry->FetchAll();
				$hash = $data["chash"];
			}else{
                $hash = md5($row["username"] . mksecret() . $row["username"]);
                $latime = date("Y-m-d H:i:s");
				$qry = $GLOBALS['DB']->prepare('INSERT INTO `accounts` (`userid`,`chash`,`lastaccess`,`username`,`email`,`baduser`) VALUES (:id, :chash, :date, :un, :em, :bu');
				$qry->bindParam(':id', $row["id"], PDO::PARAM_INT);
				$qry->bindParam(':chash', $hash, PDO::PARAM_STR);
				$qry->bindParam(':date', $latime, PDO::PARAM_STR);
				$qry->bindParam(':un', $row["username"], PDO::PARAM_STR);
				$qry->bindParam(':em', $row["email"], PDO::PARAM_STR);
				$qry->bindParam(':bu', $baduser, PDO::PARAM_STR);
				$qry->execute();
            } 
            setcookie("passhash", $hash, 0x7fffffff, "/");
        } 
    } 
	$latime = date("Y-m-d H:i:s");
	$qry = $GLOBALS['DB']->prepare('UPDATE users SET last_access= :date, ip= :ip WHERE id= :id');
	$qry->bindParam(':date', $latime, PDO::PARAM_STR);
	$qry->bindParam(':ip', $ip, PDO::PARAM_STR);
	$qry->bindParam(':id', $GLOBALS["CURUSER"]["id"], PDO::PARAM_STR);
	$qry->execute();

    if ($GLOBALS["CURUSER"]["accept_rules"] == "no" && !preg_match("/(takeprofedit|rules|faq|logout|delacct)\\.php$/", $_SERVER["PHP_SELF"])) {
        header("Location: rules.php?accept_rules");
        die();
    } 
} 

function logincookie($id, $passhash, $updatedb = 1, $expires = 0x7fffffff)
{
    setcookie("uid", $id, $expires, "/");
    setcookie("pass", $passhash, $expires, "/");

    if ($updatedb){
		$qry = $GLOBALS['DB']->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
		$qry->bindParam(':id', $id, PDO::PARAM_STR);
		$qry->execute();
	}
} 

function logoutcookie()
{
    setcookie("uid", "", 0x7fffffff, "/");
    setcookie("pass", "", 0x7fffffff, "/");
    session_unset();
    session_destroy();
} 

function loggedinorreturn()
{
    global $CURUSER, $DEFAULTBASEURL;
    if (!$CURUSER) {
        header("Location: " . $DEFAULTBASEURL . "/login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
        exit();
    } 
} 

function autoclean()
{
    $now = time();
    $docleanup = 0;
	
	$qry = $GLOBALS['DB']->prepare('SELECT value_u FROM avps WHERE arg = lastcleantime');
	$row = $qry->execute()->fetchColumn();
    if (!$row) {
		$qry = $GLOBALS['DB']->prepare('INSERT INTO avps (arg, value_u) VALUES (lastcleantime,:n)');
		$qry->bindParam(':n', $now, PDO::PARAM_STR);
		$row = $qry->execute();
        return;
    } 
    $ts = $row[0];
    if ($ts + $GLOBALS["AUTOCLEAN_INTERVAL"] > $now)
        return;
		
	$qry = $GLOBALS['DB']->prepare('UPDATE avps SET value_u= :n WHERE arg= lastcleantime AND value_u = :ts');
	$qry->bindParam(':n', $now, PDO::PARAM_STR);
	$qry->bindParam(':ts', $ts, PDO::PARAM_STR);
	$qry->execute();
	if($qry->rowCount() == 0)
		return;

    docleanup();
} 

function unesc($x)
{
    if (get_magic_quotes_gpc())
        return stripslashes($x);
    return $x;
} 

function mksize($bytes)
{
    if ($bytes < 1000 * 1024)
        return number_format($bytes / 1024, 2, ",", ".") . " KB";
    elseif ($bytes < 1000 * 1048576)
        return number_format($bytes / 1048576, 2, ",", ".") . " MB";
    elseif ($bytes < 1000 * 1073741824)
        return number_format($bytes / 1073741824, 2, ",", ".") . " GB";
    elseif ($bytes < 1000 * 1099511627776)
        return number_format($bytes / 1099511627776, 2, ",", ".") . " TB";
    else
        return number_format($bytes / 1125899906842624, 2, ",", ".") . " PB";
} 

function mksizeint($bytes)
{
    $bytes = max(0, $bytes);
    if ($bytes < 1000)
        return number_format(floor($bytes), 0, ",", ".") . " B";
    elseif ($bytes < 1000 * 1024)
        return number_format(floor($bytes / 1024), 0, ",", ".") . " KB";
    elseif ($bytes < 1000 * 1048576)
        return number_format(floor($bytes / 1048576), 0, ",", ".") . " MB";
    elseif ($bytes < 1000 * 1073741824)
        return number_format(floor($bytes / 1073741824), 0, ",", ".") . " GB";
    elseif ($bytes < 1000 * 1099511627776)
        return number_format(floor($bytes / 1099511627776), 0, ",", ".") . " TB";
    else
        return number_format(floor($bytes / 1125899906842624), 0, ",", ".") . " PB";
} 

function deadtime()
{
    return time() - floor($GLOBALS["ANNOUNCE_INTERVAL"] * 1.3);
} 

function mkprettytime($s)
{
    if ($s < 0)
        $s = 0;
    $t = array();
    foreach (array("60:sec", "60:min", "24:hour", "0:day") as $x) {
        $y = explode(":", $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        } else
            $v = $s;
        $t[$y[1]] = $v;
    } 

    if ($t["day"])
        return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    if ($t["hour"])
        return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]); 
    // if ($t["min"])
    return sprintf("%d:%02d", $t["min"], $t["sec"]); 
    // return $t["sec"] . " secs";
} 

function mkglobal($vars)
{
    if (!is_array($vars))
        $vars = explode(":", $vars);
    foreach ($vars as $v) {
        if (isset($_GET[$v]))
            $GLOBALS[$v] = unesc($_GET[$v]);
        elseif (isset($_POST[$v]))
            $GLOBALS[$v] = unesc($_POST[$v]);
        else
            return 0;
    } 
    return 1;
} 

function tr($x, $y, $noesc = 0)
{
    if ($noesc)
        $a = $y;
    else {
        $a = htmlspecialchars($y);
        $a = str_replace("\n", "<br />\n", $a);
    } 
    print("<tr><td class=\"tableb\" valign=\"top\" align=\"left\">$x</td><td class=\"tablea\" valign=\"top\" align=left>$a</td></tr>\n");
} 

function validfilename($name)
{
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
} 

function validemail($email)
{
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
} 

// Muss weg!
function sqlesc($x)
{
    return "'" . mysql_real_escape_string($x) . "'";
} 
// Muss weg!
function sqlwildcardesc($x)
{
    return str_replace(array("%", "_"), array("\\%", "\\_"), mysql_real_escape_string($x));
} 

function urlparse($m)
{
    $t = $m[0];
    if (preg_match(',^\w+://,', $t))
        return "<a href=\"$t\">$t</a>";
    return "<a href=\"http://$t\">$t</a>";
} 

function parsedescr($d, $html)
{
    if (!$html) {
        $d = htmlspecialchars($d);
        $d = str_replace("\n", "\n<br>", $d);
    } 
    return $d;
} 

function ratiostatbox()
{
    global $CURUSER;

    if ($CURUSER) {
        $ratio = ($CURUSER["downloaded"] > 0?number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 3, ",", "."):"Inf.");
        $seeds = $GLOBALS['database']->row_count('peers','`userid`=' . $CURUSER["id"] . ' AND `seeder`= yes') ?: 0;
        $leeches = $GLOBALS['database']->row_count('peers','`userid`=' . $CURUSER["id"] . ' AND `seeder`= no') ?: 0;
        $tlimits = get_torrent_limits($CURUSER);

        if ($ratio < 0.5) {
            $ratiowarn = " style=\"background-color:red;color:white;\"";
        } elseif ($ratio < 0.75) {
            $ratiowarn = " style=\"background-color:#FFFF00;color:black;\"";
        } 

        if ($tlimits["seeds"] >= 0) {
            if ($tlimits["seeds"] - $seeds < 1)
                $seedwarn = " style=\"background-color:red;color:white;\"";
			else
				$seedwarn = "";
            $tlimits["seeds"] = " / " . $tlimits["seeds"];
        } else
            $tlimits["seeds"] = "";
        if ($tlimits["leeches"] >= 0) {
            if ($tlimits["leeches"] - $leeches < 1)
                $leechwarn = " style=\"background-color:red;color:white;\"";
			else
				$leechwarn = "";
            $tlimits["leeches"] = " / " . $tlimits["leeches"];
        } else
            $tlimits["leeches"] = "";
        if ($tlimits["total"] >= 0) {
            if ($tlimits["total"] - $leeches + $seeds < 1)
                $totalwarn = " style=\"background-color:red;color:white;\"";
			else
				$totalwarn = "";
            $tlimits["total"] = " / " . $tlimits["total"];
        } else
            $tlimits["total"] = "";

        ?>
              <tr><td class="tabletitle" style="padding: 4px;"><b><?=htmlspecialchars($CURUSER["username"])?> :.</b></td></tr>
              <tr><td class="tablea" style="padding-left: 4px;">
	            <table cellspacing="0" cellpadding="2" border="0" style="width:140px;">
		          <tr><td><b>Download:</b></td><td style="text-align:right"><?=mksize($CURUSER["downloaded"])?></td></tr>
		          <tr><td><b>Upload:</b></td><td style="text-align:right"><?=mksize($CURUSER["uploaded"])?></td></tr>
		          <tr><td><b>Ratio:</b></td><td style="text-align:right" style="color:<?=get_ratio_color($ratio)?>"><?=$ratio?></td></tr>
		          <tr><td colspan="2">&nbsp;</td></tr>
		          <tr<?=$seedwarn?>><td><b>Seeds:</b></td><td style="text-align:right"><?=$seeds . $tlimits["seeds"]?></td></tr>
		          <tr<?=$leechwarn?>><td><b>Leeches:</b></td><td style="text-align:right"><?=$leeches . $tlimits["leeches"]?></td></tr>
		          <tr<?=$totalwarn?>><td><b>Gesamt:</b></td><td style="text-align:right"><?=($seeds + $leeches) . $tlimits["total"]?></td></tr>
		        </table>
              </td></tr>
<?php } 
} 

function stdhead($title = "", $msgalert = true)
{
    global $CURUSER, $_SERVER, $PHP_SELF, $BASEURL;

    if (!$GLOBALS["SITE_ONLINE"])
        die("Die Seite ist momentan aufgrund von Wartungsarbeiten nicht verfügbar.<br>");

    header("Content-Type: text/html; charset=iso-8859-1");
    header("Pragma: No-cache");
    header("Expires: 300");
    header("Cache-Control: private");

    if ($title == "")
        $title = $GLOBALS["SITENAME"];
    else
        $title = $GLOBALS["SITENAME"] . " :: " . htmlspecialchars($title);

    if ($CURUSER) {
		$qry = $GLOBALS['DB']->prepare('SELECT `uri` FROM `stylesheets` WHERE `id`= :id');
		$qry->bindParam(':id', $CURUSER["stylesheet"], PDO::PARAM_INT);
		$qry->execute();
		$ss_a = $qry->fetchObject();
        if ($ss_a) $GLOBALS["ss_uri"] = $ss_a->uri;
    }else{
		if (!isset($GLOBALS["ss_uri"])) {
			$qry = $GLOBALS['DB']->prepare("SELECT `uri` FROM `stylesheets` WHERE `default`='yes'");
			$qry->execute();
			if($qry->rowCount() > 0){
				$row = $qry->fetchObject();
			}
			$GLOBALS["ss_uri"] = $row->uri;
		} 
	}

    if ($msgalert && $CURUSER) {
		$unread = $GLOBALS['database']->row_count('messages','`folder_in`<>0 AND `receiver`=' . $CURUSER["id"] . ' && `unread`= yes');
		if($unread < 1)
			unset($unread);
        if ($CURUSER["class"] >= UC_MODERATOR) {
            $unread_mod = $GLOBALS['database']->row_count('messages','`sender`= 0 AND `receiver`= 0 && `mod_flag`= open');
			if($unread_mod < 1)
				unset($unread_mod);
        } 
    } 

    $fn = substr($PHP_SELF, strrpos($PHP_SELF, "/") + 1);
    $logo_pic = $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/";
    if (file_exists($logo_pic . "logo.gif")) $logo_pic .= "logo.gif";
    if (file_exists($logo_pic . "logo_top.gif")) $logo_pic .= "logo_top.gif";
    if (file_exists($logo_pic . "logo.jpg")) $logo_pic .= "logo.jpg";
    if (file_exists($logo_pic . "logo_top.jpg")) $logo_pic .= "logo_top.jpg";
    if (file_exists("header.jpg")) $logo_pic .= "header.jpg";
    if (file_exists($logo_pic . "header.gif")) $logo_pic .= "header.gif";

    ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?=$title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="300">
<meta http-equiv="cache-control" content="private">
<meta name="robots" content="noindex, nofollow, noarchive">
<meta name="MSSmartTagsPreventParsing" content="true">
<link rel="stylesheet" href="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/" . $GLOBALS["ss_uri"]?>.css" type="text/css">
<?php 
if ($GLOBALS["DYNAMIC_RSS"]) {
?>
<link rel="alternate" title="NetVision RSS" href="<?=$BASEURL?>/rss.php" type="application/rss+xml">
<link rel="alternate" title="NetVision RSS (Direktdownload)" href="<?=$BASEURL?>/rss.php?type=directdl" type="application/rss+xml">
<link rel="alternate" title="NetVision RSS (Benutzerkategorien)" href="<?=$BASEURL?>/rss.php?categories=profile" type="application/rss+xml">
<link rel="alternate" title="NetVision RSS (Benutzerkategorien, Direktdownload)" href="<?=$BASEURL?>/rss.php?categories=profile&type=directdl" type="application/rss+xml">
<?php
} 
?>
</head>
<body>

<table style="width:100%" cellpadding="0" cellspacing="1" align="center" border="0" class="tableoutborder">
  <tr>
    <td class="mainpage" align="center">
      <table style="width:100%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td class="logobackground" align="left"><a href="index.php?sid="><img src="<?=$logo_pic?>" border="0" alt="NetVision" title="NetVision" /></a></td>
        </tr>
        <tr>
          <td align="right" class="topbuttons" nowrap="nowrap"><span class="smallfont">
            <?php if ($GLOBALS["PORTAL_LINK"] != "") {

        ?><a href="<?=$GLOBALS["PORTAL_LINK"]?>"><img src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/top_portal.gif" border="0" alt="" title="Zum Portal" /></a><?php } 

    ?>
            </span>
          </td>
        </tr>
      </table>

      <table style="width:100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td valign="top" align="left" style="padding: 5px;width:150px">
            <table cellspacing="0" cellpadding="0" border="0" style="width:150px"><tr>
            <td align="left"><img src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/obenlinks.gif" alt="" title="" /></td>
            <td style="width:100%" class="obenmitte"></td>
            <td align="right"><img src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/obenrechts.gif" alt="" title="" /></td>
            </tr></table>
            <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableinborder">
            <?php if ($CURUSER && $CURUSER["statbox"] == "top") ratiostatbox();
    ?>              
              <tr><td class="tabletitle" style="padding: 4px;"><b>NetVision :.</b></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="index.php" title="Neuigkeiten vom Team sowie allgemeine Tracker-Stats und Umfragen">Tracker-News</a></td></tr>
              <?php if ($GLOBALS["PORTAL_LINK"] != "") {

        ?><tr><td class="tablea"><a style="display:block;padding:4px;" href="<?=$GLOBALS["PORTAL_LINK"]?>" title="Unser Portal und Forum f&uuml;r alles M&ouml;gliche">Portal</a></td></tr><?php } 

    ?>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="faq.php" title="Oft gestellte Fragen zu diversen trackerspezifischen Themen">FAQ</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="rules.php" title="Alle Verhaltensregeln f&uuml;r den Tracker - LESEPFLICHT!">Regeln</a></td></tr>
              <?php if ($CURUSER) {
        if ($GLOBALS["IRCAVAILABLE"]) {

            ?>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="chat.php" title="IRC-Serverdaten und ein einfach zu benutzendes Java-Applet">IRC Chat</a></td></tr>
              <?php } 

        ?>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="users.php" title="Liste aller Mitglieder, inkl. Suchfunktion">Mitglieder</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="topten.php" title="Diverse Top-Listen">Top 10</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="staff.php" title="Schnelle &Uuml;bersicht &uuml;ber das  Trackerteam">Team</a></td></tr>
              <tr><td class="tabletitle" style="padding: 4px;"><b>Torrents :.</b></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="browse.php" title="Verf&uuml;gbare Torrents anzeigen oder suchen">Durchsuchen</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="upload.php" title="Lade einen eigenen Torrent auf den Tracker hoch">Hochladen</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="mytorrents.php" title="Hier werden alle von Dir hochgeladenen Torrents angezeigt">Meine Torrents</a></td></tr>
              <?php if (get_user_class() >= UC_GUTEAM) {
            ?>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="guestuploads.php" title="Zeigt alle noch nicht freigeschalteten Gastuploads">Neue Gastuploads</a></td></tr>              
              <?php } 
        ?>
              <tr><td class="tabletitle" style="padding: 4px;"><b>Mein Account :.</b></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="userdetails.php?id=<?=$CURUSER["id"]?>" title="Deine Statistik-Seite, die auch andere Benutzer sehen">Mein Profil</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="my.php" title="Hier kannst Du Deine Einstellungen &auml;ndern">Profil bearbeiten</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="friends.php" title="Eine Liste Deiner &quot;Freunde&quot; auf dem Tracker">Buddyliste</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="messages.php" title="Pers&ouml;nliche Nachrichten lesen und beantworten">Nachrichten<?php
        if (isset($unread) || isset($unread_mod))
            echo "&nbsp;&nbsp;";

        if (isset($unread)) {
            echo "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "multipage.gif\" border=\"0\"> <b>$unread</b>";
            if ($unread_mod)
                echo "&nbsp;&nbsp;";
        } 

        if (isset($unread_mod)) {
            echo "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "multipagemod.gif\" border=\"0\"> <b>$unread_mod</b>";
        } 

        ?></a>
              </td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="bitbucket.php" title="Hier kannst Du Avatare und andere Bilder ablegen">BitBucket</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="logout.php" title="Beendet Deine Sitzung und l&ouml;scht die Autologin-Cookies">Ausloggen</a></td></tr>
              <?php if (get_user_class() >= UC_MODERATOR) {

            ?>
              <tr><td class="tabletitle" style="padding: 4px;"><b>Administration :.</b></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="log.php" title="Tracker-Logbuch anzeigen">Site Log</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="usersearch.php" title="Suche nach Benutzern &uuml;ber diverse Angaben">Benutzersuche</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="polls.php" title="Umfrageverwaltung">Umfragen</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="staff.php?act=last" title="Liste aller Benutzer, nach Anmeldedatum sortiert">Neueste Benutzer</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="staff.php?act=ban" title="Hier kannst Du IP-Bereiche vom Tracker aussperren">IPs sperren</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="staff.php?act=upstats" title="Schnelle &Uuml;bersicht &uuml;ber die Uploadaktivit&auml;ten">Uploader-Stats</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="bitbucket-gallery.php" title="Zeigt s&auml;mtliche BitBucket-Bilder an, nach Benutzern sortiert">BitBucket Gallerie</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="startstoplog.php" title="Diverse Werzeuge, die mit dem Eventlog verkn&uuml;pft sind">Start-/Stop-Log</a></td></tr>
              <?php if (get_user_class() >= UC_ADMINISTRATOR) {

                ?>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="adduser.php" title="Hier kannst Du einen neuen Account anlegen, der sofort aktiv ist">Account erstellen</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="staff.php?act=cleanaccs" title="Benutzer nach Ratio- und Aktivit&auml;tskriterien suchen und deaktivieren">Accountbereinigung</a></td></tr>
              <?php } 
        } 
    } else {

        ?>
              <tr><td class="tabletitle"><b>Account :.</b></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="signup.php">Registrieren</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="login.php">Einloggen</a></td></tr>
              <tr><td class="tablea"><a style="display:block;padding:4px;" href="recover.php">PW vergessen?</a></td></tr>
              <?php } 
    if ($CURUSER && $CURUSER["statbox"] == "bottom") ratiostatbox();

    ?>
            </table>
            <table cellspacing="0" cellpadding="0" border="0" style="width:150px"><tr>
            <td align="left"><img src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/untenlinks.gif" alt="" title="" /></td>
            <td style="width:100%" class="untenmitte" align="center"></td>
            <td align="right"><img src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/untenrechts.gif" alt="" title="" /></td>
            </tr></table>
          </td>
          <td valign="top" align="center" style="padding: 5px;width:100%">
<?php

} // stdhead
function stdfoot()
{
?>
					</td>
				</tr>
			</table>
		</td>
	</tr>  
</table>
</body>
</html>
<?php
} 

function genbark($x, $y)
{
    stdhead($y);

    ?>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr>
  <td class="tabletitle" colspan="10" width="100%"><span class="normalfont"><center><b> <?=htmlspecialchars($y)?> </b></center></span></td> 
 </tr><tr><td width="100%" class="tablea"><?=htmlspecialchars($x)?></td></tr></table><br>
<?php
    stdfoot();
    exit();
} 

function mksecret($len = 20)
{
    $ret = "";
    for ($i = 0; $i < $len; $i++)
    $ret .= chr(mt_rand(0, 255));
    return $ret;
} 

function httperr($code = 404)
{
    header("HTTP/1.0 404 Not found");
    print("<h1>Not Found</h1>\n");
    print("<p>Sorry pal :(</p>\n");
    exit();
} 

function gmtime()
{
    return strtotime(get_date_time());
} 

function deletetorrent($id, $owner = 0, $comment = "")
{
    global $CURUSER;

	$qry = $GLOBALS['DB']->prepare('SELECT name,numpics FROM torrents WHERE id = :id');
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	$torrent = $qry->fetchAll()[0];
	
    if (isset($torrent["numpics"]) && $torrent["numpics"] > 0) {
        for ($I = 1; $I <= $torrent["numpics"]; $I++) {
            @unlink($GLOBALS["BITBUCKET_DIR"] . "/t-" . $id . "-" . $I . ".jpg");
            @unlink($GLOBALS["BITBUCKET_DIR"] . "/f-" . $id . "-" . $I . ".jpg");
        } 
    } 
    @unlink($GLOBALS["BITBUCKET_DIR"] . "/nfo-" . $id . ".png");
	$qry = $GLOBALS['DB']->prepare('DELETE FROM torrents WHERE id = :id');
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	$qry = $GLOBALS['DB']->prepare('DELETE FROM traffic WHERE torrentid = :id');
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
    foreach(explode(".", "peers.files.comments.ratings.nowait") as $x){
		$qry = $GLOBALS['DB']->prepare('DELETE FROM :x WHERE torrent = :id');
		$qry->bindParam(':x', $x, PDO::PARAM_INT);
		$qry->bindParam(':id', $id, PDO::PARAM_INT);
		$qry->execute();
	}
    @unlink($GLOBALS["TORRENT_DIR"] . "/" . $id . ".torrent");

    if ($CURUSER && $owner > 0 && $CURUSER["id"] != $owner) {
        $msg = "Dein Torrent '" . $torrent['name'] . "' wurde von [url=" . $DEFAULTBASEURL . "/userdetails.php?id=" . $CURUSER["id"] . "]" . $CURUSER["username"] . "[/url] gelöscht.\n\n[b]Grund:[/b]\n" . $comment;
        sendPersonalMessage(0, $owner, "Einer Deiner Torrents wurde gelöscht", $msg, PM_FOLDERID_SYSTEM, 0);
    } 
} 

function pager($rpp, $count, $href, $opts = array())
{
    $pages = ceil($count / $rpp);

    if (!isset($opts["lastpagedefault"]))
        $pagedefault = 0;
    else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0)
            $pagedefault = 0;
    } 

    if (isset($_GET["page"])) {
        $page = 0 + $_GET["page"];
        if ($page < 0)
            $page = $pagedefault;
    } else
        $page = $pagedefault;

    $pager = "";

    $mp = $pages - 1;
    $as = "<b>&lt;&lt;&nbsp;Zurück</b>";
    if ($page >= 1) {
        $pager .= "<a href=\"{$href}page=" . ($page - 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    } else
        $pager .= $as;
    $pager .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $as = "<b>Weiter&nbsp;&gt;&gt;</b>";
    if ($page < $mp && $mp >= 0) {
        $pager .= "<a href=\"{$href}page=" . ($page + 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    } else
        $pager .= $as;

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted)
                    $pagerarr[] = "...";
                $dotted = 1;
                continue;
            } 
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count)
                $end = $count;
            $text = "$start&nbsp;-&nbsp;$end";
            if ($i != $page)
                $pagerarr[] = "<a href=\"{$href}page=$i\"><b>$text</b></a>";
            else
                $pagerarr[] = "<b>$text</b>";
        } 
        $pagerstr = join(" | ", $pagerarr);
        $pagertop = '<p align=\"center\">' . $pager . '<br />' . $pagerstr . '</p>';
        $pagerbottom = '<p align=\"center\">' . $pagerstr . '<br />' . $pager . '</p>';
    } else {
        $pagertop = '<p align=\"center\">' . $pager . '</p>';
        $pagerbottom = $pagertop;
    } 

    $start = $page * $rpp;

    return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
} 

// ???
// Wird diese Funktion genutzt?
/*function downloaderdata($res)
{
    $rows = array();
    $ids = array();
    $peerdata = array();
	//$qry = $GLOBALS['DB']->prepare('... WHERE id = :id');
	//$qry->bindParam(':id', $id, PDO::PARAM_INT);
	//$arr = $qry->execute()->fetchAll();
    while ($row = mysql_fetch_assoc($res)) {
        $rows[] = $row;
        $id = $row["id"];
        $ids[] = $id;
        $peerdata[$id] = array(downloaders => 0, seeders => 0, comments => 0);
    } 

    if (count($ids)) {
        $allids = implode(",", $ids);
        $res = mysql_query("SELECT COUNT(*) AS c, torrent, seeder FROM peers WHERE torrent IN ($allids) GROUP BY torrent, seeder");
        while ($row = mysql_fetch_assoc($res)) {
            if ($row["seeder"] == "yes")
                $key = "seeders";
            else
                $key = "downloaders";
            $peerdata[$row["torrent"]][$key] = $row["c"];
        } 
        $res = mysql_query("SELECT COUNT(*) AS c, torrent FROM comments WHERE torrent IN ($allids) GROUP BY torrent");
        while ($row = mysql_fetch_assoc($res)) {
            $peerdata[$row["torrent"]]["comments"] = $row["c"];
        } 
    } 

    return array($rows, $peerdata);
} */

function commenttable($rows)
{
    global $CURUSER, $_SERVER;

    $count = 0;
    foreach ($rows as $row) {
        begin_table(true);
        print("<colgroup><col width=\"150\"><col width=\"600\"></colgroup>\n");
        print("<tr><td colspan=\"2\" class=\"tablecat\">#" . $row["id"] . " by ");
        if (isset($row["username"])) {
            $title = $row["title"];
            if ($title == "")
                $title = get_user_class_name($row["class"]);
            else
                $title = htmlspecialchars($title);
            print("<a name=\"comm" . $row["id"] . "\" href=\"userdetails.php?id=" . $row["user"] . "\"><b>" .
                htmlspecialchars($row["username"]) . "</b></a>" . get_user_icons(array("donor" => $row["donor"], "enabled" => $row["enabled"], "warned" => $row["warned"])) . " ($title)\n");
        } else
            print("<a name=\"comm" . $row["id"] . "\"><i>(Gelöscht)</i></a>\n");

        print(" am " . $row["added"] .
            ($row["user"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR ? " - [<a href=\"comment.php?action=edit&amp;cid=$row[id]\">Bearbeiten</a>]" : "") .
            (get_user_class() >= UC_MODERATOR ? " - [<a href=\"comment.php?action=delete&amp;cid=$row[id]\">Löschen</a>]" : "") .
            ($row["editedby"] && get_user_class() >= UC_MODERATOR ? " - [<a href=\"comment.php?action=vieworiginal&amp;cid=$row[id]\">Original anzeigen</a>]" : "") . "</td></tr>\n");
        $avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($row["avatar"]) : "");
        if (!$avatar)
            $avatar = $GLOBALS["PIC_BASE_URL"] . "default_avatar.gif";
        $text = stripslashes(format_comment($row["text"]));
        if ($row["editedby"])
            $text .= "<p><font size=\"1\" class=\"small\">Zuletzt von <a href=\"userdetails.php?id=$row[editedby]\"><b>$row[username]</b></a> am $row[editedat] bearbeitet</font></p>\n";
        print("<tr valign=\"top\">\n");
        print("<td class=\"tableb\" align=\"center\" style=\"padding: 0px;width: 150px\"><img width=\"150\" src=\"$avatar\" alt=\"Avatar von $row[username]\"></td>\n");
        print("<td class=\"tablea\">$text</td>\n");
        print("</tr>\n");
        end_table();
    } 
} 

function searchfield($s)
{
    //return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
    return $s;
} 

function genrelist()
{
    $ret = array();
	$rows = $GLOBALS['DB']->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
	foreach($rows as $row)
		$ret[] = $row;
    return $ret;
} 

function linkcolor($num)
{
    if ($num == 0)
        return "red";
    return "black";
} 

function ratingpic($num)
{
    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5)
        return;
    return "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "$r.gif\" border=\"0\" alt=\"rating: $num / 5\" />";
} 

function browse_sortlink($field, $params)
{
    if ($field == $_GET["orderby"]) {
        return "browse.php?orderby=$field&amp;sort=" . ($_GET["sort"] == "asc" ? "desc" : "asc") . "&amp;$params";
    } else {
        return "browse.php?orderby=$field&amp;sort=" . ($_GET["sort"] == "desc" ? "desc" : "asc") . "&amp;$params";
    } 
} 

function torrenttable_row_oldschool($torrent_info)
{
    global $CURUSER;

    if (strlen($torrent_info["name"]) > 45)
        $displayname = substr($torrent_info["name"], 0, 45) . "...";
    else
        $displayname = $torrent_info["name"];

    $returnto = "&amp;returnto=" . urlencode($_SERVER["REQUEST_URI"]);
    $baselink = "details.php?id=" . $torrent_info["id"];
    if ($torrent_info["variant"] == "index") {
        $baselink .= "&amp;hit=1";
        $filelistlink = $baselink . "&amp;filelist=1";
        $commlink = $baselink . "&amp;tocomm=1";
        $seederlink = $baselink . "&amp;toseeders=1";
        $leecherlink = $baselink . "&amp;todlers=1";
        $snatcherlink = $baselink . "&amp;tosnatchers=1";
    } else {
        $baselink .= $returnto;
        $filelistlink = $baselink . "&amp;filelist=1#filelist";
        $commlink = $baselink . "&amp;page=0#startcomments";
        $seederlink = $baselink . "&amp;dllist=1#seeders";
        $leecherlink = $baselink . "&amp;dllist=1#leechers";
        $snatcherlink = $baselink . "&amp;snatcher=1#snatcher";
    } 

    if ($torrent_info["leechers"])
        $ratio = $torrent_info["seeders"] / $torrent_info["leechers"];
    elseif ($torrent_info["seeders"])
        $ratio = 1;
    else
        $ratio = 0;

    $seedercolor = get_slr_color($ratio);

    ?>
<tr>
  <td class="tableb" valign="top" style="width:1px;padding:0px;"><?php
    if (!isset($torrent_info["cat_pic"]))
        if ($torrent_info["cat_name"] != "")
            echo "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\">" . $torrent_info["cat_name"] . "</a>";
        else
            echo "-";
        else
            echo "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $torrent_info["cat_pic"] . "\" alt=\"" . $torrent_info["cat_name"] . "\" title=\"" . $torrent_info["cat_name"] . "\" border=\"0\"></a>";

        ?></td>
  <td class="tablea" style="text-align:left;vertical-align:middle;" nowrap="nowrap"><?php if (isset($torrent_info["uploaderclass"]) && $torrent_info["uploaderclass"] < UC_UPLOADER) {
            echo '<font color="red">[GU]</font> ';
        } 

        ?><a href="<?=$baselink?>" title="<?=htmlspecialchars($torrent_info["name"]);

        ?>"><b><?=htmlspecialchars($displayname)?></b></a><?php if ($torrent_info["variant"] != "guestuploads" && $torrent_info["is_new"]) echo " <font style=\"color:red\">(NEU)</font>";

        ?></td>
  <?php if ($torrent_info["variant"] == "mytorrents") {
            ?>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><a href="edit.php?id=<?=$torrent_info["id"] . $returnto?>">Bearbeiten</a></td>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><?=($torrent_info["visible"] == "yes"?"Ja":"Nein")?></td>
  <?php } elseif ($torrent_info["variant"] == "guestuploads") {
            ?>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><?php if ($torrent_info["gu_agent"] > 0) echo "Ja";
            else echo "<font color=\"red\">Nein</font>";
            ?></td>  
  <?php } elseif ($torrent_info["has_wait"]) {

            ?>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><?php echo "<font color=\"", $torrent_info["wait_color"], "\">", $torrent_info["wait_left"], "<br>Std.</font>";

            ?></td>
  <?php } 

        ?>
  <td class="tablea" style="text-align:right;vertical-align:middle;" nowrap="nowrap"><a href="<?=$filelistlink?>"><?=$torrent_info["numfiles"]?></a></td>
  <td class="tablea" style="text-align:right;vertical-align:middle;" nowrap="nowrap"><a href="<?=$commlink?>"><?=$torrent_info["comments"]?></a></td>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><?=str_replace("&nbsp;", "<br>", $torrent_info["added"])?></td>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><?=str_replace(" ", "<br>", $torrent_info["ttl"])?></td>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><?=str_replace(" ", "<br>", mksize($torrent_info["size"]))?></td>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><div style="border:1px solid black;padding:0px;width:60px;height:10px;"><div style="border:none;width:<?=60 * $torrent_info["dist"] / 100?>px;height:10px;background-image:url(<?=$GLOBALS["PIC_BASE_URL"]?>ryg-verlauf-small.png);background-repeat:no-repeat;"></div></div></td>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><a href="<?=$snatcherlink?>"><?=$torrent_info["times_completed"]?></a></td>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><a href="<?=$seederlink?>"><font color="<?=$seedercolor?>"><?=intval($torrent_info["seeders"])?></font></a></td>
  <td class="tablea" style="text-align:center;vertical-align:middle;" nowrap="nowrap"><a href="<?=$leecherlink?>"><font color="<?=linkcolor($torrent_info["seeders"])?>"><?=intval($torrent_info["leechers"])?></font></a></td>
  <td class="tablea" style="text-align:left;vertical-align:middle;" nowrap="nowrap">D:&nbsp;<?=$torrent_info["dlspeed"]?>&nbsp;KB/s<br>U:&nbsp;<?=$torrent_info["ulspeed"]?>&nbsp;KB/s</td>
  <?php if ($torrent_info["variant"] == "index") {

            ?>
  <td class="tablea" style="text-align:left;vertical-align:middle;" nowrap="nowrap"><?=$torrent_info["uploaderlink"]?></td>
  <?php } 

        ?>  
</tr>
<?php
    } 

    function torrenttable_row($torrent_info)
    {
        global $CURUSER;

        $returnto = "&amp;returnto=" . urlencode($_SERVER["REQUEST_URI"]);
        $baselink = "details.php?id=" . $torrent_info["id"];
        if ($torrent_info["variant"] == "index") {
            $baselink .= "&amp;hit=1";
            $filelistlink = $baselink . "&amp;filelist=1";
            $commlink = $baselink . "&amp;tocomm=1";
            $seederlink = $baselink . "&amp;toseeders=1";
            $leecherlink = $baselink . "&amp;todlers=1";
            $snatcherlink = $baselink . "&amp;tosnatchers=1";
        } else {
            $baselink .= $returnto;
            $filelistlink = $baselink . "&amp;filelist=1#filelist";
            $commlink = $baselink . "&amp;page=0#startcomments";
            $seederlink = $baselink . "&amp;dllist=1#seeders";
            $leecherlink = $baselink . "&amp;dllist=1#leechers";
            $snatcherlink = $baselink . "&amp;snatcher=1#snatcher";
        } 

        if ($torrent_info["leechers"])
            $ratio = $torrent_info["seeders"] / $torrent_info["leechers"];
        elseif ($torrent_info["seeders"])
            $ratio = 1;
        else
            $ratio = 0;

        $seedercolor = get_slr_color($ratio);

		//$qry = $GLOBALS['DB']->prepare('SELECT DISTINCT(user_id) as id, username, class, peers.id as peerid FROM completed,users LEFT JOIN peers ON peers.userid=users.id AND peers.torrent= :tid AND peers.seeder= yes WHERE completed.user_id=users.id AND completed.torrent_id= :tid ORDER BY complete_time DESC');
		//$qry = $GLOBALS['DB']->prepare('SELECT DISTINCT(user_id) as id, username, class, peers.id as peerid FROM completed,users LEFT JOIN peers ON peers.userid=users.id AND peers.torrent= :tid AND peers.seeder= yes WHERE completed.user_id=users.id AND completed.torrent_id= :tid ORDER BY complete_time DESC LIMIT 10');
		//$qry->bindParam(':tid', $torrent_info["id"], PDO::PARAM_INT);
		//$res = $qry->execute()->fetchAll();
		//$rows = $GLOBALS['DB']->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
        $res = mysql_query("SELECT DISTINCT(user_id) as id, username, class, peers.id as peerid 
		FROM completed,users 
		LEFT JOIN peers ON peers.userid=users.id 
		AND peers.torrent=" . $torrent_info["id"] . " 
		AND peers.seeder='yes' 
		WHERE completed.user_id=users.id 
		AND completed.torrent_id=" . $torrent_info["id"] . " 
		ORDER BY complete_time DESC LIMIT 10");

        $last10users = "";
        //foreach($res as $arr){
		while ($arr = mysql_fetch_assoc($res)) {
            if ($last10users) $last10users .= ", ";
            $arr["username"] = "<font class=\"" . get_class_color($arr["class"]) . "\">" . $arr["username"] . "</font>";
            if ($arr["peerid"] > 0) {
                $arr["username"] = "<b>" . $arr["username"] . "</b>";
            } 
            $last10users .= "<a href=\"userdetails.php?id=" . $arr["id"] . "\">" . $arr["username"] . "</a>";
        } 

        if ($last10users == "")
            $last10users = "Diesen Torrent hat noch niemand fertiggestellt.";
        else
            $last10users .= "<br/><br/>(Fettgedruckte User seeden noch)";

        if ($GLOBALS["DOWNLOAD_METHOD"] == DOWNLOAD_REWRITE)
            $download_url = "download/" . $torrent_info["id"] . "/" . rawurlencode($torrent_info["filename"]);
        else
            $download_url = "download.php?torrent=" . $torrent_info["id"];

        ?>
<tr>
  <td class="tableb" valign="top" style="color:#FFFFFF;width:1px;"><?php
        if (!isset($torrent_info["cat_pic"]))
            if ($torrent_info["cat_name"] != "")
                echo "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\">" . $torrent_info["cat_name"] . "</a>";
            else
                echo "-";
            else
                echo "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $torrent_info["cat_pic"] . "\" alt=\"" . $torrent_info["cat_name"] . "\" title=\"" . $torrent_info["cat_name"] . "\" border=\"0\"></a>";

            ?></td>
  <td class="tablea" valign="top" align="left">
    <table cellpadding="2" cellspacing="2" border="0" style="width:100%">
      <colgroup>
        <col width="20%">
        <col width="20%">
        <col width="20%">
        <col width="20%">
        <col width="20%">
      </colgroup>
      <tr>
        <td colspan="4" nowrap>
          <a href="javascript:expandCollapse('<?=$torrent_info["id"]?>');"><img id="plusminus<?=$torrent_info["id"]?>" src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/plus.gif" alt="Auf-/Zuklappen" border="0"></a>
          <?php if ($torrent_info["variant"] != "index") {

                ?>[ <a href="edit.php?id=<?=$torrent_info["id"] . $returnto?>">Bearbeiten</a> ]<?php } 

            ?>
          <?php if (isset($torrent_info["uploaderclass"]) && $torrent_info["uploaderclass"] < UC_UPLOADER) {
                echo '<font color="red">[GU]</font> ';
            } 

            ?>
          <a href="<?=$baselink?>"><b><?=$torrent_info["name"]?></b></a><?php if ($torrent_info["variant"] != "guestuploads" && $torrent_info["is_new"]) echo " <font style=\"color:red\">(NEU)</font>";
            if ($torrent_info["variant"] == "guestuploads" && $torrent_info["gu_agent"] > 0) echo " <font style=\"color:red\">(Bereits in Bearbeitung)</font>";

            ?>
        </td>
        <td nowrap>
          <?=($torrent_info["variant"] == "mytorrents"?"Hochgeladen am:":"Von: " . $torrent_info["uploaderlink"])?>
        </td>
      </tr>
      <tr>
        <td style="font-size:90%"><b><?=mksize($torrent_info["size"])?></b> in <b><?=$torrent_info["numfiles"]?></b> <a href="<?=$filelistlink?>">Datei(en)</a></td>
        <td style="font-size:90%">
          <b><font color="<?=$seedercolor?>"><?=intval($torrent_info["seeders"])?></font></b> <a href="<?=$seederlink?>">Seeder</a> &amp;
          <b><font color="<?=linkcolor($torrent_info["seeders"])?>"><?=intval($torrent_info["leechers"])?></font></b> <a href="<?=$leecherlink?>">Leecher</a></td>
        <td style="font-size:90%"><b><?=$torrent_info["times_completed"]?></b>x <a href="<?=$snatcherlink?>">heruntergeladen</a></td>
        <td style="font-size:90%"><b><?=$torrent_info["comments"]?></b> <a href="<?=$commlink?>">Kommentare</a></td>
        <td style="font-size:90%"><?=$torrent_info["added"]?></td>
      </tr>
    </table>
    <div id="details<?=$torrent_info["id"]?>" style="display:none;">
      <?php
            if ($torrent_info["seeders"] == 0 && $torrent_info["variant"] == "index")
                echo "<div style=\"padding:4px;\"><b><font color=\"red\">HINWEIS:</font></b> Es sind keine Seeder für diesen Torrent aktiv. ",
                "Dies bedeutet, dass Du diesen Torrent wahrscheinlich nicht fertigstellen kannst, solange nicht wieder ein Seeder aktiv wird. ",
                "Sollte der Torrent längere Zeit inaktiv gewesen und als \"Tot\" markiert worden sein, solltest Du im Forum um einen Reseed bitten, ",
                "Falls Du noch Interesse daran hast.</div>";

            ?>
      <table cellspacing="2" cellpadding="2" border="0" class="inposttable" style="width:100%;">
        <colgroup>
          <col width="20%">
          <col width="80%">
        </colgroup>
        <?php
            if ($torrent_info["variant"] == "mytorrents") {
                echo "<tr><td nowrap valign=\"top\"><b>Sichtbar:</b></td><td>", ($torrent_info["visible"] == "yes"?"Ja":"Nein, dieser Torrent ist inaktiv und als \"Tot\" markiert"), "</td></tr>";
            } 
            if ($torrent_info["has_wait"]) {
                echo "<tr><td nowrap valign=\"top\"><b>Wartezeit:</b></td><td><font color=\"",
                $torrent_info["wait_color"], "\">", $torrent_info["wait_left"],
                " Stunde(n)</font></td></tr>";
            } 

            ?>
        <tr><td nowrap="nowrap" valign="top"><b>Letzte 10 Downloader:</b></td><td><?=$last10users?></td></tr>
        <tr><td nowrap="nowrap" valign="top"><b>&Oslash; Downloadgeschw.:</b></td><td><?=$torrent_info["dlspeed"]?> KB/s (<?php echo $torrent_info["dlspeed"] * ($torrent_info["leechers"] + $torrent_info["seeders"]);

            ?> KB/s gesamt)</td></tr>
        <tr><td nowrap="nowrap" valign="top"><b>&Oslash; Uploadgeschw.:</b></td><td><?=$torrent_info["ulspeed"]?> KB/s (<?php echo $torrent_info["ulspeed"] * ($torrent_info["leechers"] + $torrent_info["seeders"]);

            ?> KB/s gesamt)</td></tr>
        <tr><td nowrap="nowrap" valign="top"><b>Letzte Aktivit&auml;t:</b></td><td><?=$torrent_info["last_action"]?></td></tr>
        <tr><td nowrap="nowrap" valign="top"><b>Verbleibende Zeit:</b></td><td><?=$torrent_info["ttl"]?> (falls inaktiv, sonst l&auml;nger)</td></tr>
        <tr><td nowrap="nowrap" valign="top"><b>&Oslash; Verteilung:</b></td><td><div style="border:1px solid black;padding:0px;width:300px;height:15px;"><div style="border:none;width:<?=300 * $torrent_info["dist"] / 100?>px;height:15px;background-image:url(<?=$GLOBALS["PIC_BASE_URL"]?>ryg-verlauf.png);background-repeat:no-repeat;"></div></div></td></tr>
      </table>
    </div>
  <td class="tableb" valign="top" align="center" style="width:22px;padding:4px;padding-top:10px;">
    <?php if ($torrent_info["variant"] == "guestuploads") {
                if ($torrent_info["gu_agent"] == 0)
                    echo "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "nodl.png\" width=\"22\" height=\"22\" alt=\"Nicht in Bearbeitung\" title=\"Nicht in Bearbeitung\">";
                else
                    echo "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "taken.png\" width=\"22\" height=\"22\" alt=\"Bereits in Bearbeitung\" title=\"Bereits in Bearbeitung\">";
            } else {
                if ($torrent_info["activated"] == "yes") {
                    if (!isset($torrent_info["wait_left"]) || $torrent_info["wait_left"] == 0) {

                        ?>
    <a href="<?=$download_url?>"><img src="<?=$GLOBALS["PIC_BASE_URL"]?>download.png" width="22" height="22" alt="Torrent herunterladen" title="Torrent herunterladen" border="0"></a>
    <?php } else {

                        ?>
    <img src="<?=$GLOBALS["PIC_BASE_URL"]?>nodl.png" width="22" height="22" alt="Wartezeit nicht abgelaufen" title="Wartezeit nicht abgelaufen" border="0"
    <?php } 
                } else {

                    ?>
    <img src="<?=$GLOBALS["PIC_BASE_URL"]?>nodl.png" width="22" height="22" alt="Torrent nicht freigeschaltet" title="Torrent nicht freigeschaltet" border="0"
    <?php } 
            } 

            ?>
  </td>
</tr>
<?php
        } 

        function torrenttable($res, $variant = "index", $addparam = "")
        {
            global $CURUSER; 
            // Sortierkriterien entfernen
            $addparam_nosort = preg_replace(array("/orderby=(.*?)&amp;/i", "/sort=(.*?)&amp;/i"), array("", ""), $addparam); 
            // Hat dieser Benutzer Wartezeit?
            $has_wait = get_wait_time($CURUSER["id"], 0, true);

            ?>
<script type="text/javascript">

function expandCollapse(torrentId)
{
    var plusMinusImg = document.getElementById("plusminus"+torrentId);
    var detailRow = document.getElementById("details"+torrentId);

    if (plusMinusImg.src.indexOf("<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/plus.gif") >= 0) {
        plusMinusImg.src = "<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/minus.gif";
        detailRow.style.display = "block";
    } else {
        plusMinusImg.src = "<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/plus.gif";
        detailRow.style.display = "none";
    }
}

</script>
<table cellspacing="0" cellpadding="0" border="0" style="width:100%"><tr>
        <td align="left"><img src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/obenlinks.gif" alt="" title="" /></td>
<td style="width:100%" class="obenmitte"></td>
        <td align="right"><img src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/obenrechts.gif" alt="" title="" /></td>
</tr></table>
<?php

            if ($CURUSER["oldtorrentlist"] == "yes") {

                ?>
<table border="0" cellspacing="1" cellpadding="4" class="tableinborder" style="width:100%">
<tr>
  <td class="tablecat" align="center">Typ</td>
  <td class="tablecat" align="left">Name</td>
  <?php if ($variant == "mytorrents") {

                    ?>
  <td class="tablecat" align="center">Bearbeiten</td>
  <td class="tablecat" align="center">Sichtbar</td>  
  <?php } elseif ($variant == "guestuploads") {

                    ?>
  <td class="tablecat" align="center">In&nbsp;Bearbeitung</td>
  <?php } elseif ($has_wait) {

                    ?>
  <td class="tablecat" align="center">Wartez.</td>  
  <?php } 

                ?>
  <td class="tablecat" align="right">Dateien</td>
  <td class="tablecat" align="right">Komm.</td>
  <td class="tablecat" align="center">Hinzugef.</td>
  <td class="tablecat" align="center">TTL</td>
  <td class="tablecat" align="center">Gr&ouml;&szlig;e</td>
  <td class="tablecat" align="center">Verteilung</td>
  <td class="tablecat" align="center">Fertig</td>
  <td class="tablecat" align="right">Seeder</td>
  <td class="tablecat" align="right">Leecher</td>
  <td class="tablecat" align="left">&Oslash;&nbsp;Geschw.</td>
  <?php if ($variant == "index") {

                    ?>
  <td class="tablecat" align="center">Uploader</td>          
  <?php } 

                ?>
</tr>               
            <?php
            } else {

                ?>
<table border="0" cellspacing="1" cellpadding="0" class="tableinborder" style="width:100%">
  <colgroup>
    <col width="32">
    <col width="100%">
    <col width="22">
  </colgroup>
            <?php
            } while ($row = mysql_fetch_assoc($res)) {
                $id = $row["id"];

                $torrent_info = array();
                $torrent_info["id"] = $row["id"];
                $torrent_info["name"] = htmlspecialchars($row["name"]);
                $torrent_info["activated"] = (isset($row["activated"])?$row["activated"]:"yes");
                $torrent_info["gu_agent"] = (isset($row["gu_agent"])?$row["gu_agent"]:0);
                $torrent_info["filename"] = $row["filename"];
                $torrent_info["variant"] = $variant;
                $torrent_info["category"] = $row["category"];
                $torrent_info["cat_name"] = $row["cat_name"];
                $torrent_info["type"] = $row["type"];
                $torrent_info["numfiles"] = ($row["type"] == "single"?1:$row["numfiles"]);
                $torrent_info["size"] = $row["size"];
                $torrent_info["times_completed"] = intval($row["times_completed"]);
                $torrent_info["seeders"] = $row["seeders"];
                $torrent_info["leechers"] = $row["leechers"];
                $torrent_info["uploaderlink"] = (isset($row["username"]) ? ("<a href=\"userdetails.php?id=" . $row["owner"] . "\"><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>(Gelöscht)</i>");
                $torrent_info["added"] = str_replace(" ", "&nbsp;", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($row["added"])));
                $torrent_info["comments"] = $row["comments"];
                $torrent_info["visible"] = $row["visible"];
                $torrent_info["last_action"] = str_replace(" ", "&nbsp;", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($row["last_action"])));

                if (isset($row["cat_pic"]) && $row["cat_pic"] != "")
                    $torrent_info["cat_pic"] = $row["cat_pic"];

                if (isset($row["uploaderclass"]))
                    $torrent_info["uploaderclass"] = $row["uploaderclass"];

                $torrent_info["has_wait"] = $has_wait;
                if ($has_wait) {
                    $torrent_info["wait_left"] = get_wait_time($CURUSER["id"], $id);
                    $torrent_info["wait_color"] = dechex(floor(127 * ($wait_left) / 48 + 128) * 65536);
                } 

                $speedres = mysql_query("SELECT ROUND(AVG((downloaded - downloadoffset) / (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`started`)))/1024, 2) AS dlspeed, ROUND(AVG((uploaded - uploadoffset) / (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`started`)))/1024, 2) AS ulspeed FROM peers WHERE torrent=$id");
                $speed = mysql_fetch_assoc($speedres);
                if ($speed["dlspeed"] == 0) $speed["dlspeed"] = "0";
                if ($speed["ulspeed"] == 0) $speed["ulspeed"] = "0";
                $torrent_info["dlspeed"] = $speed["dlspeed"];
                $torrent_info["ulspeed"] = $speed["ulspeed"];

                $distres = mysql_query("SELECT ROUND(AVG((" . $row["size"] . " - `to_go`) / " . $row["size"] . " * 100),2) AS `dist` FROM `peers` WHERE torrent=$id");
                $dist = mysql_fetch_assoc($distres);
                $torrent_info["dist"] = $dist["dist"];

                $ttl = (28 * 24) - floor((time() - sql_timestamp_to_unix_timestamp($row["added"])) / 3600);
                if ($ttl == 1) $ttl .= " Stunde";
                else $ttl .= " Stunden";
                $torrent_info["ttl"] = $ttl;

                $newadd = "";

                $torrentunix = sql_timestamp_to_unix_timestamp($row["added"]);
                $accessunix = sql_timestamp_to_unix_timestamp($CURUSER["last_access"]);

                if ($torrentunix >= $accessunix)
                    $torrent_info["is_new"] = true;

                if ($CURUSER["oldtorrentlist"] == "yes") {
                    torrenttable_row_oldschool($torrent_info);
                } else {
                    torrenttable_row($torrent_info);
                } 
            } 

            ?>
</table>
<table cellspacing="0" cellpadding="0" border="0" style="width:100%">
	<tr>
		<td align="left"><img src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/untenlinks.gif" alt="" title="" /></td>
		<td style="width:100%" class="untenmitte" align="center"></td>
		<td align="right"><img src="<?=$GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"]?>/untenrechts.gif" alt="" title="" /></td>
	</tr>
</table>
<?php
            return $rows;
        } 

        function hit_start()
        {
            global $RUNTIME_START, $RUNTIME_TIMES; 
            // $RUNTIME_TIMES = posix_times();
            $RUNTIME_START = gettimeofday();
        } 

		// es gibt keine table "hits"
		// TODO!!
        function hit_count()
        {
            return;
            global $RUNTIME_CLAUSE;
            if (preg_match(',([^/]+)$,', $_SERVER["SCRIPT_NAME"], $matches))
                $path = $matches[1];
            else
                $path = "(unknown)";
            $period = date("Y-m-d H") . ":00:00";
            $RUNTIME_CLAUSE = 'page = ' . $path . ' AND period = ' . $period;
			$qry = $GLOBALS['DB']->prepare("UPDATE hits SET count = count + 1 WHERE page = :path AND period = :period");
			$qry->bindParam(':path', $path, PDO::PARAM_STR);
			$qry->bindParam(':period', $period, PDO::PARAM_STR);
			$qry->execute();
            if ($qry->rowCount())
                return;
			else{
				$qry = $GLOBALS['DB']->prepare('INSERT INTO hits (page, period, count) VALUES (:path, :period, 1)');
				$qry->bindParam(':path', $path, PDO::PARAM_STR);
				$qry->bindParam(':period', $period, PDO::PARAM_STR);
				$qry->execute();
			}
        } 

        function hit_end()
        {
            return;
            global $RUNTIME_START, $RUNTIME_CLAUSE, $RUNTIME_TIMES;
            if (empty($RUNTIME_CLAUSE))
                return;
            $now = gettimeofday();
            $runtime = ($now["sec"] - $RUNTIME_START["sec"]) + ($now["usec"] - $RUNTIME_START["usec"]) / 1000000;
            $ts = posix_times();
            $sys = ($ts["stime"] - $RUNTIME_TIMES["stime"]) / 100;
            $user = ($ts["utime"] - $RUNTIME_TIMES["utime"]) / 100;
			$qry = $GLOBALS['DB']->prepare('UPDATE hits SET runs = runs + 1, runtime = runtime + :rt, user_cpu = user_cpu + :user, sys_cpu = sys_cpu + :sys WHERE :rtc');
			$qry->bindParam(':rt', $runtime, PDO::PARAM_STR);
			$qry->bindParam(':user', $user, PDO::PARAM_STR);
			$qry->bindParam(':sys', $sys, PDO::PARAM_STR);
			$qry->bindParam(':rtc', $RUNTIME_CLAUSE, PDO::PARAM_STR);
			$qry->execute();
        }

        function hash_pad($hash)
        {
            return str_pad($hash, 20);
        } 

        function hash_where($name, $hash)
        {
            $shhash = preg_replace('/ *$/s', "", $hash);
            return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
        } 

        function get_user_icons($arr, $big = false)
        {
            if ($big) {
                $donorpic = "starbig.png";
                $warnedpic = "warnedbig.gif";
                $disabledpic = "disabledbig.png";
                $newbiepic = "pacifierbig.png";
                $style = "style=\"margin-left:4pt;vertical-align:middle;\"";
            } else {
                $donorpic = "star.png";
                $warnedpic = "warned.gif";
                $disabledpic = "disabled.png";
                $newbiepic = "pacifier.png";
                $style = "style=\"margin-left:2pt;vertical-align:middle;\"";
            } 

            $pics = $arr["donor"] == "yes" ? "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $donorpic . "\" alt=\"Spender\" title=\"Dieser Benutzer hat mit einer Spende zum Erhalt des Trackers beigetragen\" border=0 $style>" : "";

            if (isset($arr["warned"]) && $arr["warned"] == "yes")
                $pics .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $warnedpic . "\" alt=\"Verwarnt\" title=\"Dieser Benutzer wurde verwarnt\" border=0 $style>";

            if (isset($arr["enabled"]) && $arr["enabled"] == "no")
                $pics .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $disabledpic . "\" alt=\"Deaktiviert\" title=\"Dieser Benutzer ist deaktiviert\" border=0 $style>";

            $timeadded = intval(sql_timestamp_to_unix_timestamp($arr["added"]));
            if ($timeadded > 0 && time() - $timeadded < 604800)
                $pics .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $newbiepic . "\" alt=\"Newbie\" title=\"Ist noch neu hier\" border=0 $style>";
            return $pics;
        } 

        require_once("include/global.php");
        require_once("include/pmfunctions.php");

        ?>
