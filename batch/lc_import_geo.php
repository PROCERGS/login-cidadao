<?php
require 'stuff.php';
ini_set('memory_limit', -1);
$config = parse_ini_file($argv[1]);
if (!$config) {
    echo "Couldn't parse the config file.";
    exit(1);
}
$soap = new SoapClient('http://sildes0217.procergs.reders:6780/geows/geows?wsdl', array(
    'trace' => true
));
$ha = $soap->__getFunctions();
$ha1 = $soap->listaTabelas();
//$result1 = $soap->listaDados(array('arg0' => 'paismundo4326'));
//$result2 = $soap->listaDados(array('arg0' => 'estadomundo4326'));
//$result3 = $soap->listaDados(array('arg0' => 'estadomundo4326'));
$result4 = $soap->listaDados(array('arg0' => 'municipiobr4326'));
echo 'oi';