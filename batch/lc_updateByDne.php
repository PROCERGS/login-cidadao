<?php
require 'stuff.php';
$config = parse_ini_file($argv[1]);
if (! $config) {
    echo "Couldn't parse the config file.";
    exit(1);
}
function updateAdressByDne($config) {
    $pdo = getPDOConnection($config);
    $pdo->beginTransaction();
    $wrong = $pdo->query("select id, cep, city_id, adress from person where cep is not null ");
    if (!$wrong) {
        print_r($pdo->errorInfo());
        return false;
    }
    $stm1 = $pdo->prepare("update person set country_id = ?, state_id = ?, city_id = ?, adress = ? where id = ?");
    $stm2 = $pdo->prepare("select a1.id, a1.state_id, a2.country_id from city a1 inner join state a2 on a1.state_id = a2.id where a1.stat = ?");
    $stm3 = $pdo->prepare('select a1.id, a1.state_id, a2.country_id from city a1 inner join state a2 on a1.state_id = a2.id where a2.iso6 = ? and translate(lower(a1.name),\'áàâãäāéèêëíìïóòôõöúùûüūÁÀÂÃÄĀÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜŪçÇ‘\',\'aaaaaaeeeeiiiooooouuuuuAAAAAAEEEEIIIOOOOOUUUUUcC\') = translate(lower(?),\'áàâãäāéèêëíìïóòôõöúùûüūÁÀÂÃÄĀÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜŪçÇ‘\',\'aaaaaaeeeeiiiooooouuuuuAAAAAAEEEEIIIOOOOOUUUUUcC\')');

    $dne = new DneHelper();
    foreach ($wrong as $person) {
        $ceps = $dne->findByCep($person['cep']);
        if ($ceps) {
            if (is_numeric($ceps['codigoMunIBGE'])) {
                if ($ceps['codigoMunIBGE'] === 0) {
                    if (!$stm3->execute(array('BR-'.$ceps['state'],$ceps['infoAdicional']))) {
                        print_r($pdo->errorInfo());
                        return false;
                    }
                    if ($res = $stm3->fetchAll()) {
                        if (!$stm1->execute(array($res[0]['country_id'], $res[0]['state_id'], $res[0]['id'], $ceps['localidade'], $person['id']))) {
                            print_r($pdo->errorInfo());
                            return false;
                        }
                    }
                } else {
                    if (!$stm2->execute(array($ceps['codigoMunIBGE']))) {
                        print_r($pdo->errorInfo());
                        return false;
                    }
                    if ($res = $stm2->fetchAll()) {
                        if (!$stm1->execute(array($res[0]['country_id'], $res[0]['state_id'], $res[0]['id'], $ceps['logradouroExtenso'], $person['id']))) {
                            print_r($pdo->errorInfo());
                            return false;
                        }
                    }
                }
            } else {
                print_r('dunno');
                return false;
            }
        } elseif (is_numeric($person['city_id']) &&  $person['city_id'] == 0) {
            if (!$stm1->execute(array(null, null, null, null, $person['id']))) {
                print_r($pdo->errorInfo());
                return false;
            }
        }
    }
    $pdo->commit();
    return true;
}
exit(updateAdressByDne($config) ? 1 : 0);
