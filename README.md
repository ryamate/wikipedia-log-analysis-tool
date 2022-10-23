# Wikipedia ログ解析ツール

Wikipedia のアクセスログの情報である pageviews ファイルを利用して、アクセスログを解析するツールです。

アクセスログには、読者がウェブページを読み込むたびにどのページが読み込まれたかが記録されています。これらの情報を使って、サイトのユーザーの活動に関する質問に答えるシステムを作成します。

## Wikipedia のアクセスログのデータについて

Wikipedia のアクセスログのデータは下記 URL からダウンロードします。

- [Index of /other/pageviews/2021/2021-12/](https://dumps.wikimedia.org/other/pageviews/2021/2021-12/)

データについての全体の解説は、下記 URL にて行われているのでご参照ください。

- [Analytics: Pageviews](https://dumps.wikimedia.org/other/pageviews/readme.html)

ダウンロードしたデータのテーブル定義は下記 URL で解説されているのでご参照ください。

- [Analytics/Data Lake/Traffic/Pageviews - Wikitech](https://wikitech.wikimedia.org/wiki/Analytics/Data_Lake/Traffic/Pageviews)

### データ形式

| domain_code    | page_title     | count_views            | total_response_size  |
| -------------- | -------------- | ---------------------- | -------------------- |
| ドメインコード | ページタイトル | 各時間のページ表示回数 | 合計レスポンスサイズ |
| aa             | Main_Page      | 4                      | 0                    |
| aa             | Wikipedia      | 1                      | 0                    |

## Wikipedia ログ解析ツールでできること

できることは以下の二つです。

### 1. 最もビュー数の多いページの表示

最もビュー数の多い記事を、指定した記事数分だけビュー数が多い順にソートし、ドメインコードとページタイトル、ビュー数を表示する

（例）コマンドライン上で 2 記事と指定した場合、下記を表示する

```bash
”en”, “Main_Page”, 120
”en”, ”Wikipedia:Umnyango_wamgwamanda”, 112
```

### 2. ドメインコードの人気順の表示

指定したドメインコードに対して、人気順にソートし、ドメインコード名と合計ビュー数を表示する

（例）コマンドライン上で「en de」と指定した場合、下記を表示する

```bash
”en”, 10700
”de”, 5300
```

## ツールの使い方

### Docker コンテナの準備

```bash
# Docker コンテナの生成
docker compose build

# Docker コンテナの起動
docker compose up -d
```

### DB のテーブル作成

```bash
docker compose exec app php databases/initialize_page_views_table.php
```

#### テーブル名：page_views

| 物理名              | 項目名                 | データ型 | 例 1      | 例 2      |
| ------------------- | ---------------------- | -------- | --------- | --------- |
| domain_code         | ドメインコード         | string   | aa        | aa        |
| page_title          | ページタイトル         | string   | Main_Page | Wikipedia |
| count_views         | 各時間のページ表示回数 | int      | 4         | 1         |
| total_response_size | 合計レスポンスサイズ   | int      | 0         | 0         |

### データのダウンロード

Wikipedia のアクセスログのデータは下記 URL からダウンロードする。

- [Index of /other/pageviews/2021/2021-12/](https://dumps.wikimedia.org/other/pageviews/2021/2021-12/)

ダウンロードしたファイルは、 **databases/** ディレクトリに解凍した状態で移動する。

ファイル名は、 page_views に変更する。 （インポートの際にテーブル名と揃えておく必要がある）

### データのインポート

Docker の db コンテナの MySQL に、`root` ユーザーでログインする。

```bash
docker compose exec db mysql -p
```

実行するとパスワードを聞かれるので、 `root` ユーザーのパスワード `pass` を入力する。

下記 SQL を実行して、 `local-infile`  を  `ON`  に設定する。

```sql
mysql> SET GLOBAL local_infile=ON;
```

`GRANT` 構文で、ユーザー権限を `SUPER` に指定する。

```sql
mysql> GRANT SUPER ON *.* To test_user@'%';
```

```sql
mysql> quit
```

テキストファイルから MySQL のテーブルへデータインポートする。

```bash
docker compose exec app mysqlimport -h db -u test_user -p -d --fields-terminated-by=' ' --local test_database databases/page_views
```

実行するとパスワードを聞かれるので、 `test_user` のパスワード `pass` を入力する。

### 1. 最もビュー数の多いページの表示

> 最もビュー数の多い記事を、指定した記事数分だけビュー数が多い順にソートし、ドメインコードとページタイトル、ビュー数を表示する

下記コマンドを実行する。

```bash
docker compose exec app php most_viewed_pages.php 20
```

**most_viewed_pages.php** の後に半角スペースを開けて、指定するページ数を入力する。

（実行例）

```bash
$ docker compose exec app php most_viewed_pages.php 20
"en.m", "Main_Page", 122058
"en", "Main_Page", 69181
"en", "Special:Search", 26630
"de", "Wikipedia:Hauptseite", 20739
"en.m", "Special:Search", 19119
"ja", "メインページ", 18475
"es.m", "Wikipedia:Portada", 15335
"es", "Wikipedia:Portada", 15261
"fr", "Wikipédia:Accueil_principal", 14744
"thankyou", "Thank_You/en", 13449
"ru", "Заглавная_страница", 13336
"en", "Lotfi_A._Zadeh", 12864
"it", "Pagina_principale", 12731
"zh", "Wikipedia:首页", 10782
"pt", "Wikipédia:Página_principal", 10485
"de.m", "Wikipedia:Hauptseite", 10386
"ja.m", "メインページ", 9421
"en", "Bible", 9024
"fr.m", "Wikipédia:Accueil_principal", 8705
"en", "-", 7227
```

### 2. ドメインコードの人気順の表示

> 指定したドメインコードに対して、人気順にソートし、ドメインコード名と合計ビュー数を表示する

下記コマンドを実行する。

```bash
docker compose exec app php popular_domain_codes.php en de ja
```

**popular_domain_codes.php** の後に半角スペースを開けて、指定するドメインコードを 2 つ以上入力する。

（実行例）

```bash
$ docker compose exec app php popular_domain_codes.php en de ja
"en", 3556081
"ja", 367924
"de", 284178
```

### Docker コンテナの停止

```bash
docker compose stop
```

## 教材

以下教材の課題です。

[独学エンジニア](https://dokugaku-engineer.com/)
