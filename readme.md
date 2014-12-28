[![Build Status](https://travis-ci.org/blar/dba.png?branch=master)](https://travis-ci.org/blar/dba)
[![Coverage Status](https://coveralls.io/repos/blar/dba/badge.png?branch=master)](https://coveralls.io/r/blar/dba?branch=master)
[![Dependency Status](https://gemnasium.com/blar/dba.svg)](https://gemnasium.com/blar/dba)
[![Dependencies Status](https://depending.in/blar/dba.png)](http://depending.in/blar/dba)

# DBA

http://php.net/dba

## Verwendung

* https://httpd.apache.org/docs/2.2/mod/mod_authn_dbm.html

## Beispiele

Auf viele Methoden kann auch über die Array-Syntax zugegriffen werden.

### Datenbank erstellen

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
