<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <div class="btn-group" id="btn-group" role="group" aria-label="Configuration Button">
        <button type="button" data-target="config-general" class="btn btn-info">General</button>
        <button type="button" data-target="config-driver" class="btn btn-secondary">Drivers</button>
        <button type="button" data-target="config-automation" class="btn btn-secondary">Automation</button>
        <button type="button" data-target="config-integration" class="btn btn-secondary">Integration Data</button>
        <a href="<?= base_url('configuration/raw') ?>" class="btn btn-secondary">Full Configuration</a>
    </div>
    <div class="row pt-3" id="content-overlay">
        <div class="col-md-6 mx-auto" id="config-general">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title h6 p-0 m-0">General Configuration</h5>
                </div>
                <div class="card-body">
                    <form class="form-config" action="<?=base_url("configuration/update")?>" method="post">
                        <div class="row p-0">
                            <div class="mb-1 col-6">
                                <label>Station ID</label>
                                <input type="text" placeholder="Stasiun ID" name="name[id_stasiun]" value="<?= $__this->getConfiguration('id_stasiun') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="mb-1 col-6">
                                <label>Station Name </label>
                                <input type="text" placeholder="Name" name="name[name_stasiun]" value="<?= $__this->getConfiguration('name_stasiun') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="mb-1 col-6">
                                <label>City</label>
                                <input type="text" placeholder="City" name="name[city]" value="<?= $__this->getConfiguration('city') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="mb-1 col-6">
                                <label>Province</label>
                                <input type="text" placeholder="Province" name="name[province]" value="<?= $__this->getConfiguration('province') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="mb-1 col-12">
                                <label>Address</label>
                                <textarea name="name[address]" class="form-control form-control-sm"><?= $__this->getConfiguration('address') ?></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-info">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 mx-auto d-none" id="config-driver">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h1 class="card-title h6 p-0 m-0">Drivers</h1>
                        <div>
                            <button type="button" data-target="#modal-add" data-toggle="modal" class="btn btn-sm btn-primary">Add New</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflow-hidden">
                        <table id="table-drivers" style="font-size:small;width: 100%;" class="table table-sm table-hover table-stirpped">
                            <thead>
                                <th>Action</th>
                                <th>ID</th>
                                <th>Status</th>
                                <th>Driver</th>
                                <th>Sensor Code</th>
                                <th>Baud Rate / Protocol</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mx-auto d-none" id="config-automation">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title h6 p-0 m-0">Automation</h5>
                </div>
                <div class="card-body">
                    <form class="form-config" action="<?=base_url("configuration/update")?>" method="post">
                        <div class="row p-0">
                            <div class="mb-1 col-6">
                                <label class="small">Interval Data Avg. <small class="text-muted" style="font-size: smaller;">(mins)</small></label>
                                <input type="number" min="1" inputmode="numeric" placeholder="Interval in mins" name="name[data_interval]" value="<?= $__this->getConfiguration('data_interval') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="mb-1 col-6">
                                <label class="small">Pump Speed. <small class="text-muted" style="font-size: smaller;">(%)</small></label>
                                <input type="number" min="1" inputmode="numeric" placeholder="Pump speed in %" name="name[pump_speed]" value="<?= $__this->getConfiguration('pump_speed') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="mb-1 col-6">
                                <label class="d-block small">Auto Restart?</label>
                                <select name="name[is_auto_restart]" class="form-control form-control-sm">
                                    <option value="1" <?= $__this->getConfiguration('is_auto_restart') == 1 ? 'selected' : '' ?>>Yes</option>
                                    <option value="1" <?= $__this->getConfiguration('is_auto_restart') == 1 ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                            <div class="mb-1 col-6">
                                <label class="small">Restart Schedule</label>
                                <input type="time" name="restart_schedule" value="<?= $__this->getConfiguration('restart_schedule') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-info">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 mx-auto d-none" id="config-integration">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title h6 p-0 m-0">Integration Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form class="form-config" action="<?=base_url("configuration/update")?>" method="post">
                                <div class="row p-0">
                                    <div class="col-12">
                                        <label class="text-muted small" style="font-size: smaller;">Server KLHK Configuration</label>
                                    </div>
                                    <div class="mb-1 col-6">
                                        <label class="d-block small">Sent to KLHK?</label>
                                        <select name="name[is_sentto_klhk]" class="form-control form-control-sm">
                                            <option value="1" <?= $__this->getConfiguration('is_sentto_klhk') == 1 ? 'selected' : '' ?>>Yes</option>
                                            <option value="1" <?= $__this->getConfiguration('is_sentto_klhk') == 1 ? 'selected' : '' ?>>No</option>
                                        </select>
                                    </div>
                                    <div class="mb-1 col-6">
                                        <label class="small">Base URL</label>
                                        <input type="text" placeholder="Base URL" name="name[klhk_api_server]" value="<?= $__this->getConfiguration('klhk_api_server') ?>" class="form-control form-control-sm">
                                    </div>
                                    <div class="mb-1 col-6">
                                        <label class="small">Username</label>
                                        <input type="text" placeholder="Username" name="name[klhk_api_username]" value="<?= $__this->getConfiguration('klhk_api_username') ?>" class="form-control form-control-sm">
                                    </div>
                                    <div class="mb-1 col-6">
                                        <label class="small">Password</label>
                                        <input type="password" placeholder="Password" name="name[klhk_api_password]" value="<?= $__this->getConfiguration('klhk_api_password') ?>" class="form-control form-control-sm">
                                    </div>
                                    <div class="mb-1 col-12">
                                        <label class="small">API-Key</label>
                                        <input type="password" placeholder="API-Key" name="name[klhk_api_key]" value="<?= $__this->getConfiguration('klhk_api_key') ?>" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12">
                                        <label class="text-muted small" style="font-size: smaller;">Server Trusur Configuration</label>
                                    </div>
                                    
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-info">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title h6 p-0 m-0">Integration Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form class="form-config" action="<?=base_url("configuration/update")?>" method="post">
                            <div class="row p-0">
                                    <div class="col-12">
                                        <label class="text-muted small" style="font-size: smaller;">Server Trusur Configuration</label>
                                    </div>
                                    <div class="mb-1 col-6">
                                        <label class="d-block small">Sent to TRUSUR?</label>
                                        <select name="name[is_sent_to_trusur]" class="form-control form-control-sm">
                                            <option value="1" <?= $__this->getConfiguration('is_sent_to_trusur') == 1 ? 'selected' : '' ?>>Yes</option>
                                            <option value="1" <?= $__this->getConfiguration('is_sent_to_trusur') == 1 ? 'selected' : '' ?>>No</option>
                                        </select>
                                    </div>
                                    <div class="mb-1 col-6">
                                        <label class="small">Base URL</label>
                                        <input type="text" placeholder="Base URL" name="name[trusur_api_server]" value="<?= $__this->getConfiguration('trusur_api_server') ?>" class="form-control form-control-sm">
                                    </div>
                                    <div class="mb-1 col-6">
                                        <label class="small">Username</label>
                                        <input type="text" placeholder="Username" name="name[trusur_api_username]" value="<?= $__this->getConfiguration('trusur_api_username') ?>" class="form-control form-control-sm">
                                    </div>
                                    <div class="mb-1 col-6">
                                        <label class="small">Password</label>
                                        <input type="password" placeholder="Password" name="name[trusur_api_password]" value="<?= $__this->getConfiguration('trusur_api_password') ?>" class="form-control form-control-sm">
                                    </div>
                                    <div class="mb-1 col-12">
                                        <label class="small">API-Key</label>
                                        <input type="password" placeholder="API-Key" name="name[trusur_api_key]" value="<?= $__this->getConfiguration('trusur_api_key') ?>" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-info">Save Changes</button>
                                    </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('modal') ?>
<div class="modal" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="modal-editTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-1 my-0">
                <h2 class="modal-title py-0 m-0 h6" id="exampleModalLongTitle">Edit Driver</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body my-0">
                <form id="form-edit-driver" action="<?= base_url('configuration/edit-driver') ?>" method="post">
                    <div class="row">
                        <div class="mb-1 col-12">
                            <input name="id" type="hidden">
                            <label class="small">Driver</label>
                            <input name="driver" type="text" readonly class="form-control form-control-sm">
                        </div>
                        <div class="mb-1 col-6">
                            <label class="small">Sensor Code</label>
                            <input name="sensor_code" required placeholder="ttyUSB* or 192.168.*.*"  class="form-control form-control-sm">
                        </div>
                        <div class="mb-1 col-6">
                            <label class="small">Baudrate / Protocol</label>
                            <input name="baud_rate" placeholder="Baudrate / Protocol"  class="form-control form-control-sm">
                        </div>
                        <div class="mb-1 col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="modal-add" tabindex="-1" role="dialog" aria-labelledby="modal-addTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-1 my-0">
                <h2 class="modal-title py-0 m-0 h6" id="exampleModalLongTitle">Add Driver</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body my-0">
                <form id="form-add-driver" action="<?= base_url('configuration/add-driver') ?>" method="post">
                    <div class="row">
                        <div class="mb-1 col-12">
                            <label class="small">Driver</label>
                            <select name="driver" required class="form-control form-control-sm">
                                <option value="">Select Driver</option>
                                <?php foreach($drivers as $driver):?>
                                    <option value="<?= $driver ?>"><?= $driver ?></option>    
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div class="mb-1 col-6">
                            <label class="small">Sensor Code</label>
                            <input name="sensor_code" required placeholder="ttyUSB* or 192.168.*.*"  class="form-control form-control-sm">
                        </div>
                        <div class="mb-1 col-6">
                            <label class="small">Baudrate / Protocol</label>
                            <input name="baud_rate" placeholder="Baudrate / Protocol"  class="form-control form-control-sm">
                        </div>
                        <div class="mb-1 col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-sm btn-primary">Add New Driver</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url("plugins/datatables-bs4/css/dataTables.bootstrap4.min.css")?>">
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="<?= base_url("plugins/datatables/jquery.dataTables.min.js") ?>"></script>
<script src="<?= base_url("plugins/datatables-bs4/js/dataTables.bootstrap4.min.js")?>"></script>
<script>
    $(document).ready(function(){
        $('#btn-group button').click(function(){
            const target = $(this).data('target')
            $('#btn-group button').removeClass('btn-info').addClass('btn-secondary')
            $(this).addClass('btn-info').removeClass('btn-secondary')

            // Overlay
            $('#content-overlay > div').addClass('d-none')
            $(`#${target}`).removeClass('d-none')
        })

        const table = $('#table-drivers').DataTable({
            theme : 'bootstrap4',
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('configuration/datatable_drivers') ?>',
                data : function (d) {
                    d.active = $('#active').val();
                },
            },
            columns: [
                {
                    data : 'id',
                    render: function (data, type, row) {
                        return `
                        <span class="btn-edit badge badge-primary p-1" style="cursor:pointer" data-id="${row.id}">
                            <i class="fas fa-pen"></i>
                        </span>
                        <span class="btn-delete badge badge-danger p-1" style="cursor:pointer" data-id="${row.id}">
                            <i class="fas fa-trash"></i>
                        </span>
                        `
                    }
                },
                {
                    data : 'id',
                },
                {
                    data : 'sensor_code',
                    render: function (data, type, row) {
                        return row?.sensor_code ? `<span class="badge badge-success">Active</span>` : `<span class="badge badge-dark">Not Use</span>`
                    }
                },
                {
                    data : 'driver',
                },
                {
                    data : 'sensor_code',
                },
                {
                    data : 'baud_rate',
                },
            ]
        })
        
        $(document).delegate('.btn-edit', 'click', function(e){
            e.preventDefault()
            const id = $(this).data('id')
            $.ajax({
                url : `<?= base_url('configuration/get-driver/') ?>${id}`,
                dataType : 'json',
                success:function(data){
                    if(data?.success){
                        const driver = data?.data
                        $('#modal-edit').modal('show')
                        $('#form-edit-driver input[name="id"]').val(driver?.id)
                        $('#form-edit-driver input[name="driver"]').val(driver?.driver)
                        $('#form-edit-driver input[name="sensor_code"]').val(driver?.sensor_code)
                        $('#form-edit-driver input[name="baud_rate"]').val(driver?.baud_rate)
                    }
                }
            })
        })
        $(document).delegate('.btn-delete', 'click', function(e){
            e.preventDefault()
            const id = $(this).data('id')
            if(confirm(`Delete driver may cause data loss & re-configuration. Are you sure?`)){
                $.ajax({
                    url : `<?= base_url('configuration/delete-driver/') ?>${id}`,
                    type : 'post',
                    dataType : 'json',
                    success:function(data){
                        if(data?.success){
                            return table.ajax.reload()
                        }
                    },
                    error: function(xhr, status, err) {
                        return toastr.error(xhr.responseJSON?.message)
                    }
                })
            }
        })

        $("#form-add-driver, #form-edit-driver").submit(function(e){
            e.preventDefault()
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType:'json',
                success: function(data){
                    if(data?.success){
                        toastr.success(data?.message)
                        $(this).find('.btn-reset').trigger('click')
                        $('#modal-add').modal('hide')
                        $('#modal-edit').modal('hide')
                        return table.ajax.reload()
                    }
                },
                error: function(xhr, status, err) {
                    return toastr.error(xhr.responseJSON?.message)
                }
            })
        })
        $(".form-config").submit(function(e){
            e.preventDefault()
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType:'json',
                success: function(data){
                    if(data?.success){
                        toastr.success(data?.message)
                    }
                },  
                error: function(xhr, status, err) {
                    return toastr.error(xhr.responseJSON?.message)
                }
            })
        })

    })
</script>
<?= $this->endSection() ?>