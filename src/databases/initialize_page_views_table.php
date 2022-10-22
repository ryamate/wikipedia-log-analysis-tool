<?php

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

function dropPageViewsTable(PDO $dbh)
{
    $sql = 'DROP TABLE IF EXISTS page_views;';
    $sth = $dbh->query($sql);
    if ($sth) {
        echo 'テーブル削除完了: page_views' . PHP_EOL;
    } else {
        exit('テーブル削除エラー: page_views' . PHP_EOL);
    }
    $sth = null;
}

function createPageViewsTable(PDO $dbh)
{
    $sql = <<<EOI
    CREATE TABLE page_views (
        domain_code VARCHAR(100),
        page_title VARCHAR(100),
        count_views INTEGER,
        total_response_size INTEGER
    );
    EOI;
    $sth = $dbh->query($sql);
    if ($sth) {
        echo 'テーブル作成完了: page_views' . PHP_EOL;
    } else {
        exit('テーブル作成エラー: page_views' . PHP_EOL);
    }
    $sth = null;
}

$dbh = dbConnect();
dropPageViewsTable($dbh);
createPageViewsTable($dbh);
$dbh = null;
