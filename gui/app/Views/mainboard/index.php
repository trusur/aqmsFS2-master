<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">List Mainboard Command</h5>
                    <div class="d-flex justify-content-between align-items-center" style="gap:5px">
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-create"><i class="fas fa-plus"></i> Add new</button>
                        <a href="<?= base_url('configurations') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>

                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?php if (request()->getGet('success')) : ?>
                            <p class="alert alert-success dismissible fade show" role="alert">
                                <i class="fas fa-info-cirlce"></i> Mainboard has been saved 
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><i class="fas fa-times"></i></button>
                            </p>
                            <?php endif; ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped" id="table-mainboards">
                                    <thead>
                                        <tr>
                                            <th>Active</th>
                                            <th>Sensor Name</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Command</th>
                                            <th>Prexif Return</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mainboards as $mainboard) : ?>
                                            <tr>
                                                <td>
                                                    <button class="btn btn-sm btn-primary btn-edit" data-id="<?= $mainboard->id ?>"><i class="fas fa-pen"></i></button>
                                                    <?php if (request()->getGet("mode") == 1) : ?>
                                                        <a href="<?= base_url("configuration/mainboard/delete/$mainboard->id") ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $mainboard->sensorname ?></td>
                                                <td><?= $mainboard->type ?></td>
                                                <td><?= $mainboard->is_enable == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?></td>
                                                <td><?= $mainboard->is_priority ?></td>
                                                <td><?= $mainboard->command ?></td>
                                                <td><?= $mainboard->prefix_return ?></td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('modal') ?>

<div class="modal" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="modal-edit-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h2 class="modal-title p-0 my-0 h6">Edit Mainboard</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-edit" method="post" action="" class="row">
                    <div class="col-md-12 form-group mb-1">
                        <label>Sensor Name</label>
                        <input type="text" placeholder="Sensor Name" name="sensorname" class="form-control" required>
                    </div>
                    <div class="col-md-4 form-group mb-1">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            <option value="read">Read</option>
                            <option value="setting">Setting</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-1">
                        <label>Status</label>
                        <select name="is_enable" class="form-control" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-1">
                        <label>Priority</label>
                        <input type="number" placeholder="Priority" name="is_priority" class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group mb-1">
                        <label>Command</label>
                        <input type="text" placeholder="Command" name="command" class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group mb-1">
                        <label>Prefix Return</label>
                        <input type="text" placeholder="Prefix Return" name="prefix_return" class="form-control" required>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="reset" class="d-none">Reset</button>
                        <button type="submit" class="btn btn-sm btn-info">Save Changes</button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
<div class="modal" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="modal-create-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h2 class="modal-title p-0 my-0 h6">Edit Mainboard</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-create" method="post" action="<?= base_url('configuration/mainboard') ?>" class="row">
                    <div class="col-md-12 form-group mb-1">
                        <label>Sensor Name</label>
                        <input type="text" placeholder="Sensor Name" name="sensorname" class="form-control" required>
                    </div>
                    <div class="col-md-4 form-group mb-1">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            <option value="read">Read</option>
                            <option value="setting">Setting</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-1">
                        <label>Status</label>
                        <select name="is_enable" class="form-control" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-1">
                        <label>Priority</label>
                        <input type="number" placeholder="Priority" name="is_priority" class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group mb-1">
                        <label>Command</label>
                        <input type="text" placeholder="Command" name="command" class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group mb-1">
                        <label>Prefix Return</label>
                        <input type="text" placeholder="Prefix Return" name="prefix_return" class="form-control" required>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="reset" class="d-none">Reset</button>
                        <button type="submit" class="btn btn-sm btn-info">Add New</button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="<?=base_url("plugins/datatables-bs4/css/dataTables.bootstrap4.min.css")?>">
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="<?= base_url("plugins/sweetalert2/sweetalert2.all.min.js") ?>"></script>
<script src="<?= base_url("plugins/datatables/jquery.dataTables.min.js") ?>"></script>
<script src="<?= base_url("plugins/datatables-bs4/js/dataTables.bootstrap4.min.js") ?>"></script>
<script>
    $(document).ready(function() {
        const table = $('#table-mainboards').DataTable({
            theme: 'bootstrap',
        });
        table.on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            $.ajax({
                url: `<?= base_url('configuration/mainboard/json') ?>/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data?.success) {
                        $("#modal-edit").modal('show');
                        $("#form-edit").attr('action', `<?= base_url('configuration/mainboard') ?>/${id}`);
                        $("#form-edit").find('input[name="sensorname"]').val(data.data.sensorname);
                        $("#form-edit").find('select[name="type"]').val(data.data.type);
                        $("#form-edit").find('select[name="is_enable"]').val(data.data.is_enable);
                        $("#form-edit").find('input[name="is_priority"]').val(data.data.is_priority);
                        $("#form-edit").find('input[name="command"]').val(data.data.command);
                        $("#form-edit").find('input[name="prefix_return"]').val(data.data.prefix_return);
                    }
                }
            })
        })
    })
</script>
<?= $this->endSection() ?>