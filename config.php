<?php
return [
    'db' => [
        'database' => $_ENV['DB_NAME'],
        'username' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
    ],
    'paths' => [
        'venv_path' => $_ENV['SCRIPT_PATH'] . '/venv',
        'get_plateau_data_script' => $_ENV['SCRIPT_PATH'] . '/geoJsonToCzml.py',
        'bld_data_path' => $_ENV['DATA_DIR'] . '/bld',
    ],
];
