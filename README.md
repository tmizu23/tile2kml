tile2kml
======================

Dynamic Google Earth SuperOverlay for Ciber Japan Data 

電子国土の地図タイルをGoogle Earthで表示させるためのプログラムです。


作成例
------
![表示例](https://raw.github.com/tmizu23/tile2kml/master/cjp.jpg)


プログラム
------
- `tile2kml.php`  
 本体です。
- `cjp.kml`  
 このKMLをGoogleErathで開きます。このKMLからtile2kml.phpを呼び出します。
- `tile2kml_prezen.php`  
 TMSタイルを表示させる例です。
- `prezen.kml`  
 TMSタイルを表示させる例です。

設置方法
------
+ tile2kml.phpとcjp.kmlの中の「ユーザー設定」の部分を設置するサーバーのURLに変更してください。
+ tile2kml.phpをサーバーに設置してから、cjp.kmlをGoogle Earthで読み込んでください。

プログラム説明
-------
SuperOverlayの仕組みを使ったKMLを作成すれば、表示エリアの地図タイルだけを呼び出しGoogle Eearthに表示させることができます。
このプログラムは、SuperOverlayのためのKMLを、サーバー側のphpで動的に作成してクライアントに返します。

 
関連情報
--------
- [http://ge-map-overlays.appspot.com/](http://ge-map-overlays.appspot.com/)  
OSM,GoogleMapsなどをGoogleEarthに表示させる例です。
- [https://developers.google.com/kml/documentation/regions?hl=ja](https://developers.google.com/kml/documentation/regions?hl=ja)  
SuperOverlayの説明です。
- [https://developers.google.com/kml/articles/phpmysqlkml?hl=ja](https://developers.google.com/kml/articles/phpmysqlkml?hl=ja)  
phpでkmlを出力する方法です。

ご注意
----------
ダウンロードしたファイルは、動作確認できるようサーバーURLを設定していますが、
実際に使用する際は、自前でサーバーをご用意ください。
動作確認用のサーバーにアクセスが集中し支障が出た場合は、サーバーをストップします。


ライセンス
----------
The MIT License
(著作権表示および無保証ということでお願いします。)
