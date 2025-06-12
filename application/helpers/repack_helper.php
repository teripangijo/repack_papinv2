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

            
            if (empty($menu)) {
                
                
                redirect('auth/blocked');
                return;
            }

            $queryMenu = $ci->db->get_where('user_menu', ['LOWER(menu)' => strtolower($menu)])->row_array(); 

            
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
        
        return ""; 
    }
}

if (!function_exists('getWeekday')) {
    function getWeekday($date_input) 
    {
        if (!is_string($date_input) || empty(trim($date_input))) {
            return '-'; 
        }

        try {
            
            $date_obj = new DateTime($date_input); 
        } catch (Exception $e) {
            
            try {
                $date_obj = DateTime::createFromFormat("m/d/Y", $date_input);
                if ($date_obj === false) { 
                    
                    return htmlspecialchars($date_input); 
                }
            } catch (Exception $e2) {
                
                return htmlspecialchars($date_input);
            }
        }
        
        if ($date_obj) {
            $numdate = $date_obj->format('w'); 

            $hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
            return $hari[$numdate];
        }
        return htmlspecialchars($date_input); 
    }
}

if (!function_exists('dateConvert')) {
    function dateConvert($date_sql)
    {
        if (!is_string($date_sql) || empty(trim($date_sql)) || $date_sql == '0000-00-00' || $date_sql == '0000-00-00 00:00:00') {
            return '-'; 
        }

        try {
            $date_obj = new DateTime($date_sql);
            if ($date_obj) {
                $bulan = array(
                    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                );
                
                $date_part = $date_obj->format('Y-m-d'); 
                list($year, $month_num, $day) = explode('-', $date_part);
                
                
                if (checkdate((int)$month_num, (int)$day, (int)$year) && isset($bulan[(int)$month_num])) {
                    return (int)$day . ' ' . $bulan[(int)$month_num] . ' ' . $year;
                } else {
                    
                    return htmlspecialchars($date_sql); 
                }
            } else {
                
                return htmlspecialchars($date_sql); 
            }
        } catch (Exception $e) {
            return htmlspecialchars($date_sql); 
        }
    }
}

if (!function_exists('status_permohonan_text_badge')) {
    function status_permohonan_text_badge($status_code) {
        $status_text = 'Tidak Diketahui';
        $status_badge = 'light';
        switch ($status_code) {
            case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
            case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
            case '1': $status_text = 'Penunjukan Pemeriksa'; $status_badge = 'primary'; break;
            case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
            case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
            case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
            default: $status_text = 'Status Tidak Dikenal (' . htmlspecialchars($status_code) . ')';
        }
        return ['text' => $status_text, 'badge' => $status_badge];
    }
}

if (!function_exists('status_pengajuan_kuota_text_badge')) {
    function status_pengajuan_kuota_text_badge($status_code) {
        $status_text = ucfirst($status_code ?? 'N/A');
        $status_badge = 'secondary';
        switch (strtolower($status_code ?? '')) {
            case 'pending': $status_badge = 'warning'; $status_text = 'Pending'; break;
            case 'approved': $status_badge = 'success'; $status_text = 'Disetujui'; break;
            case 'rejected': $status_badge = 'danger'; $status_text = 'Ditolak'; break;
            case 'diproses': $status_badge = 'info'; $status_text = 'Diproses'; break;
        }
        return ['text' => $status_text, 'badge' => $status_badge];
    }
}

if (!function_exists('status_kuota_barang_text_badge')) {
    function status_kuota_barang_text_badge($status_code) {
        $status_text = ucfirst($status_code ?? 'N/A');
        $status_badge = 'secondary'; 
        switch (strtolower($status_code ?? '')) {
            case 'active':
                $status_text = 'Aktif';
                $status_badge = 'success';
                break;
            case 'inactive':
                $status_text = 'Non-Aktif';
                $status_badge = 'warning';
                break;
            case 'habis':
                $status_text = 'Habis';
                $status_badge = 'danger';
                break;
            case 'pending_approval': 
                $status_text = 'Menunggu Persetujuan';
                $status_badge = 'info';
                break;
        }
        return ['text' => $status_text, 'badge' => $status_badge];
    }
}

?>
