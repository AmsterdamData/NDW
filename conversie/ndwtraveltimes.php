<?php
  $f = gzopen("http://opendata.ndw.nu/traveltime.xml.gz","r");
  if($f){
      ob_start();
      gzpassthru($f);
      $data = ob_get_clean();
      $xml = simplexml_load_string(str_replace(Array("soap:","soapenv:","SOAP:","SOAPENV:"), "", $data));
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
              //print_r($traveltimes[$f->properties->dgl_loc]);
              $properties->Traveltime =  (int)$traveltimes[$f->properties->dgl_loc]->travelTime->duration;
              if($properties->Traveltime > 0 && $properties->Length){
                  $properties->Velocity = round(($properties->Length / $properties->Traveltime) * 3.6);
              }
          } else {
              //print("<BR><strong>". $f->properties->dgl_loc ." not found</strong><BR>");
              //unset($json->features[$i]);
          }
          $json->features[$i]->properties = $properties;
      }
      
      
      $f = fopen("../data/reistijdenAmsterdam.geojson","w");
      fwrite($f, json_encode($json));
      fclose($f);
      
      //Add results to BigQuery
      /*
     include(dirname(__FILE__) ."/settings.php");
     require_once '../../Google/bigquery/conversie/bigquery.php';

     $bq = new MyBigQueryClass(BIGQUERY_PROJECT_ID,BIGQUERY_CLIENT_ID,BIGQUERY_SERVICE_ACCOUNT_NAME);
     date_default_timezone_set("CET");
     foreach($json->features as $i => $f){ 
        if($f->properties->Velocity){
            $row = Array(
                "id" => $f->properties->Id,
                "timestamp" => date("Y-m-d H:i:s", strtotime($f->properties->Timestamp)),
                "velocity" => $f->properties->Velocity,
                "traveltime" => $f->properties->Traveltime,
            );
            $bq->insertRow("traveltimes", "traveltimes_ndw", $row);            
        } 
     }
     */
         
     } else {
         print("File not found.");
     }
?>
