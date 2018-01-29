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

require "include/bittorrent.php";
userlogin();
loggedinorreturn();

if(isset($_GET['action']))
	$action = $_GET['action'];
else
	$action = "view";

switch($action){
	case "view":
		break;
	case "use":
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			if(isset($_POST["code"]) && strlen($_POST["code"]) == 20){
				$v = new voucher($_POST["code"]);
				$v->useVoucher($CURUSER["id"]);
				header("Refresh:0; url=". $_SERVER['PHP_SELF'] . "");
			}else
				stderr("Error", "Es fehlen Daten!");
		}else
			stderr("Error", "Das darfst Du nicht tun!");
		break;
	case "create":
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			if(get_user_class() >= UC_MODERATOR){
				$code = ((isset($_POST["code"])) ? $_POST["code"] : "");
				$date = ((isset($_POST["date"])) ? $_POST["date"] : "");
				$value = ((isset($_POST["value"])) ? $_POST["value"] : 0);
				$v = new voucher($code);
				$v->create($code,$date,$value);
				header("Refresh:0; url=". $_SERVER['PHP_SELF'] . "");
			}else
				stderr("Error", "Du hast dazu keine Berechtigung!");
		}else
			stderr("Error", "Das darfst Du nicht tun!");
		break;
	case "delete":
		if(get_user_class() >= UC_MODERATOR){
			if(isset($_GET["code"]) && strlen($_GET["code"]) == 20){
				if(isset($_GET["sure"]) && $_GET["sure"] == 1){
					$v = new voucher($_GET["code"]);
					$v->deleteVoucher();
					header("Refresh:0; url=". $_SERVER['PHP_SELF'] . "");
				}else
					stderr("Code löschen", "Du willst den Code löschen? Klicke <a href=\"" . $_SERVER['PHP_SELF'] . "?action=delete&code=" . $_GET["code"] . "&sure=1\">hier</a>, wenn Du Dir sicher bist.");
			}else
				stderr("Error", "Es scheinen Daten zu fehlen!");
		}else
			stderr("Error", "Du hast dazu keine Berechtigung!");
		break;
	case "edit":
		if(get_user_class() >= UC_MODERATOR){
			if($_SERVER["REQUEST_METHOD"] == "POST"){
				$code = ((isset($_POST["code"])) ? $_POST["code"] : "");
				$date = ((isset($_POST["date"])) ? $_POST["date"] : "");
				$value = ((isset($_POST["value"])) ? ($_POST["value"]) : 0);
				$v = new voucher($code);
				$v->date = $date;
				$v->value = $value * 1024 * 1024;
				$v->saveVoucher();
				header("Refresh:0; url=". $_SERVER['PHP_SELF'] . "");
			}else{
				if(isset($_GET["code"]) && strlen($_GET["code"]) == 20){
					$v = new voucher($_GET["code"]);
				}else
					stderr("Error", "Es scheinen Daten zu fehlen!");
			}
		}else
			stderr("Error", "Du hast dazu keine Berechtigung!");
		break;
	case "wipe":
		if(get_user_class() >= UC_MODERATOR){
			if(isset($_GET["code"]) && strlen($_GET["code"]) == 20){
				if(isset($_GET["sure"]) && $_GET["sure"] == 1){
					$v = new voucher($_GET["code"]);
					$v->resetUsers();
					header("Refresh:0; url=". $_SERVER['PHP_SELF'] . "");
				}else
					stderr("Reset", "Du willst die Codebenutzung zurücksetzen? Klicke <a href=\"" . $_SERVER['PHP_SELF'] . "?action=wipe&code=" . $_GET["code"] . "&sure=1\">hier</a>, wenn Du Dir sicher bist.");
			}else
				stderr("Error", "Es scheinen Daten zu fehlen!");
		}else
			stderr("Error", "Du hast dazu keine Berechtigung!");
		break;
}


if($action == "view"){
	stdhead("Gutscheine");
	begin_frame("Menü");
	echo "<table cellspacing=\"5\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
		"    <tr>\n".
		"        <td valign=\"top\" width=\"50%\">\n".
		"            <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
		"                <tr class=\"tabletitle\" width=\"100%\">\n".
		"                    <td width=\"100%\"><span class=\"normalfont\"><b>Code einlösen</b></span></td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td width=\"100%\" class=\"tablea\"><b>Code:<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?action=use\"><input type=\"text\" size=\"40\" name=\"code\" /> <input type=\"submit\" value=\"Einlösen!\" class=\"btn\"></form></b></td>\n".
		"                </tr>".
		"            </table>\n".
		"        </td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";
	if(get_user_class() >= UC_MODERATOR){
		echo "<script language='JavaScript' src='js/showVoucherCreator.js' type='text/javascript'></script>\n".
			"<table id=\"creator\" cellspacing=\"5\" cellpadding=\"0\" border=\"0\" style=\"width:100%;display:none\">\n".
			"    <tr>\n".
			"        <td valign=\"top\" width=\"50%\">\n".
			"            <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
			"            <form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?action=create\">\n".
			"                <tr class=\"tabletitle\" width=\"100%\">\n".
			"                    <td width=\"100%\" colspan=\"2\"><span class=\"normalfont\"><b>Code erstellen</b></span></td>\n".
			"                </tr>\n".
			"                <tr>\n".
			"                    <td width=\"20%\" class=\"tablea\"><b>Code:</b></td>\n".
			"                    <td width=\"80%\" class=\"tableb\"><input type=\"text\" size=\"40\" name=\"code\" /><p>Bis zu 20 Zeichen (A-Z, 0-9).<br>Gültige Codes sind 20 Zeichen lang, deshalb werden fehlende Zeichen aufgefüllt.<br>Frei lassen für Zufallscode.</p></td>\n".
			"                </tr>".
			"                <tr>\n".
			"                    <td width=\"20%\" class=\"tablea\"><b>Gültig am:</b></td>\n".
			"                    <td width=\"80%\" class=\"tableb\"><input id=\"creator_input_date\" type=\"text\" size=\"40\" name=\"date\" /> <input type=\"button\" id=\"immer\" class=\"btn\" value=\"Immer gültig\" /><p>0000-00-00 für \"immer\".<br>Feiertage werden, wenn kein Voucher vorhanden ist, ggf. automatisch \"versorgt\".<br>Frei lassen für heutige Gültigkeit.</p></td>\n".
			"                </tr>".
			"                <tr>\n".
			"                    <td width=\"20%\" class=\"tablea\"><b>Wert:</b></td>\n".
			"                    <td width=\"80%\" class=\"tableb\"><input id=\"creator_input_value\" type=\"text\" size=\"40\" name=\"value\" /> <input type=\"button\" id=\"plus\" class=\"btn\" value=\"Plus 100MB\" /><p>Wert in MB.<br>Wird dem Upload des Users angerechnet.<br>Frei lassen für 500MB.</p></td>\n".
			"                </tr>".
			"                <tr>\n".
			"                    <td width=\"100%\" class=\"tableb\" colspan=\"2\"><input type=\"submit\" value=\"Erstellen!\" class=\"btn\"></td>\n".
			"                </tr>".
			"            </form>\n".
			"            </table>\n".
			"        </td>\n".
			"    </tr>\n".
			"</table>\n".
			"<input type=\"button\" id=\"creator_show_button\" value=\"Voucher-Code erstellen?\" />\n".
			"<br>\n";
		$qry = $GLOBALS["DB"]->prepare("SELECT code FROM vouchers ORDER BY id DESC");
		$qry->execute();
		if($qry->rowCount()){
			echo "<table cellspacing=\"5\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
				"    <tr>\n".
				"        <td valign=\"top\" width=\"50%\">\n".
				"            <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
				"                <tr class=\"tabletitle\" width=\"100%\">\n".
				"                    <td width=\"100%\" colspan=\"6\"><span class=\"normalfont\"><b>Alle Codes</b></span></td>\n".
				"                </tr>\n".
				"                <tr class=\"tabletitle\">\n".
				"                    <td width=\"5%\"><b>Code #</b></td>\n".
				"                    <td width=\"20%\"><b>Code</b></td>\n".
				"                    <td width=\"10%\"><b>Gültig am</b></td>\n".
				"                    <td width=\"7%\"><b>Wert</b></td>\n".
				"                    <td width=\"48%\"><b>wurde benutzt von</b></td>\n".
				"                    <td width=\"10%\"><b>Aktionen</b></td>\n".
				"                </tr>";
			$data = $qry->FetchAll(PDO::FETCH_ASSOC);
			foreach($data as $vc){
				$v = new voucher($vc["code"]);
				if($v->date == "0000-00-00")
					$pretty_date = "<font color=\"green\">Immer gültig</font>";
				else{
					$pretty_date = $v->date;
					if($v->date == date("Y-m-d"))
						$pretty_date .= "<br><font color=\"green\">heute gültig!</font>";
					else
						$pretty_date .= "<br><font color=\"red\">heute nicht gültig!</font>";
				}
				$pretty_value = $v->value / 1024 / 1024;
				$pretty_unit = " MB";
				if($pretty_value >= 1024){
					$pretty_value =  str_replace(".", ",", round(($pretty_value / 1024),2));
					$pretty_unit = " GB";
				}
				$pua = $v->getPrettyUsers();
				$pretty_users_str = "";
				foreach($pua as $userid){
					if($userid == "nan"){
						$pretty_users_str = "Bisher niemand";
						break;
					}
					$qry = $GLOBALS["DB"]->prepare("SELECT username FROM users WHERE id = :uid");
					$qry->bindParam(':uid', $userid, PDO::PARAM_INT);
					$qry->execute();
					$u = $qry->Fetch(PDO::FETCH_ASSOC);
					$pretty_users_str .= "<a href=\"userdetails.php?id=" . $userid . "\">" . $u["username"] . "</a>, ";
				}
				if($pretty_users_str != "Bisher niemand")
					$pretty_users_str .= "<br><a href=\"" . $_SERVER['PHP_SELF'] . "?action=wipe&code=" . $v->code . "\">Zurücksetzen!</a>";
				
				echo "                <tr>\n".
					"                    <td class=\"tablea\">" . $v->id . "</td>\n".
					"                    <td class=\"tableb\">" . $v->code . "</td>\n".
					"                    <td class=\"tablea\">" . $pretty_date . "</td>\n".
					"                    <td class=\"tableb\">" . $pretty_value . $pretty_unit . "</td>\n".
					"                    <td class=\"tablea\">" . $pretty_users_str . "</td>\n".
					"                    <td class=\"tableb\"><a href=\"" . $_SERVER['PHP_SELF'] . "?action=edit&code=" . $v->code . "\">ändern</a> || <a href=\"" . $_SERVER['PHP_SELF'] . "?action=delete&code=" . $v->code . "\">löschen</a></td>\n".
					"                </tr>";
			}
			echo "            </table>\n".
				"        </td>\n".
				"    </tr>\n".
				"</table>\n";
		}
	}
	end_frame();
	stdfoot();
}elseif($action == "edit"){
	stdhead("Gutschein");
	begin_frame("Bearbeiten");
	echo "<script language='JavaScript' src='js/showVoucherCreator.js' type='text/javascript'></script>\n".
		"<table cellspacing=\"5\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
		"    <tr>\n".
		"        <td valign=\"top\" width=\"50%\">\n".
		"            <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
		"            <form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?action=edit\">\n".
		"                <tr class=\"tabletitle\" width=\"100%\">\n".
		"                    <td width=\"100%\" colspan=\"2\"><span class=\"normalfont\"><b>Code erstellen</b></span></td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td width=\"20%\" class=\"tablea\"><b>Code:</b></td>\n".
		"                    <td width=\"80%\" class=\"tableb\"><input type=\"hidden\" name=\"code\" value=\"" . $v->code . "\" /><input type=\"text\" size=\"40\" name=\"code_disp\" value=\"" . $v->code . "\" disabled=\"disabled\"/><p>Darf nicht geändert werden.</p></td>\n".
		"                </tr>".
		"                <tr>\n".
		"                    <td width=\"20%\" class=\"tablea\"><b>Gültig am:</b></td>\n".
		"                    <td width=\"80%\" class=\"tableb\"><input id=\"creator_input_date\" type=\"text\" size=\"40\" name=\"date\" value=\"" . $v->date . "\"/> <input type=\"button\" id=\"immer\" class=\"btn\" value=\"Immer gültig\" /><p>0000-00-00 für \"immer\".<br>Feiertage werden, wenn kein Voucher vorhanden ist, ggf. automatisch \"versorgt\".<br>Frei lassen für heutige Gültigkeit.</p></td>\n".
		"                </tr>".
		"                <tr>\n".
		"                    <td width=\"20%\" class=\"tablea\"><b>Wert:</b></td>\n".
		"                    <td width=\"80%\" class=\"tableb\"><input id=\"creator_input_value\" type=\"text\" size=\"40\" name=\"value\" value=\"" . ($v->value / 1024 / 1024) . "\"/> <input type=\"button\" id=\"plus\" class=\"btn\" value=\"Plus 100MB\" /><p>Wert in MB.<br>Wird dem Upload des Users angerechnet.<br>Frei lassen für 500MB.</p></td>\n".
		"                </tr>".
		"                <tr>\n".
		"                    <td width=\"100%\" class=\"tableb\" colspan=\"2\"><input type=\"submit\" value=\"Ändern!\" class=\"btn\"></td>\n".
		"                </tr>".
		"            </form>\n".
		"            </table>\n".
		"        </td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";
	end_frame();
	stdfoot();
}
?>