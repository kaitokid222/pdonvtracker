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

    if ($GLOBALS["CURUSER"]["accept_rules"] == "no" && !preg_match("/(takeprofedit|rules|faq|logout|delacct)\\.php$/", $_SERVER["PHP_SELF"])) {
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
// Muss weg!
function sqlwildcardesc($x)
{
	trigger_error("Veraltete Funktion sqlwildcardescesc wurde aufgerufen!");
    return str_replace(array("%", "_"), array("\\%", "\\_"), mysql_real_escape_string($x));
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

function commenttable($rows){
	global $CURUSER, $_SERVER;
	foreach ($rows as $row){
		begin_table(true);
		echo "    <colgroup>\n".
			"        <col width=\"150\"><col width=\"600\">\n".
			"    </colgroup>\n".
			"    <tr>\n".
			"        <td colspan=\"2\" class=\"tablecat\">\n".
			"            #" . $row["id"] . " von ";
		if(isset($row["username"])){
			$title = $row["title"];
			if($title == "")
				$title = get_user_class_name($row["class"]);
			else
				$title = htmlspecialchars($title);
			echo "<a name=\"comm" . $row["id"] . "\" href=\"userdetails.php?id=" . $row["user"] . "\"><b>" . htmlspecialchars($row["username"]) . "</b></a>" . get_user_icons(array("donor" => $row["donor"], "enabled" => $row["enabled"], "warned" => $row["warned"])) . " (" . $title . ")";
		}else
			echo "<a name=\"comm" . $row["id"] . "\"><i>(Gelöscht)</i></a>";
		echo " am " . $row["added"] . ($row["user"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR ? " - [<a href=\"comment.php?action=edit&amp;cid=" . $row["id"] . "\">Bearbeiten</a>]" : "") .
			(get_user_class() >= UC_MODERATOR ? " - [<a href=\"comment.php?action=delete&amp;cid=" . $row["id"] . "\">Löschen</a>]" : "") .
			($row["editedby"] && get_user_class() >= UC_MODERATOR ? " - [<a href=\"comment.php?action=vieworiginal&amp;cid=" . $row["id"] . "\">Original anzeigen</a>]" : "") . "\n" . 
			"        </td>\n".
			"    </tr>\n";
		$avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($row["avatar"]) : "");
		if(!$avatar)
			$avatar = $GLOBALS["PIC_BASE_URL"] . "default_avatar.gif";
		$text = stripslashes(format_comment($row["text"]));
		if($row["editedby"])
			$text .= "<p><font size=\"1\" class=\"small\">Zuletzt von <a href=\"userdetails.php?id=" . $row["editedby"] . "\"><b>" . $row["username"] . "</b></a> am " . $row["editedat"] . " bearbeitet</font></p>";
		echo "    <tr valign=\"top\">\n".
			"        <td class=\"tableb\" align=\"center\" style=\"padding: 0px;width: 150px\"><img width=\"150\" src=\"" . $avatar . "\" alt=\"Avatar von " . $row["username"] . "\"></td>\n";
			"        <td class=\"tablea\">" . $text . "</td>\n";
			"    </tr>\n";
		end_table();
	} 
}

function searchfield($s){
    return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
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
        return "browse.php?orderby=" . $field . "&amp;sort=" . ($_GET["sort"] == "asc" ? "desc" : "asc") . "&amp;" . $params;
    } else {
        return "browse.php?orderby=" . $field . "&amp;sort=" . ($_GET["sort"] == "desc" ? "desc" : "asc") . "&amp;" . $params;
    } 
} 

function torrenttable_row_oldschool($torrent_info){
	global $CURUSER;

	if(strlen($torrent_info["name"]) > 45)
		$displayname = substr($torrent_info["name"], 0, 45) . "...";
	else
		$displayname = $torrent_info["name"];

	$returnto = "&amp;returnto=" . urlencode($_SERVER["REQUEST_URI"]);
	$baselink = "details.php?id=" . $torrent_info["id"];
	if($torrent_info["variant"] == "index"){
		$baselink .= "&amp;hit=1";
		$filelistlink = $baselink . "&amp;filelist=1";
		$commlink = $baselink . "&amp;tocomm=1";
		$seederlink = $baselink . "&amp;toseeders=1";
		$leecherlink = $baselink . "&amp;todlers=1";
		$snatcherlink = $baselink . "&amp;tosnatchers=1";
	}else{
		$baselink .= $returnto;
		$filelistlink = $baselink . "&amp;filelist=1#filelist";
		$commlink = $baselink . "&amp;page=0#startcomments";
		$seederlink = $baselink . "&amp;dllist=1#seeders";
		$leecherlink = $baselink . "&amp;dllist=1#leechers";
		$snatcherlink = $baselink . "&amp;snatcher=1#snatcher";
	} 

	if($torrent_info["leechers"])
		$ratio = $torrent_info["seeders"] / $torrent_info["leechers"];
	elseif ($torrent_info["seeders"])
		$ratio = 1;
	else
		$ratio = 0;
	$seedercolor = get_slr_color($ratio);

	
	if (!isset($torrent_info["cat_pic"])){
		if ($torrent_info["cat_name"] != "")
			$tci = "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\">" . $torrent_info["cat_name"] . "</a>";
		else
			$tci = "-";
	}else{
		$tci = "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $torrent_info["cat_pic"] . "\" alt=\"" . $torrent_info["cat_name"] . "\" title=\"" . $torrent_info["cat_name"] . "\" border=\"0\"></a>";
	}

	if(isset($torrent_info["uploaderclass"]) && $torrent_info["uploaderclass"] < UC_UPLOADER)
		$gu = "<font color=\"red\">[GU]</font> ";
	else
		$gu = "";
		
	if($torrent_info["variant"] != "guestuploads" && $torrent_info["is_new"])
		$isnew = " <font style=\"color:red\">(NEU)</font>";
	else
		$isnew = "";

	if($torrent_info["gu_agent"] > 0)
		$gua = "Ja";
	else
		$gua = "<font color=\"red\">Nein</font>";
		
	if($torrent_info["variant"] == "mytorrents"){
		$trex = "        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"edit.php?id=" . $torrent_info["id"] . $returnto . "\">Bearbeiten</a></td>\n".
				"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . ($torrent_info["visible"] == "yes"?"Ja":"Nein") . "</td>\n";
	}elseif($torrent_info["variant"] == "guestuploads")
		$trex = "        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . $gua . "</td>\n";
	elseif($torrent_info["has_wait"])
		$trex = "        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><font color=\"" . $torrent_info["wait_color"] . "\">" . $torrent_info["wait_left"] . "<br>Std.</font></td>\n";

	if($torrent_info["variant"] == "index")
		$inde = "<td class=\"tablea\" style=\"text-align:left;vertical-align:middle;\" nowrap=\"nowrap\">" . $torrent_info["uploaderlink"] . "</td>\n";
	else
		$inde = "";

	echo "    <tr>\n".
		"        <td class=\"tableb\" valign=\"top\" style=\"width:1px;padding:0px;\">" . $tci . "</td>\n".
		"        <td class=\"tablea\" style=\"text-align:left;vertical-align:middle;\" nowrap=\"nowrap\">" . $gu . "<a href=\"" . $baselink . "\" title=\"" . htmlspecialchars($torrent_info["name"]) . "\"><b>" . htmlspecialchars($displayname) . "</b></a>" . $isnew . "</td>\n".
		$trex.
		"        <td class=\"tablea\" style=\"text-align:right;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $filelistlink . "\">" . $torrent_info["numfiles"] . "</a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:right;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $commlink . "\">" . $torrent_info["comments"] . "</a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . str_replace("&nbsp;", "<br>", $torrent_info["added"]) . "</td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . str_replace(" ", "<br>", $torrent_info["ttl"]) . "</td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . str_replace(" ", "<br>", mksize($torrent_info["size"])) . "</td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><div style=\"border:1px solid black;padding:0px;width:60px;height:10px;\"><div style=\"border:none;width:" . (60 * $torrent_info["dist"] / 100) . "px;height:10px;background-image:url(" . $GLOBALS["PIC_BASE_URL"] . "ryg-verlauf-small.png);background-repeat:no-repeat;\"></div></div></td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $snatcherlink . "\">" . $torrent_info["times_completed"] . "</a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $seederlink . "\"><font color=\"" . $seedercolor . "\">" . intval($torrent_info["seeders"]) . "</font></a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $leecherlink . "\"><font color=\"" . linkcolor($torrent_info["seeders"]) . "\">" . intval($torrent_info["leechers"]) . "</font></a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:left;vertical-align:middle;\" nowrap=\"nowrap\">D:&nbsp;" . $torrent_info["dlspeed"] . "&nbsp;KB/s<br>U:&nbsp;" . $torrent_info["ulspeed"] . "&nbsp;KB/s</td>\n".
		$inde.
		"    </tr>\n";
}

function torrenttable_row($torrent_info){
	global $CURUSER;

	$returnto = "&amp;returnto=" . urlencode($_SERVER["REQUEST_URI"]);
	$baselink = "details.php?id=" . $torrent_info["id"];
	if($torrent_info["variant"] == "index"){
		$baselink .= "&amp;hit=1";
		$filelistlink = $baselink . "&amp;filelist=1";
		$commlink = $baselink . "&amp;tocomm=1";
		$seederlink = $baselink . "&amp;toseeders=1";
		$leecherlink = $baselink . "&amp;todlers=1";
		$snatcherlink = $baselink . "&amp;tosnatchers=1";
	}else{
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
	
	$qry = $this->con->prepare("SELECT DISTINCT(user_id) as id, username, class, peers.id as peerid FROM completed,users LEFT JOIN peers ON peers.userid=users.id AND peers.torrent= :ptid AND peers.seeder='yes' WHERE completed.user_id=users.id AND completed.torrent_id= :ctid ORDER BY complete_time DESC LIMIT 10");
	$qry->bindParam(':ptid', $torrent_info["id"], PDO::PARAM_INT);
	$qry->bindParam(':ctid', $torrent_info["id"], PDO::PARAM_INT);
	$qry->execute();
	$data = $qry->FetchAll(PDO::FETCH_ASSOC);

	$last10users = "";
	foreach($data as $arr){
		if($last10users)
			$last10users .= ", ";
		$arr["username"] = "<font class=\"" . get_class_color($arr["class"]) . "\">" . $arr["username"] . "</font>";
		if($arr["peerid"] > 0){
			$arr["username"] = "<b>" . $arr["username"] . "</b>";
		}
		$last10users .= "<a href=\"userdetails.php?id=" . $arr["id"] . "\">" . $arr["username"] . "</a>";
	}

	if($last10users == "")
		$last10users = "Diesen Torrent hat noch niemand fertiggestellt.";
	else
		$last10users .= "<br/><br/>(Fettgedruckte User seeden noch)";

	if ($GLOBALS["DOWNLOAD_METHOD"] == DOWNLOAD_REWRITE)
		$download_url = "download/" . $torrent_info["id"] . "/" . rawurlencode($torrent_info["filename"]);
	else
		$download_url = "download.php?torrent=" . $torrent_info["id"];

	if (!isset($torrent_info["cat_pic"])){
		if ($torrent_info["cat_name"] != "")
			$tci = "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\">" . $torrent_info["cat_name"] . "</a>";
		else
			$tci = "-";
	}else{
		$tci = "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $torrent_info["cat_pic"] . "\" alt=\"" . $torrent_info["cat_name"] . "\" title=\"" . $torrent_info["cat_name"] . "\" border=\"0\"></a>";
	}
	
	if($torrent_info["variant"] != "index")
		$edit_l = "[ <a href=\"edit.php?id=" . $torrent_info["id"] . $returnto ."\">Bearbeiten</a> ]";
	else
		$edit_l = "";
		
	if(isset($torrent_info["uploaderclass"]) && $torrent_info["uploaderclass"] < UC_UPLOADER)
		$gu = "<font color=\"red\">[GU]</font> ";
	else
		$gu = "";

	$ecl = "<a href=\"javascript:expandCollapse('" . $torrent_info["id"] . "');\"><img id=\"plusminus" . $torrent_info["id"] . "\" src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/plus.gif\" alt=\"Auf-/Zuklappen\" border=\"0\"></a>";

	$nlink = "<a href=\"" . $baselink . "\"><b>" . $torrent_info["name"] . "</b></a>";
	
	if($torrent_info["variant"] != "guestuploads" && $torrent_info["is_new"])
		$isnew = " <font style=\"color:red\">(NEU)</font>";
	else
		$isnew = " ";
	
	if ($torrent_info["variant"] == "guestuploads" && $torrent_info["gu_agent"] > 0)
		$guagent = " <font style=\"color:red\">(Bereits in Bearbeitung)</font>";
	else
		$guagent = "";
		
	if ($torrent_info["seeders"] == 0 && $torrent_info["variant"] == "index"){
		$deadtext = "<div style=\"padding:4px;\"><b><font color=\"red\">HINWEIS:</font></b> Es sind keine Seeder für diesen Torrent aktiv. ".
					"Dies bedeutet, dass Du diesen Torrent wahrscheinlich nicht fertigstellen kannst, solange nicht wieder ein Seeder aktiv wird. ".
					"Sollte der Torrent längere Zeit inaktiv gewesen und als \"Tot\" markiert worden sein, solltest Du im Forum um einen Reseed bitten, ".
					"Falls Du noch Interesse daran hast.</div>";
	}else
		$deadtext = "";

	if($torrent_info["variant"] == "mytorrents"){
		$visibiblity = "                <tr>\n".
						"                    <td nowrap valign=\"top\"><b>Sichtbar:</b></td>\n".
						"                    <td>" . ($torrent_info["visible"] == "yes"?"Ja":"Nein, dieser Torrent ist inaktiv und als \"Tot\" markiert") . "</td>\n".
						"                </tr>\n";
	}else
		$visibiblity = "";
		
	if($torrent_info["has_wait"]){
		$wtt = "                <tr>\n".
				"                    <td nowrap valign=\"top\"><b>Wartezeit:</b></td>\n".
				"                    <td><font color=\"" . $torrent_info["wait_color"] . "\">". $torrent_info["wait_left"] . " Stunde(n)</font></td>\n".
				"                </tr>\n";
	}else
		$wtt = "";
		
	$distributionbar = "<div style=\"border:1px solid black;padding:0px;width:300px;height:15px;\"><div style=\"border:none;width:" . (300 * $torrent_info["dist"] / 100) . "px;height:15px;background-image:url(" . $GLOBALS["PIC_BASE_URL"] . "ryg-verlauf.png);background-repeat:no-repeat;\"></div></div>";

	if($torrent_info["variant"] == "guestuploads"){
		if ($torrent_info["gu_agent"] == 0)
			$xdlpic = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "nodl.png\" width=\"22\" height=\"22\" alt=\"Nicht in Bearbeitung\" title=\"Nicht in Bearbeitung\">";
		else
			$xdlpic = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "taken.png\" width=\"22\" height=\"22\" alt=\"Bereits in Bearbeitung\" title=\"Bereits in Bearbeitung\">";
	}else{
		if ($torrent_info["activated"] == "yes") {
			if(!isset($torrent_info["wait_left"]) || $torrent_info["wait_left"] == 0){
				$xdlpic = "<a href=\"" . $download_url . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "download.png\" width=\"22\" height=\"22\" alt=\"Torrent herunterladen\" title=\"Torrent herunterladen\" border=\"0\"></a>";
			}else{
				$xdlpic = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "nodl.png\" width=\"22\" height=\"22\" alt=\"Wartezeit nicht abgelaufen\" title=\"Wartezeit nicht abgelaufen\" border=\"0\" />";
			} 
		}else{
			$xdlpic = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "nodl.png\" width=\"22\" height=\"22\" alt=\"Torrent nicht freigeschaltet\" title=\"Torrent nicht freigeschaltet\" border=\"0\" />";
		}
	}
	
	echo "    <tr>\n".
		"        <td class=\"tableb\" valign=\"top\" style=\"color:#FFFFFF;width:1px;\">" . $tci . "</td>\n".
		"        <td class=\"tablea\" valign=\"top\" align=\"left\">\n".
		"            <table cellpadding=\"2\" cellspacing=\"2\" border=\"0\" style=\"width:100%\">\n".
		"                <colgroup>\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"20%\">\n".
		"                </colgroup>\n".
		"                <tr>\n".
		"                    <td colspan=\"4\" nowrap=\"nowrap\">". $ecl . $edit_l . $gu . $nlink . $isnew . $guagent . "</td>\n".
        "                    <td nowrap=\"nowrap\">".($torrent_info["variant"] == "mytorrents" ? "Hochgeladen am:" : "Von: " . $torrent_info["uploaderlink"]). "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td style=\"font-size:90%\"><b>" . mksize($torrent_info["size"]) . "</b> in <b>" . $torrent_info["numfiles"] . "</b> <a href=\"" . $filelistlink . "\">Datei(en)</a></td>\n".
		"                    <td style=\"font-size:90%\"><b><font color=\"" . $seedercolor . "\">" . intval($torrent_info["seeders"]) . "</font></b> <a href=\"" . $seederlink . "\">Seeder</a> &amp;<b><font color=\"" . linkcolor($torrent_info["seeders"]) . "\">" . intval($torrent_info["leechers"]) . "</font></b> <a href=\"" . $leecherlink . "\">Leecher</a></td>\n".
		"                    <td style=\"font-size:90%\"><b>" . $torrent_info["times_completed"] . "</b>x <a href=\"" . $snatcherlink . "\">heruntergeladen</a></td>\n".
		"                    <td style=\"font-size:90%\"><b>" . $torrent_info["comments"] . "</b> <a href=\"" . $commlink . "\">Kommentare</a></td>\n".
		"                    <td style=\"font-size:90%\">" . $torrent_info["added"] . "</td>\n".
		"                </tr>\n".
		"            </table>\n".
		"            <div id=\"details" . $torrent_info["id"] . "\" style=\"display:none;\">\n".
		$deadtext.
		"            <table cellspacing=\"2\" cellpadding=\"2\" border=\"0\" class=\"inposttable\" style=\"width:100%;\">\n".
		"                <colgroup>\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"80%\">\n".
		"                </colgroup>\n".
		$visibiblity.
		$wtt.
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>Letzte 10 Downloader:</b></td>\n".
		"                    <td>" . $last10users . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>&Oslash; Downloadgeschw.:</b></td>\n".
		"                    <td>" . $torrent_info["dlspeed"] . " KB/s (" . ($torrent_info["dlspeed"] * ($torrent_info["leechers"] + $torrent_info["seeders"])) . " KB/s gesamt)</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>&Oslash; Uploadgeschw.:</b></td>\n".
		"                    <td>" . $torrent_info["ulspeed"] . " KB/s (" . ($torrent_info["ulspeed"] * ($torrent_info["leechers"] + $torrent_info["seeders"])) . " KB/s gesamt)</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>Letzte Aktivit&auml;t:</b></td>\n".
		"                    <td>" . $torrent_info["last_action"] . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>Verbleibende Zeit:</b></td>\n".
		"                    <td>" . $torrent_info["ttl"] . " (falls inaktiv, sonst l&auml;nger)</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>&Oslash; Verteilung:</b></td>\n".
		"                    <td>" . $distributionbar . "</td>\n".
		"                </tr>\n".
		"            </table>\n".
		"            </div>\n".
		"        </td>\n". // produziert vllt einen fehler im layout
		"        <td class=\"tableb\" valign=\"top\" align=\"center\" style=\"width:22px;padding:4px;padding-top:10px;\">" . $xdlpic . "</td>\n".
		"    </tr>\n";
}

function torrenttable($res, $variant = "index", $addparam = ""){
	global $CURUSER; 
	// Sortierkriterien entfernen
	$addparam_nosort = preg_replace(array("/orderby=(.*?)&amp;/i", "/sort=(.*?)&amp;/i"), array("", ""), $addparam); 
	// Hat dieser Benutzer Wartezeit?
	$has_wait = get_wait_time($CURUSER["id"], 0, true);

	if($variant == "mytorrents"){
		$vrtt = "        <td class=\"tablecat\" align=\"center\">Bearbeiten</td>\n".
				"        <td class=\"tablecat\" align=\"center\">Sichtbar</td>\n";
	}elseif($variant == "guestuploads")
		$vrtt = "        <td class=\"tablecat\" align=\"center\">In&nbsp;Bearbeitung</td>\n";
	elseif($has_wait)
		$vrtt = "        <td class=\"tablecat\" align=\"center\">Wartez.</td>\n";
		
	if($variant == "index")
		$vrtti = "        <td class=\"tablecat\" align=\"center\">Uploader</td>\n";
	else
		$vrtti = "";

	echo "<script language=\"JavaScript\" src=\"js/expandCollapseTR.js\" type=\"text/javascript\"></script>\n".
		"<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
		"    <tr>\n".
		"        <td align=\"left\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "obenlinks.gif\" alt=\"\" title=\"\" /></td>\n".
		"        <td style=\"width:100%\" class=\"obenmitte\"></td>\n".
		"        <td align=\"right\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/obenrechts.gif\" alt=\"\" title=\"\" /></td>\n".
		"    </tr>\n".
		"</table>\n";
	if($CURUSER["oldtorrentlist"] == "yes"){
		echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"tableinborder\" style=\"width:100%\">\n".
			"    <tr>\n".
			"        <td class=\"tablecat\" align=\"center\">Typ</td>\n".
			"        <td class=\"tablecat\" align=\"left\">Name</td>\n".
			$vrtt.
			"        <td class=\"tablecat\" align=\"right\">Dateien</td>\n".
			"        <td class=\"tablecat\" align=\"right\">Komm.</td>\n".
			"        <td class=\"tablecat\" align=\"center\">Hinzugef.</td>\n".
			"        <td class=\"tablecat\" align=\"center\">TTL</td>\n".
			"        <td class=\"tablecat\" align=\"center\">Gr&ouml;&szlig;e</td>\n".
			"        <td class=\"tablecat\" align=\"center\">Verteilung</td>\n".
			"        <td class=\"tablecat\" align=\"center\">Fertig</td>\n".
			"        <td class=\"tablecat\" align=\"right\">Seeder</td>\n".
			"        <td class=\"tablecat\" align=\"right\">Leecher</td>\n".
			"        <td class=\"tablecat\" align=\"left\">&Oslash;&nbsp;Geschw.</td>\n".
			$vrtti.
			"    </tr>\n";
	}else{
		echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\" class=\"tableinborder\" style=\"width:100%\">\n".
			"    <colgroup>\n".
			"        <col width=\"32\">\n".
			"        <col width=\"100%\">\n".
			"        <col width=\"22\">\n".
			"    </colgroup>\n";
	}

	while($row = mysql_fetch_assoc($res)){
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

		if(isset($row["cat_pic"]) && $row["cat_pic"] != "")
			$torrent_info["cat_pic"] = $row["cat_pic"];

		if(isset($row["uploaderclass"]))
			$torrent_info["uploaderclass"] = $row["uploaderclass"];

		if($has_wait){
			$torrent_info["has_wait"] = $has_wait;
			$torrent_info["wait_left"] = get_wait_time($CURUSER["id"], $id);
			$torrent_info["wait_color"] = dechex(floor(127 * ($wait_left) / 48 + 128) * 65536);
		} 

		$speedres = mysql_query("SELECT ROUND(AVG((downloaded - downloadoffset) / (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`started`)))/1024, 2) AS dlspeed, ROUND(AVG((uploaded - uploadoffset) / (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`started`)))/1024, 2) AS ulspeed FROM peers WHERE torrent=$id");
		$speed = mysql_fetch_assoc($speedres);
		if($speed["dlspeed"] == 0)
			$speed["dlspeed"] = "0";
		if($speed["ulspeed"] == 0)
			$speed["ulspeed"] = "0";
		$torrent_info["dlspeed"] = $speed["dlspeed"];
		$torrent_info["ulspeed"] = $speed["ulspeed"];

		$distres = mysql_query("SELECT ROUND(AVG((" . $row["size"] . " - `to_go`) / " . $row["size"] . " * 100),2) AS `dist` FROM `peers` WHERE torrent=$id");
		$dist = mysql_fetch_assoc($distres);
		$torrent_info["dist"] = $dist["dist"];

		$ttl = (28 * 24) - floor((time() - sql_timestamp_to_unix_timestamp($row["added"])) / 3600);
		if($ttl == 1)
			$ttl .= " Stunde";
		else
			$ttl .= " Stunden";
		$torrent_info["ttl"] = $ttl;

		$newadd = "";

		$torrentunix = sql_timestamp_to_unix_timestamp($row["added"]);
		$accessunix = sql_timestamp_to_unix_timestamp($CURUSER["last_access"]);

		if($torrentunix >= $accessunix)
			$torrent_info["is_new"] = true;

		if($CURUSER["oldtorrentlist"] == "yes") {
			torrenttable_row_oldschool($torrent_info);
		}else{
			torrenttable_row($torrent_info);
		} 
	} 

	echo "</table>\n".
		"<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
		"    <tr>\n".
		"        <td align=\"left\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/untenlinks.gif\" alt=\"\" title=\"\" /></td>\n".
		"        <td style=\"width:100%\" class=\"untenmitte\" align=\"center\"></td>\n".
		"        <td align=\"right\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/untenrechts.gif\" alt=\"\" title=\"\" /></td>\n".
		"    </tr>\n".
		"</table>\n";
	return $rows;
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

/* nur für announce.php und scrape nötig
function hash_where($name, $hash){
	$shhash = preg_replace('/ *$/s', "", $hash);
	return "(" . $name . " = '" . $hash . "' OR " . $name . " = '" . $shhash . "')";
}*/

function get_user_icons($arr, $big = false){
	if($big){
		$donorpic = "starbig.png";
		$warnedpic = "warnedbig.gif";
		$disabledpic = "disabledbig.png";
		$newbiepic = "pacifierbig.png";
		$style = "style=\"margin-left:4pt;vertical-align:middle;\"";
	}else{
		$donorpic = "star.png";
		$warnedpic = "warned.gif";
		$disabledpic = "disabled.png";
		$newbiepic = "pacifier.png";
		$style = "style=\"margin-left:2pt;vertical-align:middle;\"";
	} 

	$pics = $arr["donor"] == "yes" ? "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $donorpic . "\" alt=\"Spender\" title=\"Dieser Benutzer hat mit einer Spende zum Erhalt des Trackers beigetragen\" border=\"0\" " . $style . ">" : "";

	if(isset($arr["warned"]) && $arr["warned"] == "yes")
		$pics .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $warnedpic . "\" alt=\"Verwarnt\" title=\"Dieser Benutzer wurde verwarnt\" border=\"0\" " . $style . ">";

	if(isset($arr["enabled"]) && $arr["enabled"] == "no")
		$pics .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $disabledpic . "\" alt=\"Deaktiviert\" title=\"Dieser Benutzer ist deaktiviert\" border=\"0\" " . $style . ">";

	$timeadded = intval(sql_timestamp_to_unix_timestamp($arr["added"]));
	if($timeadded > 0 && time() - $timeadded < 604800)
		$pics .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $newbiepic . "\" alt=\"Newbie\" title=\"Ist noch neu hier\" border=\"0\" " . $style . ">";
	return $pics;
}
?>