# pdonvtracker
Netvision Bittorrent-Tracker 2017

## Über pdonvtracker

Das Ziel dieses Repos ist es, eine Version des beliebten nvtrackers zu erstellen, die 2017 mit den aktuellen Versionen der Dienstprogramme lauffähig ist.

### Hauptmission

Alte mysql_ Calls durch neue, nicht veraltete, pdo-calls zu ersetzen.
```
$res = mysql_query("SELECT userid,torrent,UNIX_TIMESTAMP(started) AS started,finishedat,uploaded,downloaded FROM peers");
```
wird zu
```
$qry = $GLOBALS['DB']->prepare('SELECT userid,torrent,UNIX_TIMESTAMP(started) AS started,finishedat,uploaded,downloaded FROM peers');
$qry->execute();
```

### Quelle

* [NV-Technik](http://www.netvision-technik.de/forum/) - Entwicklerforum für Bittorrent-Technologie
