Syllabus Calendar
=================

# セットアップ
 - `$ git clone https://github.com/google/google-api-php-client`
 - GoogleDeveloperConsoleからclient\_secret.jsonをもらってくる。
 - コールバックは `'http://' . [シラバスへのパス] . '/cal/index.php'`がおすすめ(to do 設定ファイルに書けるようにする)

# 使い方
`http:// [シラバスへのパス] /cal/`を叩くとOAuthに飛びます。ログインするとかなーり昔のイベントの名前が列挙されます。(今のところそれだけ)

