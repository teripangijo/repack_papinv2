<!-- Bootstrap core JavaScript-->
<script src="<?= base_url('assets'); ?>/vendor/jquery/jquery.min.js"></script>
<script src="<?= base_url('assets'); ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="<?= base_url('assets'); ?>/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="<?= base_url('assets'); ?>/js/sb-admin-2.min.js"></script>

<script>
        // Setup AJAX untuk secara otomatis mengirim token CSRF
        $.ajaxSetup({
            data: {
                '<?= $this->security->get_csrf_token_name(); ?>': $('meta[name="csrf-token-hash"]').attr('content')
            },
            // Fungsi ini akan memperbarui token setiap kali AJAX sukses,
            // jika token di-regenerasi oleh server.
            success: function(data, textStatus, jqXHR) {
                // Coba ambil token baru dari header response jika ada
                var new_token_hash = jqXHR.getResponseHeader('X-CSRF-TOKEN');
                if(new_token_hash){
                    $('meta[name="csrf-token-hash"]').attr('content', new_token_hash);
                }
            }
        });

        // Jika Anda menggunakan DataTables, konfigurasinya seperti ini:
        // $.extend(true, $.fn.dataTable.defaults, {
        //     "fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
        //         aoData.push({
        //             "name": "<?= $this->security->get_csrf_token_name(); ?>",
        //             "value": $('meta[name="csrf-token-hash"]').attr('content')
        //         });
        //         oSettings.jqXHR = $.ajax( {
        //             "dataType": 'json',
        //             "type": "POST",
        //             "url": sSource,
        //             "data": aoData,
        //             "success": function(data, textStatus, jqXHR) {
        //                 var new_token_hash = jqXHR.getResponseHeader('X-CSRF-TOKEN');
        //                 if(new_token_hash){
        //                    $('meta[name="csrf-token-hash"]').attr('content', new_token_hash);
        //                 }
        //                 fnCallback(data);
        //             }
        //         });
        //     }
        // });
    </script>

</body>

</html>