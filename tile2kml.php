<?php
mb_internal_encoding("UTF-8");

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


////メイン////////
//x,y,zはもとのタイル座標値。t=0：XYZ,t=1：TMS。cは地図の種類
$x = $_GET['x'];
$y = $_GET['y'];
$z = $_GET['z'];
$t = $_GET['t'];
$c = $_GET['c'];
//cx,cy,czはTMS座標。内部ではTMS座標で計算
$cx = $x;
$cy = $y;
$cz = $z;
if($t==0){//XYZの場合TMSに変換
 list($cx,$cy) = TMS_XYZ($x,$y,$z);
}

//XYZやTMSの場合はこちら
//$ext = '.png';
//$baseurl = 'http://www.ecoris.co.jp/map/data/geohex/';
//$url = $baseurl . $z . '/' . $x . '/' . $y . $ext;

//電子国土用
$ext = '.png';
if($c==0){
 if($z<9){
  $baseurl = 'http://cyberjapandata.gsi.go.jp/sqras/all/JAIS/latest/';
 }elseif($z<12){
  $baseurl = 'http://cyberjapandata.gsi.go.jp/sqras/all/BAFD1000K/latest/';
 }elseif($z<15){
  $baseurl = 'http://cyberjapandata.gsi.go.jp/sqras/all/BAFD200K/latest/';
 }elseif($z<18){
  $baseurl = 'http://cyberjapandata.gsi.go.jp/sqras/all/DJBMM/latest/';
 }elseif($z<19){
  $baseurl = 'http://cyberjapandata.gsi.go.jp/sqras/all/FGD/latest/';
 }
}elseif($c==1){
 if($z<9){
  $baseurl = 'http://cyberjapandata.gsi.go.jp/sqras/all/RELIEF/latest/';
 }elseif($z<12){
  $baseurl = 'http://cyberjapandata2.gsi.go.jp/sqras/all/BAFD1000KG/latest/';
 }elseif($z<15){
  $baseurl = 'http://cyberjapandata2.gsi.go.jp/sqras/all/BAFD200KG/latest/';
 }elseif($z<18){
  $baseurl = 'http://cyberjapandata.gsi.go.jp/sqras/all/DJBMO/latest/';
  $ext = '.jpg';
 }elseif($z<19){
  $baseurl = 'http://cyberjapandata.gsi.go.jp/sqras/all/FGD/latest/';
 }
}

$xpad = sprintf("%07d", $x);
$ypad = sprintf("%07d", $y);
$xystr = substr($xpad,0,1).substr($ypad,0,1).'/'.substr($xpad,1,1).substr($ypad,1,1).'/'.substr($xpad,2,1).substr($ypad,2,1).'/'.substr($xpad,3,1).substr($ypad,3,1).'/'.substr($xpad,4,1).substr($ypad,4,1).'/'.substr($xpad,5,1).substr($ypad,5,1);
$url = $baseurl . $z . '/' . $xystr . '/' . $xpad . $ypad . $ext;
////ここまで電子国土用

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
//対象範囲以外は画像を表示させない。(↓は電子国土の場合。レベル19以降も表示しないことにしてある)
if((5<$z && $z <18 && 16<=$bounds[0] && $bounds[2]<=48 && 121 <= $bounds[1] && $bounds[3] <= 158)|| ($z==5 && 26<=$x && $x<=29 && 11<=$y && $y<=14)){
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

if($z<18){
 //Next Zoom Link
 for($i=0;$i<=1;$i++){
	for($j=0;$j<=1;$j++){
		$nx = 2*$cx + $i;
		$ny = 2*$cy + $j;
		$nz = $cz + 1;
		$bounds = TileLatLonBounds($nx, $ny, $nz );
		if($t==0){
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
		$kml[] = ' <href>http://www.ecoris.co.jp/map/otm/tile2kml.php</href>';
		$kml[] = ' <httpQuery>x='. $nx . '&amp;y=' . $ny . '&amp;z=' . $nz . '&amp;t=' . $t . '&amp;c=' . $c .'</httpQuery>';
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