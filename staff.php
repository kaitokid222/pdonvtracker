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

require "include/bittorrent.php";
dbconn();
loggedinorreturn();


if(isset($_GET["act"]))
	$act = $_GET["act"];
else
	$act = false;

// DELETE FORUM ACTION
if (isset($_GET['action']) && $_GET['action'] == "del") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Permission denied.");

    if (!$id) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?act=forum");
        die();
    } 

    $result = mysql_query ("SELECT * FROM topics where forumid = '" . $_GET['id'] . "'");
    if ($row = mysql_fetch_array($result)) {
        do {
            mysql_query ("DELETE FROM posts where topicid = '" . $row["id"] . "'") or sqlerr(__FILE__, __LINE__);
        } while ($row = mysql_fetch_array($result));
    } 
    mysql_query ("DELETE FROM topics where forumid = '" . $_GET['id'] . "'") or sqlerr(__FILE__, __LINE__);
    mysql_query ("DELETE FROM forums where id = '" . $_GET['id'] . "'") or sqlerr(__FILE__, __LINE__);

    header("Location: " . $_SERVER['PHP_SELF'] . "?act=forum");
    die();
} 
// EDIT FORUM ACTION
if (isset($_POST['action']) && $_POST['action'] == "editforum") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Permission denied.");

    if (!$name && !$desc && !$id) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?act=forum");
        die();
    } 

    mysql_query("UPDATE forums SET sort = '" . $_POST['sort'] . "', name = " . sqlesc($_POST['name']) . ", description = " . sqlesc($_POST['desc']) . ", minclassread = '" . $_POST['readclass'] . "', minclasswrite = '" . $_POST['writeclass'] . "', minclasscreate = '" . $_POST['createclass'] . "' where id = '" . $_POST['id'] . "'") or sqlerr(__FILE__, __LINE__);
    header("Location: " . $_SERVER['PHP_SELF'] . "?act=forum");
    die();
} 
// ADD FORUM ACTION
if(isset($_POST['action']) && $_POST['action'] == "addforum") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Permission denied.");

    if (!$name && !$desc) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?act=forum");
        die();
    } 

    mysql_query("INSERT INTO forums (sort, name,  description,  minclassread,  minclasswrite, minclasscreate) VALUES(" . $_POST['sort'] . ", " . sqlesc($_POST['name']) . ", " . sqlesc($_POST['desc']) . ", " . $_POST['readclass'] . ", " . $_POST['writeclass'] . ", " . $_POST['createclass'] . ")") or sqlerr(__FILE__, __LINE__);

    header("Location: " . $_SERVER['PHP_SELF'] . "?act=forum");
    die();
} 
// ADD IP TO BAN LIST ACTION
if (isset($_POST['action']) && $_POST['action'] == "ban") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Permission denied.");

    if (!$_POST["first"] && !$_POST["last"]) {
        stderr("Fehler", "Keine IP / keinen Bereich angegeben!");
    } 
    $first = ip2long($_POST['first']);
    $last = ip2long($_POST['last']);
    mysql_query("INSERT INTO bans (first, last, comment, added, addedby) VALUES(" . $first . ", " . $last . ", " . sqlesc($_POST['comment']) . ", '" . get_date_time() . "', " . $CURUSER["id"] . ")") or sqlerr(__FILE__, __LINE__);

    stderr("IP (Bereich) gesperrt", "<p>Die angegebene IP bzw. der Bereich wurde gesperrt.</p><p><a href=\"" . $_SERVER['PHP_SELF'] . "?act=ban\">Zurück</a></p>");
} 
// DELETE IP FROM BAN LIST
if (isset($_GET['action']) && $_GET["action"] == "ipdel") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Permission denied.");

    if (!$_GET["id"]) {
        stderr("Fehler", "Keine Einträge ausgewählt!");
    } 
    if (is_array($_GET["id"])) {
        foreach ($_GET["id"] As $id) {
            mysql_query ("DELETE FROM bans where id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        } 
    } else {
        mysql_query ("DELETE FROM bans where id = " . sqlesc($_GET['id'])) or sqlerr(__FILE__, __LINE__);
    } 
    stderr("IPs (Bereiche) entsperrt", "<p>Die angegebenen IPs bzw. Bereiche wurde entsperrt.</p><p><a href=\"" . $_SERVER['PHP_SELF'] . "?act=ban\">Zurück</a></p>");
} 


stdhead("Staff");
if(!$act){
    // LIST ALL ADMINISTRATORS AND MODERATORS
    $dt = time() - 180;
    $dt = get_date_time($dt); 

	$qry = $GLOBALS['DB']->prepare("SELECT users.id, users.username, users.last_access, users.class, countries.name as cname, countries.flagpic as cflag FROM users LEFT JOIN countries ON countries.id = users.country WHERE class >= '10' AND status='confirmed' ORDER BY username");
	$qry->execute();
	$data = $qry->fetchAll(PDO::FETCH_ASSOC);

	$staff_table = array(10 => "", 20 => "" ,25 => "" ,50 => "" ,100 => "");
	$counters = array(10 => 0, 20 => 0 ,25 => 0 ,50 => 0 ,100 => 0);
	foreach($data as $arr){
        if($counters[$arr['class']] && $counters[$arr['class']] % 3 == 0){
            $staff_table[$arr['class']] = $staff_table[$arr['class']] . "</tr><tr height=15>";
        } 
        $staff_table[$arr['class']] = $staff_table[$arr['class']] . 
			"<td class=\"tablea\"><a class=\"altlink\" href=\"userdetails.php?id=" . $arr['id'] . "\"><font class=\"" . get_class_color($arr['class']) . "\">" . $arr['username'] . "</font></a></td>\n".
			"<td class=\"tableb\">" . ($arr['last_access'] > $dt ? "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_online2.gif\" border=\"0\" alt=\"online\">" : "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_offline2.gif\" border=\"0\" alt=\"offline\">") . "</td>\n". 
			"<td class=\"tablea\"><a href=\"messages.php?action=send&amp;receiver=" . $arr['id'] . "\">" . "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_pm2.gif\" border=\"0\"></a></td>\n" . 
			"<td class=\"tableb\">" . ($arr["cflag"] != "" ? "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "flag/" . $arr['cflag'] . "\" alt=\"" . $arr['cname'] . "\" border=\"0\" width=\"19\" height=\"12\">" : "") . "</td>\n";
        $counters[$arr['class']]++;
        if (($counters[$arr['class']] % 3) != 0)
            $staff_table[$arr['class']] .= "<td class=inposttable>&nbsp;</td>";
    }

	$class_arr = array_keys($counters);
	foreach($class_arr as $I){
        if ($counters[$I] % 3)
            $staff_table[$I] .= "<td class=\"inposttable\" colspan=\"" . ((3 - $counters[$I] % 3 + 1) * 5) . "\">&nbsp;</td>\n";
    } 

    
echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:750px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td colspan=\"10\" width=\"100%\">\n".
	"            <span class=\"smallfont\"><center><b>Tracker-Team</b></center></span>\n".
	"        </td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\"><center>\n".
	"            <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:725px\" class=\"tableinborder\">\n".
	"                <colgroup>\n".
	"                    <col width=\"125\"><col width=\"25\"><col width=\"35\"><col width=\"35\">\n".
	"                    <col width=\"85\">\n".
	"                    <col width=\"125\"><col width=\"25\"><col width=\"35\"><col width=\"35\">\n".
	"                    <col width=\"85\">\n".
	"                    <col width=\"125\"><col width=\"25\"><col width=\"35\"><col width=\"35\">\n".
	"                </colgroup>\n".
	"                <tr>\n".
	"                    <td class=\"tablea\" colspan=\"14\">Alle Fragen &uuml;ber Client-Software sowie bereits im FAQ beantwortete Fragen werden stillschweigend ignoriert.</td>\n".
	"                </tr>\n";
$staff_table_reverse = array_reverse($staff_table, true);
foreach($staff_table_reverse as $userclass => $trow){
	if($counters[$userclass] > 1){
		if($userclass == 100)
			$plural = "s";
		elseif($userclass >= 25 && $userclass < 100)
			$plural = "en";
		else
			$plural = "";
	}else
		$plural = "";
	echo "                <tr>\n".
		"                    <td class=\"tablecat\" colspan=\"14\"><b>" . get_user_class_name($userclass) . $plural . "</b></td>\n".
		"                </tr>\n".
		"                <tr height=\"15\">\n".
		$trow.
		"                </tr>\n";
	if($userclass > 10){
		echo "                <tr>\n".
			"                    <td class=\"inposttable\" colspan=\"14\">&nbsp;</td>\n".
			"                </tr>\n";
	}
}
echo "</table>\n".
	"</center></td>\n".
	"</tr>\n".
	"</table>\n".
	"<br>\n";
}
// ende teamliste

if(get_user_class() >= UC_MODERATOR){
    // LIST OF THE MOD TOOLS (ONLY VISIBLE WHEN YOU ARE MOD, ELSE YOU ONLY SEE LIST OF MODS
?>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Team-Werkzeuge</b> - Nur für Moderatoren sichtbar.
</center></span></td></tr><tr><td width="100%" class="tablea">
<table cellspacing="1" cellpadding="4" class="tableinborder" style="width:100%">
<colgroup>
  <col width="150">
  <col>
</colgroup>
<tr>
        <td class=tableb><a class=altlink href=<?=$_SERVER['PHP_SELF']?>?act=upstats>Upload-Stats</a></td>
        <td class=tablea>Upload- und Kategorieaktivit&auml;t anzeigen</td>
</tr>
<tr>
        <td class=tableb><a class=altlink href=news.php>News-Seite</a></td>
        <td class=tablea>Newseintr&auml;ge auf der Startseite hinzuf&uuml;gen, &auml;ndern oder l&ouml;schen</td>
</tr>
<?php
if(get_user_class() >= UC_ADMINISTRATOR){
?>
<tr>
        <td class=tableb><a class=altlink href=<?=$_SERVER['PHP_SELF']?>?act=cleanaccs>Accountbereinigung</a></td>
        <td class=tablea>Accounts nach Inaktivit&auml;t und Transfer / Ratio bereinigen</td>
</tr>
<?php
} 
?>
<tr>
        <td class=tableb><a class=altlink href=<?=$_SERVER['PHP_SELF']?>?act=last>Neueste Benutzer</a></td>
        <td class=tablea>Die 100 neuesten Benutzer</td>
</tr>
<tr>
        <td class=tableb><a class=altlink href=<?=$_SERVER['PHP_SELF']?>?act=forum>Foren verwalten</a></td>
        <td class=tablea>Foren hinzuf&uuml;gen, &auml;ndern oder l&ouml;schen</td>
</tr>
<tr>
        <td class=tableb><a class=altlink href=<?=$_SERVER['PHP_SELF']?>?act=ban>IPs sperren</a></td>
        <td class=tablea>Eine IP oder einen IP-Bereich von der Seite ausschlie&szlig;en</td>
</tr>
<tr>
        <td class=tableb><a class=altlink href=usersearch.php>Benutzersuche</a></td>
        <td class=tablea>Eine Benutzersuche durchf&uuml;hren</td>
</tr>
</table>
</td></tr></table>
<script type="text/javascript">
function selectall() {
    var myForm = document.getElementById('delform');
    
    for (I=0; I<=myForm.elements.length; I++) {
        eval("myForm.elements['delid" + I + "']").checked = true;
    } 
}
</script>

<br>

<?php
    if ($act == "cleanaccs" && get_user_class() >= UC_ADMINISTRATOR) {
        $since_arr = array(0 => "---",
            1 => "1 Woche",
            2 => "2 Wochen",
            3 => "3 Wochen",
            4 => "4 Wochen",
            6 => "6 Wochen",
            8 => "8 Wochen",
            10 => "10 Wochen",
            12 => "12 Wochen"
            );

        $scale_arr = array(1 => "Bytes",
            1024 => "KB",
            (1024 * 1024) => "MB",
            (1024 * 1024 * 1024) => "GB"
            );

        if (!isset($_POST["uploadscale"])) $_POST["uploadscale"] = 1;
        if (!isset($_POST["downloadscale"])) $_POST["downloadscale"] = 1;

        ?>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Accountbereinigung</b> - Suche nach inaktiven Benutzern ohne nennenswerten Transfer
</center></span></td></tr><tr><td width="100%" class="tablea">
<?php

        echo '<form id="delform" action="staff.php?act=cleanaccs" method="post">';
        echo '<table border="0" align="center" cellpadding="2" cellspacing="1" class="tableinborder"><tr>';
        echo '<td class="tableb">Registriert seit min.:</td><td class="tablea"><select name="regsince" size="1">';
        foreach ($since_arr as $timespan => $desc) {
            echo '<option value="' . $timespan . '"';
            if ($_POST["regsince"] == $timespan) {
                echo ' selected="selected"';
                $regsince = $desc;
            } 
            echo '>' . $desc . '</option>' . "\n";
        } 
        echo '</select></td><td class="tableb">Inaktiv seit min.:</td><td class="tablea"><select name="inactsince" size="1">';
        reset($since_arr);
        foreach ($since_arr as $timespan => $desc) {
            echo '<option value="' . $timespan . '"';
            if ($_POST["inactsince"] == $timespan) {
                echo ' selected="selected"';
                $inactsince = $desc;
            } 
            echo '>' . $desc . '</option>' . "\n";
        } 
        echo '</select></td></tr>';
        echo '<tr><td class="tableb">Uploadmenge:</td>';
        echo '<td class="tablea"><select name="uplminmax" size="1">';
        echo '<option value="min"';
        if (!isset($_POST["uplminmax"]) || $_POST["uplminmax"] == "min") echo ' selected="selected"';
        echo '>Min.</option><option value="max"';
        if ($_POST["uplminmax"] == "max") echo ' selected="selected"';
        echo '>Max.</option></select><input type="text" name="upload" size="10" value="' . htmlspecialchars(intval($_POST["upload"])) . '"> ';
        echo '<select name="uploadscale" size="1">';
        foreach ($scale_arr as $size => $text) {
            echo '<option value="' . $size . '"';
            if ($_POST["uploadscale"] == $size) {
                echo ' selected="selected"';
                $upscale = $text;
            } 
            echo '>' . $text . '</option>' . "\n";
        } 
        echo '</td><td class="tableb">Downloadmenge:</td><td class="tablea"><select name="dwnminmax" size="1"><option value="min"';
        if (!isset($_POST["dwnminmax"]) || $_POST["dwnminmax"] == "min") echo ' selected="selected"';
        echo '>Min.</option><option value="max"';
        if ($_POST["dwnminmax"] == "max") echo ' selected="selected"';
        echo '>Max.</option></select><input type="text" name="download" size="10" value="' . htmlspecialchars(intval($_POST["download"])) . '"> ';
        echo '<select name="downloadscale" size="1">';
        reset($scale_arr);
        foreach ($scale_arr as $size => $text) {
            echo '<option value="' . $size . '"';
            if ($_POST["downloadscale"] == $size) {
                echo ' selected="selected"';
                $downscale = $text;
            } 
            echo '>' . $text . '</option>' . "\n";
        } 
        echo '</td></tr><tr><td class="tableb">Ratio:</td><td class="tablea"><select name="ratiominmax" size="1"><option value="min"';
        if (!isset($_POST["ratiominmax"]) || $_POST["ratiominmax"] == "min") echo ' selected="selected"';
        echo '>Min.</option><option value="max"';
        if ($_POST["ratiominmax"] == "max") echo ' selected="selected"';
        echo '>Max.</option></select><input type="text" name="ratio" size="5" maxlength="6" value="' . htmlspecialchars(floatval($_POST["ratio"])) . '"></td>';
        echo '<td class="tablea" colspan="2" align="center"><input type="submit" name="suchen" value="Suchen"></td></tr>';
        echo '</table><br>';

        if (isset($_POST["delaccts"])) {
            // Markierte Accounts deaktivieren
            $matchstr = "Registriert seit " . $regsince . ", inaktiv seit " . $inactsince . "\n";
            $matchstr .= "Download " . ($_POST["dwnminmax"] == "max"?"max.":"min.") . " " . intval($_POST["download"]) . " " . $downscale . "\n";
            $matchstr .= "Upload " . ($_POST["uplminmax"] == "max"?"max.":"min.") . " " . intval($_POST["upload"]) . " " . $downscale . "\n";
            $matchstr .= "Ratio " . ($_POST["ratiominmax"] == "max"?"max.":"min.") . " " . floatval($_POST["ratio"]);
            if (is_array($_POST["id"])) {
                foreach ($_POST["id"] as $userid) {
                    $userid = intval($userid);
                    $res = mysql_query("SELECT username FROM users WHERE id=$userid");
                    $arr = mysql_fetch_assoc($res);
                    write_modcomment($userid, $CURUSER["id"], "Deaktiviert wegen Accountbereinigung.\nKriterien:\n$matchstr");
                    write_log("accdisabled", "Benutzeraccount '<a href=\"userdetails.php?id=$userid\">$arr[username]</a>' wurde von $CURUSER[username] deaktiviert (Accountbereinigung).");
                    mysql_query("UPDATE users SET enabled = 'no' WHERE id=$userid");
                } 

                echo "<p style=\"color: red;\">Alle " . count($_POST["id"]) . " markierten Benutzeraccounts wurden deaktivert! Beim nächsten Durchlauf des CleanUp-Scripts sollten alle Benutzer entfernt werden, die länger als " . $GLOBALS["DISABLED_TIMEOUT"] . " Tage inaktiv waren (wahrscheinlich alle). <a href=\"docleanup.php\">Klicke hier</a>, um das CleanUp-Script direkt auszuführen.</p>";
            } else {
                echo "<p style=\"color: red;\">Keine Benutzer zum Löschen ausgewählt!</p>";
            } 
        } elseif (isset($_POST["suchen"])) {
            if ($_POST["uplminmax"] == "min") $uplminmax = ">=";
            else $uplminmax = "<=";
            if ($_POST["dwnminmax"] == "min") $dwnminmax = ">=";
            else $dwnminmax = "<=";
            if ($_POST["ratiominmax"] == "min") $ratiominmax = "<=";
            else $ratiominmax = ">=";

            $where = "";
            if (intval($_POST["regsince"]) > 0) {
                $since_time = time() - (intval($_POST["regsince"]) * 86400 * 7);
                $where .= " added <= FROM_UNIXTIME($since_time)";
            } 

            if (intval($_POST["inactsince"]) > 0) {
                $since_time = time() - (intval($_POST["inactsince"]) * 86400 * 7);
                $where .= ($where != ""?" AND":"") . " last_access <= FROM_UNIXTIME($since_time)";
            } 

            if (isset($_POST["ratio"]) && intval($_POST["download"])) {
                $ratio_max = floatval($_POST["ratio"]);
                $where .= ($where != ""?" AND":"") . " $ratio_max $ratiominmax (users.uploaded / users.downloaded)";
            } 

            $where .= ($where != ""?" AND":"") . " uploaded $uplminmax " . (intval($_POST["upload"]) * intval($_POST["uploadscale"]));
            $where .= " AND downloaded $dwnminmax " . (intval($_POST["download"]) * intval($_POST["downloadscale"]));
            $where .= " AND enabled = 'yes' AND class<" . UC_VIP;

            $sql = "SELECT * FROM users WHERE $where ORDER BY added DESC";
            $result = mysql_query ($sql);

            echo '<table border="0" align="center" cellpadding="4" cellspacing="1" class="tableinborder">';
            echo "<tr><td class=tablea colspan=\"8\">Suchergebnisse, neueste Benutzer zuerst. Die Suche hat " . mysql_num_rows($result) . " Ergebnisse geliefert.</td></tr>";
            echo "<tr><td class=tablecat align=left>User</td><td class=tablecat>Ratio</td><td class=tablecat>IP</td><td class=tablecat>Date Joined</td><td class=tablecat>Last Access</td><td class=tablecat>Download</td><td class=tablecat>Upload</td><td class=tablecat>Deaktivieren <input type=\"button\" value=\"ALLE\" onclick=\"selectall();\"></td></tr>";

            if ($row = mysql_fetch_array($result)) {
                $I = 0;
                do {
                    if ($row["uploaded"] == "0") {
                        $ratio = "---";
                    } elseif ($row["downloaded"] == "0") {
                        $ratio = "---";
                    } else {
                        $ratio = number_format($row["uploaded"] / $row["downloaded"], 3);
                        $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
                    } 
                    echo "<tr><td class=tablea><a href=userdetails.php?id=" . $row["id"] . "><b><font color=" . get_class_color($row["class"]) . ">" . $row["username"] . "</font></b></a></td>";
                    echo "<td class=tableb><strong>" . $ratio . "</strong></td><td class=tablea>" . $row["ip"] . "</td><td class=tableb>" . $row["added"] . "<br>(Vor " . get_elapsed_time(sql_timestamp_to_unix_timestamp($row["added"])) . ")</td>";
                    echo "<td class=tablea>" . $row["last_access"] . "<br>(Vor " . get_elapsed_time(sql_timestamp_to_unix_timestamp($row["last_access"])) . ")</td><td class=tableb>" . mksize($row["downloaded"]) . "</td>";
                    echo "<td class=tablea>" . mksize($row["uploaded"]) . "</td>";
                    echo "<td class=tableb><input type=\"checkbox\" id=\"delid$I\" name=\"id[]\" value=\"" . $row["id"] . "\"></td></tr>\n";

                    $I++;
                } while ($row = mysql_fetch_array($result));
                echo "<tr><td class=tablea colspan=\"8\" align=\"center\"><input type=\"submit\" name=\"delaccts\" value=\"Markierte Accounts deaktivieren\"></td></tr>";
            } else {
                print "<tr><td colspan=\"8\">Keine Benutzer gefunden, bitte Suche bearbeiten!</td></tr>";
            } 
            echo "</table>";
        } 
        echo "</form>";
        end_frame();
    } 

    if ($act == "upstats") {
        // SHOWS THE STATISTICS OF THE UPLOADERS
        $res = mysql_query("SELECT COUNT(*) FROM torrents") or sqlerr(__FILE__, __LINE__);
        $n = mysql_fetch_row($res);
        $n_tor = $n[0];

        $res = mysql_query("SELECT COUNT(*) FROM peers") or sqlerr(__FILE__, __LINE__);
        $n = mysql_fetch_row($res);
        $n_peers = $n[0];

        $uporder = $_GET['uporder'];
        $catorder = $_GET["catorder"];

        if ($uporder == "lastul")
            $orderby = "last DESC, name";
        elseif ($uporder == "torrents")
            $orderby = "n_t DESC, name";
        elseif ($uporder == "peers")
            $orderby = "n_p DESC, name";
        else
            $orderby = "name";

        $query = "SELECT u.id, u.class, u.username AS name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) as n_p
        FROM users as u LEFT JOIN torrents as t ON u.id = t.owner LEFT JOIN peers as p ON t.id = p.torrent WHERE u.class = 3
        GROUP BY u.id UNION SELECT u.id, u.class, u.username AS name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) as n_p
        FROM users as u JOIN torrents as t ON u.id = t.owner LEFT JOIN peers as p ON t.id = p.torrent WHERE u.class <> 3
        GROUP BY u.id ORDER BY $orderby";

        $res = mysql_query($query) or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) == 0)
            stdmsg("Sorry...", "Keine Uploader.");
        else {
            begin_frame("Uploader-Aktivitäten", true);
            begin_table();
            print("<tr>\n
        <td class=tablecat><a href=\"" . $_SERVER['PHP_SELF'] . "?act=upstats&amp;uporder=uploader&amp;catorder=$catorder\" class=tablecatlink>Uploader</a></td>\n
        <td class=tablecat><a href=\"" . $_SERVER['PHP_SELF'] . "?act=upstats&amp;uporder=lastul&amp;catorder=$catorder\" class=tablecatlink>Letzter Upload</a></td>\n
        <td class=tablecat><a href=\"" . $_SERVER['PHP_SELF'] . "?act=upstats&amp;uporder=torrents&amp;catorder=$catorder\" class=tablecatlink>Torrents</a></td>\n
        <td class=tablecat>Proz.</td>\n
        <td class=tablecat><a href=\"" . $_SERVER['PHP_SELF'] . "?act=upstats&amp;uporder=peers&amp;catorder=$catorder\" class=tablecatlink>Peers</a></td>\n
        <td class=tablecat>Proz.</td>\n
        </tr>\n");
            while ($uper = mysql_fetch_array($res)) {
                print("<tr><td class=tablea><a href=userdetails.php?id=" . $uper['id'] . "><font class=\"" . get_class_color($uper["class"]) . "\"><b>" . $uper['name'] . "</b></font></a></td>\n");
                print("<td class=tableb " . ($uper['last']?(">" . $uper['last'] . " (vor " . get_elapsed_time(sql_timestamp_to_unix_timestamp($uper['last'])) . ")"):" style=\"text-align: center;\">---") . "</td>\n");
                print("<td class=tablea style=\"text-align: right;\">" . ($uper["class"] == UC_UPLOADER && $uper['n_t'] < 4?'<img src="' . $GLOBALS["PIC_BASE_URL"] . 'warned.gif" alt="!!">&nbsp;':"") . $uper['n_t'] . "</td>\n");
                print("<td class=tableb style=\"text-align: right;\">" . ($n_tor > 0?number_format(100 * $uper['n_t'] / $n_tor, 1) . "%":"---") . "</td>\n");
                print("<td class=tablea style=\"text-align: right;\">" . $uper['n_p'] . "</td>\n");
                print("<td class=tableb style=\"text-align: right;\">" . ($n_peers > 0?number_format(100 * $uper['n_p'] / $n_peers, 1) . "%":"---") . "</td></tr>\n");
            } 
            end_table();
            end_frame();
        } 

        if ($n_tor == 0)
            stdmsg("Sorry...", "Keine Kategorien definiert!");
        else {
            if ($catorder == "lastul")
                $orderby = "last DESC, c.name";
            elseif ($catorder == "torrents")
                $orderby = "n_t DESC, c.name";
            elseif ($catorder == "peers")
                $orderby = "n_p DESC, name";
            else
                $orderby = "c.name";

            $res = mysql_query("SELECT c.name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) AS n_p
        FROM categories as c LEFT JOIN torrents as t ON t.category = c.id LEFT JOIN peers as p
        ON t.id = p.torrent GROUP BY c.id ORDER BY $orderby") or sqlerr(__FILE__, __LINE__);

            begin_frame("Kategorie-Aktivität", true);
            begin_table();
            print("<tr><td class=tablecat><a href=\"" . $_SERVER['PHP_SELF'] . "?act=upstats&amp;uporder=$uporder&amp;catorder=category\" class=tablecatlink>Category</a></td>
        <td class=tablecat><a href=\"" . $_SERVER['PHP_SELF'] . "?act=upstats&amp;uporder=$uporder&amp;catorder=lastul\" class=tablecatlink>Letzter Upload</a></td>
        <td class=tablecat><a href=\"" . $_SERVER['PHP_SELF'] . "?act=upstats&amp;uporder=$uporder&amp;catorder=torrents\" class=tablecatlink>Torrents</a></td>
        <td class=tablecat>Proz.</td>
        <td class=tablecat><a href=\"" . $_SERVER['PHP_SELF'] . "?act=upstats&amp;uporder=$uporder&amp;catorder=peers\" class=tablecatlink>Peers</a></td>
        <td class=tablecat>Proz.</td></tr>\n");
            while ($cat = mysql_fetch_array($res)) {
                print("<tr><td class=tablea>" . $cat['name'] . "</b></a></td>");
                print("<td class=tableb " . ($cat['last']?(">" . $cat['last'] . " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($cat['last'])) . " ago)"):" style=\"text-align:center;\">---") . "</td>");
                print("<td class=tablea style=\"text-align:right;\">" . $cat['n_t'] . "</td>");
                print("<td class=tableb style=\"text-align:right;\">" . number_format(100 * $cat['n_t'] / $n_tor, 1) . "%</td>");
                print("<td class=tablea style=\"text-align:right;\">" . $cat['n_p'] . "</td>");
                print("<td class=tableb style=\"text-align:right;\">" . ($n_peers > 0?number_format(100 * $cat['n_p'] / $n_peers, 1) . "%":"---") . "</td>\n");
            } 
            end_table();
            end_frame();
        } 

        end_frame();
    } 
    ?>

<?php

    if ($act == "last") {
        begin_frame("Neueste Benutzer");

        $arr = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) AS `cnt` FROM `users` WHERE `enabled`='yes'"));
        $usercnt = $arr["cnt"];
        $pager = pager(100, $usercnt, "staff.php?act=last&");

        echo $pager[0];

        begin_table();
        echo "<tr><td class=tablecat align=left>Benutzer</td><td class=tablecat>Ratio</td><td class=tablecat>IP</td><td class=tablecat>Registriert</td><td class=tablecat>Letzter Zugriff</td><td class=tablecat>Download</td><td class=tablecat>Upload</td><td class=tablecat>Ehem. Accs</td></tr>";

        $result = mysql_query ("SELECT * FROM users WHERE enabled =  'yes' AND status = 'confirmed' ORDER BY added DESC " . $pager[2]);
        if ($row = mysql_fetch_array($result)) {
            do {
                if ($row["uploaded"] == "0")
                    $ratio = "---";
                elseif ($row["downloaded"] == "0")
                    $ratio = "---";
                else {
                    $ratio = number_format($row["uploaded"] / $row["downloaded"], 3);
                    $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
                } 
                // Get previous accounts
                $uidres = mysql_query("SELECT DISTINCT(`chash`) FROM `accounts` WHERE `userid`=" . $row["id"]);
                $acccnt = 0;
                while ($chash = mysql_fetch_assoc($uidres)) {
                    $ucntarr = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) AS `cnt` FROM `accounts` WHERE `chash`=" . sqlesc($chash["chash"]) . " AND `userid`<>" . $row["id"]));
                    $acccnt += $ucntarr["cnt"];
                } 
                // These user are all n00bs, so do not show the pacifier.
				// bullshit throws out errors.. readded schnuller
                $uicons = array("enabled" => $row["enabled"], "warned" => $row["warned"], "donor" => $row["donor"], "added" => $row["added"]);
                echo "<tr><td class=tablea><a href=userdetails.php?id=" . $row["id"] . "><b>" . $row["username"] . "</b></a>&nbsp;" . get_user_icons($uicons) . "</td>\n";
                echo "<td class=tableb style=\"text-align:right;\"><strong>" . $ratio . "</strong></td>\n";
                echo "<td class=tablea>" . $row["ip"] . "</td>\n";
                echo "<td class=tableb>" . $row["added"] . "</td>\n";
                echo "<td class=tablea>" . $row["last_access"] . "</td>\n";
                echo "<td class=tablea style=\"text-align:right;\">" . mksize($row["downloaded"]) . "</td>\n";
                echo "<td class=tableb style=\"text-align:right;\">" . mksize($row["uploaded"]) . "</td>\n";
                if ($acccnt)
                    echo "<td class=tablea style=\"text-align:right;\"><font color=\"red\"><b>" . $acccnt . "</b></font></td>\n";
                else
                    echo "<td class=tablea style=\"text-align:right;\">-</td>\n";
                echo "</tr>\n";
            } while ($row = mysql_fetch_array($result));
        } else {
            print "<tr><td class=tablea>Sorry, no records were found!</td></tr>";
        } 

        end_table();
        echo $pager[1];
        end_frame();
    } 

    ?>


<?php if ($act == "forum") {
        // SHOW FORUMS WITH FORUM MANAGMENT TOOLS
        begin_frame("Forums");

        ?>
<script language="JavaScript">
<!--
function confirm_delete(id)
{
   if(confirm('Are you sure you want to delete this forum?'))
   {
      self.location.href='<?php $PHP_SELF;
        ?>?action=del&id='+id;
   }
}
//-->
</script>
<?php
        echo '<table width="700"  border="0" align="center" cellpadding="2" cellspacing="0">';
        echo "<tr><td class=tablecat align=left>Name</td><td class=tablecat>Topics</td><td class=tablecat>Posts</td><td class=tablecat>Read</td><td class=tablecat>Write</td><td class=tablecat>Create topic</td><td class=tablecat>Modify</td></tr>";
        $result = mysql_query ("SELECT  * FROM forums ORDER BY sort ASC");
        if ($row = mysql_fetch_array($result)) {
            do {
                // if ($row["uploaded"] == "0" || $row["downloaded"] == "0") { $ratio = "inf"; } else {
                // $ratio = $row["uploaded"] / $row["downloaded"];
                // $ratio = number_format($ratio, 2);
                // }
                echo "<tr><td><a href=forums.php?action=viewforum&forumid=" . $row["id"] . "><b>" . $row["name"] . "</b></a><br>" . $row["description"] . "</td>";
                echo "<td>" . $row["topiccount"] . "</td><td>" . $row["postcount"] . "</td><td>minimal " . get_user_class_name($row["minclassread"]) . "</td><td>minimal " . get_user_class_name($row["minclasswrite"]) . "</td><td>minimal " . get_user_class_name($row["minclasscreate"]) . "</td><td align=center nowrap><b><a href=\"" . $PHP_SELF . "?act=editforum&id=" . $row["id"] . "\">EDIT</a>&nbsp;|&nbsp;<a href=\"javascript:confirm_delete('" . $row["id"] . "');\"><font color=red>L&Ouml;SCHEN</font></a></b></td></tr>";
            } while ($row = mysql_fetch_array($result));
        } else {
            print "<tr><td>Sorry, no records were found!</td></tr>";
        } 
        echo "</table>";

        ?>
<br><br>
<form method=post action="<?=$_SERVER['PHP_SELF']?>">
<table width="600"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=tablecat>Make new forum</td>
  </tr>
  <tr>
    <td><b>Forum name</td>
    <td><input name="name" type="text" size="20" maxlength="60"></td>
  </tr>
  <tr>
    <td><b>Forum description  </td>
    <td><input name="desc" type="text" size="30" maxlength="200"></td>
  </tr>
    <tr>
    <td><b>Minimun read permission </td>
    <td>
    <select name=readclass>\n
    <?php
        $maxclass = get_user_class();
        for ($i = 0; $i <= $maxclass; ++$i)
        print("<option value=$i" . ($user["class"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");

        ?>
        </select>
    </td>
  </tr>
  <tr>
    <td><b>Minimun write permission </td>
    <td><select name=writeclass>\n
    <?php
        $maxclass = get_user_class();
        for ($i = 0; $i <= $maxclass; ++$i)
        print("<option value=$i" . ($user["class"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");

        ?>
        </select></td>
  </tr>
  <tr>
    <td><b>Minimun create topic permission </td>
    <td><select name=createclass>\n
    <?php
        $maxclass = get_user_class();
        for ($i = 0; $i <= $maxclass; ++$i)
        print("<option value=$i" . ($user["class"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");

        ?>
        </select></td>
  </tr>
    <tr>
    <td><b>Forum rank </td>
    <td>
    <select name=sort>\n
    <?php
        $res = mysql_query ("SELECT sort FROM forums");
        $nr = mysql_num_rows($res);
        $maxclass = $nr + 1;
        for ($i = 0; $i <= $maxclass; ++$i)
        print("<option value=$i>$i \n");

        ?>
        </select>


    </td>
  </tr>

  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="addforum"><input type="submit" name="Submit" value="Make forum"></td>
  </tr>
</table>

<?php
        end_frame();
    } 
    ?>

<?php if ($act == "editforum") {
        // EDIT PAGE FOR THE FORUMS
        $id = $_GET["id"];
        begin_frame("Edit Forum");
        $result = mysql_query ("SELECT * FROM forums where id = '$id'");
        if ($row = mysql_fetch_array($result)) {
            do {

                ?>

<form method=post action="<?=$_SERVER['PHP_SELF']?>
                ?>">
<table width="600"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=tablecat>edit forum: <?=$row["name"];
                ?></td>
  </tr>
  <tr>
    <td><b>Forum name</td>
    <td><input name="name" type="text" size="20" maxlength="60" value="<?=$row["name"];
                ?>"></td>
  </tr>
  <tr>
    <td><b>Forum description  </td>
    <td><input name="desc" type="text" size="30" maxlength="200" value="<?=$row["description"];
                ?>"></td>
  </tr>
    <tr>
    <td><b>Minimun read permission </td>
    <td>
    <select name=readclass>\n
    <?php
                $maxclass = get_user_class();
                for ($i = 0; $i <= $maxclass; ++$i)
                print("<option value=$i" . ($row["minclassread"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");

                ?>
        </select>
    </td>
  </tr>
  <tr>
    <td><b>Minimun write permission </td>
    <td><select name=writeclass>\n
    <?php
                $maxclass = get_user_class();
                for ($i = 0; $i <= $maxclass; ++$i)
                print("<option value=$i" . ($row["minclasswrite"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");

                ?>
        </select></td>
  </tr>
  <tr>
    <td><b>Minimun create topic permission </td>
    <td><select name=createclass>\n
    <?php
                $maxclass = get_user_class();
                for ($i = 0; $i <= $maxclass; ++$i)
                print("<option value=$i" . ($row["minclasscreate"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");

                ?>
        </select></td>
  </tr>
    <tr>
    <td><b>Forum rank </td>
    <td>
    <select name=sort>\n
    <?php
                $res = mysql_query ("SELECT sort FROM forums");
                $nr = mysql_num_rows($res);
                $maxclass = $nr + 1;
                for ($i = 0; $i <= $maxclass; ++$i)
                print("<option value=$i" . ($row["sort"] == $i ? " selected" : "") . ">$i \n");

                ?>
        </select>


    </td>
  </tr>

  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="editforum"><input type="hidden" name="id" value="<?=$id;
                ?>"><input type="submit" name="Submit" value="Edit forum"></td>
  </tr>
</table>

<?php
            } while ($row = mysql_fetch_array($result));
        } else {
            print "Sorry, no records were found!";
        } 
        end_frame();
    } 
    ?>


<?php if ($act == "ban") {
        // BAN IP PAGE WITH OVERVIEW FROM BANNED IP'S
        begin_frame("IP bannen");

        ?>

<form method=post action="<?=$_SERVER['PHP_SELF']?>">
<table width="450"  border="0" cellspacing="1" cellpadding="3" align="center" class="tableinborder">
<tr align="center">
    <td colspan="2" class=tablecat>IP bannen</td>
  </tr>
  <tr>
    <td class=tableb><b>IP (Bereich)</td>
    <td class=tablea><input name="first" type="text" size="15" maxlength="16"> bis <input name="last" type="text" size="15" maxlength="16"></td>
  </tr>
  <tr>
    <td class=tableb><b>Kommentar  </td>
    <td class=tablea><input name="comment" type="text" size="30" maxlength="200"></td>
  </tr>

  <tr align="center">
    <td class=tablea colspan="2" style="text-align:center"><input type="hidden" name="action" value="ban"><input type="submit" name="Submit" value="IP bannen"></td>
  </tr>
</table>
</form>
<br><br>
<script language="JavaScript">
<!--
function confirm_delete(id)
{
   if(confirm('Sicher, dass Du diese IP (bzw. diesen Bereich) ENTBANNEN willst?'))
   {
      self.location.href='<?php $PHP_SELF;
        ?>?action=ipdel&id='+id;
   }
}

function selectall()
{
    var myForm = document.getElementById('delform');
    
    for (I=0; I<=myForm.elements.length; I++) {
        eval("myForm.elements['delid" + I + "']").checked = true;
    }    
}

//-->
</script>
<form id="delform" action="staff.php" method="get">
<input type="hidden" name="action" value="ipdel">
<?php
        begin_table();
        echo "<tr><td class=tablecat align=left>IP (Bereich)</td><td class=tablecat>Hinzugefügt</td><td class=tablecat>Von</td><td class=tablecat>Kommentar</td><td class=tablecat>Ändern</td></tr>";

        $result = mysql_query ("SELECT  * FROM bans ORDER BY added DESC");
        if ($row = mysql_fetch_array($result)) {
            $I = 0;
            do {
                $res = mysql_query ("SELECT username FROM users where id = '" . $row["addedby"] . "' limit 1");
                $rw = mysql_fetch_array($res);

                if ($row["last"] == $row["first"]) {
                    $range = long2ip($row["first"]);
                } else {
                    $range = long2ip($row["first"]) . " - " . long2ip($row["last"]);
                } 
                echo "<tr><td class=tablea><b>" . $range . "</b></td>\n";
                echo "<td class=tableb style=\"text-align:center\">" . $row["added"] . "</td>\n";
                echo "<td class=tablea><a href=\"userdetails.php?id=" . $row["addedby"] . "\"><b>" . $rw["username"] . "</a></td>\n";
                echo "<td class=tableb>" . $row["comment"] . "</td>\n";
                echo "<td class=tablea nowrap=nowrap><input type=\"checkbox\" id=\"delid" . $I . "\" name=\"id[]\" value=\"" . $row["id"] . "\">&nbsp;<a href=\"javascript:confirm_delete('" . $row["id"] . "');\"><b><font color=red>L&Ouml;SCHEN</font></a></b></td></tr>";
                $I++;
            } while ($row = mysql_fetch_array($result));
            echo "<tr><td colspan=5 class=tablea style=\"text-align:center\"><input type=\"button\" onclick=\"selectall();\" value=\"Alle markieren\">&nbsp;<input type=\"submit\" value=\"Markierte Bans löschen\"></td></tr>\n";
        } else {
            print "<tr><td colspan=5 class=tablea align=center><b>Es wurden keine Einträge gefunden!</td></tr>";
        } 
        end_table();

        ?>
</form>
<?php
    } 
} 

stdfoot();
?>