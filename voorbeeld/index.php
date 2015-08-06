<!DOCTYPE html>
<html>
  <head>
    <META HTTP-EQUIV="REFRESH" CONTENT="300">
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <title>Voorbeeldcode Realtime Verkeersdata</title>
    <style>
        #map{
            position: absolute;
            bottom: 0px;
            left: 0px;
            top: 0px;
            right: 0px; 
        }        

        #info{
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px; 
            height: 100px;
            background: white;
            font-family: Arial;
        }
        h1{
            padding: 10px;
            margin: 0px
        } 

        dl {
            margin: 0px;
            padding: 0px;
        }
        dt {
            float: left;
            margin: 10px;
            font-weight: bold;
        }
        dt:after {
            content: ":";
        }
        dd {
            float: left;
            margin: 10px;
        }
    </style>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>     
    <script type='text/javascript'>    
    
    function load() {
     var myLatlng = new google.maps.LatLng(52.36500, 4.90000);
     zoom = 12;

     var myOptions = {
        zoom: zoom,
        mapTypeId: google.maps.MapTypeId.TERRAIN
     }
     var map = new google.maps.Map(document.getElementById("map"), myOptions);        
     map.setCenter(myLatlng,zoom);     

        <?php
        
            function speedToColor($type, $speed){
                if($type == "H"){
                    //Snelweg
                    $speedColors = Array(
                        120 => "#00B22D", 
                        90 => "#AAFF00",
                        70 => "#FFFF00",
                        50 => "#FF9E00",
                        30 => "#FF0000",
                        1 => "#BE0000",
                        0 => "#D0D0D0"
                    );
                } else {
                    //Geen snelweg
                    $speedColors = Array(
                        70 => "#00B22D", 
                        40 => "#AAFF00",
                        30 => "#FFFF00",
                        20 => "#FF9E00",
                        10 => "#FF0000",
                        1 => "#BE0000",
                        0 => "#D0D0D0"
                    );
                }
                foreach($speedColors as $minspeed => $color){
                    if($speed >= $minspeed) return $color;
                }
                return "#D0D0D0";
            }                      
        
            $count = 0;
            $jsontxt = file_get_contents("http://tools.amsterdamopendata.nl/ndw/data/reistijdenAmsterdam.geojson");
            $json = json_decode($jsontxt);
            foreach($json->features as $feature){
                $count++;
                if($feature->properties->Velocity >= 0){
                    $color = speedToColor($feature->properties->Type, $feature->properties->Velocity);
                    $points = $feature->geometry->coordinates;
                    $info = "<H1>". $feature->properties->Name . "</H1><dl>";
                    $info .= "<dt>Lengte</dt> <dd>". $feature->properties->Length ." meter</dd>";
                    $info .= "<dt>Snelheid</dt> <dd>". $feature->properties->Velocity ." km/u</dd>";
                    $info .= "<dt>Huidige reistijd</dt> <dd>". floor($feature->properties->Traveltime / 60) .":". str_pad($feature->properties->Traveltime % 60,2,"0") ."</dd></dl>";
                    $split = "";
                    print("var path". $count ." = [");
                    foreach($points as $point){
                        $lat = $point[1];
                        $lon = $point[0];
                        print($split . " new google.maps.LatLng(". $lat .", ". $lon .")");
                        $split = ",";
                    }
                    print("];\n");
               
                    print("var line". $count ." = new google.maps.Polyline({map: map, path: path". $count .", strokeColor: '". $color ."', strokeOpacity: 1.0,strokeWeight: 3, title: '". $title ."'});\n");
                    print("google.maps.event.addListener(line". $count .", 'click', function() {document.getElementById('info').innerHTML = '". $info ."'; });\n");
                } else {
                    //Do not show. Velocity < 0
                }
            }
        ?>
    }
  </script>
  </head>
  <body onLoad='load()'>
    <div id='map'></div>
    <div id='info'></div>
  </body>
</html>