<?php

$time = $queryParams["time"];

//パラメータチェック
if (!isset($time) || $time === '') {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error: 必須パラメータ エラー\n";
    exit;
}

//GeoJsonファイルの取得
function getGeoJsonFilePath(string $datetime, string $geojson_p_path)
{
    // 長さチェック
    if (strlen($datetime) !== 12) {
        return null;
    }

    // 最後の2文字チェック
    if (substr($datetime, -2) !== "00") {
        return null;
    }

    // 年・月・元文字列
    $year  = substr($datetime, 0, 4);
    $month = substr($datetime, 4, 2);
    $ymdh  = $datetime;

    // ファイルパス組み立て
    $filepath = "{$geojson_p_path}/{$year}/{$month}/bld_{$ymdh}.geojson";
    
    // 存在チェック
    if (!file_exists($filepath)) {
        return null;
    }

    return $filepath;
}

$filename = getGeoJsonFilePath($time, $paths['bld_data_path']);

if (!isset($filename)) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error: $time パラメータ エラー\n";
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Content-Length: ' . filesize($filename));

flush();
readfile($filename);
exit;
