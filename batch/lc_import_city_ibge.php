<?php
require 'stuff.php';
require 'vendor/autoload.php';
$config = parse_ini_file($argv[1]);
if (!$config) {
    echo "Couldn't parse the config file.";
    exit(1);
}
$filename = getTempNam();
$year = date("Y");
if (!file_get_contents_curl("ftp://geoftp.ibge.gov.br/organizacao_territorial/divisao_territorial/".$year."/dtb_".$year.".zip", $filename)) {
    exit(1);
}
$filename1 = "DTB_".$year."_Municipio.xls";
$zip = new Zip_Manager();
$zip->open($filename);
$zip->filteredExtractTo('./', array('/^'.$filename1.'$/'));
$zip->close();
unlink($filename);

$pdo = getPDOConnection($config);
$pdo->beginTransaction();
$st1 = $pdo->prepare('select id from city a1 where a1.id = ?');
$st2 = $pdo->prepare('insert into city (id, state_id, name, stat) values (?,'.
    '(select a1.id from state a1 inner join country a2 on a1.country_id = a2.id where a2.iso2 = \'BR\' and a1.stat = left(cast(? as varchar), 2))'.
    ', ?, ?)');
$st3 = $pdo->prepare('update city set state_id = COALESCE((select a1.id from state a1 inner join country a2 on a1.country_id = a2.id where a2.iso2 = \'BR\' and a1.stat = left(cast(? as varchar), 2)), state_id)'.
', name = ?, stat = ? where id = ?');
PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, array('memoryCacheSize' => '2GB'));
$xls = PHPExcel_IOFactory::load($filename1);
$sheet = $xls->getSheet(0);
foreach ($sheet->getRowIterator(2) as $row) {
    $bdRow = array();
    foreach ($row->getCellIterator() as $idx2 => $cell) {
        $bdRow[$idx2] = utf8_encode_recursivo($cell->getValue());
    }
    if (!$st1->execute(array(
        $bdRow[7]
    ))) {
        print_r($bdRow);
        print_r($pdo->errorInfo());
        exit(1);
    }
    if ($r = $st1->fetchAll()) {
        if (!$st3->execute(array(trim2($bdRow[7]), trim2($bdRow[8]), trim2($bdRow[7]), $r[0]['id']))) {
            print_r($bdRow);
            print_r($pdo->errorInfo());
            exit(1);
        }
    } else {
        if (!$st2->execute(array(trim2($bdRow[7]), trim2($bdRow[7]), trim2($bdRow[8]), trim2($bdRow[7])))) {
            print_r($bdRow);
            print_r($pdo->errorInfo());
            exit(1);
        }
    }
}
$pdo->commit();
unlink($filename1);

