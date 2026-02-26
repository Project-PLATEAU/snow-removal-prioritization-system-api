# 環境構築手順書

# 1 本書について

本書では、除雪優先度算出システム（Web API）の利用環境構築手順について記載しています。

> [!NOTE]
> 除雪優先度算出システムは3つのリポジトリに分割して格納しています。インストールは以下の順序で行います。
> * [snow-removal-prioritization-system](https://github.com/Project-PLATEAU/snow-removal-prioritization-system)　（表示システム）
> * [snow-removal-prioritization-system-data](https://github.com/Project-PLATEAU/snow-removal-prioritization-system-data) （データ演算）
> * snow-removal-prioritization-system-api　（Web API、本リポジトリ）

# 2 動作環境

本システムの動作環境は以下のとおりです。

| 項目 | ソフトウェア | バージョン |
| - | - | - |
| OS | Linuxなど、Webサーバーソフトが構築できるOS<br>本書ではUbuntu 24.04を基に説明します | - |
| WEBサーバー | Apache | 2.4.58 |
| スクリプト言語 | PHP | 8.3.6 |
| データベース | MySQL | 8.0.45 |
| バージョン管理 | git | 2.43.0 |
| PHPパッケージ管理 | composer | 2.9.5 |
| スクリプト言語 | Python<br>必要なモジュール：<br>annotated-types 0.7.0<br>geojson 3.2.0<br>numpy 2.3.2<br>pydantic 2.11.7<br>pydantic_core 2.33.2<br>python-dateutil 2.9.0.post0<br>six 1.17.0<br>typing-inspection 0.4.1<br>typing_extensions 4.14.1<br>以下のディレクトリに仮想環境を作成する<br>`/var/www/html/api/scripts/venv` | 3.12.3 |

# 3 設定手順

## 3-1 前準備

本システムは、データをデータベースに保存する部分があるため、データを格納するデータベースを作成し、本システムから当該データベースへ接続するための認証情報を持つアカウントを作成します。これらの操作は、サーバー側のターミナルから実行します。\
なお、以下のコマンド内の`plateau_user_password`には、任意のパスワードを設定してください。

```
sudo mysql -u root -p

mysql> CREATE DATABASE plateau;
mysql> CREATE USER 'plateau_user'@'localhost' IDENTIFIED BY 'plateau_user_password';
mysql> GRANT SELECT, INSERT, UPDATE, DELETE, CREATE ON plateau.* TO 'plateau_user'@'localhost';
mysql> EXIT;
```

WEBサーバーのドキュメントルートに本システム用のディレクトリ及びURLで取得できるデータ用のディレクトリを以下のコマンドで作成します。\
`user`の代わりに、OSのユーザー名を設定してください。

```
cd /var/www/html
sudo mkdir api
sudo mkdir data
sudo chown user:user api
sudo chown user:user data
```

## 3-2 ソースファイルのダウンロード

本システム用のディレクトリにソースファイルを以下のコマンドでダウンロードし、必要な設定を行います。

```
cd /var/www/html/api
git clone --branch main --single-branch https://github.com/Project-PLATEAU/snow-removal-prioritization-system-api.git .
```

以下のコマンドで必要な外部ライブラリ（パッケージ）を一括ダウンロード・インストールします。

```
cd /var/www/html/api
composer install
```

## 3-3 環境設定

以下のコマンドで環境設定ファイルを作成します。

```
cd /var/www/html/api
cp .env.example　.env
```

`.env`をテキストエディターで開き、設定を行います。データベースの設定は3-1にて設定した値を記入します。また、演算システムによって作成されるデータへのパスは、`DATA_DIR`に設定します。

```
DB_NAME=plateau
DB_USER=plateau_user
DB_PASS=plateau_user_password
DATA_DIR=/mnt/disk/data

# PLATEAU VIEW向けデータを作成するスクリプトのディレクトリ
SCRIPT_PATH=/var/www/html/api/scripts
```

以下のコマンドで必要なPython仮想環境を作成します。

```
cd /var/www/html/api/scripts/
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

## 3-4 データディレクトリの準備

以下のスクリプトを実行すると、URLで取得できるデータ用のディレクトリに演算データへのリンク及び凡例情報ファイルが作成されます。

```
cd /var/www/html/api/setup
bash data_dir_setup.sh data_dir
```

ここで`data_dir`は演算データのディレクトリ（例えば、`/mnt/disk/data`）を示します。
