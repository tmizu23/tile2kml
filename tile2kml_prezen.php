<?php
mb_internal_encoding("UTF-8");
/*******************************************************************************
# The MIT License
#
#  Copyright (c) 2012, Takayuki Mizutani ECORIS Inc.
# 
#  Permission is hereby granted, free of charge, to any person obtaining a
#  copy of this software and associated documentation files (the "Software"),
#  to deal in the Software without restriction, including without limitation
#  the rights to use, copy, modify, merge, publish, distribute, sublicense,
#  and/or sell copies of the Software, and to permit persons to whom the
#  Software is furnished to do so, subject to the following conditions:
# 
#  The above copyright notice and this permission notice shall be included
#  in all copies or substantial portions of the Software.
# 
#  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
#  OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
#  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
#  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
#  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
#  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
#  DEALINGS IN THE SOFTWARE.
********************************************************************************/

//x,y,zはもとのタイル座標値。TMS=0：XYZ,TMS=1：TMS。cは地図の種類（使用しない場合もある）
$x = $_GET['x'];
$y = $_GET['y'];
$z = $_GET['z'];
$c = $_GET['c'];

/*************** ユーザー設定  *************************/

//          このプログラムを設置するパス

$phpurl = 'http://www.ecoris.co.jp/map/tile2kml_prezen.php';

/********************************************************/

///////////////////////////////地図設定/////////////////////////


//XYZやTMSの場合はこちら
$minx = 139.238869038526;
$maxx = 139.463447403723;
$miny = 37.4356075646937;
$maxy = 37.613718539038;
$minz = 5;
$maxz = 16;
$TMS = 1;
$ext = '.png';
$baseurl = 'http://www.ecoris.co.jp/map/data/prezen/slide/';
$url = $baseurl . $z . '/' . $x . '/' . $y . $ext;

///////////////////////地図設定ここまで//////////////////////////////////////////

$tileSize = 256;
$originShift = 2 * pi() * 6378137 / 2.0;
$initialResolution = 2 * pi() * 6378137 / $tileSize;

function Resolution($zoom){
	global $initialResolution;
	return $initialResolution / pow(2,$zoom);
}

function TMS_XYZ($tx, $ty, $zoom){
	return array($tx, (pow(2,$zoom) - 1) - $ty);
}
function PixelsToMeters($px, $py, $zoom){
	global $originShift;
	$res = Resolution($zoom);
	$mx = $px * $res - $originShift;
	$my = $py * $res - $originShift;
	return array($mx, $my);
}
function TileBounds($tx, $ty, $zoom){
	global $tileSize;
	list($minx, $miny) = PixelsToMeters( $tx*$tileSize, $ty*$tileSize, $zoom );
	list($maxx, $maxy) = PixelsToMeters( ($tx+1)*$tileSize, ($ty+1)*$tileSize, $zoom );
	return array($minx, $miny, $maxx, $maxy);
}
function MetersToLatLon($mx, $my ){
	global $originShift;
	$lon = ($mx / $originShift) * 180.0;
	$lat = ($my / $originShift) * 180.0;
	$lat = 180 / pi() * (2 * atan( exp( $lat * pi() / 180.0)) - pi() / 2.0);
	return array($lat, $lon);
}
function TileLatLonBounds($tx, $ty, $zoom ){
	$bounds = TileBounds($tx, $ty, $zoom);
	list($minLat, $minLon) = MetersToLatLon($bounds[0], $bounds[1]);
	list($maxLat, $maxLon) = MetersToLatLon($bounds[2], $bounds[3]);
	return array( $minLat, $minLon, $maxLat, $maxLon );
}


//cx,cy,czはTMS座標。内部ではTMS座標で計算
$cx = $x;
$cy = $y;
$cz = $z;
if($TMS==0){//XYZの場合TMSに変換
 list($cx,$cy) = TMS_XYZ($x,$y,$z);
}
//タイルの四隅の緯度経度を計算
$bounds = TileLatLonBounds($cx, $cy, $cz );

//Create KML
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://www.opengis.net/kml/2.2">';
$kml[] = ' <Document>';

/*
//タイル情報を出力しない場合
$kml[] = ' <Style>';
$kml[] = ' <ListStyle id="hideChildren">';
$kml[] = ' <listItemType>checkHideChildren</listItemType>';
$kml[] = ' </ListStyle>';
$kml[] = ' </Style>';
*/
$kml[] = ' <Region>';
$kml[] = ' <Lod>';
$kml[] = ' <minLodPixels>128</minLodPixels>';
$kml[] = ' <maxLodPixels>2048</maxLodPixels>';
$kml[] = ' </Lod>';
$kml[] = ' <LatLonAltBox>';
$kml[] = ' <north>' . $bounds[2] . '</north>';
$kml[] = ' <south>' . $bounds[0] . '</south>';
$kml[] = ' <east>' . $bounds[3] . '</east>';
$kml[] = ' <west>' . $bounds[1] . '</west>';
$kml[] = ' </LatLonAltBox>';
$kml[] = ' </Region>';

//This Zoom Image
//対象範囲以外は画像を表示させない。
if(($minz<=$z && $z <=$maxz && $miny<$bounds[2] && $bounds[0]<$maxy && $minx < $bounds[3] && $bounds[1] < $maxx)){
 $kml[] = ' <GroundOverlay>';
 $kml[] = ' <drawOrder>'. $z .'</drawOrder>';
 $kml[] = ' <Icon>';
 $kml[] = ' <href>' . $url .'</href>';
 $kml[] = ' </Icon>';
 $kml[] = ' <LatLonBox>';
 $kml[] = ' <north>' . $bounds[2] . '</north>';
 $kml[] = ' <south>' . $bounds[0] . '</south>';
 $kml[] = ' <east>' . $bounds[3] . '</east>';
 $kml[] = ' <west>' . $bounds[1] . '</west>';
 $kml[] = ' </LatLonBox>';
 $kml[] = ' </GroundOverlay>';
}

if($z<$maxz){
 //Next Zoom Link
 for($i=0;$i<=1;$i++){
	for($j=0;$j<=1;$j++){
		$nx = 2*$cx + $i;
		$ny = 2*$cy + $j;
		$nz = $cz + 1;
		$bounds = TileLatLonBounds($nx, $ny, $nz );
		if($TMS==0){
			list($nx,$ny) = TMS_XYZ($nx,$ny,$nz);
		}
		$kml[] = ' <NetworkLink>';
		$kml[] = ' <name>' . $nz . '-' . $nx . '-' . $ny . '</name>';
		$kml[] = ' <Region>';
		$kml[] = ' <Lod>';
		$kml[] = ' <minLodPixels>128</minLodPixels>';
		$kml[] = ' <maxLodPixels>-1</maxLodPixels>';
		$kml[] = ' </Lod>';
		$kml[] = ' <LatLonAltBox>';
		$kml[] = ' <north>' . $bounds[2] . '</north>';
		$kml[] = ' <south>' . $bounds[0] . '</south>';
		$kml[] = ' <east>' . $bounds[3] . '</east>';
		$kml[] = ' <west>' . $bounds[1] . '</west>';
		$kml[] = ' </LatLonAltBox>';
		$kml[] = ' </Region>';
		$kml[] = ' <Link>';
		$kml[] = ' <href>' . $phpurl . '</href>';
		$kml[] = ' <httpQuery>x='. $nx . '&amp;y=' . $ny . '&amp;z=' . $nz . '&amp;c=' . $c .'</httpQuery>';
		$kml[] = ' <viewRefreshMode>onRegion</viewRefreshMode>';
		$kml[] = ' <viewFormat/>';
		$kml[] = ' </Link>';
		$kml[] = ' </NetworkLink>';
	}
 }
}
// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;

/*
//kml出力。デバッグ用
$fp = fopen($z ."-". $x ."-". $y .".kml", "w");
@fwrite( $fp, $kmlOutput);
fclose($fp);
*/

?>