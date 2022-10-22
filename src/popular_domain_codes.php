<?php

require_once 'lib/sql.php';

const MIN_NUM_OF_SEARCHES = 2;

// 1. 入力値の受け取り ['popular_domain_codes.php', 'en', 'de'] → ['en', 'de']
if ($_SERVER['argc'] > MIN_NUM_OF_SEARCHES) {
    $inputs = array_slice($_SERVER['argv'], 1);

    // 2. SELECT文の実行
    $data = getPopularDomainCodes($inputs);

    // 3. 結果の表示
    foreach ($data as $record) {
        echo '"' . $record['domain_code'] . '", ' . $record['total_count_views'] . PHP_EOL;
    }
} else {
    echo 'ドメインコードを 2 つ以上入力してください。' . PHP_EOL
        . 'ex: php popular_domain_codes.php en de ja' . PHP_EOL;
}
