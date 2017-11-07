<?php

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

// User levels
define ('UC_USER', 0);
define ('UC_POWER_USER', 1);
define ('UC_VIP', 5);
define ('UC_UPLOADER', 10);
define ('UC_GUTEAM', 20);
define ('UC_MODERATOR', 25);
define ('UC_ADMINISTRATOR', 50);
define ('UC_SYSOP', 100);

// PM special folder IDs
define ('PM_FOLDERID_INBOX', -1);
define ('PM_FOLDERID_OUTBOX', -2);
define ('PM_FOLDERID_SYSTEM', -3);
define ('PM_FOLDERID_MOD', -4);


$client_uas = array("/^Azureus ([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)(;.+?)?$/i",
    "/^Azureus ([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)_B([0-9]+)(;.+?)?$/i",
    "/^BitTorrent\\/S-([0-9]+\\.[0-9]+(\\.[0-9]+)*)(.*)$/i",
    "/^BitTorrent\\/U-([0-9]+\\.[0-9]+\\.[0-9]+)$/i",
    "/^BitTor(rent|nado)\\/T-(.+)$/i",
    "/^BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)$/i",
    "/^Python-urllib\\/[0-9]+\\.[a-z0-9]+$/i",
    "/^Python-urllib\\/.+?, BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)$/i",
    "/^Python-urllib\\/.+?, BitTorrent\\/TurboBT ([0-9]+\\.[0-9]+(\\.[0-9]+)*)$/i",
    "/^BitTorrent\\/BitSpirit$/i",
    "/^BitTorrent\\/brst(.+)$/i",
    "/^RAZA (.+)$/i",
    "/^BitTorrent\\/ABC-([0-9]+\\.[0-9]+\\.[0-9]+)$/i",
    "/^BitComet\\/([0-9]+\\.[0-9]+)$/i"
    );

$clean_uas = array("Azureus/\\1",
    "Azureus/\\1 (Beta \\2)",
    "Shadow's/\\1",
    "UPnP/\\1",
    "BitTornado/\\2",
    "BitTorrent/\\1",
    "G3 Torrent",
    "BitTorrent/\\1",
    "TurboBT/\\1",
    "BitSpirit",
    "Burst/\\1",
    "Shareaza/\\1",
    "ABC/\\1",
    "BitComet/\\1"
    );

$smilies = array(":-)" => "smile1.gif",
    ":smile:" => "smile2.gif",
    ":-D" => "grin.gif",
    ":lol:" => "laugh.gif",
    ":w00t:" => "w00t.gif",
    ":-P" => "tongue.gif",
    ";-)" => "wink.gif",
    ":-|" => "noexpression.gif",
    ":-/" => "confused.gif",
    ":-(" => "sad.gif",
    ":'-(" => "cry.gif",
    ":weep:" => "weep.gif",
    ":-O" => "ohmy.gif",
    ":o)" => "clown.gif",
    "8-)" => "cool1.gif",
    "|-)" => "sleeping.gif",
    ":innocent:" => "innocent.gif",
    ":whistle:" => "whistle.gif",
    ":unsure:" => "unsure.gif",
    ":closedeyes:" => "closedeyes.gif",
    ":cool:" => "cool2.gif",
    ":fun:" => "fun.gif",
    ":thumbsup:" => "thumbsup.gif",
    ":thumbsdown:" => "thumbsdown.gif",
    ":blush:" => "blush.gif",
    ":unsure:" => "unsure.gif",
    ":yes:" => "yes.gif",
    ":no:" => "no.gif",
    ":love:" => "love.gif",
    ":?:" => "question.gif",
    ":!:" => "excl.gif",
    ":idea:" => "idea.gif",
    ":arrow:" => "arrow.gif",
    ":arrow2:" => "arrow2.gif",
    ":hmm:" => "hmm.gif",
    ":hmmm:" => "hmmm.gif",
    ":huh:" => "huh.gif",
    ":geek:" => "geek.gif",
    ":look:" => "look.gif",
    ":rolleyes:" => "rolleyes.gif",
    ":kiss:" => "kiss.gif",
    ":shifty:" => "shifty.gif",
    ":blink:" => "blink.gif",
    ":smartass:" => "smartass.gif",
    ":sick:" => "sick.gif",
    ":crazy:" => "crazy.gif",
    ":wacko:" => "wacko.gif",
    ":alien:" => "alien.gif",
    ":wizard:" => "wizard.gif",
    ":wave:" => "wave.gif",
    ":wavecry:" => "wavecry.gif",
    ":baby:" => "baby.gif",
    ":angry:" => "angry.gif",
    ":ras:" => "ras.gif",
    ":sly:" => "sly.gif",
    ":devil:" => "devil.gif",
    ":evil:" => "evil.gif",
    ":evilmad:" => "evilmad.gif",
    ":sneaky:" => "sneaky.gif",
    ":axe:" => "axe.gif",
    ":slap:" => "slap.gif",
    ":wall:" => "wall.gif",
    ":rant:" => "rant.gif",
    ":jump:" => "jump.gif",
    ":yucky:" => "yucky.gif",
    ":nugget:" => "nugget.gif",
    ":smart:" => "smart.gif",
    ":shutup:" => "shutup.gif",
    ":shutup2:" => "shutup2.gif",
    ":crockett:" => "crockett.gif",
    ":zorro:" => "zorro.gif",
    ":snap:" => "snap.gif",
    ":beer:" => "beer.gif",
    ":beer2:" => "beer2.gif",
    ":drunk:" => "drunk.gif",
    ":strongbench:" => "strongbench.gif",
    ":weakbench:" => "weakbench.gif",
    ":dumbells:" => "dumbells.gif",
    ":music:" => "music.gif",
    ":stupid:" => "stupid.gif",
    ":dots:" => "dots.gif",
    ":offtopic:" => "offtopic.gif",
    ":spam:" => "spam.gif",
    ":oops:" => "oops.gif",
    ":lttd:" => "lttd.gif",
    ":please:" => "please.gif",
    ":sorry:" => "sorry.gif",
    ":hi:" => "hi.gif",
    ":yay:" => "yay.gif",
    ":cake:" => "cake.gif",
    ":hbd:" => "hbd.gif",
    ":band:" => "band.gif",
    ":punk:" => "punk.gif",
    ":rofl:" => "rofl.gif",
    ":bounce:" => "bounce.gif",
    ":mbounce:" => "mbounce.gif",
    ":thankyou:" => "thankyou.gif",
    ":gathering:" => "gathering.gif",
    ":hang:" => "hang.gif",
    ":chop:" => "chop.gif",
    ":rip:" => "rip.gif",
    ":whip:" => "whip.gif",
    ":judge:" => "judge.gif",
    ":chair:" => "chair.gif",
    ":tease:" => "tease.gif",
    ":box:" => "box.gif",
    ":boxing:" => "boxing.gif",
    ":guns:" => "guns.gif",
    ":shoot:" => "shoot.gif",
    ":shoot2:" => "shoot2.gif",
    ":flowers:" => "flowers.gif",
    ":wub:" => "wub.gif",
    ":lovers:" => "lovers.gif",
    ":kissing:" => "kissing.gif",
    ":kissing2:" => "kissing2.gif",
    ":console:" => "console.gif",
    ":group:" => "group.gif",
    ":hump:" => "hump.gif",
    ":hooray:" => "hooray.gif",
    ":happy2:" => "happy2.gif",
    ":clap:" => "clap.gif",
    ":clap2:" => "clap2.gif",
    ":weirdo:" => "weirdo.gif",
    ":yawn:" => "yawn.gif",
    ":bow:" => "bow.gif",
    ":dawgie:" => "dawgie.gif",
    ":cylon:" => "cylon.gif",
    ":book:" => "book.gif",
    ":fish:" => "fish.gif",
    ":mama:" => "mama.gif",
    ":pepsi:" => "pepsi.gif",
    ":medieval:" => "medieval.gif",
    ":rambo:" => "rambo.gif",
    ":ninja:" => "ninja.gif",
    ":hannibal:" => "hannibal.gif",
    ":party:" => "party.gif",
    ":snorkle:" => "snorkle.gif",
    ":evo:" => "evo.gif",
    ":king:" => "king.gif",
    ":chef:" => "chef.gif",
    ":mario:" => "mario.gif",
    ":pope:" => "pope.gif",
    ":fez:" => "fez.gif",
    ":cap:" => "cap.gif",
    ":cowboy:" => "cowboy.gif",
    ":pirate:" => "pirate.gif",
    ":pirate2:" => "pirate2.gif",
    ":rock:" => "rock.gif",
    ":cigar:" => "cigar.gif",
    ":icecream:" => "icecream.gif",
    ":oldtimer:" => "oldtimer.gif",
    ":trampoline:" => "trampoline.gif",
    ":banana:" => "bananadance.gif",
    ":smurf:" => "smurf.gif",
    ":yikes:" => "yikes.gif",
    ":osama:" => "osama.gif",
    ":saddam:" => "saddam.gif",
    ":santa:" => "santa.gif",
    ":indian:" => "indian.gif",
    ":pimp:" => "pimp.gif",
    ":nuke:" => "nuke.gif",
    ":jacko:" => "jacko.gif",
    ":ike:" => "ike.gif",
    ":greedy:" => "greedy.gif",
    ":super:" => "super.gif",
    ":wolverine:" => "wolverine.gif",
    ":spidey:" => "spidey.gif",
    ":spider:" => "spider.gif",
    ":bandana:" => "bandana.gif",
    ":construction:" => "construction.gif",
    ":sheep:" => "sheep.gif",
    ":police:" => "police.gif",
    ":detective:" => "detective.gif",
    ":bike:" => "bike.gif",
    ":fishing:" => "fishing.gif",
    ":clover:" => "clover.gif",
    ":horse:" => "horse.gif",
    ":shit:" => "shit.gif",
    ":soldiers:" => "soldiers.gif",
    );

$privatesmilies = array(":)" => "smile1.gif", 
    // ";)" => "wink.gif",
    ":wink:" => "wink.gif",
    ":D" => "grin.gif",
    ":P" => "tongue.gif",
    ":(" => "sad.gif",
    ":'(" => "cry.gif",
    ":|" => "noexpression.gif", 
    // "8-)" => "cool1.gif",
    ":Boozer:" => "alcoholic.gif",
    ":deadhorse:" => "deadhorse.gif",
    ":spank:" => "spank.gif",
    ":yoji:" => "yoji.gif",
    ":locked:" => "locked.gif",
    ":grrr:" => "angry.gif", // legacy
    "O:-" => "innocent.gif", // legacy
    ":sleeping:" => "sleeping.gif", // legacy
    "-_-" => "unsure.gif", // legacy
    ":clown:" => "clown.gif",
    ":mml:" => "mml.gif",
    ":rtf:" => "rtf.gif",
    ":morepics:" => "morepics.gif",
    ":rb:" => "rb.gif",
    ":rblocked:" => "rblocked.gif",
    ":maxlocked:" => "maxlocked.gif",
    ":hslocked:" => "hslocked.gif",
    );
    
// Set this to the line break character sequence of your system
$linebreak = "\r\n";

/* ersetzt durch pdo_row_count()
function get_row_count($table, $suffix = "")
{
    if ($suffix)
        $suffix = " $suffix";
    ($r = mysql_query("SELECT COUNT(*) FROM $table$suffix")) or die(mysql_error());
    ($a = mysql_fetch_row($r)) or die(mysql_error());
    return $a[0];
} */

function stdmsg($heading, $text)
{

    ?>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr>
  <td class="tabletitle" colspan="10" width="100%"><b><?=$heading?></b></td> 
 </tr><tr><td width="100%" class="tablea"><?=$text?></td></tr></table><br>
<?php
} 

function stderr($heading, $text)
{
    stdhead();
    stdmsg($heading, $text);
    stdfoot();
    die;
} 

function sqlerr($file = '', $line = '')
{
    print("<table border=0 bgcolor=blue align=left cellspacing=0 cellpadding=10 style='background: blue'>" . "<tr><td class=embedded><font color=white><h1>SQL Error</h1>\n" . "<b>" . mysql_error() . ($file != '' && $line != '' ? "<p>in $file, line $line</p>" : "") . "</b></font></td></tr></table>");
    die;
} 
// Returns the current time in GMT in MySQL compatible format.
function get_date_time($timestamp = 0)
{
    if ($timestamp)
        return date("Y-m-d H:i:s", $timestamp);
    else
        return date("Y-m-d H:i:s");
} 

function encodehtml($s, $linebreaks = true)
{
    $s = str_replace("<", "&lt;", str_replace("&", "&amp;", $s));
    if ($linebreaks)
        $s = nl2br($s);
    return $s;
} 

function get_dt_num()
{
    return date("YmdHis");
} 

function format_urls($s)
{
    return preg_replace("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^()<>\s]+)/i",
        "\\1<a href=\"redir.php?url=\\2\">\\2</a>", $s);
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
function _strlastpos ($haystack, $needle, $offset = 0)
{
    $addLen = strlen ($needle);
    $endPos = $offset - $addLen;
    while (true) {
        if (($newPos = strpos ($haystack, $needle, $endPos + $addLen)) === false) break;
        $endPos = $newPos;
    } 
    return ($endPos >= 0) ? $endPos : false;
} 

function format_quotes($s)
{
    while ($old_s != $s) {
        $old_s = $s; 
        // [quote]Text[/quote]
        $s = preg_replace("/\[quote\](.+?)\[\/quote\]/is",
            "<p><b>Zitat:</b></p><table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\"><tr><td class=\"inposttable\">\\1</td></tr></table><br>", $s); 
        // [quote=Author]Text[/quote]
        $s = preg_replace("/\[quote=(.+?)\](.+?)\[\/quote\]/is",
            "<p><b>\\1 hat geschrieben:</b></p><table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\"><tr><td class=\"inposttable\">\\2</td></tr></table><br>", $s);
    } 

    return $s;
} 

function format_comment($text, $strip_html = true)
{
    global $smilies, $privatesmilies;

    $s = stripslashes($text); 
    // This fixes the extraneous ;) smilies problem. When there was an html escaped
    // char before a closing bracket - like >), "), ... - this would be encoded
    // to &xxx;), hence all the extra smilies. I created a new :wink: label, removed
    // the ;) one, and replace all genuine ;) by :wink: before escaping the body.
    // (What took us so long? :blush:)- wyz
    $s = str_replace(";)", ":wink:", $s);

    if ($strip_html)
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
    $s = preg_replace("/\[color=([a-zA-Z]+)\]((\s|.)+?)\[\/color\]/i",
        "<font color=\\1>\\2</font>", $s); 
    // [color=#ffcc99]Text[/color]
    $s = preg_replace("/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\]((\s|.)+?)\[\/color\]/i",
        "<font color=\\1>\\2</font>", $s); 
    // [url=http://www.example.com]Text[/url]
    $s = preg_replace("/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/i",
        "<a href=\"\\1\">\\2</a>", $s); 
    // [url]http://www.example.com[/url]
    $s = preg_replace("/\[url\]([^()<>\s]+?)\[\/url\]/i",
        "<a href=\"\\1\">\\1</a>", $s); 
    // [size=4]Text[/size]
    $s = preg_replace("/\[size=([1-7])\]((\s|.)+?)\[\/size\]/i",
        "<font size=\\1>\\2</font>", $s); 
    // [font=Arial]Text[/font]
    $s = preg_replace("/\[font=([a-zA-Z ,]+)\]((\s|.)+?)\[\/font\]/i",
        "<font face=\"\\1\">\\2</font>", $s);
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
    $s = str_replace("  ", " &nbsp;", $s);

    reset($smilies);
    while (list($code, $url) = each($smilies))
    $s = str_replace($code, "<img src=\"/pic/smilies/$url\" border=\"0\" alt=\"" . htmlspecialchars($code) . "\">", $s);

    reset($privatesmilies);
    while (list($code, $url) = each($privatesmilies))
    $s = str_replace($code, "<img border=\"0\" src=\"/pic/smilies/$url\" alt=\"\">", $s);

    return $s;
} 

function get_user_class()
{
    global $CURUSER;
    return $CURUSER["class"];
} 

function get_user_class_name($class)
{
    switch ($class) {
        case UC_USER: return "User";
        case UC_POWER_USER: return "Power User";
        case UC_VIP: return "VIP";
        case UC_UPLOADER: return "Uploader";
        case UC_GUTEAM: return "GU-Betreuer";
        case UC_MODERATOR: return "Moderator";
        case UC_ADMINISTRATOR: return "Administrator";
        case UC_SYSOP: return "SysOp";
    } 
    return "";
} 

function is_valid_user_class($class)
{
    return is_numeric($class) && floor($class) == $class && $class >= UC_USER && $class <= UC_SYSOP;
} 

function is_valid_id($id)
{
    return is_numeric($id) && ($id > 0) && (floor($id) == $id);
} 

function delete_acct($id){ 
    // Mailadresse holen
	$qry = $GLOBALS['DB']->prepare('SELECT `email`,`username`,`status` FROM `users` WHERE `id`= :id');
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount() > 0){
		$userinfo = $qry->fetchObject();
	}
	
    //$userinfo = @mysql_fetch_assoc(@mysql_query("SELECT `email`,`username`,`status` FROM `users` WHERE `id`=$id"));
	
    if ($userinfo->email && $userinfo->status == "confirmed") {
        $mailbody = "Dein Account auf ".$GLOBALS["SITENAME"]." wurde gelöscht. Dies ist entweder passiert,
weil Du Dich längere Zeit nicht mehr eingeloggt hast, oder Dein Account von einem
Administrator deaktiviert wurde.

Diese E-Mail dient dazu, Dich darüber zu informieren, dass Du diesen Account nun nicht
mehr nutzen kannst. Bitte antworte nicht auf diese E-Mail!";
        mail("\"" . $userinfo->username . "\" <" . $userinfo->email . ">", "Account gelöscht auf ".$GLOBALS["SITENAME"], $mailbody);
    } 
	
	$sql = array();
	$sql[] = ['DELETE FROM `users` WHERE `id`= :id'];
	$sql[] = ['DELETE FROM `bitbucket` WHERE `user`= :id'];
	$sql[] = ['DELETE FROM `nowait` WHERE `user_id`= :id'];
	$sql[] = ['DELETE FROM `pmfolders` WHERE `owner`= :id'];
	$sql[] = ['DELETE FROM `traffic` WHERE `userid`= :id'];
	$sql[] = ['DELETE FROM `modcomments` WHERE `userid`= :id'];
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
	$qry = $GLOBALS['DB']->prepare('SELECT `id` FROM `messages` WHERE `sender`=$id OR `receiver`= :id');
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount() > 0){
		$msgs = $qry->fetchAll();
		foreach($msgs as $m){
			$msgids[] = $m["id"];
		}
	}
    $msgids = implode(",", $msgids);
	// enthält... mehr mysql..
    // . | . .
	// . v . .
	deletePersonalMessages($msgids, $id);

    write_log("accdeleted", "Der Benutzer '".htmlspecialchars($userinfo->username)."' mit der ID " . $id . " wurde aus der Datenbank gelöscht.");
} 
// -------- Begins a main frame
function begin_main_frame()
{
    print("<table class=\"main\" width=\"750\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">" . "<tr><td class=\"embedded\">\n");
} 
// -------- Ends a main frame
function end_main_frame()
{
    print("</td></tr></table>\n");
} 

function begin_frame($caption = "", $center = false, $width = "100%")
{
    if ($center)
        $tdextra = " style=\"text-align: center\"";
	else
		$tdextra = " ";
	
    ?><table cellpadding="4" cellspacing="1" border="0" style="width:<?=$width?>" class="tableinborder">
 <tr>
  <td class="tabletitle" colspan="10" width="100%" style="text-align: center"><b><?=$caption?></b></td> 
 </tr><tr><td width="100%" class="tablea"<?=$tdextra?>>
<?php
} 

function attach_frame($padding = 10)
{
    print("</td></tr><tr><td class=\"tablea\" style=\"border-top: 0px\">\n");
} 

function end_frame()
{
    print("</td></tr></table><br>\n");
} 

function begin_table($fullwidth = false, $padding = 4)
{
    if ($fullwidth)
        $width = " width=\"100%\"";
	else
		$width = " ";
    print("<table class=\"tableinborder\" $width border=\"0\" cellspacing=\"1\" cellpadding=\"$padding\">\n");
} 

function end_table()
{
    print("</table><br>\n");
} 
// -------- Inserts a smilies frame
// (move to globals)
function insert_smilies_frame()
{
    global $smilies, $BASEURL;

    begin_frame("Smilies", true);

    print("<center>");
    begin_table(false, 5);

    print("<tr>");
    for ($I = 0; $I < 3; $I++) {
        if ($I > 0) print("<td class=\"tablecat\">&nbsp;</td>");
        print("<td class=\"tablecat\">Eingeben...</td><td class=\"tablecat\">...f&uuml;r Smilie</td>");
    } 
    print("</tr>\n");

    $I = 0;
    print("<tr>");
    while (list($code, $url) = each($smilies)) {
        if ($I && $I % 3 == 0) print("</tr>\n<tr>");
        if ($I % 3) print("<td class=\"inposttable\">&nbsp;</td>");
        print("<td class=\"tablea\">$code</td><td class=\"tableb\"><img src=\"$BASEURL/pic/smilies/$url\"></td>");
        $I++;
    } 
    if ($I % 3)
        print("<td class=\"inposttable\" colspan=" . ((3 - $I % 3) * 3) . ">&nbsp;</td>");
    print("</tr>\n");
    end_table();
    print("</center>");
    end_frame();
} 

function sql_timestamp_to_unix_timestamp($s)
{
    return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
} 

function get_ratio_color($ratio)
{
    if ($ratio < 0.1) return "#ff0000";
    if ($ratio < 0.2) return "#ee0000";
    if ($ratio < 0.3) return "#dd0000";
    if ($ratio < 0.4) return "#cc0000";
    if ($ratio < 0.5) return "#bb0000";
    if ($ratio < 0.6) return "#aa0000";
    if ($ratio < 0.7) return "#990000";
    if ($ratio < 0.8) return "#880000";
    if ($ratio < 0.9) return "#770000";
    if ($ratio < 1) return "#660000";
    return "#000000";
} 

function get_slr_color($ratio)
{
    if ($ratio < 0.025) return "#ff0000";
    if ($ratio < 0.05) return "#ee0000";
    if ($ratio < 0.075) return "#dd0000";
    if ($ratio < 0.1) return "#cc0000";
    if ($ratio < 0.125) return "#bb0000";
    if ($ratio < 0.15) return "#aa0000";
    if ($ratio < 0.175) return "#990000";
    if ($ratio < 0.2) return "#880000";
    if ($ratio < 0.225) return "#770000";
    if ($ratio < 0.25) return "#660000";
    if ($ratio < 0.275) return "#550000";
    if ($ratio < 0.3) return "#440000";
    if ($ratio < 0.325) return "#330000";
    if ($ratio < 0.35) return "#220000";
    if ($ratio < 0.375) return "#110000";
    return "#000000";
} 

function get_class_color($class)
{
    switch ($class) {
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

function write_log($typ, $text)
{
    $typ = sqlesc($typ);
    $text = sqlesc($text);
    $added = sqlesc(get_date_time());
    mysql_query("INSERT INTO `sitelog` (`typ`, `added`, `txt`) VALUES($typ, $added, $text)") or sqlerr(__FILE__, __LINE__);
}

function write_modcomment($uid, $moduid, $text)
{
    $text = sqlesc(stripslashes($text));
    mysql_query("INSERT INTO `modcomments` (`added`, `userid`, `moduid`, `txt`) VALUES (NOW(), $uid, $moduid, $text)");
}

function get_elapsed_time($ts)
{
    /* $mins = floot((gmtime() - $ts) / 60); */

    $mins = floor((time() - $ts) / 60);
    $hours = floor($mins / 60);
    $mins -= $hours * 60;
    $days = floor($hours / 24);
    $hours -= $days * 24;
    $weeks = floor($days / 7);
    $days -= $weeks * 7;
    $t = "";
    if ($weeks > 0)
        return "$weeks Woche" . ($weeks > 1 ? "n" : "");
    if ($days > 0)
        return "$days Tag" . ($days > 1 ? "en" : "");
    if ($hours > 0)
        return "$hours Stunde" . ($hours > 1 ? "n" : "");
    if ($mins > 0)
        return "$mins Minute" . ($mins > 1 ? "n" : "");
    return "< 1 Minute";
} 

function hex_esc($matches)
{
    return sprintf("%02x", ord($matches[0]));
} 

function getagent($httpagent, $peer_id)
{
    global $client_uas, $clean_uas; 
    // Spezialfälle mittels Peer-ID bestimmen
    if (substr($peer_id, 0, 4) == "exbc")
        $httpagent = "BitComet/" . ord(substr($peer_id, 4, 1)) . "." . ord(substr($peer_id, 5, 1));
    if (preg_match("/^-BC(\d\d)(\d\d)-/", $peer_id, $matches))
        $httpagent = "BitComet/" . intval($matches[1]) . "." . intval($matches[2]);

    return preg_replace($client_uas, $clean_uas, $httpagent);
} 

function get_wait_time($userid, $torrentid, $only_wait_check = false, $left = -1)
{
    $res = mysql_query("SELECT users.class, users.downloaded, users.uploaded, UNIX_TIMESTAMP(users.added) AS u_added, UNIX_TIMESTAMP(torrents.added) AS t_added, nowait.`status` AS `status` FROM users LEFT JOIN torrents ON torrents.id = $torrentid LEFT JOIN nowait ON nowait.user_id = $userid AND nowait.torrent_id = $torrentid WHERE users.id = $userid");
    $arr = mysql_fetch_assoc($res);

    if (($arr["status"] != "granted" || ($arr["status"] == "granted" && $left > 0 && $GLOBALS["NOWAITTIME_ONLYSEEDS"])) && $arr["class"] < UC_VIP) {
        $gigs = $arr["uploaded"] / 1073741824;
        $elapsed = floor((time() - $arr["t_added"]) / 3600);
        $regdays = floor((time() - $arr["u_added"]) / 86400);
        $ratio = (($arr["downloaded"] > 0) ? ($arr["uploaded"] / $arr["downloaded"]) : 1);

        $wait_times = explode("|", $GLOBALS["WAIT_TIME_RULES"]);

        $wait = 0;
        foreach ($wait_times as $rule) {
            $rule = explode(":", $rule, 4);
            // Format [#w][#d] or *
            // eg: 1w or 1w2d or 2d or * or 0
            preg_match("/([0-9]+w)?([0-9]+d)?|([\\*0])?/", $rule[2], $regrule);
            $regruledays = intval($regrule[1])*7 + intval($regrule[2]);
            
            if (($ratio < $rule[0] || $gigs < $rule[1]) && ($regruledays==0 || ($regruledays>0 && $regdays < $regruledays)))
                $wait = max($wait, $rule[3], 0);
        }

        if ($only_wait_check)
            return ($wait > 0);

        return max($wait - $elapsed, 0);
    } 
    return 0;
} 

function get_cur_wait_time($userid)
{
    $res = mysql_query("SELECT class, downloaded, uploaded, UNIX_TIMESTAMP(added) AS added FROM users WHERE users.id = $userid");
    $arr = mysql_fetch_assoc($res);

    if ($arr["class"] < UC_VIP) {
        $gigs = $arr["uploaded"] / 1073741824;
        $regdays = floor((time() - $arr["added"]) / 86400);
        $ratio = (($arr["downloaded"] > 0) ? ($arr["uploaded"] / $arr["downloaded"]) : 1);

        $wait_times = explode("|", $GLOBALS["WAIT_TIME_RULES"]);

        $wait = 0;
        foreach ($wait_times as $rule) {
            $rule = explode(":", $rule, 4);
            // Format [#w][#d] or *
            // eg: 1w or 1w2d or 2d or * or 0
            preg_match("/([0-9]+w)?([0-9]+d)?|([\\*0])?/", $rule[2], $regrule);
            $regruledays = intval($regrule[1])*7 + intval($regrule[2]);
            
            if (($ratio < $rule[0] || $gigs < $rule[1]) && ($regruledays==0 || ($regruledays>0 && $regdays < $regruledays)))
                $wait = max($wait, $rule[3], 0);
        } 

        return $wait;
    } 
    return 0;
}

function get_torrent_limits($userinfo)
{
    $limit = array("seeds" => -1, "leeches" => -1, "total" => -1);

    if ($userinfo["tlimitall"] == 0) {
        // Auto limit
        $ruleset = explode("|", $GLOBALS["TORRENT_RULES"]);
        $ratio = (($userinfo["downloaded"] > 0) ? ($userinfo["uploaded"] / $userinfo["downloaded"]) : (($userinfo["uploaded"] > 0) ? 1 : 0));
        $gigs = $userinfo["uploaded"] / 1073741824;
        
        $limit = array("seeds" => 0, "leeches" => 0, "total" => 0);
        foreach ($ruleset as $rule) {
            $rule_parts= explode(":", $rule);
            if ($ratio >= $rule_parts[0] && $gigs >= $rule_parts[1] && $limit["total"] <= $rule_parts[4]) {
                $limit["seeds"] = $rule_parts[2];
                $limit["leeches"] = $rule_parts[3];
                $limit["total"] = $rule_parts[4];
            }
        }
    } elseif ($userinfo["tlimitall"] > 0) {
        // Manual limit
        $limit["seeds"] = $userinfo["tlimitseeds"];
        $limit["leeches"] = $userinfo["tlimitleeches"];
        $limit["total"] = $userinfo["tlimitall"];
    }
    
    return $limit;
}

function resize_image($origfn, $tmpfile, $target_filename)
{
	// Bild laden
	if (preg_match("/(jp(e|eg|g))$/i", $origfn)) {
   		$img_pic = @imagecreatefromjpeg($tmpfile);
	}
	if (preg_match("/png$/i", $origfn)) {
   		$img_pic = @imagecreatefrompng($tmpfile);
	}
	if (preg_match("/gif$/i", $origfn)) {
   		$img_pic = @imagecreatefromgif($tmpfile);
	}
    	
	if (!$img_pic)
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

function torrent_image_upload($file, $id, $picnum)
{
	if (!isset($file) || $file["size"] < 1) {
		tr_status("err");
        array_push($GLOBALS["uploaderrors"], "Es wurden keine Daten von '".$file["name"]."' empfangen!");
        return FALSE;
    }
    
    if ($file["size"] > $GLOBALS["MAX_UPLOAD_FILESIZE"]) {
		tr_status("err");
        array_push($GLOBALS["uploaderrors"], "Die Bilddatei '".$file["name"]."' ist zu groß (max. ".mksizeint($GLOBALS["MAX_UPLOAD_FILESIZE"]).")!");
        return FALSE;
    }

    $it = exif_imagetype($file["tmp_name"]);
	if ($it != IMAGETYPE_GIF && $it != IMAGETYPE_JPEG && $it != IMAGETYPE_PNG) {
		tr_status("err");
        array_push($GLOBALS["uploaderrors"], "Sorry, die hochgeladene Datei '".$file["name"]."' konnte nicht als gültige Bilddatei verifiziert werden.");
        return FALSE;
    }

    $i = strrpos($file["name"], ".");
	if ($i !== false)
	{
		$ext = strtolower(substr($file["name"], $i));
		if (($it == IMAGETYPE_GIF  && $ext != ".gif") || 
            ($it == IMAGETYPE_JPEG && $ext != ".jpg") || 
            ($it == IMAGETYPE_PNG  && $ext != ".png")) {
		    tr_status("err");
            array_push($GLOBALS["uploaderrors"], "Ung&uuml;tige Dateinamenerweiterung: <b>$ext</b>");
            return FALSE;
        }
		$filename .= $ext;
	}
	else {
		tr_status("err");
        array_push($GLOBALS["uploaderrors"], "Die Datei '".$file["name"]."' besitzt keine Dateinamenerweiterung.");
        return FALSE;
    }

    $img = resize_image($file["name"], $file["tmp_name"], $GLOBALS["BITBUCKET_DIR"] . "/t-$id-$picnum.jpg", 100);    
    if ($img === FALSE) {
		tr_status("err");
        array_push($GLOBALS["uploaderrors"], "Das Bild '".$file["name"]."' konnte nicht verkleinert werden.");
        return FALSE;
    }
    $ret = imagejpeg($img, $GLOBALS["BITBUCKET_DIR"] . "/f-$id-$picnum.jpg", 85);
    imagedestroy($img);
    if (!$ret) {
		tr_status("err");
        array_push($GLOBALS["uploaderrors"], "Die Originalversion des Bildes '".$file["name"]."' konnte nicht auf dem Server gespeichert werden - bitte SysOp benachrichtigen!");
        return FALSE;
    } else {
        tr_status("ok");
        return TRUE;
    }
}

function strip_ascii_art($text)
{
    // First, remove all "weird" characters.
    $text = preg_replace("/[^a-zA-Z0-9öäüÖÄÜß\\-_?!&[\\]().,;:+=#*~@\\/\\\\'\"><\\s]/", "", $text);
    
    while ($text != $oldtext) {
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

function gen_nfo_pic($nfotext, $target_filename)
{
    // Make array of NFO lines and break lines at 80 chars
    $nfotext = preg_replace('/\r\n/', "\n", $nfotext);
    $lines = explode("\n", $nfotext);
    for ($I=0;$I<count($lines);$I++) {
        $lines[$I] = chop($lines[$I]);
        $lines[$I] = wordwrap($lines[$I], 82, "\n", 1);
    }
    $lines = explode("\n", implode("\n", $lines));
    
    // Get longest line
    $cols = 0;
    for ($I=0;$I<count($lines);$I++) {
        
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
    if ($font < 5)
        die("Konnte das NFO-Font nicht laden. Admin benachrichtigen!");
    
    $imagewidth = $cols * imagefontwidth($font) + 1;
    $imageheight = $linecnt * imagefontheight($font) + 1;
    
    $nfoimage = imagecreate($imagewidth, $imageheight);
    $white = imagecolorallocate($nfoimage, 255, 255, 255);
    $black = imagecolorallocate($nfoimage, 0, 0, 0);
    
    for ($I=0;$I<$linecnt;$I++)
        imagestring($nfoimage, $font, 0, $I*imagefontheight($font), $lines[$I], $black);
    
    return imagepng($nfoimage, $target_filename);
}

?>