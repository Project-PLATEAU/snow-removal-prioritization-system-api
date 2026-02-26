<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$python_cmd = $paths['venv_path'] . '/bin/python';
$python_script = $paths['get_plateau_data_script'];

$lon1 = $queryParams["lon1"];
$lon2 = $queryParams["lon2"];
$lat1 = $queryParams["lat1"];
$lat2 = $queryParams["lat2"];
$time = $queryParams["time"];
$kind = $queryParams["kind"];

//パラメータチェック
if ( !isset($lon1) || $lon1 === '' || !isset($lon2) || $lon2 === '' || 
    !isset($lat1) || $lat1 === '' || !isset($lat2) || $lat2 === '' || 
    !isset($time) || $time === '' || !isset($kind) || $kind === ''){
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error: 必須パラメータ エラー\n";
    exit;
}


// 大きさチェック関数
function check_greater($name1, $val1, $name2, $val2) {
    if ($val2 <= $val1) {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Error: $name2 は $name1 より大きくなければなりません。\n";
        return false;
    }
    
    return true;
}

//GeoJsonファイルの取得
function getGeoJsonFilePath(string $datetime, string $data_path)
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
    $geojson_p_path = "${data_path}/{$year}/{$month}/bld_{$ymdh}.geojson";
    
    // 存在チェック
    if (!file_exists($geojson_p_path)) {
        return null;
    }

    return $geojson_p_path;
}


// 大小関係チェック
if (check_greater('lon1', $lon1, 'lon2', $lon2) == false){
    exit;
}
if (check_greater('lat1', $lat1 ,'lat2', $lat2) == false){
    exit;
}

$filename = getGeoJsonFilePath($time, $paths['bld_data_path']);
if (!isset($filename) ){
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error: $time パラメータ エラー\n";
    exit;
}

// コマンド構築
$cmd = $python_cmd . " " . $python_script . " " . $kind . " " . 
  sprintf('%.8F', $lon1) . " " . sprintf('%.8F', $lon2)  . " " . sprintf('%.8F', $lat1)  . " " . sprintf('%.8F', $lat2)  . " " . $filename;

// 実行
exec($cmd, $output, $return_var);

// 終了コードチェック
if ($return_var !== 0) {
    header("Content-Type: application/json; charset=utf-8");
    header("HTTP/1.1 500 Internal Server Error"); // サーバーエラーとして返す
    echo "Error:  実行エラー\n";
    exit;
}

$outfilename = pathinfo($filename, PATHINFO_FILENAME). "_" . $kind . ".czml";

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Content-Disposition: attachment; filename="' . $outfilename . '"');
header('Content-Length: ' . strlen($output[0]));

echo implode("\n", $output)

?>
