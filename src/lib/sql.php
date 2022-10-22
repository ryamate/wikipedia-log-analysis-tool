<?php

/**
 * DB接続
 *
 * @return PDO
 */
function dbConnect(): PDO
{
    $dbHost = 'db';
    $dbUsername = 'test_user';
    $dbPassword = 'pass';
    $dbDatabase = 'test_database';
    $dataSourceName = "mysql:host=$dbHost;dbname=$dbDatabase;charset=utf8mb4";

    try {
        $dbh = new PDO($dataSourceName, $dbUsername, $dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    } catch (PDOException $e) {
        echo 'DB接続エラー' . $e->getMessage();
        exit();
    };

    return $dbh;
}

/**
 * 最もビュー数の多い記事を、指定した記事数分だけビュー数が多い順にソートし、ドメインコードとページタイトル、ビュー数を取得する。
 *
 * @param string $input コマンドラインで受け取った入力値 ex. 10
 * @return array $results ex. [ 0 => ['domain_code' => 'en.m', 'page_title' => 'Main_Page', 'count_views' => 122058],...
 */
function getMostViewedPages(string $input): array
{
    $sql = <<<SQL
        SELECT
            domain_code,
            page_title,
            count_views
        FROM
            page_views
        ORDER BY
            count_views DESC
        LIMIT
            :input
        ;
        SQL;

    $dbh = dbConnect(); // DB接続
    $stmt = $dbh->prepare($sql); // 実行準備
    $stmt->bindValue(':input', (int)$input, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh = null; // DB切断
    return $results;
}

/**
 * 指定したドメインコードに対して、人気順にソートし、ドメインコード名と合計ビュー数を取得する。
 *
 * @param array $inputs コマンドラインで受け取った入力値 ex. en de ja
 * @return array $result ex. [0 => ['domain_code' => 'en', 'total_count_views' => '3556081'], 1 => ...
 */
function getPopularDomainCodes(array $inputs): array
{
    $inClause = substr(str_repeat(',?', count($inputs)), 1);

    $sql = <<<SQL
        SELECT
            domain_code,
            SUM(count_views) AS 'total_count_views'
        FROM
            page_views
        WHERE
            domain_code
            IN (
                {$inClause}
            )
        GROUP BY
            domain_code
        ORDER BY
            SUM(count_views) DESC
        ;
        SQL;

    $dbh = dbConnect(); // DB接続
    $stmt = $dbh->prepare($sql); // 実行準備
    $stmt->execute($inputs); // SQL実行
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh = null; // DB切断
    return $results;
}
