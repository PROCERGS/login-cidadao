<?php
require 'stuff.php';
$config = parse_ini_file($argv[1]);
if (!$config) {
    echo "Couldn't parse the config file.";
    exit(1);
}
$filename = getTempNam();
if (!file_get_contents_curl("http://www.geopostcodes.com/inc/download.php?f=NGA&t=9", $filename)) {
    exit(1);
}

$zip = new Zip_Manager();
$zip->open($filename);
$zip->filteredExtractTo('./');
$zip->close();
unlink($filename);
$filename = "GeoPC_NGA.csv";
$f = fopen($filename, 'rb');
if (!$f) {
    exit(1);
}
$row = fgetcsv($f, null, ';');
if ($row != array(
    'iso',
    'nga',
    'country',
    'code',
    'name',
    'class'
)) {
    exit(1);
}
$pdo = getPDOConnection($config);
$pdo->beginTransaction();
$st1 = $pdo->prepare('select id id from state a1 where a1.fips = ?');
$st2 = $pdo->prepare('select a1.id id from state a1 inner join country a2 on a1.country_id = a2.id where a2.iso = ? and a1.name = ?');
$st3 = $pdo->prepare('update state set fips = ?, name = ?, class = ?, country_id = (select id from country where iso = ?) where id = ?');
$st4 = $pdo->prepare('insert into state (id, country_id, fips, name, class) values (nextval(\'state_id_seq\'), (select id from country where iso = ?), ?, ?, ?)');
while ($row = fgetcsv($f, null, ';')) {
    if (!$st1->execute(array(
        $row[3]
    ))) {
        print_r($row);
        print_r($pdo->errorInfo());
        exit(1);
    }
    if ($r = $st1->fetchAll()) {
        if (!$st3->execute(array(trim2($row[3]), trim2($row[4]), trim2($row[5]), trim2($row[0]), $r[0]['id']))) {
            print_r($row);
            print_r($pdo->errorInfo());
            exit(1);
        }
    } else {
        if (!$st2->execute(array(
            $row[0],
            $row[4]
        ))) {
            print_r($row);
            print_r($pdo->errorInfo());
            exit(1);
        }
        if ($r = $st2->fetchAll()) {
            if (!$st3->execute(array(trim2($row[3]), trim2($row[4]), trim2($row[5]), trim2($row[0]), $r[0]['id']))) {
                print_r($row);
                print_r($pdo->errorInfo());
                exit(1);
            }
        } else {
            if (!$st4->execute(array(trim2($row[0]), trim2($row[3]), trim2($row[4]), trim2($row[5])))) {
                print_r($row);
                print_r($pdo->errorInfo());
                exit(1);
            }
        }
    }
}
$pdo->commit();
unlink($filename);
exit(0);



