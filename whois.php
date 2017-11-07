<?php

if (!preg_match("/^(\\d{1,3})\\.(\\d{1,3})\\.(\\d{1,3})\\.(\\d{1,3})$/", $_GET["ip"])) {
    echo "Keine gültige IP angegeben!";
    die();
}


$parts = explode(".", $_GET["ip"]);
foreach($parts as $part) {
    if (intval($part)<0 || intval($part)>255) {
	echo "Keine gültige IP angegeben!";
	die();
    }
}

?>
<html>
<head>
<title>WHOIS Data zu IP <?=$_GET["ip"]?></title>
</head>
<body>
<pre>
<?php

system("whois ".escapeshellcmd($_GET["ip"]));

?>
</pre>
</body>
</html>