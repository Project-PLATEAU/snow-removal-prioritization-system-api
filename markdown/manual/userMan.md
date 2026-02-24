# Web API仕様書

# 1 本書について

本書では、除雪優先度算出システム用のWeb APIの使い方について記載しています。

# 2 APIの使い方

除雪優先度算出システム用のWeb APIは以下のような形式でアクセスできます。

```
<システムURL>/api/<パラメータ>
```

ここで、`<システムURL>`は本システムにアクセスするためのURL（例えば、`https://data.jyosetsu-yusendo.jp`）です。\
取得されるデータは以下に示す`<パラメータ>`によって決定します。\
以降は`.../api/...`として表記します。


## 2-1 PLATEAU VIEW向けエクスポートデータの取得

PLATEAU VIEWのMyDataにインポート可能なCZMLデータを取得します。

**URL：**\
`.../api/get_plateau_data`

**メソッド：**\
`GET`

**クエリパラメータ：**

| パラメータ | 説明 | 形式 |
| - | - | - |
| kind | データの種類 | SnowRemovalPriority: 除雪優先度<br>SnowLoad: 積雪重量 |
| lon1<br>lon2<br>lat1<br>lat2 | 取得範囲 | 度単位 |
| time | 取得データの時間 | YYYYMMDDHH00 |

**使用例：**
```
.../api/get_plateau_data?kind=SnowRemovalPriority&lon1=138.83276&lon2=138.869239&lat1=37.4407&lat2=37.4553&time=202602171800
```

## 2-2 QGIS向けエクスポートデータの取得

QGISにインポート可能なGeoJSONデータ（全範囲）を取得します。

**URL：**\
`.../api/get_qgis_data`

**メソッド：**\
`GET`

**クエリパラメータ：**

| パラメータ | 説明 | 形式 |
| - | - | - |
| time | 取得データの時間 | YYYYMMDDHH00 |

**使用例：**
```
.../api/get_qgis_data?time=202602171800
```

## 2-3 フィードバック情報の送信

表示システムによって送信されたフィードバック情報をデータベースに保存します。

**URL：**\
`.../api/send_field_reports`

**メソッド：**\
`POST`

**クエリパラメータ：**

| パラメータ | 説明 | 形式 |
| - | - | - |
| username | ユーザー名 | 文字列 |
| email | メールアドレス | 文字列 |
| feedback | フィードバックの内容 | 文字列 |


## 2-4 アラート通知設定情報の送信

表示システムによって送信されたアラート通知設定情報をデータベースに保存します。

**URL：**\
`.../api/submit_alert`

**メソッド：**\
`POST`

**クエリパラメータ：**

| パラメータ | 説明 | 形式 |
| - | - | - |
| username | ユーザー名 | 文字列 |
| email | メールアドレス | 文字列 |
| bld_id | 建物ID | 文字列 |
| bld_name | 建物の名前 | 文字列 |
| alertStopCheck | 登録済みの設定を削除するかどうかフラグ | 0 or 1 |


## 2-5 演算データの取得方法

演算システムによって作成された分布画像や建物のデータは、APIを通さず、URLで直接にアクセスして取得します。各種データへの取得方法は以下の通りです。\
なお、以下のYYYY, MM, DD, HHは、取得するデータの年、月、日、時間を示します。

**分布画像（積雪深）**
```
.../data/png/sd/YYYY/MM/sd_YYYYMMDDHH00.png
```

**分布画像（積雪重量）**
```
.../data/png/sw/YYYY/MM/sw_YYYYMMDDHH00.png
```

**建物の形状、積雪重量、除雪優先度のデータ**
```
.../data/bld_components/YYYY/MM/YYYYMMDDHH00_area_zoom.geojson
```
ここで、areaは地域の名前（nagaoka, tochio）、zoomはズームレーベル（1,2,3）です。

