<?php
// Edit these values, or set them as environment variables on your host.
return [
  'host'     => getenv('DB_HOST') ?: 'localhost',
  'port'     => getenv('DB_PORT') ?: '3306',
  'name'     => getenv('DB_NAME') ?: 'day_care',
  'user'     => getenv('DB_USER') ?: 'root',
  'pass'     => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
  'currency' => getenv('CURRENCY') ?: '৳',
];
