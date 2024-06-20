<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header py-1">
                    <div class="d-flex justify-content-between">
                        <h1 class="h6 card-title p-0 m-0">Raw Sensor Values</h1>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div id="legend-info" style="font-size: small;" class="position-relative bg-info text-light p-2 rounded mb-3">
                            <div class="d-flex flex-column">
                                <strong> Legends:</strong>
                                <label class="text-light p-0 m-0">$sensor[<span class="text-warning">X</span>][<span class="text-danger">Y</span>]</label>
                                <label class="text-light p-0 m-0"><span class="text-warning">X</span> = Sensor Reader ID</label>
                                <label class="text-light p-0 m-0"><span class="text-danger">Y</span> = PIN</label>
                            </div>
                            <div id="close" class="position-absolute text-light" style="right: 5px; top: 5px; cursor: pointer;" data-dismiss="alert">
                                <p>Close</p>
                            </div>
                        </div>
                        <table style="width: 100%;font-size:small" class="table table-sm table-hover table-striped table-bordered">
                            <thead>
                                <th width="12%">Variable</th>
                                <th>Raw Value</th>
                                <th>Last Update</th>
                            </thead>
                            <tbody>
                                <?php foreach($sensor_values as $sensor_value):?>
                                    <tr id="sensor_value_<?=$sensor_value->id?>">
                                        <td>$sensor[<?=$sensor_value->sensor_reader_id?>][<?=$sensor_value->pin?>]</td>
                                        <td class="value"><?=$sensor_value->value?></td>
                                        <td class="timestamp text-nowrap"><?=$sensor_value->xtimestamp?></td>
                                    </tr>
                                <?php endforeach;?>
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
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // Global Variable
        let interval_realtime

        $("#close").click(function() {
            $("#legend-info").hide();
        })


        setInterval(getRealtimeValue, 1000)

        function getRealtimeValue() {
            $.ajax({
                url: '<?= base_url('rht/realtime') ?>',
                type: 'GET',
                timeout: 500,
                dataType: 'json',
                success: function(data) {
                    if(data?.success){
                        data?.data?.map(function(sensor_value){
                            let element = $(`#sensor_value_${sensor_value.id}`)
                            element.find(".value").html(sensor_value.value)
                            element.find(".timestamp").html(sensor_value.updated_at)
                        })
                    }
                },
                error: function(xhr, status, error)  {
                    return toastr.error(xhr.responseJSON.message);
                }
            })
        }
    })
</script>

<?= $this->endSection() ?>