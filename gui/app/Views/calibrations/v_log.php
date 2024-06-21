<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Calibration Logs</h5>
                    <div>
                        <button type="button" data-toggle="modal" data-target="#modal-filter" class="btn btn-sm btn-info">Filter</button>
                        <a href="<?= base_url('calibration') ?>" class="btn btn-sm btn-secondary">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-logs" class="table table-sm table-striped table-bordered" style="width:100%; font-size:small">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Parameter</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Target Value</th>
                                    <th>Value Before</th>
                                    <th>Value After</th>
                                    <th>Duration</th> 
                                    <th>Start</th>
                                    <th>End</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('modal') ?>
<div class="modal" id="modal-filter" tabindex="-1" role="dialog" aria-labelledby="modal-filter-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h2 class="modal-title p-0 my-0 h6">Filter</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-filter"  class="row">
                    <div class="col-md-12 form-group mb-1">
                        <label>Parameter</label>
                        <select name="parameter_id" class="form-control">
                            <option value="">All</option>
                            <?php foreach ($parameters as $parameter): ?>
                                <option value="<?= $parameter->id ?>"><?= $parameter->caption_id ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-1">
                        <label>Type</label>
                        <select name="calibration_type" class="form-control">
                            <option value="">All</option>
                            <option value="0">Zero</option>
                            <option value="1">Span</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-1">
                        <label>Status</label>
                        <select name="is_executed" class="form-control">
                            <option value="">All</option>
                            <option value="0">Pending</option>
                            <option value="1">On Progress</option>
                            <option value="2">Done</option>
                            <option value="3">Failed</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="reset" class="d-none">Reset</button>
                        <button type="submit" class="btn btn-sm btn-info">Set Filter</button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url("plugins/datatables-bs4/css/dataTables.bootstrap4.min.css") ?>">
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="<?= base_url("plugins/datatables/jquery.dataTables.min.js") ?>"></script>
<script src="<?= base_url("plugins/datatables-bs4/js/dataTables.bootstrap4.min.js") ?>"></script>
<script>
    $(document).ready(function(){
        const table = $("#table-logs").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('calibration/datatable-logs') ?>",
                data: function (params) {
                    params.parameter_id = $('#form-filter select[name="parameter_id"]').val();
                    params.calibration_type = $('#form-filter select[name="calibration_type"]').val();
                    params.is_executed = $('#form-filter select[name="is_executed"]').val();
                }
            },
            columns: [
                {data: "id"},
                {
                    data: "parameter_id",
                    render: function (data, type, row) {
                        return row?.caption_id
                    }
                },
                {
                    data: "is_executed",
                    render: function (data) {
                        switch (data) {
                            case '1':
                                return `<span class="badge badge-primary">On Progress</span>`;
                                break;
                            case '2':
                                return `<span class="badge badge-success">Done</span>`;
                                break;
                            case '3':
                                return `<span class="badge badge-danger">Failed</span>`;
                                break;
                            case '0':
                            default:
                                return `<span class="badge badge-secondary">Pending</span>`;
                                break;
                        }
                    }
                },
                {
                    data: "calibration_type",
                    render: function (data) {

                        return data == 1 ? `<span class="badge badge-success">SPAN</span>` : `<span class="badge badge-primary">Zero</span>`;
                    }

                },
                {data: "target_value"},
                {data: "value_before"},
                {data: "value_after"},
                {data: "duration"},
                {data: "start_calibration"},
                {data: "end_calibration"},
            ]
        })
        $("#form-filter").submit(function(e){
            e.preventDefault();
            table.ajax.reload();
            $("#modal-filter").modal("hide");
        })
    })
</script>
<?= $this->endSection() ?>