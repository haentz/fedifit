<?php

// todo: exceptions!
function saveRouteToImage($route, $filename, $mapstyle=1, $style=1):boolean {

    $points = Polyline::decode($route);
    $points = Polyline::pair($points);

    // get bounding box
    $polygon = new \League\Geotools\Polygon\Polygon(
        $points
    );
    $boundingBox = $polygon->getBoundingBox();


    // calcualte middle point of map
    $geotools   = new \League\Geotools\Geotools();
    $southWest = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getSouth(), $boundingBox->getwest()]);
    $northEast = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getNorth(), $boundingBox->getEast()]);
    $coordA   = $southWest;
    $coordB   = $northEast;
    $vertex    =  $geotools->vertex()->setFrom($coordA)->setTo($coordB);
    $middlePoint = $vertex->middle(); // \League\Geotools\Coordinate\Coordinate

    // calculate zoom

    // TODO: is this right??


    $zoomLevel = 12;
    $latDiff = $northEast->getLatitude() - $southWest->getLatitude();
    $lngDiff = $northEast->getLongitude() - $southWest->getLongitude();
    
    $maxDiff=$lngDiff>$latDiff?$lngDiff:$latDiff;
    if ($maxDiff < 360 / pow(2, 20)) {
        $zoomLevel = 21;
    } else {
        $zoomLevel = (int) (-1*( (log($maxDiff)/log(2)) - (log(360)/log(2))));
        if ($zoomLevel < 1)
            $zoomLevel = 1;
    }
    $zoomLevel++;
    // print_r($boundingBox);
    //League\Geotools\BoundingBox\BoundingBox Object ( [north:League\Geotools\BoundingBox\BoundingBox:private] => 48.08094 [east:League\Geotools\BoundingBox\BoundingBox:private] => 11.52298 [south:League\Geotools\BoundingBox\BoundingBox:private] => 48.08094 [west:League\Geotools\BoundingBox\BoundingBox:private] => 11.52298 [hasCoordinate:League\Geotools\BoundingBox\BoundingBox:private] => 1 [ellipsoid:League\Geotools\BoundingBox\BoundingBox:private] => League\Geotools\Coordinate\Ellipsoid Object ( [name:protected] => WGS 84 [a:protected] => 6378137 [invF:protected] => 298.257223563 ) [precision:League\Geotools\BoundingBox\BoundingBox:private] => 8 )





    $lineToDraw = new Line('FF0000DD', 2);

    foreach($points as $point) {
        
        $lineToDraw->addPoint(new LatLng($point[0], $point[1]));
    }

    $tileserver = "https://stamen-tiles.a.ssl.fastly.net/terrain/{z}/{x}/{y}.jpg";

    $tileLayer1 = (new TileLayer(
        $tileserver,
        'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.',
        '0123'
    ));
    

    $image = (new OpenStreetMap(new LatLng($middlePoint->getLatitude(), $middlePoint->getLongitude()), $zoomLevel, 800, 800))
        ->addDraw($lineToDraw)
        ->getImage();
        
    
    if ($style==1)  {
        
        $fontsize = 50;
        $bottomBorder = 60;

        $textbounding = $image->writeTextAndGetBoundingBox("Duration: ", '../include/font.ttf', $fontsize, '#00000066', 20 , 800-$fontsize*2-$bottomBorder-20, "left", "top");
        $minutes = floor($stravaActivity["moving_time"]/60);
        $textbounding = $image->writeTextAndGetBoundingBox(intdiv($minutes, 60).':'. ($minutes % 60) . "h", '../include/font.ttf', $fontsize, '#00000066', 380 , 800-$fontsize*2-$bottomBorder-20, "right", "top");
        
        $textbounding = $image->writeTextAndGetBoundingBox('Distance: ', '../include/font.ttf', $fontsize, '#00000066', 20, 800-$fontsize-$bottomBorder, "left", "top");
        $textbounding = $image->writeTextAndGetBoundingBox(floor($stravaActivity["distance"]/1000). "km", '../include/font.ttf', $fontsize, '#00000066',  380, 800-$fontsize-$bottomBorder, "right", "top"); //x: $textbounding["top-right"]["x"]

        $textbounding = $image->writeTextAndGetBoundingBox('Ascend: ', '../include/font.ttf', $fontsize, '#00000066', 420, 800-$fontsize*2-$bottomBorder-20, "left", "top");
        $textbounding = $image->writeTextAndGetBoundingBox($stravaActivity["total_elevation_gain"] . "m", '../include/font.ttf', $fontsize, '#00000066', 780 , 800-$fontsize*2-$bottomBorder-20, "right", "top");

        $textbounding = $image->writeTextAndGetBoundingBox('(/)Speed: ', '../include/font.ttf', $fontsize, '#00000066', 420, 800-$fontsize-$bottomBorder, "left", "top");
        $textbounding = $image->writeTextAndGetBoundingBox(round($stravaActivity["average_speed"],1). "km/h", '../include/font.ttf', $fontsize, '#00000066',  780, 800-$fontsize-$bottomBorder, "right", "top"); //x: $textbounding["top-right"]["x"]
        
    }
   
   
    $image->saveJPG('../images/'.$filename,82);
    
    return true;
}

?>