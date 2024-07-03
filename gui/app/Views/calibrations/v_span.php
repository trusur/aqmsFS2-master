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
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped" style="width: 100%;">
                            <tbody>
                                <tr>
                                    <th>Parameter</th>
                                    <td><?=$calibration->caption_id?></td>
                                </tr>
                                <tr>
                                    <th>Durations <small>(sec)</small></th>
                                    <td>300</td>
                                </tr>
                                <tr>
                                    <th>Target Value <small>(ppm)</small></th>
                                    <td>000</td>
                                </tr>
                                <tr>
                                    <th>Remaining <small>(sec)</small></th>
                                    <td>300</td>
                                </tr>
                                <tr>
                                    <th>Current Value <small>(ppm)</small></th>
                                    <td>000</td>
                                </tr>
                                <tr>
                                    <th>Action</th>
                                    <td>
                                        <form action="<?= base_url('calibration/span/'.$calibration->id)?>" method="post">
                                            <input type="hidden" value="<?=$calibration->id?>">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                Set SPAN
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
           <div class="card">
            <div class="card-body">
                <div id="linechart"></div>
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
<script src="<?=base_url("plugins/apexchart/dist/apexcharts.min.js")?>"></script>
<script>
    $(document).ready(function(){
        const chart = new ApexCharts(document.querySelector("#linechart"), {
            chart: {
                type: 'line',
                height: 300,
                zoom: {
                    enabled: false
                },
                animations: {
                    enabled: true,
                    easing: 'linear',
                    dynamicAnimation: {
                        speed: 1000
                    }
                }
            },
            stroke: {
                curve: 'smooth'
            },
            series: [
                {
                    name: "<?= $calibration->caption_id?> <small>ppm</small>",
                    data: []
                }
            ],
            title: {
                text: 'Value Trends by Timestamp',
                align: 'left'
            },
            xaxis: {
                type: 'category',
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                    opacity: 0.5
                },
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
        chart.render()
        function setChartAsInit(){
            chart.updateSeries([{
                name: "<?= $calibration->caption_id?> <small>ppm</small>",
                data: []
            }])
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
                            let xtime = d.created_at.split(" ")[1] ?? ""
                            series.push({
                                x : xtime,
                                y : parseFloat(d.value)
                            })
                        })
                        chart.updateSeries([
                            {
                                data : series   
                            }
                        ])
                    }else{
                        setChartAsInit()
                    }
                },
                error : function(err){
                    setChartAsInit()
                }
            })
            setTimeout(updateChart, 1000);
        }

        updateChart()
      
    })
</script>
<?= $this->endSection() ?>