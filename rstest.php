<html>
<head>
<title>Ratio-Histogramme</title>
</head>
<body>
<?php

$files = explode("\n",shell_exec("find ./bitbucket -name 'rstat-*.png'"));
foreach($files as $file) {
    if ($file != "") echo '<img src="'.$file."\">\n";
}

?>
</body>
</html>