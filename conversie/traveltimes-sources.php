<?php
error_reporting(E_ALL);
$f = gzopen("ftp://83.247.110.3/traveltime.gz","r");
ob_start();
gzpassthru($f);
$data = ob_get_clean();
$xml = simplexml_load_string(str_replace("soap:", "", $data));
$sources = Array();
$timestamp = null;
foreach($xml->Body->d2LogicalModel->payloadPublication->siteMeasurements as $m){
    $source = substr((string)$m->measurementSiteReference["id"],0,5);
    if(array_key_exists($source, $sources)){
        $sources[$source] += 1;
    } else {
        $sources[$source] = 1;
    }
}

ksort($sources);
echo json_encode($sources);
?>
