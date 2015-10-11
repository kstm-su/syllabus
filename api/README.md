# API仕様書

## シラバス検索API
シラバスを検索して、該当するシラバスの概要を取得します。
パス: /api/search.php

### リクエストパラメータ
| パラメータ名 | 型 | 説明 |
|--------------|----|------|
| id | ID | シラバスのID |
| year | Number | 開講年度 |
| code | String | 履修コード |
| department_id | ID | 開講部局ID |
| department_code | Code | 開講部局コード |
| department | Number | 開講部局名 |
| credit | Number | 単位数 |
| teacher_id | ID | 教員ID |
| teacher | String | 教員名 |
| semester_id | ID | 学期ID |
| semester | Semester | 学期区分 |
| room_id | ID | 教室ID |
| room | String | 教室名 |
| place_id | ID | 開講場所ID |
| place_code | Code | 開講場所コード |
| place | String | 開講場所 |
| title | String | 授業名 |
| word | String | 全文検索 |
| schedule | Schedule | 曜日時限 |
| public | Boolean | 市民開放授業 |
| ches | Boolean | 県内大学履修科目 |
| intensive | Boolean | 集中講義 |
| offset | Integer | 何個目の結果から返すか |
| count | Integer | 返す結果の個数 |

#### パラメータの型
- ID : 一致している整数 (例. 1, 100, 1000)
- Number : 範囲を指定できる数値 (例. 1, 2..3, 10.., ..100)
- Code : 一致している文字列 (例. T, A, MA)
- String : あいまい検索
- Schedule : 曜日(SU,MO,TU,WE,TH,FR,SA)と時限(数値)
- Semester : 学期(first:前期, second:後期, fullyear:通年, other:その他)
- Boolean : 0か1
- Integer : 整数

#### リクエストの処理
1. 同じパラメータにスペースが含まれる場合は、スペースで区切ってAND検索
2. 配列形式の同じ名前のパラメータはOR検索
3. 違う名前のパラメータはAND検索

### レスポンス
| フィールド名 | 型 | 値の説明 |
|--------------|----|----------|
| syllabus | [Syllabus] | シラバスデータの配列 |
| offset | Integer | 開始点 |
| count | 返す結果の個数 |

#### Syllabus型
