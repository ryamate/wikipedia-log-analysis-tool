<?php

require_once 'lib/sql.php';

/**
 * most_viewed_pages.php のコマンドラインでの入力値をバリデーションする。
 *
 * @param string $input コマンドラインで受け取った入力値 ex. 10
 * @return string $error
 */
function validationInputOfMostViewedPages(string $input): string
{
    $error = '';
    if (((int)$input < 0) || !preg_match('/^-?[0-9]+$/', $input)) {
        $error = '1 以上の整数を入力してください。' . PHP_EOL;
    }
    return $error;
}
