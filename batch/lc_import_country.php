<?php
require 'stuff.php';
$config = parse_ini_file($argv[1]);
if (! $config) {
    echo "Couldn't parse the config file.";
    exit(1);
}
$filename = getTempNam();
if (! file_get_contents_curl("http://www.geopostcodes.com/inc/download.php?f=Countries&t=9", $filename)) {
    exit(1);
}

$zip = new Zip_Manager();
$zip->open($filename);
$zip->filteredExtractTo('./');
$zip->close();
unlink($filename);
$filename = "GeoPC_Countries.csv";
$f = fopen($filename, 'rb');
if (! $f) {
    exit(1);
}
$row = fgetcsv($f, null, ';');
if ($row != array(
    'iso',
    'country',
    'sovereign',
    'postalformat',
    'postalname',
    'geopc'
)) {
    exit(1);
}
$pdo = getPDOConnection($config);
$pdo->beginTransaction();
$st1 = $pdo->prepare('select 1 has from country where iso = ?');
$st2 = $pdo->prepare('update country set name = ?, postal_format = ?, postal_name = ? where iso = ?');
$st3 = $pdo->prepare('insert into country (id, name, postal_format, postal_name, iso) values (nextval(\'country_id_seq\'), ?,?,?,?)');
while ($row = fgetcsv($f, null, ';')) {
    if (! $st1->execute(array(
        $row[0]
    ))) {
        exit(1);
    }
    if ($st1->fetchAll()) {
        $st2->bindParam(1, trim2($row[1]), PDO::PARAM_STR | PDO::PARAM_NULL);
        $st2->bindParam(2, trim2($row[3]), PDO::PARAM_STR | PDO::PARAM_NULL);
        $st2->bindParam(3, trim2($row[4]), PDO::PARAM_STR | PDO::PARAM_NULL);
        $st2->bindParam(4, trim($row[0]), PDO::PARAM_STR | PDO::PARAM_NULL);
        if (! $st2->execute()) {
            print_r($row);
            print_r($pdo->errorInfo());
            exit(1);
        }
    } else {
        $st3->bindParam(1, trim2($row[1]), PDO::PARAM_STR | PDO::PARAM_NULL);
        $st3->bindParam(2, trim2($row[3]), PDO::PARAM_STR | PDO::PARAM_NULL);
        $st3->bindParam(3, trim2($row[4]), PDO::PARAM_STR | PDO::PARAM_NULL);
        $st3->bindParam(4, trim($row[0]), PDO::PARAM_STR | PDO::PARAM_NULL);
        if (! $st3->execute()) {
            print_r($row);
            print_r($pdo->errorInfo());
            exit(1);
        }
    }
}
$pdo->commit();
unlink($filename);
exit(0);



