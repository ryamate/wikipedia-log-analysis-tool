<?php

require_once 'lib/sql.php';
require_once 'lib/validation.php';

const NO_ARG = 1;
const DEFAULT_NUM_OF_SEARCH = 10;

// 1. 入力値の受け取り
if ($_SERVER['argc'] === NO_ARG) {
    $input = DEFAULT_NUM_OF_SEARCH;
} else {
    $input = $_SERVER['argv'][1];
}

// 2. 入力値のバリデーション(正の整数かどうか)
$error = validationInputOfMostViewedPages($input);

// 数字以外→エラーメッセージ
if (!empty($error)) {
    echo $error;
} else {
    // 3. SELECT文の実行
    $data = getMostViewedPages($input);

    // 4. 結果の表示
    foreach ($data as $record) {
        echo '"' . $record['domain_code'] . '", "' . $record['page_title'] . '", ' . $record['count_views'] . PHP_EOL;
    }
}
