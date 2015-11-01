[![Build Status](https://travis-ci.org/blar/dba.svg?branch=master)](https://travis-ci.org/blar/dba)
[![Coverage Status](https://coveralls.io/repos/blar/dba/badge.svg?branch=master&service=github)](https://coveralls.io/github/blar/dba?branch=master)
[![Dependency Status](https://gemnasium.com/blar/dba.svg)](https://gemnasium.com/blar/dba)

# DBA

Eine objektoriertierte Schnittstelle für die [DBA-Funktionen von PHP](http://php.net/dba).

## Verwendung

Der Apache-Webserver kann die Benutzerinformationen für die [Authentifizierung aus einer DBM-Datei](https://httpd.apache.org/docs/2.2/mod/mod_authn_dbm.html) laden.

## Beispiele

Auf viele Methoden kann auch über die Array-Syntax zugegriffen werden.

### Verfügbare Treiber

Die verfügbaren Treiber (die im Parameter **driverName** angegeben werden) können über die statische Methode **Dba::getDrivers()** abgefragt werden. Eine typische Ausgabe kann wie folgt aussehen:

    array(5) {
       [0]=>
       string(3) "cdb"
       [1]=>
       string(8) "cdb_make"
       [2]=>
       string(7) "inifile"
       [3]=>
       string(8) "flatfile"
       [4]=>
       string(4) "qdbm"
    }

Welche Datenbanktreiber unterstützt werden, hängt vom jeweiligen System und den Einstellungen ab mit denen PHP kompiliert wurde. Falls eine neue Datenbankdatei angelegt werden soll, kann für die Entscheidung welcher Treiber verwendet werden soll, der [Benchmarkvergleich von Tokyo Tyrant](http://tokyocabinet.sourceforge.net/benchmark.pdf) hilfreich sein.

### Datenbank erstellen

#### INI-Datei

    $dba = new Dba('test.ini', Dba::MODE_READ | Dba::MODE_WRITE | Dba::MODE_CREATE, array(
        'driverName' => 'inifile'
    ));

#### GNU Database Manager (GDBM)

    $dba = new Dba('test.gdbm', Dba::MODE_READ | Dba::MODE_WRITE | Dba::MODE_CREATE, array(
        'driverName' => 'gdbm'
    ));

#### Tiny Constant Database (CDB)

CDB kann entweder mit der Option driverName **cdb_make** erstellt oder mit der Option driverName **cdb** gelesen werden.
Das Aktualisieren oder Löschen von Einträgen ist nicht möglich.

    $dba = new Dba('test.cdb', Dba::MODE_WRITE | Dba::MODE_CREATE, array(
        'driverName' => 'cdb_make'
    ));

    $dba = new Dba('test.cdb', Dba::MODE_READ, array(
        'driverName' => 'cdb'
    ));

### Einträge hinzufügen

    $dba->insert('foo', 23);
    $dba->insert('bar', 42);
    
    $dba['foo'] = 23;
    $dba['bar'] = 42;

### Einträge prüfen

    $dba->exists('foo');
    
    isset($dba['foo']);

### Einträge aktualisieren

    $dba->replace('foo', 1337);

    $dba['foo'] = 1337;

### Einträge löschen

    $dba->remove('bar');
    
    unset($dba['bar']);

### Alle Einträge in einer Datenbank auslesen

    foreach($dba as $key => $value) {
        var_dump($value);
    }
