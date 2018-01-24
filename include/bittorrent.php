<?php
error_reporting(E_ALL);
//error_reporting(0);
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

function local_user(){
    global $_SERVER;
    return $_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"];
} 

if (!file_exists("include/secrets.php") || !file_exists("include/config.php"))
    die("<html><head><title>FEHLER</title></head><body><p>Der Tracker wurde noch nicht konfiguriert.</p>
        <p><a href=\"inst/install.php\">Zum Installationsscript</a></p></body></html>");

// legacy parts
require_once("include/secrets.php");
require_once("include/config.php");
require_once("include/cleanup.php"); // neubauen class cleanup
require_once("include/shoutcast.php"); // kein plan. scheint zu funktionieren
require_once("include/std.php");

require_once("include/global.php");
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
	trigger_error("Veraltete Funktion dbconn wurde aufgerufen!");
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
    global $CURUSER, $DEFAULTBASEURL;
    if(!$CURUSER){
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

?>