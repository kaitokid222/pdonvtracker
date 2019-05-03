<?php
error_reporting(E_ALL);
//error_reporting(0);
date_default_timezone_set('Europe/Berlin');
ini_set('date.timezone', 'Europe/Berlin');
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
if (!file_exists("include/secrets.php") || !file_exists("include/config.php"))
    die("<html><head><title>FEHLER</title></head><body><p>Der Tracker wurde noch nicht konfiguriert.</p>
        <p><a href=\"inst/install.php\">Zum Installationsscript</a></p></body></html>");

// legacy parts
require_once("include/secrets.php");
require_once("include/config.php");
require_once("include/cleanup.php"); // neubauen class cleanup
require_once("include/shoutcast.php"); // kein plan. scheint zu funktionieren
require_once("include/std.php");
$GLOBALS["SCRIPT_START_TIME"] = unique_ts();

//require_once("include/global.php");
require_once("include/pmfunctions.php");

require_once("include/class/db.php");
$database = new db($dsn);
$GLOBALS['DB'] = $database->getPDO();

/*
// TODO
// require_once("include/class/usergenerator.php");
// $UserGenerator = new usergenerator();
// $CURUSER = $UserGenerator->getUser();
*/

require_once("include/class/polls.php");
require_once("include/class/shoutbox.php");
require_once("include/class/tupload.php");
require_once("include/class/user.php");
require_once("include/class/rating.php");
require_once("include/class/holidays.php");
require_once("include/class/vouchers.php");

function unique_ts(){
	$milliseconds = microtime();
	$timestring = explode(" ", $milliseconds);
	$sg = $timestring[1];
	$mlsg = substr($timestring[0], 2, 4);
	$timestamp = $sg.$mlsg;
	return $timestamp;
}

function local_user(){
	global $_SERVER;
	return $_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"];
}

function set_last_access($id){
	$latime = date("Y-m-d H:i:s");
	$qry = $GLOBALS['DB']->prepare('UPDATE users SET last_access= :date WHERE id= :id');
	$qry->bindParam(':date', $latime, PDO::PARAM_STR);
	$qry->bindParam(':id', $GLOBALS["CURUSER"]["id"], PDO::PARAM_INT);
	$qry->execute();
}
	
//https://inkplant.com/code/ipv6-to-number
function ipaddress_to_ipnumber($ipaddress) {
	$pton = @inet_pton($ipaddress);
	if (!$pton) { return false; }
	$number = '';
	foreach (unpack('C*', $pton) as $byte) {
		$number .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
	}
	return base_convert(ltrim($number, '0'), 2, 10);
}

function check_ip_version($ip){
	if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false){
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
			return 4;
		else
			return false;
	}else
		return 6;
		
}

function is_valid_ip($ip){
	$ipv = check_ip_version($ip);
	if($ipv == 4){
		if (!preg_match("/^(\\d{1,3})\\.(\\d{1,3})\\.(\\d{1,3})\\.(\\d{1,3})$/", $ip))
			return false;
		$parts = explode(".", $_GET["ip"]);
		foreach($parts as $part){
			if (intval($part)<0 || intval($part)>255)
				return false;
		}
		return true;
	}elseif($ipv == 6){
		if(!preg_match("/^([0-9a-f\.\/:]+)$/",strtolower($ip)))
			return false;
		if(substr_count($ip,":") < 2)
			return false;
		$part = preg_split("/[:\/]/",$ip);
		foreach($part as $i){
			if(strlen($i) > 4)
				return false;
		}
		return true;
	}else
		return false;
}

function validip($ip){
	if(isset($ip)){
		if(ipaddress_to_ipnumber($ip) > 0){
			if(check_ip_version($ip) !== false){
				if(is_valid_ip($ip)){
					if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE))
						return true;
					else
						return false;
				}else
					return false;
			}else
				return false;
		}else
			return false;
	}else
		return false;
} 

function getip(){
	$client  = @$_SERVER['HTTP_CLIENT_IP'];
	$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	$remote  = $_SERVER['REMOTE_ADDR'];
	if(filter_var($client, FILTER_VALIDATE_IP))
		$ip = $client;
	elseif(filter_var($forward, FILTER_VALIDATE_IP))
		$ip = $forward;
	else
		$ip = $remote;
	return $ip;
}

// die quelle des bösen
function dbconn($autoclean = false){
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db, $_SERVER;
	//trigger_error("Veraltete Funktion dbconn wurde aufgerufen!");
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

	// userlogin() ersetzt in überarbeiteten dateien dbconn()
	// userlogin selbst ist pdo aber eine sehr schwache
	// lösung. hier muss eine klasse "das ruder übernehmen"
    userlogin();

    if ($autoclean)
        register_shutdown_function("autoclean");
}

function sqlerr($file = '', $line = ''){
	trigger_error("Veraltete Funktion sqlerr wurde aufgerufen!");
	print("<table border=0 bgcolor=blue align=left cellspacing=0 cellpadding=10 style='background: blue'>" . "<tr><td class=embedded><font color=white><h1>SQL Error</h1>\n" . "<b>" . mysql_error() . ($file != '' && $line != '' ? "<p>in $file, line $line</p>" : "") . "</b></font></td></tr></table>");
	die;
}

function userlogin(){
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
		// hier ist der angriffspunkt um rework zu vermeiden
		// geschätzte kompatibilität 99%
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

    if($GLOBALS["CURUSER"]["accept_rules"] == "no" && !preg_match("/(my|rules|faq|logout|delacct)\\.php$/", $_SERVER["PHP_SELF"])) {
        header("Location: rules.php?accept_rules");
        die();
    }
}

function logincookie($id, $passhash, $updatedb = 1, $expires = 0x7fffffff){
    setcookie("uid", $id, $expires, "/");
    setcookie("pass", $passhash, $expires, "/");

    if($updatedb){
		$qry = $GLOBALS['DB']->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
		$qry->bindParam(':id', $id, PDO::PARAM_STR);
		$qry->execute();
	}
}

function logoutcookie(){
    setcookie("uid", "", 0x7fffffff, "/");
    setcookie("pass", "", 0x7fffffff, "/");
    session_unset();
    session_destroy();
}

function loggedinorreturn(){
    global $CURUSER;
    if(!$CURUSER){
        header("Location: " . $GLOBALS["DEFAULTBASEURL"] . "/login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
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

function unesc($x){
	if(get_magic_quotes_gpc())
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
	trigger_error("Veraltete Funktion sqlesc wurde aufgerufen!");
    return "'" . mysql_real_escape_string($x) . "'";
} 

function sqlwildcardesc($x){
	return str_replace(array("%", "_"), array("\\%", "\\_"), $x);
} 

function urlparse($m)
{
    $t = $m[0];
    if (preg_match(',^\w+://,', $t))
        return "<a href=\"" . $t . "\">" . $t . "</a>";
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
    foreach(explode(".", "peers.files.comments.torrents_ratings.nowait") as $x){
		$qry = $GLOBALS['DB']->prepare('DELETE FROM :x WHERE torrent = :id');
		$qry->bindParam(':x', $x, PDO::PARAM_INT);
		$qry->bindParam(':id', $id, PDO::PARAM_INT);
		$qry->execute();
	}
    @unlink($GLOBALS["TORRENT_DIR"] . "/" . $id . ".torrent");

    if ($CURUSER && $owner > 0 && $CURUSER["id"] != $owner) {
        $msg = "Dein Torrent '" . $torrent['name'] . "' wurde von [url=" . $GLOBALS["DEFAULTBASEURL"] . "/userdetails.php?id=" . $CURUSER["id"] . "]" . $CURUSER["username"] . "[/url] gelöscht.\n\n[b]Grund:[/b]\n" . $comment;
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

function searchfield($s){
    return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
} 

function genrelist(){
    $ret = array();
	$rows = $GLOBALS['DB']->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
	//foreach($rows as $row)
	//	$ret[] = $row;
    return $rows;
} 

function linkcolor($num){
    if ($num == 0)
        return "red";
    return "black";
} 

function browse_sortlink($field, $params){
	if($field == $_GET["orderby"]) {
		return "browse.php?orderby=" . $field . "&amp;sort=" . ($_GET["sort"] == "asc" ? "desc" : "asc") . "&amp;" . $params;
	}else{
		return "browse.php?orderby=" . $field . "&amp;sort=" . ($_GET["sort"] == "desc" ? "desc" : "asc") . "&amp;" . $params;
	}
}

function hit_start(){
	global $RUNTIME_START, $RUNTIME_TIMES; 
	$RUNTIME_START = gettimeofday();
} 

function hit_count(){
	return;
	global $RUNTIME_CLAUSE;
	if(preg_match(',([^/]+)$,', $_SERVER["SCRIPT_NAME"], $matches))
		$path = $matches[1];
	else
		$path = "(unknown)";
	$period = date("Y-m-d H") . ":00:00";
	$RUNTIME_CLAUSE = 'page = ' . $path . ' AND period = ' . $period;
	$qry = $GLOBALS['DB']->prepare("UPDATE hits SET count = count + 1 WHERE page = :path AND period = :period");
	$qry->bindParam(':path', $path, PDO::PARAM_STR);
	$qry->bindParam(':period', $period, PDO::PARAM_STR);
	$qry->execute();
	if($qry->rowCount())
		return;
	else{
		$qry = $GLOBALS['DB']->prepare('INSERT INTO hits (page, period, count) VALUES (:path, :period, 1)');
		$qry->bindParam(':path', $path, PDO::PARAM_STR);
		$qry->bindParam(':period', $period, PDO::PARAM_STR);
		$qry->execute();
	}
}

function hit_end(){
	return;
	global $RUNTIME_START, $RUNTIME_CLAUSE, $RUNTIME_TIMES;
	if(empty($RUNTIME_CLAUSE))
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

function hash_pad($hash){
	return str_pad($hash, 20);
} 

// aus der global.php

// Set this to the line break character sequence of your system
$linebreak = "\r\n";

// Returns the current time in GMT in MySQL compatible format.
function get_date_time($timestamp = 0){
	if($timestamp != 0)
		return date("Y-m-d H:i:s", $timestamp);
	else
		return date("Y-m-d H:i:s");
}

function encodehtml($s, $linebreaks = true){
	$s = str_replace("<", "&lt;", str_replace("&", "&amp;", $s));
	if($linebreaks)
		$s = nl2br($s);
	return $s;
}

function get_dt_num(){
    return date("YmdHis");
}

function format_urls($s){
	return preg_replace("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^()<>\s]+)/i", "\\1<a href=\"redir.php?url=\\2\">\\2</a>", $s);
} 

/*

// Removed this fn, I've decided we should drop the redir script...
// it's pretty useless since ppl can still link to pics...
// -Rb

function format_local_urls($s)
{
	return preg_replace(
    "/(<a href=redir\.php\?url=)((http|ftp|https|ftps|irc):\/\/(www\.)?torrentbits\.(net|org|com)(:8[0-3])?([^<>\s]*))>([^<]+)<\/a>/i",
    "<a href=\\2>\\8</a>", $s);
}
*/
// Finds last occurrence of needle in haystack
// in PHP5 use strripos() instead of this
function _strlastpos($haystack, $needle, $offset = 0){
	$addLen = strlen ($needle);
	$endPos = $offset - $addLen;
	while(true){
		if(($newPos = strpos ($haystack, $needle, $endPos + $addLen)) === false)
			break;
		$endPos = $newPos;
	}
	return ($endPos >= 0) ? $endPos : false;
}

function format_quotes($s){
	$old_s = '';
	while($old_s != $s){
		$old_s = $s; 
		// [quote]Text[/quote]
		$s = preg_replace("/\[quote\](.+?)\[\/quote\]/is", "<p><b>Zitat:</b></p><table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\"><tr><td class=\"inposttable\">\\1</td></tr></table><br>", $s); 
		// [quote=Author]Text[/quote]
		$s = preg_replace("/\[quote=(.+?)\](.+?)\[\/quote\]/is", "<p><b>\\1 hat geschrieben:</b></p><table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\"><tr><td class=\"inposttable\">\\2</td></tr></table><br>", $s);
	}
	return $s;
}

function format_comment($text, $strip_html = true){
	global $smilies, $privatesmilies;

	$s = stripslashes($text); 

	// This fixes the extraneous ;) smilies problem. When there was an html escaped
	// char before a closing bracket - like >), "), ... - this would be encoded
	// to &xxx;), hence all the extra smilies. I created a new :wink: label, removed
	// the ;) one, and replace all genuine ;) by :wink: before escaping the body.
	// (What took us so long? :blush:)- wyz
	$s = str_replace(";)", ":wink:", $s);

	if($strip_html)
		$s = htmlspecialchars($s); 
	// [center]Centered text[/center]
	$s = preg_replace("/\[center\]((\s|.)+?)\[\/center\]/i", "<center>\\1</center>", $s); 
	// [list]List[/list]
	$s = preg_replace("/\[list\]((\s|.)+?)\[\/list\]/", "<ul>\\1</ul>", $s); 
	// [list=disc|circle|square]List[/list]
	$s = preg_replace("/\[list=(disc|circle|square)\]((\s|.)+?)\[\/list\]/", "<ul type=\"\\1\">\\2</ul>", $s); 
	// [list=1|a|A|i|I]List[/list]
	$s = preg_replace("/\[list=(1|a|A|i|I)\]((\s|.)+?)\[\/list\]/", "<ol type=\"\\1\">\\2</ol>", $s); 
	// [*]
	$s = preg_replace("/\[\*\]/", "<li>", $s); 
	// [b]Bold[/b]
	$s = preg_replace("/\[b\]((\s|.)+?)\[\/b\]/", "<b>\\1</b>", $s); 
	// [i]Italic[/i]
	$s = preg_replace("/\[i\]((\s|.)+?)\[\/i\]/", "<i>\\1</i>", $s); 
	// [u]Underline[/u]
	$s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/", "<u>\\1</u>", $s); 
	// [u]Underline[/u]
	$s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/i", "<u>\\1</u>", $s); 
	// [img]http://www/image.gif[/img]
	$s = preg_replace("/\[img\]([^\s'\"<>]+?)\[\/img\]/i", "<img src=\"\\1\" alt=\"\" border=\"0\">", $s); 
	// [img=http://www/image.gif]
	$s = preg_replace("/\[img=([^\s'\"<>]+?)\]/i", "<img src=\"\\1\" alt=\"\" border=\"0\">", $s); 
	// [color=blue]Text[/color]
	$s = preg_replace("/\[color=([a-zA-Z]+)\]((\s|.)+?)\[\/color\]/i", "<font color=\\1>\\2</font>", $s); 
	// [color=#ffcc99]Text[/color]
	$s = preg_replace("/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\]((\s|.)+?)\[\/color\]/i", "<font color=\\1>\\2</font>", $s); 
	// [url=http://www.example.com]Text[/url]
	if($_SERVER["SCRIPT_NAME"] == "/details.php" && strpos($s, ".jpg") !== false)
		$s = preg_replace("/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/i", "<a href=\"\\1\" data-lightbox=\"preview\">\\2</a>", $s);
	else
		$s = preg_replace("/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/i", "<a href=\"\\1\">\\2</a>", $s); 
	// [url]http://www.example.com[/url]
	$s = preg_replace("/\[url\]([^()<>\s]+?)\[\/url\]/i", "<a href=\"\\1\">\\1</a>", $s); 
	// [size=4]Text[/size]
	$s = preg_replace("/\[size=([1-7])\]((\s|.)+?)\[\/size\]/i", "<font size=\\1>\\2</font>", $s); 
	// [font=Arial]Text[/font]
	$s = preg_replace("/\[font=([a-zA-Z ,]+)\]((\s|.)+?)\[\/font\]/i", "<font face=\"\\1\">\\2</font>", $s);
	// Quotes
	$s = format_quotes($s); 
	// URLs
	$s = format_urls($s);
	// $s = format_local_urls($s); 
	// Linebreaks
	$s = nl2br($s); 
	// [pre]Preformatted[/pre]
	$s = preg_replace("/\[pre\]((\s|.)+?)\[\/pre\]/i", "<tt><nobr>\\1</nobr></tt>", $s); 
	// [nfo]NFO-preformatted[/nfo]
	$s = preg_replace("/\[nfo\]((\s|.)+?)\[\/nfo\]/i", "<tt><nobr><font face=\"MS Linedraw\" size=\"2\" style=\"font-size: 10pt; line-height: 10pt\">\\1</font></nobr></tt>", $s); 
	// Maintain spacing
	//$s = str_replace("  ", " &nbsp;", $s);
	// Fix für Umlaute by Cerberus
	$s = str_replace(array("  ", "&amp;acute;", "&amp;quot;","&amp;lt;","&amp;gt;", "Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", "&amp;Auml;", "&amp;Ouml;", "&amp;Uuml;", "&amp;auml;", "&amp;ouml;", "&amp;uuml;", "&amp;szlig;"), array(" &nbsp;", "&acute;", "&quot;","&lt;","&gt;", "&Auml;", "&Ouml;", "&Uuml;", "&auml;", "&ouml;", "&uuml;", "&szlig;", "&Auml;", "&Ouml;", "&Uuml;", "&auml;", "&ouml;", "&uuml;", "&szlig;"),$s);  

	reset($smilies);
	//while(list($code, $url) = each($smilies))
	foreach($smilies as $code => $url)
		$s = str_replace($code, "<img src=\"/pic/smilies/" . $url . "\" border=\"0\" alt=\"" . htmlspecialchars($code) . "\">", $s);

	reset($privatesmilies);
	//while(list($code, $url) = each($privatesmilies))
	foreach($privatesmilies as $code => $url)
		$s = str_replace($code, "<img border=\"0\" src=\"/pic/smilies/" . $url . "\" alt=\"\">", $s);
	return $s;
} 

function get_user_class(){
    global $CURUSER;
    return $CURUSER["class"];
} 

function get_user_class_name($class){
	switch ($class){
		case UC_USER:
			return "User";
		case UC_POWER_USER:
			return "Power User";
		case UC_VIP:
			return "VIP";
		case UC_UPLOADER:
			return "Uploader";
		case UC_GUTEAM:
			return "GU-Betreuer";
		case UC_MODERATOR:
			return "Moderator";
		case UC_ADMINISTRATOR:
			return "Administrator";
		case UC_SYSOP:
			return "SysOp";
	} 
	return "";
}

function get_class_color($class){
	switch($class){
		case UC_SYSOP:
			return "ucsysop";
		case UC_ADMINISTRATOR:
			return "ucadministrator";
		case UC_MODERATOR:
			return "ucmoderator";
		case UC_GUTEAM:
			return "ucguteam";
		case UC_UPLOADER:
			return "ucuploader";
		case UC_VIP:
			return "ucvip";
		case UC_POWER_USER:
			return "ucpoweruser";
		case UC_USER:
			return "ucuser";
	}
}

function is_valid_user_class($class){
	return is_numeric($class) && floor($class) == $class && $class >= UC_USER && $class <= UC_SYSOP;
} 

function is_valid_id($id){
	return is_numeric($id) && ($id > 0) && (floor($id) == $id);
} 

function delete_acct($id){ 
	$qry = $GLOBALS['DB']->prepare('SELECT `email`,`username`,`status` FROM `users` WHERE `id`= :id');
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount() > 0)
		$userinfo = $qry->fetchObject();

	if($userinfo->email && $userinfo->status == "confirmed"){
		$mailbody = "Dein Account auf ".$GLOBALS["SITENAME"]." wurde gelöscht. Dies ist entweder passiert, ".
					"weil Du Dich längere Zeit nicht mehr eingeloggt hast, oder Dein Account von einem ".
					"Administrator deaktiviert wurde. ".
					"Diese E-Mail dient dazu, Dich darüber zu informieren, dass Du diesen Account nun nicht ".
					"mehr nutzen kannst. Bitte antworte nicht auf diese E-Mail!";
		mail("\"" . $userinfo->username . "\" <" . $userinfo->email . ">", "Account gelöscht auf ".$GLOBALS["SITENAME"], $mailbody);
	}

	$sql = array();
	$sql[] = 'DELETE FROM `users` WHERE `id`= :id';
	$sql[] = 'DELETE FROM `bitbucket` WHERE `user`= :id';
	$sql[] = 'DELETE FROM `nowait` WHERE `user_id`= :id';
	$sql[] = 'DELETE FROM `pmfolders` WHERE `owner`= :id';
	$sql[] = 'DELETE FROM `traffic` WHERE `userid`= :id';
	$sql[] = 'DELETE FROM `modcomments` WHERE `userid`= :id';
	foreach($sql as $s){
		$qry = $GLOBALS['DB']->prepare($s);
		$qry->bindParam(':id', $id, PDO::PARAM_INT);
		$qry->execute();
	}

	$qry = $GLOBALS['DB']->prepare('SELECT `filename` FROM `bitbucket` WHERE `user`= :id');
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount() > 0){
		$bucketfiles = $qry->fetchAll();
		foreach($bucketfiles as $f){
			 @unlink($GLOBALS["BITBUCKET_DIR"] . "/" . $f["filename"]);
		}
	}

    // Nachrichten löschen
	$msgids = array();
	$qry = $GLOBALS['DB']->prepare('SELECT `id` FROM `messages` WHERE `sender`= :id OR `receiver`= :id');
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount() > 0){
		$msgs = $qry->fetchAll();
		foreach($msgs as $m){
			$msgids[] = $m["id"];
		}
	}
    $msgids = implode(",", $msgids);
	deletePersonalMessages($msgids, $id);
    write_log("accdeleted", "Der Benutzer '".htmlspecialchars($userinfo->username)."' mit der ID " . $id . " wurde aus der Datenbank gelöscht.");
	return TRUE;
} 

function sql_timestamp_to_unix_timestamp($s){
	return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}

function get_ratio_color($ratio){
	if($ratio < 0.1)
		return "#ff0000";
	if($ratio < 0.2)
		return "#ee0000";
	if($ratio < 0.3)
		return "#dd0000";
	if($ratio < 0.4)
		return "#cc0000";
	if($ratio < 0.5)
		return "#bb0000";
	if($ratio < 0.6)
		return "#aa0000";
	if($ratio < 0.7)
		return "#990000";
	if($ratio < 0.8)
		return "#880000";
	if($ratio < 0.9)
		return "#770000";
	if($ratio < 1)
		return "#660000";
	return "#000000";
} 

function get_slr_color($ratio){
	if($ratio < 0.025)
		return "#ff0000";
	if($ratio < 0.05)
		return "#ee0000";
	if($ratio < 0.075)
		return "#dd0000";
	if($ratio < 0.1)
		return "#cc0000";
	if($ratio < 0.125)
		return "#bb0000";
	if($ratio < 0.15)
		return "#aa0000";
	if($ratio < 0.175)
		return "#990000";
	if($ratio < 0.2)
		return "#880000";
	if($ratio < 0.225)
		return "#770000";
	if($ratio < 0.25)
		return "#660000";
	if($ratio < 0.275)
		return "#550000";
	if($ratio < 0.3)
		return "#440000";
	if($ratio < 0.325)
		return "#330000";
	if($ratio < 0.35)
		return "#220000";
	if($ratio < 0.375)
		return "#110000";
	return "#000000";
}

function write_log($typ, $text){
	$added = get_date_time();
	$qry = $GLOBALS['DB']->prepare("INSERT INTO `sitelog` (`typ`, `added`, `txt`) VALUES(:typ, :added, :text)");
	$qry->bindParam(':typ', $typ, PDO::PARAM_STR);
	$qry->bindParam(':added', $added, PDO::PARAM_STR);
	$qry->bindParam(':text', $text, PDO::PARAM_STR);
	$qry->execute();
}

function write_modcomment($uid, $moduid, $text){
	$text = stripslashes($text);
	$qry = $GLOBALS['DB']->prepare("INSERT INTO `modcomments` (`added`, `userid`, `moduid`, `txt`) VALUES (NOW(), :uid, :moduid, :text)");
	$qry->bindParam(':uid', $uid, PDO::PARAM_INT);
	$qry->bindParam(':moduid', $moduid, PDO::PARAM_INT);
	$qry->bindParam(':text', $text, PDO::PARAM_STR);
	$qry->execute();
}

function get_elapsed_time($ts){
	/* $mins = floot((gmtime() - $ts) / 60); */
	$mins = floor((time() - $ts) / 60);
	$hours = floor($mins / 60);
	$mins -= $hours * 60;
	$days = floor($hours / 24);
	$hours -= $days * 24;
	$weeks = floor($days / 7);
	$days -= $weeks * 7;
	$t = "";
	if($weeks > 0)
		return $weeks . " Woche" . ($weeks > 1 ? "n" : "");
	if($days > 0)
		return $days . " Tag" . ($days > 1 ? "en" : "");
	if($hours > 0)
		return $hours . " Stunde" . ($hours > 1 ? "n" : "");
	if($mins > 0)
		return $mins . " Minute" . ($mins > 1 ? "n" : "");
	return "< 1 Minute";
} 

function hex_esc($matches){
	return sprintf("%02x", ord($matches[0]));
}

function getagent($httpagent, $peer_id){
	global $client_uas, $clean_uas; 
	// Spezialfälle mittels Peer-ID bestimmen
	if(substr($peer_id, 0, 4) == "exbc")
		$httpagent = "BitComet/" . ord(substr($peer_id, 4, 1)) . "." . ord(substr($peer_id, 5, 1));
	if(preg_match("/^-BC(\d\d)(\d\d)-/", $peer_id, $matches))
		$httpagent = "BitComet/" . intval($matches[1]) . "." . intval($matches[2]);
	return preg_replace($client_uas, $clean_uas, $httpagent);
} 

function get_wait_time($userid, $torrentid, $only_wait_check = false, $left = -1){
	$qry = $GLOBALS["DB"]->prepare("SELECT users.class, users.downloaded, users.uploaded, UNIX_TIMESTAMP(users.added) AS u_added, UNIX_TIMESTAMP(torrents.added) AS t_added, nowait.`status` AS `status` FROM users LEFT JOIN torrents ON torrents.id = :torrentid LEFT JOIN nowait ON nowait.user_id = :userid AND nowait.torrent_id = :torrentid WHERE users.id = :userid");
	$qry->bindParam(':userid', $userid, PDO::PARAM_INT);
	$qry->bindParam(':torrentid', $torrentid, PDO::PARAM_INT);
	$qry->execute();
	$arr = $qry->Fetch(PDO::FETCH_ASSOC);

	if(($arr["status"] != "granted" || ($arr["status"] == "granted" && $left > 0 && $GLOBALS["NOWAITTIME_ONLYSEEDS"])) && $arr["class"] < UC_VIP){
		$gigs = $arr["uploaded"] / 1073741824;
		$elapsed = floor((time() - $arr["t_added"]) / 3600);
		$regdays = floor((time() - $arr["u_added"]) / 86400);
		$ratio = (($arr["downloaded"] > 0) ? ($arr["uploaded"] / $arr["downloaded"]) : 1);
		$wait_times = explode("|", $GLOBALS["WAIT_TIME_RULES"]);
		$wait = 0;
		foreach($wait_times as $rule){
			$rule = explode(":", $rule, 4);
			// Format [#w][#d] or *
			// eg: 1w or 1w2d or 2d or * or 0
			preg_match("/([0-9]+w)?([0-9]+d)?|([\\*0])?/", $rule[2], $regrule);
			$regrule[1] = (isset($regrule[1])) ? $regrule[1] : 0;
			$regrule[2] = (isset($regrule[2])) ? $regrule[1] : 0;
			$regruledays = intval($regrule[1])*7 + intval($regrule[2]);
			if(($ratio < $rule[0] || $gigs < $rule[1]) && ($regruledays==0 || ($regruledays>0 && $regdays < $regruledays)))
				$wait = max($wait, $rule[3], 0);
		}
		if($only_wait_check)
			return ($wait > 0);
		return max($wait - $elapsed, 0);
	} 
	return 0;
}

function get_cur_wait_time($userid){
	$qry = $GLOBALS["DB"]->prepare("SELECT class, downloaded, uploaded, UNIX_TIMESTAMP(added) AS added FROM users WHERE users.id = :userid");
	$qry->bindParam(':userid', $userid, PDO::PARAM_INT);
	$qry->execute();
	$arr = $qry->Fetch(PDO::FETCH_ASSOC);

	if($arr["class"] < UC_VIP){
		$gigs = $arr["uploaded"] / 1073741824;
		$regdays = floor((time() - $arr["added"]) / 86400)+1;
		$ratio = (($arr["downloaded"] > 0) ? ($arr["uploaded"] / $arr["downloaded"]) : 1);
		$wait_times = explode("|", $GLOBALS["WAIT_TIME_RULES"]);
		$wait = 0;
		foreach($wait_times as $rule){
			$rule = explode(":", $rule, 4);
			// Format [#w][#d] or *
			// eg: 1w or 1w2d or 2d or * or 0
			preg_match("/([0-9]+w)?([0-9]+d)?|([\\*0])?/", $rule[2], $regrule);
			$regruledays = (int)$regrule[1]*7 + $regrule[2];
			if(($ratio < $rule[0] || $gigs < $rule[1]) && ($regruledays==0 || ($regruledays>0 && $regdays < $regruledays)))
				$wait = max($wait, $rule[3], 0);
		}
		return $wait;
	} 
	return 0;
}

function get_torrent_limits($userinfo){
	$limit = array("seeds" => -1, "leeches" => -1, "total" => -1);
	if($userinfo["tlimitall"] == 0){
		// Auto limit
		$ruleset = explode("|", $GLOBALS["TORRENT_RULES"]);
		$ratio = (($userinfo["downloaded"] > 0) ? ($userinfo["uploaded"] / $userinfo["downloaded"]) : (($userinfo["uploaded"] > 0) ? 1 : 0));
		$gigs = $userinfo["uploaded"] / 1073741824;
		$limit = array("seeds" => 0, "leeches" => 0, "total" => 0);
		foreach($ruleset as $rule){
			$rule_parts= explode(":", $rule);
			if($ratio >= $rule_parts[0] && $gigs >= $rule_parts[1] && $limit["total"] <= $rule_parts[4]){
				$limit["seeds"] = $rule_parts[2];
				$limit["leeches"] = $rule_parts[3];
				$limit["total"] = $rule_parts[4];
			}
		}
	}elseif($userinfo["tlimitall"] > 0){
		// Manual limit
		$limit["seeds"] = $userinfo["tlimitseeds"];
		$limit["leeches"] = $userinfo["tlimitleeches"];
		$limit["total"] = $userinfo["tlimitall"];
	}
	return $limit;
}

function resize_image($origfn, $tmpfile, $target_filename){
	// Bild laden
	if(preg_match("/(jp(e|eg|g))$/i", $origfn)){
		$img_pic = @imagecreatefromjpeg($tmpfile);
	}

	if(preg_match("/png$/i", $origfn)){
		$img_pic = @imagecreatefrompng($tmpfile);
	}

	if(preg_match("/gif$/i", $origfn)){
		$img_pic = @imagecreatefromgif($tmpfile);
	}

	if(!$img_pic)
		return FALSE;
        
	$size_x = imagesx($img_pic);
	$size_y = imagesy($img_pic);				

	$tn_size_x = 150;
	$tn_size_y = (int)((float)$size_y / (float)$size_x * (float)150);
		
	// Thumbnail erzeugen
	$img_tn = imagecreatetruecolor($tn_size_x, $tn_size_y);
	imagecopyresampled($img_tn, $img_pic, 0, 0, 0, 0, $tn_size_x, $tn_size_y, $size_x, $size_y);		

	// Bild speichern
	$dummy = imagejpeg($img_tn, $target_filename, 85);

	imagedestroy($img_tn);

	return $img_pic;
}

function strip_ascii_art($text){
	// First, remove all "weird" characters.
	$text = preg_replace("/[^a-zA-Z0-9öäüÖÄÜß\\-_?!&[\\]().,;:+=#*~@\\/\\\\'\"><\\s]/", "", $text);
	$oldtext = "";
	while($text != $oldtext){
		$oldtext = $text; 
		// Remove all repeating umlauts
		$text = preg_replace("/[öäüÖÄÜß]{2,}/", "", $text);
		// Remove all "free" umlauts, not enclosed by other word chars
		$text = preg_replace("/(^|\\s)[öäüÖÄÜß]+(\\s|$)/sm", "", $text);
	}
	// Remove trailing spaces at end of line
	$text = preg_replace("/([\\t ]+)(\\s$)/m", "\\2", $text);
	return $text;
}

function gen_nfo_pic($nfotext, $target_filename){
	// Make array of NFO lines and break lines at 80 chars
	$nfotext = preg_replace('/\r\n/', "\n", $nfotext);
	$lines = explode("\n", $nfotext);
	for($I=0;$I<count($lines);$I++){
		$lines[$I] = chop($lines[$I]);
		$lines[$I] = wordwrap($lines[$I], 82, "\n", 1);
	}
	$lines = explode("\n", implode("\n", $lines));
    // Get longest line
    $cols = 0;
	for($I=0;$I<count($lines);$I++){    
		$lines[$I] = chop($lines[$I]);
		if (strlen($lines[$I]) > $cols)
			$cols = strlen($lines[$I]);
	}

	// Allow a maximum of 500 lines of text
	$lines = array_slice($lines, 0, 500);
		
	// Get line count
	$linecnt = count($lines);
		
	// Load font
	$font = imageloadfont("terminal.gdf");
	if($font < 5)
		die("Konnte das NFO-Font nicht laden. Admin benachrichtigen!");
		
	$imagewidth = $cols * imagefontwidth($font) + 1;
	$imageheight = $linecnt * imagefontheight($font) + 1;
		
	$nfoimage = imagecreate($imagewidth, $imageheight);
	$white = imagecolorallocate($nfoimage, 255, 255, 255);
	$black = imagecolorallocate($nfoimage, 0, 0, 0);

	for($I=0;$I<$linecnt;$I++)
		imagestring($nfoimage, $font, 0, $I*imagefontheight($font), $lines[$I], $black);
	return imagepng($nfoimage, $target_filename);
}

?>