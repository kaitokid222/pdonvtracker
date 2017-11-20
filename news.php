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

if (get_user_class() < UC_ADMINISTRATOR)
    stderr("Error", "Permission denied.");
?>
<script type="text/javascript" src="<?=$BASEURL?>/js/nicEdit.js"></script>
<script type="text/javascript">
	bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
</script>
<?php

if(isset($_GET["action"]))
	$action = $_GET["action"];
else
	$action = "";
// Delete News Item    //////////////////////////////////////////////////////
if ($action == 'delete') {
    $newsid = $_GET["newsid"];
    if (!is_valid_id($newsid))
        stderr("Error", "Ungültige News-ID - Code 1.");

    $returnto = $_GET["returnto"];

    $sure = $_GET["sure"];
    if (!$sure)
        stderr("News-Eintrag löschen", "Willst Du wirklich einen News-Eintrag löschen? Klicke\n" . "<a href=\"news.php?action=delete&newsid=$newsid&returnto=$returnto&sure=1\">hier</a>, wenn Du Dir sicher bist.");

    mysql_query("DELETE FROM news WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);

    if ($returnto != "")
        header("Location: $returnto");
    else
        $warning = "Der News-Eintrag wurde erfolgreich gelöscht.";
} 
// Add News Item    /////////////////////////////////////////////////////////
if ($action == 'add') {
    $title = $_POST["title"];
    if (!$title)
        stderr("Fehler", "Der Titel darf nicht leer sein!");

    $body = $_POST["body"];
    if (!$body)
        stderr("Fehler", "Der Beitrag darf nicht leer sein!");

    $added = $_POST["added"];
    if (!$added)
        $added = sqlesc(get_date_time());

    mysql_query("INSERT INTO news (userid, added, title, body) VALUES (" . $CURUSER['id'] . ", $added, " . sqlesc(stripslashes($title)) . ", " . sqlesc(stripslashes($body)) . ")") or sqlerr(__FILE__, __LINE__);
    if (mysql_affected_rows() == 1)
        $warning = "News-Beitrag erfolgreich hinzugefügt.";
    else
        stderr("Fehler", "Gerade ist irgendetwas merkwürdiges passiert.");
} 
// Edit News Item    ////////////////////////////////////////////////////////
if ($action == 'edit') {
    $newsid = $_GET["newsid"];

    if (!is_valid_id($newsid))
        stderr("Fehler", "Ungültige News-ID - Code 2.");

    $res = mysql_query("SELECT * FROM news WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) != 1)
        stderr("Fehler", "Kein News-Eintrag mit der ID $newsid vorhanden.");

    $arr = mysql_fetch_array($res);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST["title"];
        if (!$title)
            stderr("Fehler", "Der Titel darf nicht leer sein!");
    
        $body = $_POST['body'];
        if ($body == "")
            stderr("Fehler", "Der Beitrag darf nicht leer sein!");

        $title = sqlesc(stripslashes($title));
        $body = sqlesc(stripslashes($body));

        $editedat = sqlesc(get_date_time());

        mysql_query("UPDATE news SET title=$title,body=$body WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);

        $returnto = $_POST['returnto'];

        if ($returnto != "")
            header("Location: $returnto");
        else
            $warning = "Der News-Beitrag wurde erfolgreich geändert.";
    } else {
        $returnto = $_GET['returnto'];
        stdhead();
		begin_frame("News-Beitrag bearbeiten", false, "600px;");
		if (isset($warning))
			echo "<p><font size=-3>(" . $warning . ")</font></p>";
    
		echo "<form method='post' action='news.php?action=edit&newsid=" . $newsid . "'><input type='hidden' name='returnto' value='" . $returnto . "'>
			<table class='tableinborder' width='100%' border='0' cellspacing='1' cellpadding='4'>
			<tr><td class='tableb'>Titel:</td><td class='tablea'><input type='text' name='title' size='80' maxlength='255' value='" . (stripslashes($arr["title"])) . "'></td></tr>
			<tr><td class='tableb'>Text:</td><td class='tablea'><textarea id='newseditor' name='body' cols='80' rows='10' style='width:600px;height:400px;'>" . (stripslashes($arr["body"])) . "</textarea><br>(<b>HTML</b> ist erlaubt)</td></tr>
			<tr><td class='tableb' colspan='2'><div align=center><input type='submit' value='Okay' class='btn'></div></td></tr>
			</table></form>";
		end_frame();
		stdfoot();
        die;
    } 
} 
// Other Actions and followup    ////////////////////////////////////////////
stdhead("Site news");
begin_frame("<img src=\"".$GLOBALS["PIC_BASE_URL"]."news_add.png\" width=\"22\" height=\"22\" alt=\"News hinzufügen\" title=\"News hinzufügen\" style=\"vertical-align: middle;border:none\"> Neuen News-Beitrag schreiben", false, "600px;");
if (isset($warning))
	echo "<p><font size=-3>(" . $warning . ")</font></p>";
print("<form method=\"post\" action=\"news.php?action=add\">\n");
print("<table class='tableinborder' width='100%' border='0' cellspacing='1' cellpadding='4'>");
print("<tr><td class=\"tableb\">Titel:</td><td class=\"tablea\"><input type=\"text\" name=\"title\"  size=\"80\" maxlength=\"255\"></td></tr>\n");
print("<tr><td class=\"tableb\">Text:</td><td class=\"tablea\"><textarea id=\"newseditor\" name=\"body\" cols=\"80\" rows=\"10\" style=\"width:600px;height:400px;\"></textarea><br>(<b>HTML</b> ist erlaubt)</td></tr>\n");
print("<tr><td class=\"tableb\" colspan=\"2\"><div align=center><input type=submit value='Okay' class=btn></div></td></tr>\n");
print("</table></form>\n");
end_frame();

$res = mysql_query("SELECT * FROM news ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) > 0) {
    begin_frame("News bearbeiten", false, "650px;");
    while ($arr = mysql_fetch_array($res)) {
        $newsid = $arr["id"];
        $title = htmlspecialchars($arr["title"]);
        $body = $arr["body"];
        $userid = $arr["userid"];
        $added = $arr["added"] . " (vor " . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . ")";
        $res2 = mysql_query("SELECT username, donor FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
        $arr2 = mysql_fetch_array($res2);

        $postername = $arr2["username"];

        if ($postername == "")
            $by = "unknown[$userid]";
        else
            $by = "<a href=userdetails.php?id=$userid><b>$postername</b></a>" .
            ($arr2["donor"] == "yes" ? "<img src=\"".$GLOBALS["PIC_BASE_URL"]."donor.png\" alt='Donor'>" : "");

        begin_table(TRUE);
        print("<tr><td class=tablecat>");
        print("<b>$title</b><br>");
        print("$added&nbsp;---&nbsp;by&nbsp$by");
        print(" - [<a href=?action=edit&newsid=$newsid><b>Edit</b></a>]");
        print(" - [<a href=?action=delete&newsid=$newsid><b>Delete</b></a>]");
        print("</td></tr>\n");
        print("<tr valign=top><td class=tablea>$body</td></tr>\n");
        end_table();
    } 
    end_frame();
} else
    stdmsg("Sorry", "No news available!");
stdfoot();
die;

?>