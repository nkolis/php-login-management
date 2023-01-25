<?php

function getDatabaseConfig(): array
{
  return [
    'database' => [
      'test' => [
        'url' => 'mysql:host=127.0.0.1:3306;dbname=php_login_management_test',
        'username' => 'root',
        'password' => ''
      ],
      'production' => [
        'url' => 'mysql:host=126.0.0.1:3306;dbname=php_login_management',
        'username' => 'root',
        'password' => ''
      ]
    ]
  ];
}
