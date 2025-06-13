<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

// Helper function untuk konversi string 'TRUE'/'FALSE' dari konstanta ke boolean
if (!function_exists('const_bool')) {
    function const_bool($const_name, $default_boolean_value = false) {
        if (!defined($const_name)) {
            return $default_boolean_value;
        }
        return (strtoupper(constant($const_name)) === 'TRUE');
    }
}

$db['default'] = array(
    'dsn'      => $_ENV['DB_DSN'] ?? '',
    'hostname' => $_ENV['DB_HOSTNAME'] ?? null,
    'username' => $_ENV['DB_USERNAME'] ?? null,
    'password' => $_ENV['DB_PASSWORD'] ?? null,
    'database' => $_ENV['DB_DATABASE'] ?? null,
    'dbdriver' => $_ENV['DB_DRIVER'] ?? 'mysqli',
    'dbprefix' => $_ENV['DB_PREFIX'] ?? '',
    'pconnect' => (strtoupper($_ENV['DB_PCONNECT'] ?? 'FALSE') === 'TRUE'),
    'db_debug' => (strtoupper($_ENV['DB_DEBUG'] ?? 'TRUE') !== 'FALSE'),
    'cache_on' => (strtoupper($_ENV['DB_CACHE_ON'] ?? 'FALSE') === 'TRUE'),
    'cachedir' => $_ENV['DB_CACHEDIR'] ?? '',
    'char_set' => $_ENV['DB_CHARSET'] ?? 'utf8',
    'dbcollat' => $_ENV['DB_COLLATION'] ?? 'utf8_general_ci',
    'swap_pre' => $_ENV['DB_SWAP_PRE'] ?? '',
    'encrypt'  => (strtoupper($_ENV['DB_ENCRYPT'] ?? 'FALSE') === 'TRUE'),
    'compress' => (strtoupper($_ENV['DB_COMPRESS'] ?? 'FALSE') === 'TRUE'),
    'stricton' => (strtoupper($_ENV['DB_STRICTON'] ?? 'FALSE') === 'TRUE'),
    'failover' => array(),
    'save_queries' => (strtoupper($_ENV['DB_SAVE_QUERIES'] ?? 'TRUE') !== 'FALSE'),
    'port'     => $_ENV['DB_PORT'] ?? 3306,
);
