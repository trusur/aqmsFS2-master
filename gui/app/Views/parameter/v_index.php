<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-1">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title py-0 my-0 h6">Parameter</h1>
                <div>
                    <button type="button" class="btn btn-sm btn-secondary">Filter</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="width: 100%;overflow:hidden">
                <table id="table-parameter" class="table table-sm table-hover table-striped" style="font-size: small;">
                    <thead>
                        <th>Action</th>
                        <th>Type</th>
                        <th>Code</th>
                        <th>Parameter</th>
                        <th>Unit</th>
                        <th>Is Active</th>
                        <th>Formula</th>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?= $this->include("parameter/modal_edit_parameter")?>
<!-- Span Calibraton -->
<?= $this->include("parameter/modal_span_calibration")?>


<?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="<?=base_url("plugins/datatables-bs4/css/dataTables.bootstrap4.min.css")?>">
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="<?=base_url("plugins/datatables/jquery.dataTables.min.js")?>"></script>
<script src="<?=base_url("plugins/datatables-bs4/js/dataTables.bootstrap4.min.js")?>"></script>
<script>
    $(document).ready(function(){
        const table = $('#table-parameter').DataTable({
            processing:true,
            serverSide:true,
            ajax: {
                url: '<?= base_url('parameter/datatable') ?>',
                data : function (d) {
                    d.type = $('#type').val();
                }
            },
            columns: [
                {
                    data : 'id',
                    render:function(data,type,row){
                        return `
                            <div style="font-size:smaller; column-gap: 5px" class="d-flex align-items-center">
                                <button type="button" class="btn-show btn btn-sm p-0 px-1 btn-info"><i class="fas fa-eye"></i></button>
                                <button type="button" class="btn-edit btn btn-sm p-0 px-1 btn-primary"><i class="fas fa-pen"></i></button>
                            </div>
                        `
                    }
                },
                {
                    data: 'p_type',
                    render: function (data, type, row) {
                        let text = `` 
                        switch(row.p_type){
                            case `gas`:
                                text = `<span class="badge badge-success">Gas</span>`
                                break;
                            case `particulate`:
                                text = `<span class="badge badge-warning">Particulate</span>`
                                break;
                            case `particulate_flow`:
                                text = `<span class="badge badge-warning">Particulate Flow</span>`
                                break;
                            case `weather`:
                                text = `<span class="badge badge-primary">Weather</span>`
                                break;
                            default:
                                text = `<span class="badge badge-secondary">${row.p_type}</span>`
                                break;
                        }
                        return text
                    }
                },
                {
                    data : 'code'
                },
                {
                    data : 'caption_en'
                },
                {
                    data : 'default_unit'
                },
                {
                    data : 'is_view',
                    render: function(data,type,row){
                        return row.is_view ? `<span class="badge badge-success">Active</span>` : `<span class="badge badge-danger">No</span>`
                    }
                },
                {
                    data : 'formula',
                    render: function(data,type,row){
                        return `
                            <div class="d-flex align-items-center" style="gap:5px">
                                <button type="button" class="btn-copy btn p-1 btn-primary btn-edit" style="font-size:smaller"><i class="fas fa-copy"></i></button>
                                <textarea class="form-control" readonly style="resize:none">${row.formula ?? 'Not Set'}</textarea>
                            </div>
                        `
                    }
                },

            ]

        })

        $(document).delegate('.btn-copy','click',function(){
            const sibling = $(this).siblings('textarea')
            if(!navigator.clipboard.writeText(sibling.val())){
                toastr.error(`Cant copy formula`)
            }
        })
    })

</script>


<!-- Custom JS Here -->
<!-- <script>
    $(document).ready(function() {
        var intervalChange, intervalFirst;
        // Close Modal
        $('#paramModal').on('hidden.bs.modal', function() {
            clearInterval(intervalChange)
            clearInterval(intervalFirst)
        })
        $('.btn-edit').click(function(e) {
            e.preventDefault();
            let param_id = $(this).attr('data-id');
            var btnEdit = $(this);
            btnEdit.html(`<i class="fas fa-spinner fa-spin"></i>`);
            try {
                $.ajax({
                    url: '<?= base_url('parameter/detail') ?>',
                    data: {
                        id: param_id
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            let parameter = data.data;
                            if (parameter?.p_type != "gas") {
                                $('#content-formula').hide();
                                $('#btnGenerate').hide();
                            } else {
                                $('#btnGenerate').show();
                                $('#content-formula').show();
                            }
                            $('input[name="id"]').val(parameter?.id);
                            $('input[name="code"]').val(parameter?.code);
                            $('input[name="caption_id"]').val(parameter?.caption_id);
                            $('input[name="molecular_mass"]').val(parameter?.molecular_mass);
                            $(`input[name="is_view"][value="${parameter?.is_view}"]`).attr('checked', true);
                            $(`input[name="is_graph"][value="${parameter?.is_graph}"]`).attr('checked', true);
                            $('select[name="sensor_value_id"]').val(parameter?.sensor_value_id);
                            $('input[name="voltage1"]').val(parameter?.voltage1);
                            $('input[name="voltage2"]').val(parameter?.voltage2);
                            $('input[name="concentration1"]').val(parameter?.concentration1);
                            $('input[name="concentration2"]').val(parameter?.concentration2);
                            $('input[name="formula"]').val(parameter?.formula);
                            $('#paramModal').modal('show');
                            btnEdit.html(`Edit`);
                            clearInterval(intervalFirst);
                            clearInterval(intervalChange);
                            intervalFirst = setInterval(() => {
                                getCurrentVoltage();
                            }, 1000);
                        }
                    }
                })
            } catch (err) {

            }
        });
        // Span Calibration
        $('.btn-span-calibration').click(function() {
            let param_id = $(this).attr('data-id');
            var btnEdit = $(this);
            btnEdit.html(`<i class="fas fa-spinner fa-spin"></i>`);
            try {
                $.ajax({
                    url: '<?= base_url('parameter/detail') ?>',
                    data: {
                        id: param_id
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            let parameter = data.data;
                            $('#spanModalTitle').html(`${parameter?.caption_id} Span Calibration`);
                            $('#spanGas').html(parameter?.caption_id);
                            $('#spanModal').modal('show');
                            btnEdit.html(`Span Calibration`);
                        }
                    }
                })
            } catch (err) {
                console.log(err);
            }
        })

        $('#sensor_value_id').change(function() {
            clearInterval(intervalChange);
            intervalChange = setInterval(() => {
                getCurrentVoltage();
            }, 1000);
            try {
                clearInterval(intervalFirst);
            } catch (err) {

            }
        })
        $('#btnGenerate').click(function() {
            try {
                generate_formula();
            } catch (err) {
                toastr.error(err);
            }
        })
        $('#paramModal').find('button[name="Save"]').click(function() {
            setTimeout(() => {
                location.reload();
            }, 1000);
        });

        function getCurrentVoltage() {
            let sensor_value_id = $('#sensor_value_id').val();
            if (sensor_value_id === null) {
                return;
            }
            try {
                $.ajax({
                    url: '<?= base_url('parameter/voltage') ?>',
                    data: {
                        sensor_value_id: sensor_value_id
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data?.success) {
                            try {
                                $('#voltage').val(data?.data?.value);
                                $('#sensor_pin').val(data?.data?.pin);
                                $('#sensor_reader_id').val(data?.data?.sensor_reader_id);
                            } catch (err) {
                                toastr.error(err);
                            }
                        }
                    }
                })

            } catch (err) {

            }
        }

        function generate_formula() {
            setTimeout(() => {
                let a = 0.0;
                let b = 0.0;
                let sign = "";
                let pin = $('#sensor_pin').val();
                let sensor_reader_id = $('#sensor_reader_id').val();
                let concentration2 = parseFloat($("#concentration2").val());
                let concentration1 = parseFloat($("#concentration1").val());
                let voltage1 = parseFloat($("#voltage1").val());
                let voltage2 = parseFloat($("#voltage2").val());
                let molecular_mass = parseFloat($("#molecular_mass").val()) * 1000;
                a = (concentration2 - concentration1) / (voltage2 - voltage1);
                b = concentration1 - (a * voltage1);
                console.log(a);
                console.log(b);
                if (b < 0) {
                    b = b * -1;
                    sign = "-";
                } else sign = "+";
                let formula = "round(((" + a + " * " + "explode(\";\",$sensor[" + sensor_reader_id + "][" + pin + "])[1]) " + sign + " " + b + ") * " + molecular_mass + " / 24.45,2)";
                // let formula = "round((" + a + " * " + "$sensor[" + sensor_reader_id + "][" + pin + "]) " + sign + " " + b + ",6)";
                $("#formula").val(formula);
            }, 500);
        }
    })
</script> -->
<?= $this->endSection() ?>