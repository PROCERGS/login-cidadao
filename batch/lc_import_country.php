<?php
require 'stuff.php';
$config = parse_ini_file($argv[1]);
if (!$config) {
    echo "Couldn't parse the config file.";
    exit(1);
}

function getCountryPostal($config)
{
    $filename = getTempNam();
    if (!file_get_contents_curl("http://www.geopostcodes.com/inc/download.php?f=Countries&t=9", $filename)) {
        return false;
    }

    $zip = new Zip_Manager();
    $zip->open($filename);
    $zip->filteredExtractTo('./');
    $zip->close();
    unlink($filename);
    $filename = "GeoPC_Countries.csv";
    $f = fopen($filename, 'rb');
    if (!$f) {
        return false;
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
        return false;
    }
    $pdo = getPDOConnection($config);
    $pdo->beginTransaction();
    $st1 = $pdo->prepare('select id from country where iso2 = ?');
    $st2 = $pdo->prepare('update country set postal_format = ?, postal_name = ? where id = ?');
    $st3 = $pdo->prepare('insert into country (id, name, postal_format, postal_name, iso2) values (nextval(\'country_id_seq\'), ?,?,?,?)');
    while ($row = fgetcsv($f, null, ';')) {
        if (!$st1->execute(array(
            $row[0]
        ))) {
            return false;
        }
        if ($r = $st1->fetchAll()) {
            $st2->bindParam(1, trim2($row[3]), PDO::PARAM_STR | PDO::PARAM_NULL);
            $st2->bindParam(2, trim2($row[4]), PDO::PARAM_STR | PDO::PARAM_NULL);
            $st2->bindParam(3, $r[0]['id'], PDO::PARAM_INT);
            if (!$st2->execute()) {
                print_r($row);
                print_r($pdo->errorInfo());
                exit(1);
            }
        } else {
            $st3->bindParam(1, trim2($row[1]), PDO::PARAM_STR | PDO::PARAM_NULL);
            $st3->bindParam(2, trim2($row[3]), PDO::PARAM_STR | PDO::PARAM_NULL);
            $st3->bindParam(3, trim2($row[4]), PDO::PARAM_STR | PDO::PARAM_NULL);
            $st3->bindParam(4, trim($row[0]), PDO::PARAM_STR | PDO::PARAM_NULL);
            if (!$st3->execute()) {
                print_r($row);
                print_r($pdo->errorInfo());
                return false;
            }
        }
    }
    $pdo->commit();
    unlink($filename);
    return true;
}

function updateByNsg($config)
{
    $filename = getTempNam();
    if (!file_get_contents_curl('https://nsgreg.nga.mil/NSGDOC/files/doc/Document/GENC%20Standard%20Index%20XML%20Ed2.0.zip', $filename, 'https://nsgreg.nga.mil/doc/view?i=2382')) {
        return false;
    }

    $zip = new Zip_Manager();
    $zip->open($filename);
    $zip->filteredExtractTo('./', array(
        '/^GENC Standard Index Ed2.0.xml$/'
    ));
    $zip->close();
    unlink($filename);
    $filename = realpath("./GENC Standard Index Ed2.0.xml");
    $dom = new DOMDocument();
    if (!$dom->load($filename)) {
        return false;
    }
    $dom->documentElement->removeAttributeNS('http://api.nsgreg.nga.mil/schema/genc/2.0', 'genc');
    $xpath = new DOMXPath($dom);
    // $nodes = $xpath->query('//GENCStandardBaselineIndex/GeopoliticalEntity[encoding/char3Code][encoding/char2Code][encoding/numericCode][name]');
    $nodes = $xpath->query('//GENCStandardBaselineIndex/GeopoliticalEntity');
    if (!$nodes || !$nodes->length) {
        return false;
    }
    $pdo = getPDOConnection($config);

    $st1 = $pdo->prepare('select id from country where iso3 = ?');
    $st2 = $pdo->prepare('update country set name = ? where id = ?');
    $st3 = $pdo->prepare('insert into country (id, name, iso2, iso3, iso_num, reviewed) values (nextval(\'country_id_seq\'), ?,?,?,?, 0)');

    $pdo->beginTransaction();
    foreach ($nodes as $node) {
        $char3Code = $node->getElementsByTagName('char3Code')->item(0)->nodeValue;
        if (!$st1->execute(array(
            $char3Code
        ))) {
            print_r($char3Code);
            print_r($pdo->errorInfo());
            return false;
        }
        if ($r = $st1->fetchAll()) {
            if (!$st2->execute(array(
                $node->getElementsByTagName('name')
                    ->item(0)->nodeValue,
                $r[0]['id']
            ))) {
                print_r($char3Code);
                print_r($pdo->errorInfo());
                return false;
            }
        } else {
            if (!$st3->execute(array(
                $node->getElementsByTagName('name')
                    ->item(0)->nodeValue,
                $node->getElementsByTagName('char2Code')
                    ->item(0)->nodeValue,
                $char3Code,
                $node->getElementsByTagName('numericCode')
                    ->item(0)->nodeValue
            ))) {
                print_r($char3Code);
                print_r($pdo->errorInfo());
                return false;
            }
        }
        /*
         * echo $node->getElementsByTagName('char3Code')->item(0)->nodeValue; echo $node->getElementsByTagName('char2Code')->item(0)->nodeValue; echo $node->getElementsByTagName('numericCode')->item(0)->nodeValue; echo $node->getElementsByTagName('name')->item(0)->nodeValue;
         */
    }

    $st1->closeCursor();
    $st2->closeCursor();
    $st3->closeCursor();

    $st1 = $pdo->prepare('select id id from state a1 where a1.iso6 = ?');
    $st2 = $pdo->prepare('select a1.id id from state a1 inner join country a2 on a1.country_id = a2.id where a2.iso3 = ? and a1.name = ?');
    $st3 = $pdo->prepare('update state set iso6 = ?, name = ?, country_id = (select id from country where iso3 = ?) where id = ?');
    $st4 = $pdo->prepare('insert into state (id, country_id, iso6, name, reviewed) values (nextval(\'state_id_seq\'), (select id from country where iso3 = ?), ?, ?, 0)');
    $xpath = new DOMXPath($dom);
    // $nodes = $xpath->query('//GENCStandardBaselineIndex/AdministrativeSubdivision[country][encoding/char6Code][name]');
    $nodes = $xpath->query('//GENCStandardBaselineIndex/AdministrativeSubdivision');
    if (!$nodes || !$nodes->length) {
        return false;
    }
    foreach ($nodes as $node) {
        $char6Code = $node->getElementsByTagName('char6Code')->item(0)->nodeValue;
        if (!$st1->execute(array(
            $char6Code
        ))) {
            print_r($char6Code);
            print_r($pdo->errorInfo());
            exit(1);
        }
        if ($r = $st1->fetchAll()) {
            if (!$st3->execute(array(
                $char6Code,
                $node->getElementsByTagName('name')
                    ->item(0)->nodeValue,
                $node->getElementsByTagName('country')
                    ->item(0)->nodeValue,
                $r[0]['id']
            ))) {
                print_r($char6Code);
                print_r($pdo->errorInfo());
                exit(1);
            }
        } else {
            if (!$st2->execute(array(
                $node->getElementsByTagName('country')
                    ->item(0)->nodeValue,
                $node->getElementsByTagName('name')
                    ->item(0)->nodeValue
            ))) {
                print_r($char6Code);
                print_r($pdo->errorInfo());
                exit(1);
            }
            if ($r = $st2->fetchAll()) {
                if (!$st3->execute(array(
                    $char6Code,
                    $node->getElementsByTagName('name')
                        ->item(0)->nodeValue,
                    $node->getElementsByTagName('country')
                        ->item(0)->nodeValue,
                    $r[0]['id']
                ))) {
                    print_r($char6Code);
                    print_r($pdo->errorInfo());
                    exit(1);
                }
            } else {
                if (!$st4->execute(array(
                    $node->getElementsByTagName('country')
                        ->item(0)->nodeValue,
                    $char6Code,
                    $node->getElementsByTagName('name')
                        ->item(0)->nodeValue
                ))) {
                    print_r($char6Code);
                    print_r($pdo->errorInfo());
                    exit(1);
                }
            }
        }
    }
    /*
     * echo $node->getElementsByTagName('country')->item(0)->nodeValue; echo $node->getElementsByTagName('char6Code')->item(0)->nodeValue; echo $node->getElementsByTagName('name')->item(0)->nodeValue;
     */
    $pdo->commit();
    return true;
}
exit(updateByNsg($config) && getCountryPostal($config) ? 0 : 1);



