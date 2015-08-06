<?php
error_reporting(E_ALL);
  $f = gzopen("ftp://83.247.110.3/traveltime.gz","r");
  ob_start();
  gzpassthru($f);
  $data = ob_get_clean();
  $xml = simplexml_load_string(str_replace("soap:", "", $data));
  $traveltimes = Array();
  $timestamp = null;
  foreach($xml->Body->d2LogicalModel->payloadPublication->siteMeasurements as $m){
      $traveltimes[(string)$m->measurementSiteReference["id"]] = $m->measuredValue->measuredValue->basicData;
      if($m->measurementTimeDefault) $timestamp = (string)$m->measurementTimeDefault;

  }

  echo json_encode($traveltimes);
?>
