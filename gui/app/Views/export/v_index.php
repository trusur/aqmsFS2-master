<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="h5">Filter</h3>
                    <div class="d-flex justify-content-between mb-3">
                        <form id="form-filter-date" class="form-inline" style="gap:5px">
                            <label class="sr-only">Begin Time</label>
                            <select name="data_source" class="form-control form-control-sm">
                                <option value="">measurements</option>
                                <?php foreach($data_sources as $table):?>
                                    <option value="<?=$table?>"><?=$table?></option>
                                <?php endforeach;?>
                            </select>
                            <label class="sr-only">Begin Time</label>
                            <input type="datetime-local" name="begindate" class="form-control form-control-sm" title="Begin Time">
                            <label class="sr-only">End Time</label>
                            <input type="datetime-local" name="enddate" class="form-control form-control-sm" title="End Time">
                            <button type="button" id="btn-filter" class="btn btn-sm btn-outline-primary" title="Filter">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" id="btn-export" class="btn btn-sm btn-outline-success" title="Export">
                                <i class="fas fa-file"></i> Export Filtered
                            </button>
                        </form>

                    </div>
                    <div class="table-responsive">
                        <table id="export-tbl" style="width: 100%;font-size:x-small" class="table table-sm stripped">
                            <thead>
                                <tr>
                                    <th>STASIUN</th>
                                    <th>WAKTU</th>
                                    <th>STATUS</th>
                                    <?php foreach ($parameters as $parameter) : ?>
                                        <th><?= $parameter->caption_en; ?></th>
                                    <?php endforeach ?>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
<?= $this->endSection() ?>
<?= $this->section('css') ?>
<!-- Custom CSS Here -->
<link rel="stylesheet" href="<?=base_url("plugins/datatables-bs4/css/dataTables.bootstrap4.min.css")?>">
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<!-- Custom JS Here -->
<script src="<?= base_url('plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        <?php if(session()->get("error")):?>
            toastr.error(`<?= session()->get("error") ?>`);
        <?php endif;?>
        
        const table = $('table[id="export-tbl"]').DataTable({
            pageLength: 5,
            searching: false,
            dom: 'Bfrtip',
            ajax: {
                url: '<?= site_url('export/datatable') ?>',
                data : function(d) {
                    d.begindate = $('#form-filter-date input[name="begindate"]').val();
                    d.enddate = $('#form-filter-date input[name="enddate"]').val();
                }
            },  
            processing: true,
            serverSide: true,
            columns: [
                {
                    data: 'id_stasiun',
                },
                {
                    data: 'waktu'
                },
                {
                    data: 'is_sent_cloud',
                    render: function(data,type,row) {
                        return row?.is_sent_cloud == 1 ? `<span class="badge badge-success">Sent</span>` : `<span class="badge badge-danger">Not Sent</span>`
                    }
                },
                <?php foreach ($parameters as $parameter) : ?> {
                        data: '<?= $parameter->code; ?>'
                    },
                <?php endforeach ?>
            ]
        });

        $('#btn-filter').click(function() {
            table.ajax.reload()
        })

        $('#btn-export').click(function() {
            toastr.info(`Processing...`)
            $(this).html(`<i class="fas fa-spinner fa-spin"></i> Exporting...`)
            let begindate = $('#form-filter-date input[name="begindate"]').val()
            let enddate = $('#form-filter-date input[name="enddate"]').val()
            let data_source = $('#form-filter-date input[name="data_source"]').val() ?? 'measurements'
            window.location.href = `<?= base_url('export/csv') ?>?begindate=${begindate}&enddate=${enddate}&data_source=${data_source}`
            return $(this).html(`<i class="fas fa-file"></i> Export Filtered`)
        })
    });
</script>
<?= $this->endSection() ?>