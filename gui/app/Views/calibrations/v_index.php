<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Calibrations</h5>
                </div>
                <div class="card-body">
                    <!-- <p class="alert alert-warning text-danger dismissable" role="alert">
                        <i class="fas fa-info-circle fa-xs mr-1"></i> Pastikan aliran gas kalibrasi span Gas sudah terpasang dengan benar pada saluran sampling dengan laju alir 0.9 lpm
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </p>
                    <p class="alert alert-warning text-danger dismissable" role="alert">
                        <i class="fas fa-info-circle fa-xs mr-1"></i> Kesalahan penggunaan gas kalibrasi dapat mempengaruhi daya akurasi pengukuran pada sensor Gas 
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </p> -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between flex-column" style="gap: 5px;">
                                        <a href="<?= base_url('calibration/configuration')?>" class="btn btn-sm btn-secondary w-100">Configurations &raquo;</a>
                                        <a href="<?= base_url('calibration/logs') ?>" class="btn btn-sm btn-secondary w-100">Calibrations Logs &raquo;</a>
                                        <a href="#" class="btn btn-sm btn-info w-100">Zero Cal. All Params</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php foreach($parameters as $parameter):?>
                        <div class="col-md-4 mb-3">
                            <div class="card border-primary h-100">
                                <div class="card-body ">
                                    <h3 class="h2 text-center"><?= $parameter->caption_id ?></h3>
                                    <div class="d-flex justify-content-between" style="gap: 5px;">
                                        <a href="#" class="btn btn-sm btn-info w-100">Zero Cal.</a>
                                        <a href="#" class="btn btn-sm btn-primary w-100">SPAN Cal.</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach;?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('css') ?>
<?= $this->endSection() ?>
<?= $this->section('js') ?>

<?= $this->endSection() ?>