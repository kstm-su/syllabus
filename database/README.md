# database (データベース更新用スクリプト集)

## 使い方
1. データベースを作る
2. 管理者ユーザーとSELECT専用ユーザーを作る
3. `config.sample.php`を編集して`config.php`にリネーム
4. `update.sh`を実行

## ファイル構成
- classroom.php : 講義室に関するデータを整形
- htmldata.php : 生HTMLを解析して必要な情報を抜き出す
- json.php : レスポンス用JSONを生成
- list.php : シラバス一覧を取得
- raw.php : 一覧からシラバスの生データを取得
- schedule.php : 時限曜日データを整形
- semester.php : 学期データを整形
- summary.php : 検索用にデータを集計
- tables.sql : テーブル初期化SQL
- teacher.php : 講師に関するデータを整形
- textdata.php : 生テキストファイルを解析
- update.sh : 更新用スクリプト

## 注意
- 生データの取得はかなり時間がかかります(１時間程度)
- 消えたシラバス情報があると警告が出ます(だれかエラー処理書いてください)
- シラバス情報システムの構造が変わると使えません
- UNIQUEを設定してINSERTを蹴っているので欠番が頻出します (MySQLの設定で回避可)

## ユーザー権限
- admin: ALL
- guest: SELECT
