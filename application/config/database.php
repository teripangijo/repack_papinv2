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
    'dsn'      => defined('DB_DSN') ? DB_DSN : '',
    'hostname' => defined('DB_HOSTNAME') ? DB_HOSTNAME : null,
    'username' => defined('DB_USERNAME') ? DB_USERNAME : null,
    'password' => defined('DB_PASSWORD') ? DB_PASSWORD : null,
    'database' => defined('DB_DATABASE') ? DB_DATABASE : null,
    'dbdriver' => defined('DB_DRIVER') ? DB_DRIVER : 'mysqli',
    'dbprefix' => defined('DB_PREFIX') ? DB_PREFIX : '',
    'pconnect' => const_bool('DB_PCONNECT', FALSE),
    'db_debug' => const_bool('DB_DEBUG', TRUE), // Default TRUE untuk development
    'cache_on' => const_bool('DB_CACHE_ON', FALSE),
    'cachedir' => defined('DB_CACHEDIR') ? DB_CACHEDIR : '',
    'char_set' => defined('DB_CHARSET') ? DB_CHARSET : 'utf8',
    'dbcollat' => defined('DB_COLLATION') ? DB_COLLATION : 'utf8_general_ci',
    'swap_pre' => defined('DB_SWAP_PRE') ? DB_SWAP_PRE : '',
    'encrypt'  => const_bool('DB_ENCRYPT', FALSE),
    'compress' => const_bool('DB_COMPRESS', FALSE),
    'stricton' => const_bool('DB_STRICTON', FALSE),
    'failover' => array(),
    'save_queries' => const_bool('DB_SAVE_QUERIES', TRUE),
    'port'     => defined('DB_PORT') ? (int)DB_PORT : 3306,
);

// HAPUS SEMUA echo, var_dump, dan exit dari sini
// Contoh:
// echo "<pre>..."; var_dump($db['default']); exit; // PASTIKAN INI SUDAH DIHAPUS