<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Calibrations</h5>
                    <div>
                       <a href="<?= base_url('configuration') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
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
                                        <a href="<?= base_url('calibration/configuration') ?>" class="btn btn-sm btn-secondary w-100">Calibration List &raquo;</a>
                                        <a href="<?= base_url('calibration/logs') ?>" class="btn btn-sm btn-secondary w-100">Calibraion Logs &raquo;</a>
                                        <!-- <button disabled style="cursor:not-allowed" class="btn btn-sm btn-info w-100">Zero Cal. All Params</button> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php foreach ($parameters as $parameter): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border-primary h-100">
                                    <div class="card-body ">
                                        <h3 class="h2 text-center"><?= $parameter->caption_id ?></h3>
                                        <div class="d-flex justify-content-between" style="gap: 5px;">
                                            <button type="button" data-caption="<?= $parameter->caption_id ?>" data-id="<?= $parameter->id ?>" class="btn btn-cal-zero btn-sm btn-info w-100">Zero Cal.</button>
                                            <button type="button" data-caption="<?= $parameter->caption_id ?>" data-id="<?= $parameter->id ?>" class="btn btn-cal-span btn-sm btn-primary w-100">SPAN Cal.</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
<script src="<?= base_url("plugins/sweetalert2/sweetalert2.all.min.js") ?>"></script>
<script>
    $(document).ready(function() {
        $('.btn-cal-zero').click(function() {
            let id = $(this).data('id')
            let parameter = $(this).data('caption')
            Swal.fire({
                title: 'Are you sure?',
                html: `
                    <p>Continue to zero calibration for ${parameter}?</p>
                    <p class="alert alert-warning text-danger dismissable" role="alert">
                        <i class="fas fa-info-circle fa-xs mr-1"></i> Pastikan aliran gas kalibrasi sudah terpasang dengan benar pada saluran sampling dengan laju alir 0.9 lpm
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </p>

                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, zero calibration!',
                // input: 'number',
                // inputLabel: 'Target Value',
                // inputValue: 0,
                // inputPlaceholder: 'Target Value',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('calibration/zero') ?>',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            Swal.fire(
                                'Zero Calibration Success!',
                                response.message,
                                'success'
                            ).then((result) => {
                                location.reload()
                            })
                        }
                    })
                }
            })
        })
        $('.btn-cal-span').click(function() {
            let id = $(this).data('id')
            let parameter = $(this).data('caption')
            Swal.fire({
                title: 'Are you sure?',
                html: `
                    <p>Continue to span calibration for ${parameter}?</p>
                    <p class="alert alert-warning text-danger dismissable" role="alert">
                        <i class="fas fa-info-circle fa-xs mr-1"></i> Pastikan aliran gas kalibrasi sudah terpasang dengan benar pada saluran sampling dengan laju alir 0.9 lpm
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, span calibration!',
                input: 'number',
                inputLabel: 'Target Value',
                inputValue: 1,
                inputPlaceholder: 'Target Value',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('calibrations') ?>',
                        type: 'POST',
                        data: {
                            parameter_id: id,
                            calibration_type: 1,
                            target_value: result.value,
                        },
                        success: function(response) {
                            console.log(response)
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
                                confirmButtonText: 'Lanjut Kalibrasi',
                            }).then((result) => {
                                location.href = `<?= base_url('calibration/span') ?>/${response?.data ?? ''}`
                            })
                        }
                    })
                }
            })
        })
    })
</script>

<?= $this->endSection() ?>