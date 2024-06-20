<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Calibration Configuration</h5>
                    <a href="<?=base_url("calibrations")?>" class="btn btn-sm btn-secondary">Go Back</a>
                </div>
                <div class="card-body">
                    <p class="alert alert-warning text-danger dismissible" role="alert">
                        Restricted Area, all changes will be affected to measurements sensor. Please use it carefully.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </p>
                    <form id="form-config" action="<?=base_url("calibration/configuration")?>" method="post" class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">Status Auto Cal.</label>
                            <select name="name[status_auto_cal]" class="form-control" required>
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Schedule Auto Cal. At</label>
                            <input type="time" name="name[schedule_auto_cal]" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Zero Cal. Duration <small>in seconds</small></label>
                            <input type="number" name="name[zero_cal_duration]" min="60" placeholder="in seconds" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Span Cal. Duration <small>in seconds</small></label>
                            <input type="number" name="name[span_cal_duration]" min="60" placeholder="in seconds" class="form-control" required>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-sm btn-primary">
                                Save Changes
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="<?=base_url("plugins/sweetalert2/sweetalert2.min.css")?>">
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="<?=base_url("plugins/sweetalert2/sweetalert2.all.min.js")?>"></script>
<script>
    $(document).ready(function() {
        $("#form-config").submit(function(e) {
            e.preventDefault()
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: $(this).attr('action'),
                        type:'post',
                        dataType:'json',
                        data:$(this).serialize(),
                        success:function(data){
                            if(data?.success){
                                Swal.fire({
                                    icon : 'success',
                                    title : 'Success',
                                    text : data?.message,
                                    showConfirmButton : false,
                                })
                            }
                        },
                        error:function(xhr,status,error){
                            Swal.fire({
                                icon : 'error',
                                title : 'Whooops!',
                                text : xhr?.responseJSON?.message,
                                showConfirmButton : false,
                            })
                        }
                    })
                }
            })
        })
    })
</script>

<?= $this->endSection() ?>