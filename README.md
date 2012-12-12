tile2kml
======================

Dynamic Google Earth SuperOverlay for Ciber Japan Data 

電子国土の地図タイルをGoogle Earthで表示させるためのプログラムです。
SuperOverlayの仕組みを使ったKMLを作成すれば、google mapsのように
表示エリアの地図タイルだけを呼びだし、Google Eearthに表示させることができます。

このプログラムは、SuperOverlayのためのKMLをサーバー側のphpで動的に作成してクライアントに返します。

作成例
------
![表示例](https://raw.github.com/tmizu23/tile2kml/blob/master/cjp.jpg)


プログラム
------
- `tile2kml.php`  
 本体です。
- `cjp.kml`  
 このKMLをGoogleErathで開きます。このKMLからtile2kml.phpを呼び出します。

 
関連情報
--------
[http://ge-map-overlays.appspot.com/](OSM,GoogleMapsなどの例)
[https://developers.google.com/kml/documentation/regions?hl=ja](SuperOverlayの説明)
[https://developers.google.com/kml/articles/phpmysqlkml?hl=ja](phpでkmlを出力する方法)


ライセンス
----------
The MIT License
(著作権表示および無保証ということでお願いします。)
