<?php
include $basedir.'/vendor/autoload.php';

use \DantSu\OpenStreetMapStaticAPI\OpenStreetMap;
use \DantSu\OpenStreetMapStaticAPI\LatLng;
use \DantSu\OpenStreetMapStaticAPI\Line;
use \DantSu\OpenStreetMapStaticAPI\Markers;
use \DantSu\OpenStreetMapStaticAPI\TileLayer;
use DantSu\PHPImageEditor\Image;

// todo: exceptions!
function saveRouteToImage($stravaActivity, $filename, $mapstyle=1, $style=1):bool {
    //error_log(print_r($stravaActivity, true));
    $route = $stravaActivity->summary_polyline;

    $points = Polyline::decode($route);
    $points = Polyline::pair($points);

    // get bounding box
    $polygon = new \League\Geotools\Polygon\Polygon(
        $points
    );
    $boundingBox = $polygon->getBoundingBox();


    // calcualte middle point of map
    $geotools   = new \League\Geotools\Geotools();
    $southWest = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getSouth(), $boundingBox->getWest()]);
    $northEast = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getNorth(), $boundingBox->getEast()]);
    $coordA   = $southWest;
    $coordB   = $northEast;
    $vertex    =  $geotools->vertex()->setFrom($coordA)->setTo($coordB);
    $middlePoint = $vertex->middle(); // \League\Geotools\Coordinate\Coordinate

    /*
    find out maximum distnace in m opf bounding box
    divide this by pixel per tile to find optimal tile size (zoom)
    */
    $coordA   = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getNorth(), $boundingBox->getWest()]);
    $coordB   = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getNorth(), $boundingBox->getEast()]);
  
    $distanceEastWest = $geotools->distance()->setFrom($coordA)->setTo($coordB);
  
    $coordA   = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getNorth(), $boundingBox->getWest()]);
    $coordB   = new \League\Geotools\Coordinate\Coordinate([$boundingBox->getSouth(), $boundingBox->getWest()]);
  
    $distanceNorthSouth = $geotools->distance()->setFrom($coordA)->setTo($coordB);

$longerDistance = $distanceEastWest->flat()>$distanceNorthSouth->flat()?$distanceEastWest->flat():$distanceNorthSouth->flat();
error_log($longerDistance);
//$longerDistance+2000;
// array meter pro pixel from 16 down to 10
$mProPixel = [3,5,10,19,38,76,152];
$i = 0;
while ($longerDistance/$mProPixel[$i]>600) {
    $i++;
    error_log($longerDistance/$mProPixel[$i]);
}
$zoomLevel = 16-$i;

// ein zooml;even weiter raus malen auf 1000x1000, damnn croppen auf bounding + 20px oder so und dann runterskalieren auf 800 falls größer

    // calculate zoom

    // TODO: is this right??


    // $zoomLevel = 12;
    // $latDiff = $northEast->getLatitude() - $southWest->getLatitude();
    // $lngDiff = $northEast->getLongitude() - $southWest->getLongitude();
    
    // $maxDiff=$lngDiff>$latDiff?$lngDiff:$latDiff;
    // if ($maxDiff < 360 / pow(2, 20)) {
    //     $zoomLevel = 21;
    // } else {
    //     $zoomLevel = (int) (-1*( (log($maxDiff)/log(2)) - (log(360)/log(2))));
    //     if ($zoomLevel < 1)
    //         $zoomLevel = 1;
    // }
    // $zoomLevel+=2;
    // print_r($boundingBox);
    //League\Geotools\BoundingBox\BoundingBox Object ( [north:League\Geotools\BoundingBox\BoundingBox:private] => 48.08094 [east:League\Geotools\BoundingBox\BoundingBox:private] => 11.52298 [south:League\Geotools\BoundingBox\BoundingBox:private] => 48.08094 [west:League\Geotools\BoundingBox\BoundingBox:private] => 11.52298 [hasCoordinate:League\Geotools\BoundingBox\BoundingBox:private] => 1 [ellipsoid:League\Geotools\BoundingBox\BoundingBox:private] => League\Geotools\Coordinate\Ellipsoid Object ( [name:protected] => WGS 84 [a:protected] => 6378137 [invF:protected] => 298.257223563 ) [precision:League\Geotools\BoundingBox\BoundingBox:private] => 8 )





    $lineToDraw = new Line('FF0000DD', 6);

    foreach($points as $point) {
        $lineToDraw->addPoint(new LatLng($point[0], $point[1]));
    }
    $tileserver = "https://stamen-tiles.a.ssl.fastly.net/terrain/{z}/{x}/{y}.png";
  // $tileserver = "https://{a|b|c}.tile.opentopomap.org/{z}/{x}/{y}.png";
    $tileLayer1 = (new TileLayer(
        $tileserver,
        'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.',
        '0123'
    ));
    



//todo: find max boundiung rect and trim image
        $osm =new OpenStreetMap(new LatLng($middlePoint->getLatitude(), $middlePoint->getLongitude()), $zoomLevel, 1000, 1000, $tileLayer1);
    $image = ($osm)
    ->addDraw($lineToDraw)
        ->getImage();
        //->crop(600,600);
        

   
    //$northEast->getLatitude() - $southWest->getLatitude();
    //$lngDiff = $northEast->getLongitude() - $southWest->getLongitude();

    $northEastXY = $osm->getMapData()->convertLatLngToPxPosition(new LatLng($northEast->getLatitude(),$northEast->getLongitude()));
    error_log("xy ".print_r($northEastXY,true));
    $southWestXY = $osm->getMapData()->convertLatLngToPxPosition(new LatLng($southWest->getLatitude(),$southWest->getLongitude()));
    error_log("xy ".print_r($southWestXY,true));
    
    $maxSizes = [$northEastXY->getX(),$northEastXY->getY(),$southWestXY->getX(),$southWestXY->getY()];
    $maxSize = max($maxSizes);
    error_log($maxSize);

    $imageSize = 600;

    $image->crop($maxSize+10,$maxSize+10)
    ->resize($imageSize,$imageSize);
    
    
   
    if ($style==1)  {
        

        
        $margin = 5;
        $attribution = function (Image $image, $margin): array {
            return $image->writeTextAndGetBoundingBox(
               
                       'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.',
                  
                '../include/font.ttf',
                10,
                '7878A8',
                $margin,
                $margin,
                Image::ALIGN_LEFT,
                Image::ALIGN_TOP
            );
        };

        $bbox = $attribution(Image::newCanvas(1, 1), $margin);
        $imageAttribution = Image::newCanvas($bbox['bottom-right']['x'] + $margin, $bbox['bottom-right']['y'] + $margin);
        $imageAttribution->drawRectangle(0, 0, $imageAttribution->getWidth(), $imageAttribution->getHeight(), 'FFFFFF33');
        $attribution($imageAttribution, $margin);

        $image->pasteOn($imageAttribution, Image::ALIGN_LEFT, Image::ALIGN_TOP);



        $fontsize = 40;
        $smallfontsize = 20;
        $border = 10;
        // $textcolor = '#288FBA05';
        // $textcolor = '#DC772E05';
        $textcolor = '#00000033';

        $textbounding = $image->writeTextAndGetBoundingBox("Duration: ", '../include/font.ttf', $fontsize, $textcolor, $border , $imageSize-$fontsize*2-$border*2, "left", "top");
        $minutes = $stravaActivity->movingTime;
        $textbounding = $image->writeTextAndGetBoundingBox(intdiv($minutes, 60).':'. ($minutes % 60) , '../include/font.ttf', $fontsize, $textcolor, $imageSize/2-$border*4 , $imageSize-$fontsize*2-$border*2, "right", "top");
        
        $textbounding = $image->writeTextAndGetBoundingBox("h" , '../include/font.ttf', $smallfontsize, $textcolor, $textbounding["bottom-right"]["x"] , $textbounding["bottom-right"]["y"], "left", "bottom");
        

        $textbounding = $image->writeTextAndGetBoundingBox('Distance: ', '../include/font.ttf', $fontsize, $textcolor, $border, $imageSize-$fontsize-$border, "left", "top");
        $textbounding = $image->writeTextAndGetBoundingBox(floor($stravaActivity->distance), '../include/font.ttf', $fontsize, $textcolor,  $imageSize/2-$border*4, $imageSize-$fontsize-$border, "right", "top"); //x: $textbounding["top-right"]["x"]

        $textbounding = $image->writeTextAndGetBoundingBox("km" , '../include/font.ttf', $smallfontsize, $textcolor, $textbounding["bottom-right"]["x"] , $textbounding["bottom-right"]["y"], "left", "bottom");
        

        $textbounding = $image->writeTextAndGetBoundingBox('Ascend: ', '../include/font.ttf', $fontsize, $textcolor, $imageSize/2+$border/2, $imageSize-$fontsize*2-$border*2, "left", "top");
        $textbounding = $image->writeTextAndGetBoundingBox($stravaActivity->elevationGain , '../include/font.ttf', $fontsize, $textcolor, $imageSize-$border*4 , $imageSize-$fontsize*2-$border*2, "right", "top");


        $textbounding = $image->writeTextAndGetBoundingBox("m" , '../include/font.ttf', $smallfontsize, $textcolor, $textbounding["bottom-right"]["x"] , $textbounding["bottom-right"]["y"], "left", "bottom");
        
        $textbounding = $image->writeTextAndGetBoundingBox('avg. Speed: ', '../include/font.ttf', $fontsize, $textcolor, $imageSize/2+$border/2,$imageSize-$fontsize-$border, "left", "top");
        $textbounding = $image->writeTextAndGetBoundingBox($stravaActivity->averageSpeed , '../include/font.ttf', $fontsize, $textcolor,  $imageSize-$border*4, $imageSize-$fontsize-$border, "right", "top"); //x: $textbounding["top-right"]["x"]
        

        $textbounding = $image->writeTextAndGetBoundingBox("kmh" , '../include/font.ttf', $smallfontsize, $textcolor, $textbounding["bottom-right"]["x"] , $textbounding["bottom-right"]["y"], "left", "bottom");
        

    }


    $image->saveJPG('../images/'.$filename,90);
    
    return true;
}

?>