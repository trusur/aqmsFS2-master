<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <form action="<?= base_url('login') ?>" method="POST">
        <div class="row">
            <div class="col-md-6 col-12 mx-auto">
                <div class="card">
                    <div class="card-body login-card-body">
                        <form method="POST" action="<?= base_url('login') ?>" id="formLogin" autocomplete="off">
                            <input type="hidden" name="url_direction" value="<?= @$url_direction; ?>">
                            <div class="input-group mb-3">
                                <input name="username" class="form-control" placeholder="Username">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-user"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="input-group mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Password">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-lock"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script>
    $('#formLogin').submit();
</script>
<?= $this->endSection() ?>