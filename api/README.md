# API仕様書

## データ型
### 一般的なデータ型
- Integer : 整数
- Double : 小数
- Boolean : ブール値
- String : 文字列

### NULL
型の後ろに"?"のついているものは、NULLの可能性があります。

### 配列
型を[]で囲っているものは、配列を表します。

### Syllabus型
検索APIの返り値のデータ構造です。

| フィールド名 | データ型 | 値の説明 |
|--------------|----|----------|
| id | Integer | シラバスID |
| year | Integer | 開講年度 |
| department | String | 開講部局 |
| department_code | String | 開講学部コード |
| code | String? | 履修コード |
| query | String | 公式シラバス表示用クエリ |
| title | String | 授業名 |
| title_english | String? | 授業名(英語) |
| semester | String | 開講学期 |
| schedule | [Schedule] | 曜日時限の配列 |
| classroom | [Room] | 教室 |
| credit | Double | 単位数 |
| target | String? | 対象学生 |
| style | String? | 授業形態 |
| note | String? | 備考 |
| public | Boolean | 市民開放授業 |
| ches | Boolean | 県内大学履修科目 |

### Schedule型
曜日時限の情報です。

| フィールド名 | データ型 | 値の説明 |
|--------------|----|----------|
| day | Integer? | 曜日を表す数値(日=0, 月=1, 火=2, 水=3, 木=4, 金=5, 土=6) |
| period | Integer? | 時限 |
| early | Boolean | 前半 |
| late | Boolean | 後半 |
| intensive | Boolean | 集中 |
| irregular | Boolean | 不定 |

### Room型
教室の情報です。

| フィールド名 | データ型 | 値の説明 |
|--------------|----|----------|
| place | String | 開講場所 |
| place_code | String | 開講場所部局コード |
| name | String | 教室名 |

## シラバス検索API
シラバスを検索して、該当するシラバスの概要を取得します。
パス: /api/search.php

### リクエストパラメータ
検索APIには下記の項目を指定できます。

| フィールド名 | データ型 | 検索タイプ | 値の説明 |
|--------------|----------|------------|----------|
| id | Integer | equal | シラバスID |
| year | Integer | range | 開講年度 |
| code | String | like | 履修コード |
| department_id | Integer | equal | 開講部局ID |
| department_code | String | equal | 開講部局コード |
| department | String | like | 開講部局名 |
| credit | Double | range | 単位数 |
| teacher_id | Integer | equal | 教員ID |
| teacher | String | like | 教員名 |
| semester_id | Integer | equal | 学期ID |
| semester | String | semester | 学期区分 |
| room_id | Integer | equal | 教室ID |
| room | String | like | 教室名 |
| place_id | Integer | equal | 開講場所の部局ID |
| place_code | String | equal | 開講場所の部局コード |
| place | String | like | 開講場所 |
| title | String | like | 授業名 |
| word | String | like | 全文検索 |
| schedule | String | schedule | 曜日時限 |
| public | Boolean | equal | 市民開放授業 |
| ches | Boolean | equal | 県内大学履修科目 |
| intensive | Boolean | equal | 集中講義 |
| offset | Integer | page | 何個目の結果から返すか |
| count | Integer | page | 返す結果の個数 |

#### 検索タイプについて
- equal : 一致するもののみを検索。
- range : 範囲を指定して検索。
          数値 もしくは "[数値]..[数値]" の書式が使用できる。
          .. を使う場合は片方の[数値]は省略可。
- like : 含まれているものを検索。
- schedule : 曜日と時限の指定をする。
             曜日を表すアルファベット2文字(日=SU, 月=MO, 火=TU, 水=WE, 木=TH, 金=FR, 土=SA)と
             時限を表す数字(1-)を組み合わせる。(例:月曜2限 => MO2)
             曜日時限の設定されていない授業を検索する場合はNULLを指定する。
- semester : 学期区分を指定する。
             指定できるのは、前期=first 後期=second 通年=full その他=other の4つ。
- page : ページ制御を設定。デフォルト値は offset=0, count=100。

#### リクエストの処理手順
1. 同じパラメータにスペースが含まれる場合は、スペースで区切ってAND検索
2. 配列形式の同じ名前のパラメータはOR検索
3. 違う名前のパラメータはAND検索

### レスポンス
| フィールド名 | データ型 | 値の説明 |
|--------------|----|----------|
| syllabus | [Syllabus] | シラバスデータの配列 |
| offset | Integer | 開始点 |
| count | Integer | 返す結果の個数 |
