<?php
$user_to_change = isset($user_for_pass_change) ? $user_for_pass_change : null;
$user_id_to_change = isset($user_to_change['id']) ? $user_to_change['id'] : null;
$user_name_to_change = isset($user_to_change['name']) ? htmlspecialchars($user_to_change['name']) : 'N/A';
$user_email_to_change = isset($user_to_change['email']) ? htmlspecialchars($user_to_change['email']) : 'N/A';

if ($user_id_to_change === null) {
    echo '<div class="alert alert-danger">Error: User ID for password change is missing.</div>';
    return; 
}
?>

<body class="bg-gradient-primary">

    <div class="container">

        <div class="card o-hidden border-0 shadow-lg my-5 col-lg-7 mx-auto">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-lg">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Change Password</h1>
                            </div>

                            <?php
                            if ($this->session->flashdata('message')) {
                                echo $this->session->flashdata('message');
                            }
                            ?>

                            <?php ?>
                            <form class="user" method="post" action="<?= site_url('auth/changepass/' . $user_id_to_change); ?>">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control form-control-user" id="name" name="name" placeholder="Full Name" value="<?= $user_name_to_change; ?>" readonly> <?php ?>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="text" class="form-control form-control-user" id="email" name="email" placeholder="Email Address" value="<?= $user_email_to_change; ?>" readonly> <?php ?>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label for="password">New Password <span class="text-danger">*</span></label>
                                        <?php ?>
                                        <input type="password" class="form-control form-control-user <?= (form_error('password')) ? 'is-invalid' : ''; ?>"
                                               id="password" name="password" placeholder="New Password">
                                        <?php ?>
                                        <?= form_error('password', '<small class="text-danger pl-1">', '</small>'); ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="password2">Repeat New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control form-control-user <?= (form_error('password2')) ? 'is-invalid' : ''; ?>"
                                               id="password2" name="password2" placeholder="Repeat Password">
                                        <?php ?>
                                        <?= form_error('password2', '<small class="text-danger pl-1">', '</small>'); ?>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Change Password
                                </button>
                                <hr>
                            </form>
                            <hr>
                            <div class="text-center">
                                <?php ?>
                                <a class="btn btn-secondary btn-sm" href="<?= site_url('user'); ?>">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php ?>

</body>
