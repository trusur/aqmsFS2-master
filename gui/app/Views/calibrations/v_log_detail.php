<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Calibration Logs </h5>
                    <div>
                        <a href="<?= base_url('calibration/logs') ?>" class="btn btn-sm btn-secondary">Go Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <table class="table table-sm table-striped">
                                <tbody>
                                    <tr>
                                        <th>Calibration ID</th>
                                        <td><?= $calibration->id ?></td>
                                    </tr>
                                    <tr>
                                        <th>Parameter</th>
                                        <td><?= $calibration->caption_id ?></td>
                                    </tr>
                                    <tr>
                                        <th>Type</th>
                                        <td><?= $calibration->calibration_type == 1 ? "Span" : "Zero" ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><?= $calibration->is_executed == 2 ? "Done" : ($calibration->is_executed == 1 ? "On Progress" : ($calibration->is_executed == 3 ? "Failed" : "Pending")) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Duration</th>
                                        <td><?= $calibration->duration ?></td>
                                    </tr>
                                    <tr>
                                        <th>Target Value</th>
                                        <td><?= $calibration->target_value ?></td>
                                    </tr>
                                    <tr>
                                        <th>Before Value</th>
                                        <td><?= $calibration->value_before ?></td>
                                    </tr>
                                    <tr>
                                        <th>After Value</th>
                                        <td><?= $calibration->value_before ?></td>
                                    </tr>
                                    <tr>
                                        <th>Start Calibration</th>
                                        <td><?= $calibration->start_calibration ?></td>
                                    </tr>
                                    <tr>
                                        <th>End Calibration</th>
                                        <td><?= $calibration->end_calibration ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-8">
                            <div id="linechart" class="w-100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="<?=base_url("plugins/apexchart/dist/apexcharts.min.js")?>"></script>
<script>
    $(document).ready(function(){
        const chart = new ApexCharts(document.querySelector("#linechart"), {
            series: [
                {
                    name: "<?= $calibration->caption_id?> <small>ppm</small>",
                    data: []
                }
            ],
            chart: {
                height: '100%',
                type: 'line',
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: true
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: 'Value Trends by Timestamp',
                align: 'left'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: [],
            },
            annotations: {
                yaxis: [
                    {
                    y: <?= $calibration->target_value ?>,
                        borderColor: '#FF2171',
                        label: {
                            borderColor: '#FF2171',
                            style: {
                            color: '#fff',
                            background: '#FF2171'
                            },
                            text: 'Target Value : <?= $calibration->target_value ?>'
                        }
                    }
                ]
            }
        });
        chart.render();
        function setChartAsInit(){
            chart.updateSeries([{
                name: "<?= $calibration->caption_id?> <small>ppm</small>",
                data: []
            }])
            chart.updateOptions({
                xaxis: {
                    categories: []
                }
            })
        }

        function updateChart() {
            $.ajax({
                url: "<?= base_url('calibration/log/calibration-log/'.$calibration->id) ?>",
                dataType : "json",
                success : function(response){
                    let {data} = response
                    if(data.length > 0){
                        data = data.reverse()
                        let series = [], labels = []
                        data.forEach(d => {
                            series.push(d.value)
                            labels.push(d.created_at.split(" ")[1] ?? "")
                        })
                        chart.updateSeries([{
                            name: "<?= $calibration->caption_id?> <small>ppm</small>",
                            data: series
                        }])
                        chart.updateOptions({
                            xaxis: {
                                categories: labels
                            }
                        })
                    }else{
                        setChartAsInit()
                    }
                },
                error : function(err){
                    setChartAsInit()
                }
            })
        }

        updateChart()
      
    })
</script>
<?= $this->endSection() ?>