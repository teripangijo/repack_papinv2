<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package CodeIgniter
 * @author  EllisLab Dev Team
 * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright   Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    https://codeigniter.com
 * @since   Version 1.0.0
 * @filesource
 */

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 */
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 */
switch (ENVIRONMENT)
{
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
    break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        if (version_compare(PHP_VERSION, '5.3', '>='))
        {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        }
        else
        {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
        }
    break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1); // EXIT_ERROR
}

/*
 *---------------------------------------------------------------
 * LOAD COMPOSER AUTOLOAD & ENVIRONMENT VARIABLES (.env)
 *---------------------------------------------------------------
 */

// Load Composer's autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo "ERROR FATAL: File vendor/autoload.php tidak ditemukan. Pastikan Anda telah menjalankan 'composer install' di direktori proyek Anda.";
    exit(1); // EXIT_ERROR
}

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    // load() di phpdotenv v5 mengembalikan array dari variabel yang berhasil dimuat.
    // Jika tidak ada variabel yang dimuat, atau file .env kosong, $loadedVars akan kosong.
    $loadedVars = $dotenv->load();

    // Validasi bahwa variabel penting ada dan tidak kosong di dalam file .env
    // Ini akan melempar exception jika gagal, yang akan ditangkap oleh blok catch di bawah.
    $dotenv->required(['DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'DB_PORT'])->notEmpty();

    // Daftar variabel konfigurasi database yang kita butuhkan dari .env beserta nilai defaultnya (jika perlu)
    // Nilai default di sini akan digunakan jika variabel TIDAK ADA SAMA SEKALI di .env,
    // meskipun `required()` di atas seharusnya sudah memastikan keberadaannya.
    $dbConfigKeys = [
        'DB_DSN'          => '',
        'DB_HOSTNAME'     => null, // Akan diambil dari .env
        'DB_USERNAME'     => null, // Akan diambil dari .env
        'DB_PASSWORD'     => null, // Akan diambil dari .env
        'DB_DATABASE'     => null, // Akan diambil dari .env
        'DB_DRIVER'       => 'mysqli',
        'DB_PREFIX'       => '',
        'DB_PCONNECT'     => 'FALSE', // Nilai harus string 'TRUE' atau 'FALSE'
        'DB_DEBUG'        => 'TRUE',  // Nilai harus string 'TRUE' atau 'FALSE'
        'DB_CACHE_ON'     => 'FALSE', // Nilai harus string 'TRUE' atau 'FALSE'
        'DB_CACHEDIR'     => '',
        'DB_CHARSET'      => 'utf8',
        'DB_COLLATION'    => 'utf8_general_ci',
        'DB_SWAP_PRE'     => '',
        'DB_ENCRYPT'      => 'FALSE', // Nilai harus string 'TRUE' atau 'FALSE'
        'DB_COMPRESS'     => 'FALSE', // Nilai harus string 'TRUE' atau 'FALSE'
        'DB_STRICTON'     => 'FALSE', // Nilai harus string 'TRUE' atau 'FALSE'
        'DB_SAVE_QUERIES' => 'TRUE',  // Nilai harus string 'TRUE' atau 'FALSE'
        'DB_PORT'         => '3306'   // Akan diambil dari .env
    ];

    foreach ($dbConfigKeys as $key => $defaultValue) {
        // Ambil nilai dari $loadedVars (hasil parsing .env) jika ada, jika tidak gunakan defaultValue
        // $dotenv->required() di atas seharusnya sudah memastikan variabel utama ada di $loadedVars
        $valueToDefine = $loadedVars[$key] ?? $defaultValue;
        
        if (!defined($key)) {
            define($key, $valueToDefine);
        }
    }

    // PENTING: Hapus semua echo, var_dump, dan exit; dari sini agar aplikasi berjalan normal
    // Contoh:
    // echo "Variabel lingkungan telah dimuat dan konstanta didefinisikan.";
    // exit; // JANGAN ADA exit; DI SINI LAGI

} catch (Dotenv\Exception\InvalidPathException $e) {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'ERROR Dotenv (InvalidPathException): Tidak bisa menemukan atau membaca file .env. Pastikan file .env ada di direktori (' . __DIR__ . ') dan dapat dibaca. Pesan: ' . htmlspecialchars($e->getMessage());
    exit(1);
} catch (Dotenv\Exception\InvalidFileException $e) {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'ERROR Dotenv (InvalidFileException): Periksa sintaks atau format isi file .env Anda. Pesan: ' . htmlspecialchars($e->getMessage());
    exit(1);
} catch (Dotenv\Exception\ValidationException $e) { // Menangkap error validasi dari required()
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'ERROR Dotenv (ValidationException): Variabel lingkungan yang dibutuhkan tidak ada atau kosong di file .env. Pastikan semua variabel (DB_HOSTNAME, DB_USERNAME, dll.) terdefinisi dan tidak kosong di file .env. Pesan: ' . htmlspecialchars($e->getMessage());
    exit(1);
} catch (Exception $e) { // Menangkap error umum lainnya
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'ERROR Umum Proses Dotenv: ' . htmlspecialchars($e->getMessage()) . "<br><pre>Trace: " . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    exit(1);
}

/*
 *---------------------------------------------------------------
 * SYSTEM DIRECTORY NAME
 *---------------------------------------------------------------
 */
    $system_path = 'system';

/*
 *---------------------------------------------------------------
 * APPLICATION DIRECTORY NAME
 *---------------------------------------------------------------
 */
    $application_folder = 'application';

/*
 *---------------------------------------------------------------
 * VIEW DIRECTORY NAME
 *---------------------------------------------------------------
 */
    $view_folder = '';

// ... (Sisa kode CodeIgniter standar seperti $routing, $assign_to_config) ...
// ... (Blok "Resolve the system path") ...
// ... (Blok "Now that we know the path, set the main path constants") ...
// ... (Blok "LOAD THE BOOTSTRAP FILE") ...

// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS. DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 * Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

    // Set the current directory correctly for CLI requests
    if (defined('STDIN'))
    {
        chdir(dirname(__FILE__));
    }

    if (($_temp = realpath($system_path)) !== FALSE)
    {
        $system_path = $_temp.DIRECTORY_SEPARATOR;
    }
    else
    {
        // Ensure there's a trailing slash
        $system_path = strtr(
            rtrim($system_path, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
        ).DIRECTORY_SEPARATOR;
    }

    // Is the system path correct?
    if ( ! is_dir($system_path))
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
        exit(3); // EXIT_CONFIG
    }

/*
 * -------------------------------------------------------------------
 * Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
    // The name of THIS file
    define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

    // Path to the system directory
    define('BASEPATH', $system_path);

    // Path to the front controller (this file) directory
    define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

    // Name of the "system" directory
    define('SYSDIR', basename(BASEPATH));

    // The path to the "application" directory
    if (is_dir($application_folder))
    {
        if (($_temp = realpath($application_folder)) !== FALSE)
        {
            $application_folder = $_temp;
        }
        else
        {
            $application_folder = strtr(
                rtrim($application_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
        }
    }
    elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
    {
        $application_folder = BASEPATH.strtr(
            trim($application_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
        );
    }
    else
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
        exit(3); // EXIT_CONFIG
    }

    define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

    // The path to the "views" directory
    if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
    {
        $view_folder = APPPATH.'views';
    }
    elseif (is_dir($view_folder))
    {
        if (($_temp = realpath($view_folder)) !== FALSE)
        {
            $view_folder = $_temp;
        }
        else
        {
            $view_folder = strtr(
                rtrim($view_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
        }
    }
    elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
    {
        $view_folder = APPPATH.strtr(
            trim($view_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
        );
    }
    else
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
        exit(3); // EXIT_CONFIG
    }

    define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require_once BASEPATH.'core/CodeIgniter.php';