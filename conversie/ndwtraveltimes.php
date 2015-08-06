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
  
  $json = json_decode(file_get_contents("ndw-shapefiles-amsterdam.geojson"));
  foreach($json->features as $i => $f){
      $properties = new stdClass();
      $properties->Id =  $json->features[$i]->properties->dgl_loc;
      $properties->Name =  $json->features[$i]->properties->naam;
      $properties->Type =  $json->features[$i]->properties->wegtype;
      $properties->Timestamp = $timestamp;
      $properties->Length =  $json->features[$i]->properties->lengte;
      if(array_key_exists($f->properties->dgl_loc, $traveltimes)){
          $properties->Traveltime =  (int)$traveltimes[$f->properties->dgl_loc]->travelTime->duration;
          if($properties->Traveltime > 0 && $properties->Length){
              $properties->Velocity = round(($properties->Length / $properties->Traveltime) * 3.6);
          }
      } else {
          //unset($json->features[$i]);
      }
      $json->features[$i]->properties = $properties;
  }
  
  $f = fopen("../data/reistijdenAmsterdam.geojson","w");
  fwrite($f, json_encode($json));
  fclose($f);
?>
