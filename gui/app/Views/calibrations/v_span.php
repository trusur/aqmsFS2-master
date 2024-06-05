<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-5">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h2 text-light">Span <?= lang('Global.Calibration') ?></h1>
        <div>
            <a href="#" onclick="return window.history.go(-1)" class="btn btn-sm btn-primary">
                <i class="fas fa-xs fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <form action="">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label><?= lang('Global.Voltage Field') ?></label>
                            <select name="" class="form-control"></select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Span <?= lang('Global.Concentration') ?></label>
                                    <input type="text" name="" placeholder="Span <?= lang('Global.Concentration') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Span <?= lang('Global.Voltage') ?></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Span <?= lang('Global.Voltage') ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-info" type="button" id="button-addon2"><i class="fas fa-check"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?= lang('Global.Voltage') ?></label>
                            <input type="text" name="" placeholder="<?= lang('Global.Voltage') ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Formula</label>
                            <input type="text" name="" placeholder="Formula" class="form-control">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="Save" class="btn btn-info" id="btn-save"><?= lang('Global.Save Changes') ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="position-fixed" style="z-index: 999;right:11vw;bottom:20px;">
                
            </div> -->
    </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('css') ?>
<!-- Custom CSS Here -->
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<!-- Custom JS Here -->
<?= $this->endSection() ?>