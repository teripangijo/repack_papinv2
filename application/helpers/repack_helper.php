<?php

if (!function_exists('is_loggedin')) {
    function is_loggedin()
    {
        $ci = get_instance();
        if (!$ci->session->userdata('email')) {
            redirect('auth');
        } else {
            $role_id = $ci->session->userdata('role_id');
            $menu = $ci->uri->segment(1);

            // Memastikan $menu tidak kosong sebelum query
            if (empty($menu)) {
                // Mungkin redirect ke halaman default atau tampilkan error jika menu kosong tidak diharapkan
                // Untuk sekarang, kita bisa biarkan atau redirect ke auth/blocked
                redirect('auth/blocked');
                return;
            }

            $queryMenu = $ci->db->get_where('user_menu', ['LOWER(menu)' => strtolower($menu)])->row_array(); // Bandingkan dengan lowercase

            // Memastikan $queryMenu mengembalikan hasil dan 'id' ada
            if ($queryMenu && isset($queryMenu['id'])) {
                $menu_id = $queryMenu['id'];

                $userAccess = $ci->db->get_where('user_access_menu', [
                    'role_id' => $role_id,
                    'menu_id' => $menu_id
                ]);

                if ($userAccess->num_rows() < 1) {
                    redirect('auth/blocked');
                }
            } else {
                // Jika menu tidak ditemukan di user_menu, mungkin blok akses juga
                // Tergantung logika bisnis, apakah semua segment(1) harus ada di user_menu
                redirect('auth/blocked');
            }
        }
    }
}

if (!function_exists('checked_access')) {
    function checked_access($role_id, $menu_id)
    {
        $ci = get_instance();

        $ci->db->where('role_id', $role_id);
        $ci->db->where('menu_id', $menu_id);
        $result = $ci->db->get('user_access_menu');

        if ($result->num_rows() > 0) {
            return "checked='checked'";
        }
        // Return string kosong jika tidak ada akses, agar tidak ada output yang tidak diinginkan
        return ""; 
    }
}

if (!function_exists('getWeekday')) {
    function getWeekday($date_input) // Mengganti nama parameter agar lebih jelas
    {
        if (!is_string($date_input) || empty(trim($date_input))) {
            return '-'; // Kembalikan strip jika input tidak valid
        }

        try {
            // Coba parse dengan format YYYY-MM-DD dulu (umum dari database/datepicker)
            $date_obj = new DateTime($date_input); 
        } catch (Exception $e) {
            // Jika gagal, coba format m/d/Y sebagai fallback (jika Anda masih memiliki format ini)
            try {
                $date_obj = DateTime::createFromFormat("m/d/Y", $date_input);
                if ($date_obj === false) { // createFromFormat mengembalikan false jika gagal
                    // error_log('Invalid date format in getWeekday (m/d/Y): ' . $date_input);
                    return htmlspecialchars($date_input); // Kembalikan input asli jika semua format gagal
                }
            } catch (Exception $e2) {
                // error_log('Invalid date format in getWeekday (m/d/Y exception): ' . $date_input . ' - ' . $e2->getMessage());
                return htmlspecialchars($date_input);
            }
        }
        
        // Jika $date_obj berhasil dibuat
        if ($date_obj) {
            $numdate = $date_obj->format('w'); // 0 (for Sunday) through 6 (for Saturday)

            $hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
            return $hari[$numdate];
        }
        return htmlspecialchars($date_input); // Fallback jika $date_obj tidak valid
    }
}

if (!function_exists('dateConvert')) {
    function dateConvert($date_sql)
    {
        // Handle jika input bukan string atau kosong, atau tanggal default MySQL yang tidak valid
        if (!is_string($date_sql) || empty(trim($date_sql)) || $date_sql == '0000-00-00' || $date_sql == '0000-00-00 00:00:00') {
            return '-'; // Kembalikan strip jika tanggal tidak valid atau kosong
        }

        try {
            // Coba buat objek DateTime. Ini akan menangani format YYYY-MM-DD dan YYYY-MM-DD HH:MM:SS
            $date_obj = new DateTime($date_sql);

            // Jika berhasil membuat objek DateTime, format ke bahasa Indonesia
            if ($date_obj) {
                $bulan = array(
                    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                );
                // Ambil bagian tanggal saja jika inputnya adalah datetime
                $date_part = $date_obj->format('Y-m-d'); 
                list($year, $month_num, $day) = explode('-', $date_part);
                
                // Pastikan hasil explode valid sebelum mengakses array $bulan
                if (checkdate((int)$month_num, (int)$day, (int)$year) && isset($bulan[(int)$month_num])) {
                    return (int)$day . ' ' . $bulan[(int)$month_num] . ' ' . $year;
                } else {
                    // error_log('Invalid date components in dateConvert after explode: ' . $date_part);
                    return htmlspecialchars($date_sql); // Kembalikan input asli jika komponen tanggal tidak valid
                }
            } else {
                // Ini seharusnya tidak sering terjadi jika try-catch bekerja, tapi sebagai fallback
                return htmlspecialchars($date_sql); 
            }
        } catch (Exception $e) {
            // Jika terjadi exception saat membuat objek DateTime (misalnya format tanggal sangat salah)
            // Anda bisa log error di sini jika perlu: error_log('Invalid date format in dateConvert: ' . $date_sql . ' - ' . $e->getMessage());
            return htmlspecialchars($date_sql); // Kembalikan input asli (setelah di-escape)
        }
    }
}

?>
