<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Dashboard Monitoring'); ?></h1>
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Pengajuan Kuota</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_pengajuan_kuota ?? 0; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Permohonan Impor</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_permohonan_impor ?? 0; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-ship fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pengajuan Kuota Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $permohonan_kuota_pending ?? 0; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-hourglass-half fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Permohonan Impor Baru/Diproses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $permohonan_impor_baru ?? 0; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-folder-open fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p>Selamat datang, <?= htmlspecialchars($user['name']); ?>. Anda dapat memantau data pengajuan kuota dan permohonan impor melalui menu di samping.</p>
</div>