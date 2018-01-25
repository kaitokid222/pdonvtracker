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

require_once "include/bittorrent.php";

userlogin();
loggedinorreturn();

if (get_user_class() < UC_ADMINISTRATOR)
    stderr("Error", "Permission denied.");
?>
<script type="text/javascript" src="js/nicEdit.js"></script>
<script type="text/javascript">
		bkLib.onDomLoaded(function() {
			nicEditors.allTextAreas()
		});
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

    if (isset($_GET["returnto"])){
		$returnto_str = "&returnto=" . str_replace("/", "", $_GET["returnto"]);
		$returnto = $_GET["returnto"];
	}else{
		$returnto_str = "";
		$returnto = "";
	}

    if (!isset($_GET["sure"]))
        stderr("News-Eintrag löschen", "Willst Du wirklich einen News-Eintrag löschen? Klicke\n" . "<a href=\"news.php?action=delete&newsid=" . $newsid . $returnto_str . "&sure=1\">hier</a>, wenn Du Dir sicher bist.");

	$qry = $GLOBALS['DB']->prepare('DELETE FROM news WHERE id= :id');
	$qry->bindParam(':id', $newsid, PDO::PARAM_INT);
	$qry->execute();

    if ($returnto != "")
        header("Location: " . $returnto);
    else
        $warning = "Der News-Eintrag wurde erfolgreich gelöscht.";
} 
// Add News Item    /////////////////////////////////////////////////////////
if ($action == 'add') {
    if (!isset($_POST["title"]) || $_POST["title"] == "")
        stderr("Fehler", "Der Titel darf nicht leer sein!");
	else
		$title = stripslashes($_POST["title"]);

    if (!isset($_POST["body"]) || $_POST["body"] == "")
        stderr("Fehler", "Der Beitrag darf nicht leer sein!");
	else
		$body = stripslashes($_POST["body"]);

	if(!isset($_POST["added"]) || $_POST["added"] == "")
		$added = get_date_time();
	else
		$added = $_POST["added"];

	$qry = $GLOBALS['DB']->prepare('INSERT INTO news (userid, added, title, body) VALUES (:uid, :added, :title, :body)');
	$qry->bindParam(':uid', $CURUSER["id"], PDO::PARAM_INT);
	$qry->bindParam(':added', $added, PDO::PARAM_STR);
	$qry->bindParam(':title', $title, PDO::PARAM_STR);
	$qry->bindParam(':body', $body, PDO::PARAM_STR);
	$qry->execute();
	if($qry->rowCount())
		$warning = "News-Beitrag erfolgreich hinzugefügt.";
    else
        stderr("Fehler", "Gerade ist irgendetwas merkwürdiges passiert.");
} 
// Edit News Item    ////////////////////////////////////////////////////////
if ($action == 'edit') {
    $newsid = $_GET["newsid"];

    if (!is_valid_id($newsid))
        stderr("Fehler", "Ungültige News-ID - Code 2.");

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (!isset($_POST["title"]) || $_POST["title"] == "")
			stderr("Fehler", "Der Titel darf nicht leer sein!");
		else
			$title = stripslashes($_POST["title"]);

		if (!isset($_POST["body"]) || $_POST["body"] == "")
			stderr("Fehler", "Der Beitrag darf nicht leer sein!");
		else
			$body = stripslashes($_POST["body"]);

		$qry = $GLOBALS['DB']->prepare('UPDATE news SET title= :title,body= :body WHERE id= :id');
		$qry->bindParam(':title', $title, PDO::PARAM_STR);
		$qry->bindParam(':body', $body, PDO::PARAM_STR);
		$qry->bindParam(':id', $newsid, PDO::PARAM_INT);
		$qry->execute();

		if (isset($_GET["returnto"]))
			$returnto = $_GET["returnto"];
		else
			$returnto = "";

        if ($returnto != "")
            header("Location: " . $returnto);
        else
            $warning = "Der News-Beitrag wurde erfolgreich geändert.";
    } else {
		if (isset($_GET["returnto"]))
			$returnto = $_GET["returnto"];
		else
			$returnto = "";

		$qry = $GLOBALS['DB']->prepare('SELECT title, body FROM news WHERE id= :id');
		$qry->bindParam(':id', $newsid, PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount())
			$arr = $qry->fetchAll()[0];
		else
			stderr("Fehler", "Kein News-Eintrag mit der ID " . $newsid . " vorhanden.");
			
	    stdhead();
		begin_frame("News-Beitrag bearbeiten", false, "600px;");
			
		if (isset($warning))
			echo "<p><font size=-3>(" . $warning . ")</font></p>";
    
		echo "<form method='post' action='news.php?action=edit&newsid=" . $newsid . "'><input type='hidden' name='returnto' value='" . $returnto . "'>
			<table class='tableinborder' width='100%' border='0' cellspacing='1' cellpadding='4'>
			<tr><td class='tableb'>Titel:</td><td class='tablea'><input type='text' name='title' size='80' maxlength='255' value='" . stripslashes($arr["title"]) . "'></td></tr>
			<tr><td class='tableb'>Text:</td><td class='tablea'><textarea id='newseditor' name='body' cols='80' rows='10' style='width:600px;height:400px;'>" . stripslashes($arr["body"]) . "</textarea><br>(<b>HTML</b> ist erlaubt)</td></tr>
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

$qry = $GLOBALS['DB']->prepare('SELECT news.*, users.username as postername FROM news LEFT JOIN users ON news.userid = users.id ORDER BY added DESC');
$qry->execute();
if($qry->rowCount()){
	$arr = $qry->fetchAll();
    begin_frame("News bearbeiten", false, "650px;");
    foreach($arr as $news){
        $added = $news["added"] . " (vor " . (get_elapsed_time(sql_timestamp_to_unix_timestamp($news["added"]))) . ")";

        if (!isset($news["postername"]) || $news["postername"] == "")
            $by = "unknown[" . $news["userid"] . "]";
        else
            $by = "<a href=userdetails.php?id=" . $news["userid"] . "><b>" . $news["postername"] . "</b></a>";

        begin_table(TRUE);
        print("<tr><td class=tablecat>");
        print("<b>" . htmlspecialchars($news["title"]) . "</b><br>");
        print($added . "&nbsp;---&nbsp;by&nbsp" . $by);
        print(" - [<a href=?action=edit&newsid=" . $news["id"] . "><b>Edit</b></a>]");
        print(" - [<a href=?action=delete&newsid=" . $news["id"] . "><b>Delete</b></a>]");
        print("</td></tr>\n");
        print("<tr valign=top><td class=tablea>" . $news["body"] . "</td></tr>\n");
        end_table();
    } 
    end_frame();
}else
	stdmsg("Fehler", "Kein News-Eintrag vorhanden.");

stdfoot();
die;
?>