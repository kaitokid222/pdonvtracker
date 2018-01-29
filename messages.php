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
dbconn(false);
loggedinorreturn();

initFolder();
if(isset($_REQUEST["folder"]))
	$GLOBALS["FOLDER"] = intval($_REQUEST["folder"]);
else
	$GLOBALS["FOLDER"] = 0;
if ($GLOBALS["FOLDER"] == 0) $GLOBALS["FOLDER"] = PM_FOLDERID_INBOX;

if ($CURUSER["class"] < UC_MODERATOR && $GLOBALS["FOLDER"] == PM_FOLDERID_MOD)
    stderr("Fehler", "Du hast keinen Zugriff auf diesen Ordner");

if ($GLOBALS["FOLDER"] > 0)
    $res = mysql_query("SELECT * FROM `pmfolders` WHERE id=".$GLOBALS["FOLDER"]." AND `owner`=".$CURUSER["id"]);
else {
    switch ($GLOBALS["FOLDER"]) {
    case PM_FOLDERID_INBOX:
            $foldername = "__inbox";
            break;
    case PM_FOLDERID_OUTBOX:
            $foldername = "__outbox";
            break;
    case PM_FOLDERID_SYSTEM:
            $foldername = "__system";
            break;
    case PM_FOLDERID_MOD:
            $foldername = "__mod";
            break;
    default:
            $foldername = "__invalid";
            break;
    }
    $res = mysql_query("SELECT * FROM `pmfolders` WHERE name='".$foldername."' AND `owner`=".$CURUSER["id"]);
}

if (mysql_num_rows($res) == 0)
    stderr("Fehler", "Der angegebene Ordner ist ungültig!");

$finfo = mysql_fetch_assoc($res);

if (isset($_GET["sortfield"]) && in_array($_GET["sortfield"], array('added','subject','sendername','receivername'))) {
    if (isset($_GET["sortorder"]) && in_array($_GET["sortorder"], array("ASC", "DESC"))) {
        $finfo["sortfield"] = $_GET["sortfield"];
        $finfo["sortorder"] = $_GET["sortorder"];
        
        mysql_query("UPDATE `pmfolders` SET `sortfield`=".sqlesc($_GET["sortfield"]).",`sortorder`=".sqlesc($_GET["sortorder"])." WHERE `id`=".$finfo["id"]);
    }
}

if ($GLOBALS["PM_PRUNE_DAYS"] > 0 && ($finfo["prunedays"] == 0 || $finfo["prunedays"] > $GLOBALS["PM_PRUNE_DAYS"]))
    $finfo["prunedays"] = $GLOBALS["PM_PRUNE_DAYS"];

// Action-Mapping
if (isset($_REQUEST["reply"]))      $_REQUEST["action"] = "reply";
if (isset($_REQUEST["delete"]))     $_REQUEST["action"] = "delete";
if (isset($_REQUEST["move"]))       $_REQUEST["action"] = "move";
if (isset($_REQUEST["markread"]))   $_REQUEST["action"] = "markread";
if (isset($_REQUEST["markunread"])) $_REQUEST["action"] = "markunread";
if (isset($_REQUEST["markclosed"])) $_REQUEST["action"] = "markclosed";
if (isset($_REQUEST["markopen"]))   $_REQUEST["action"] = "markopen";

if (isset($_REQUEST["action"])) {
    // Aktionen ohne Nachrichten
    switch ($_REQUEST["action"]) {
        case "createfolder":
            createFolderDialog();
            die();
        case "deletefolder":
            deleteFolderDialog();
            die();
        case "config":
            folderConfigDialog();
            die();
        case "send":
            sendMessageDialog();
            die();
    }

    if ((!isset($_REQUEST["id"]) || intval($_REQUEST["id"]) == 0) && !is_array($_REQUEST["selids"]))
        stderr("Fehler", "Keine Nachricht für diese Aktion ausgewählt!");
    
    // selids numerisch machen!
	if(isset($_REQUEST["selids"])){
		if (is_array($_REQUEST["selids"])) {
			for ($I=0; $I<count($_REQUEST["selids"]); $I++) {
				$_REQUEST["selids"][$I] = intval($_REQUEST["selids"][$I]);
			}
		}
    }
    checkMessageOwner();
    
    if (isset($_REQUEST["id"])) {
        $selids = intval($_REQUEST["id"]);
    } elseif (is_array($_REQUEST["selids"])) {
        $selids = implode(",", $_REQUEST["selids"]);
    }

    switch ($_REQUEST["action"]) {
        case "markopen":
            mysql_query("UPDATE `messages` SET `mod_flag`='open' WHERE `id` IN ($selids)");
            break;
        case "markclosed":
            mysql_query("UPDATE `messages` SET `mod_flag`='closed' WHERE `id` IN ($selids)");
            break;
        case "markread":
            mysql_query("UPDATE `messages` SET `unread`='' WHERE `id` IN ($selids)");
            break;
        case "markunread":
            mysql_query("UPDATE `messages` SET `unread`='yes' WHERE `id` IN ($selids)");
            break;
        case "reply":
            if ((!isset($_REQUEST["id"]) || intval($_REQUEST["id"]) == 0))
                stderr("Fehler", "Die Nachrichten-ID kann nur über den Parameter 'id' übergeben werden - was probierst Du da?!?");
            sendMessageDialog(intval($_REQUEST["id"]));
            die();
        case "read":
            displayMessage(intval($_REQUEST["id"]));
            die();
        case "delete":
            deletePersonalMessages($selids);
            break;
        case "move":
            if ($GLOBALS["FOLDER"] == PM_FOLDERID_SYSTEM || $GLOBALS["FOLDER"] == PM_FOLDERID_MOD)
                stderr("Fehler", "Aus diesem Ordner können keine Nachrichten verschoben werden!");
            
            $target_folder = intval($_REQUEST["to_folder"]);
            if ($target_folder == 0) {
                selectTargetFolderDialog($selids);
                die();
            }
            
            if ($target_folder == PM_FOLDERID_SYSTEM || $target_folder == PM_FOLDERID_MOD)
                stderr("Fehler", "In diesen Ordner können keine Nachrichten verschoben werden!");
                
            if ($target_folder != PM_FOLDERID_INBOX && $target_folder != PM_FOLDERID_OUTBOX)
                checkFolderOwner($target_folder);
                
            mysql_query("UPDATE `messages` SET `folder_in`=".intval($_REQUEST["to_folder"])." WHERE `id` IN ($selids) AND `folder_in`=".$GLOBALS["FOLDER"]." AND `receiver`=".$CURUSER["id"]);
            mysql_query("UPDATE `messages` SET `folder_out`=".intval($_REQUEST["to_folder"])." WHERE `id` IN ($selids) AND `folder_out`=".$GLOBALS["FOLDER"]." AND `sender`=".$CURUSER["id"]);
            break;
    }

}

switch ($GLOBALS["FOLDER"]) {
    case PM_FOLDERID_INBOX:
        $finfo["name"] = "Posteingang";
        break;
    case PM_FOLDERID_OUTBOX:
        $finfo["name"] = "Postausgang";
        break;
    case PM_FOLDERID_SYSTEM:
        $finfo["name"] = "Systemnachrichten";
        break;
    case PM_FOLDERID_MOD:
        $finfo["name"] = "Mod-Benachrichtigungen";
        break;
}

stdhead("Persönliche Nachrichten");
?>
<script type="text/javascript">

function selectAll()
{
    var I=1;
    var check = document.forms['msgform'].elements['selall'].checked;

    while (eval("document.forms['msgform'].elements['chkbox" + I + "']") != 'undefined') {
        eval("document.forms['msgform'].elements['chkbox" + I + "']").checked = check;
        I++;
    }
}

</script>

<form id="msgform" method="post" action="messages.php">
<input type="hidden" name="folder" value="<?=$GLOBALS["FOLDER"]?>">
<table cellspacing="10" cellpadding="0" border="0">
  <colgroup>
    <col width="100%">
    <col width="200">
  </colgroup>
  <tr>
    <td valign="top">
<?php
begin_frame('<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/mail_generic22.png" width="22" height="22" alt="" style="vertical-align: middle;"> Nachrichten - '.htmlspecialchars($finfo["name"]));
begin_table(TRUE);
?>
<colgroup>
  <col width="16">
  <col width="55%">
  <col width="15%">
  <col width="15%">
  <col width="15%">
  <col width="48">
</colgroup>
<tr>
  <th class="tablecat"><input onclick="selectAll();" type="checkbox" id="selall" name="selall" value="1"></th>
  <th class="tablecat" nowrap="nowrap"><?php if ($finfo["sortfield"] == "subject") echo ($finfo["sortorder"]=="ASC"?'<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/up.png" style="vertical-align:middle">&nbsp;':'<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/down.png" style="vertical-align:middle">&nbsp;'); ?><a href="messages.php?folder=<?=$GLOBALS["FOLDER"]?>&amp;sortfield=subject&amp;sortorder=<?php if ($finfo["sortfield"] == "subject") echo ($finfo["sortorder"]=="ASC"?"DESC":"ASC"); else echo $finfo["sortorder"]; ?>">Betreff</a></th>
  <th class="tablecat" nowrap="nowrap"><?php if ($finfo["sortfield"] == "sendername") echo ($finfo["sortorder"]=="ASC"?'<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/up.png" style="vertical-align:middle">&nbsp;':'<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/down.png" style="vertical-align:middle">&nbsp;'); ?><a href="messages.php?folder=<?=$GLOBALS["FOLDER"]?>&amp;sortfield=sendername&amp;sortorder=<?php if ($finfo["sortfield"] == "sendername") echo ($finfo["sortorder"]=="ASC"?"DESC":"ASC"); else echo $finfo["sortorder"]; ?>">Absender</a></th>
  <th class="tablecat" nowrap="nowrap"><?php if ($finfo["sortfield"] == "receivername") echo ($finfo["sortorder"]=="ASC"?'<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/up.png" style="vertical-align:middle">&nbsp;':'<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/down.png" style="vertical-align:middle">&nbsp;'); ?><a href="messages.php?folder=<?=$GLOBALS["FOLDER"]?>&amp;sortfield=receivername&amp;sortorder=<?php if ($finfo["sortfield"] == "receivername") echo ($finfo["sortorder"]=="ASC"?"DESC":"ASC"); else echo $finfo["sortorder"]; ?>">Empfänger</a></th>
  <th class="tablecat" nowrap="nowrap"><?php if ($finfo["sortfield"] == "added") echo ($finfo["sortorder"]=="ASC"?'<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/up.png" style="vertical-align:middle">&nbsp;':'<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/down.png" style="vertical-align:middle">&nbsp;'); ?><a href="messages.php?folder=<?=$GLOBALS["FOLDER"]?>&amp;sortfield=added&amp;sortorder=<?php if ($finfo["sortfield"] == "added") echo ($finfo["sortorder"]=="ASC"?"DESC":"ASC"); else echo $finfo["sortorder"]; ?>">Datum</a></th>
  <th class="tablecat">&nbsp;</th>
</tr>
<?php

if ($GLOBALS["FOLDER"] == PM_FOLDERID_MOD) {
    // Nachrichten älter als 7 Tage löschen
    mysql_query("DELETE FROM `messages` WHERE `folder_in`=".PM_FOLDERID_MOD." AND `sender`=0 AND `receiver`=0 AND UNIX_TIMESTAMP(`added`)<".(time()-7*86400));
    if ($_REQUEST["closed"]==1)
        $msgres = mysql_query("SELECT `messages`.`id`,`messages`.`folder_in`,`messages`.`folder_out`,`messages`.`mod_flag`,'0' AS `sender`,'0' AS`receiver`,`messages`.`unread`,`messages`.`subject`,`messages`.`added`,'System' AS `sendername`,'Tracker-Team' AS `receivername`  FROM `messages` WHERE `folder_in`=".PM_FOLDERID_MOD." AND `receiver`=0 AND `mod_flag`='closed' ORDER BY ".$finfo["sortfield"]." ".$finfo["sortorder"]);
    else
        $msgres = mysql_query("SELECT `messages`.`id`,`messages`.`folder_in`,`messages`.`folder_out`,`messages`.`mod_flag`,'0' AS `sender`,'0' AS`receiver`,`messages`.`unread`,`messages`.`subject`,`messages`.`added`,'System' AS `sendername`,'Tracker-Team' AS `receivername`  FROM `messages` WHERE `folder_in`=".PM_FOLDERID_MOD." AND `receiver`=0 AND `mod_flag`='open' ORDER BY ".$finfo["sortfield"]." ".$finfo["sortorder"]);        
} else {
    // Nachrichten löschen, falls Pruning gewünscht
    if ($finfo["prunedays"] > 0) {
        $prunetime = time()-$finfo["prunedays"]*86400;
        mysql_query("DELETE FROM `messages` WHERE `folder_out`=0 AND `folder_in`=".$GLOBALS["FOLDER"]." AND `receiver`=".$CURUSER["id"]." AND UNIX_TIMESTAMP(`added`)<".$prunetime);
        mysql_query("DELETE FROM `messages` WHERE `folder_in`=0 AND `folder_out`=".$GLOBALS["FOLDER"]." AND `sender`=".$CURUSER["id"]." AND UNIX_TIMESTAMP(`added`)<".$prunetime);
        mysql_query("UPDATE `messages` SET `folder_in`=0 WHERE `folder_in`=".$GLOBALS["FOLDER"]." AND `receiver`=".$CURUSER["id"]." AND UNIX_TIMESTAMP(`added`)<".$prunetime);
        mysql_query("UPDATE `messages` SET `folder_out`=0 WHERE `folder_out`=".$GLOBALS["FOLDER"]." AND `sender`=".$CURUSER["id"]." AND UNIX_TIMESTAMP(`added`)<".$prunetime);
    }
    $msgres = mysql_query("SELECT `messages`.`id`,`messages`.`folder_in`,`messages`.`folder_out`,`messages`.`sender`,`messages`.`receiver`,`messages`.`unread`,`messages`.`subject`,`messages`.`added`,`sender`.`username` AS `sendername`,`receiver`.`username` AS `receivername`  FROM `messages` LEFT JOIN `users` AS `sender` ON `sender`.`id`=`messages`.`sender` LEFT JOIN `users` AS `receiver` ON `receiver`.`id`=`messages`.`receiver` WHERE (`folder_in`=".$GLOBALS["FOLDER"]." AND `receiver`=".$CURUSER["id"].") OR (`folder_out`=".$GLOBALS["FOLDER"]." AND `sender`=".$CURUSER["id"].") ORDER BY ".$finfo["sortfield"]." ".$finfo["sortorder"]);
}

if (mysql_num_rows($msgres) == 0) {
    echo '<tr><td class="tablea" colspan="6">Dieser Ordner enthält keine Nachrichten.</td></tr>'."\n";
} else {
    $msgnr = 1;
    while ($msg = mysql_fetch_assoc($msgres)) {
        messageLine($msg, $msgnr);
        $msgnr++;
    }

?>
<tr>
  <td class="tablea" colspan="6">
    <table cellspacing="2" cellpadding="2" border="0">
      <tr>
        <td>Ausgewählte Nachrichten:</td>
<?php if ($GLOBALS["FOLDER"] != PM_FOLDERID_MOD) { ?>
        <td>
          <input type="submit" name="delete" value="Löschen">
          <input type="submit" name="markread" value="Als gelesen markieren">
          <input type="submit" name="markunread" value="Als ungelesen markieren"></td>
      </tr>
<?php if ($GLOBALS["FOLDER"] != PM_FOLDERID_SYSTEM) { ?>
      <tr>
        <td>...verschieben nach:</td>
        <td>
          <select name="to_folder" size="1">
            <option>** Bitte Ordner auswählen **</option>
            <option value="<?=PM_FOLDERID_INBOX?>">Posteingang</option>
            <option value="<?=PM_FOLDERID_OUTBOX?>">Postausgang</option>
<?php
        getFolders(0, 0, TRUE);
?>
          </select>
          <input type="submit" name="move" value="Verschieben">
        </td>
<?php
        }
    } else {
?>
        <td><input type="submit" name="markclosed" value="Als erledigt markieren"></td>
        <td><input type="submit" name="markopen" value="Als ausstehend markieren"></td>
        <?php
    }
?>
        </tr>
    </table>
  </td>
</tr>
<?php
}

end_table();
end_frame();
?>
    </td>
    <td valign="top">
<?php
begin_frame('<img src="'.$GLOBALS["PIC_BASE_URL"].'pm/folder_mail22.png" width="22" height="22" alt="" style="vertical-align: middle;"> Ordner', FALSE, "200px;");
begin_table(TRUE);
// Hauptordner
folderLine(PM_FOLDERID_INBOX, "Posteingang", "folder_mail.png");
folderLine(PM_FOLDERID_OUTBOX, "Postausgang", "folder_sent_mail.png");
folderLine(PM_FOLDERID_SYSTEM, "Systemnachrichten", "system.png");
    
if ($CURUSER["class"] >= UC_MODERATOR) {
    folderLine(PM_FOLDERID_MOD, "Mod-Benachrichtigungen", "folder_red.png");
    folderLine(PM_FOLDERID_MOD, "Erledigt", "ok.png", 1);
}

getFolders();
end_table();
?>
        <img src="<?=$GLOBALS["PIC_BASE_URL"]?>pm/folder_new.png" alt="Neuer Ordner" title="Neuer Ordner" style="vertical-align:middle">&nbsp;<a href="messages.php?folder=<?=$GLOBALS["FOLDER"]?>&amp;action=createfolder">Ordner erstellen</a><br>
        <img src="<?=$GLOBALS["PIC_BASE_URL"]?>pm/configure.png" alt="Konfigurieren" title="Konfigurieren" style="vertical-align:middle">&nbsp;<a href="messages.php?folder=<?=$GLOBALS["FOLDER"]?>&amp;action=config">Ordner konfigurieren</a><br>
        <?php if ($GLOBALS["FOLDER"] > 0) { ?>
        <img src="<?=$GLOBALS["PIC_BASE_URL"]?>pm/editdelete.png" alt="Löschen" title="Löschen" style="vertical-align:middle">&nbsp;<a href="messages.php?folder=<?=$GLOBALS["FOLDER"]?>&amp;action=deletefolder">Ordner löschen</a><br>
        <?php } ?>
        <br>
        <?php
        end_frame();
?>
    </td>
  </tr>
</table>
</form>
<?php

stdfoot();

?>