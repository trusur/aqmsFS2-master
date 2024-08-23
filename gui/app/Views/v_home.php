<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-1">
    <div class="row mt-2 justify-content-start bg-dark">
        <div class="col-sm-4 mx-2" style="overflow: hidden">
            <?php if (count($particulates) > 0) : ?>
                <h1 class="h4 mt-2 text-light" data-intro="Partikulat"><?= lang('Global.Particulate') ?></h1>
                <div id="particulate">
                    <?php foreach ($particulates as $particulate) : ?>
                        <div class="my-1 mx-n2 shadow px-3 py-2 rounded" style="border:5px solid RGBA(28,183,160,1);background: RGBA(28,183,160,0.7);">
                            <span class="h6 py-0 font-weight-bold text-light"><?= $particulate->caption_id ?> <small style="font-size: smaller;">(<?= $particulate->default_unit ?>)</small></span>
                            <div class="m-0 row">
                                <div class="col-auto d-flex align-items-center justify-content-start text-light">
                                    <h3 class="h1 mr-1 text-light" id="value_<?= $particulate->code ?>">0</h3>
                                </div>
                                <div class="col d-flex align-items-center justify-content-end" style="color:#FFFF00">
                                    <h3 class="h5 mr-1" id="value_<?= $particulate->code ?>_flow" style="color:#FFFF00"></h3>
                                    l/mnt
                                </div>
                            </div>
                            <div class="row text-right text-light d-none p-0 m-0">
                                <small class="col-12 xtimestamp" id="time_<?= $particulate->code ?>" style="font-size: 10px"><?=date("Y-m-d H:i:s")?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif ?>
        </div>
        <div class="col-sm mx-2">
            <h1 class="h4 mt-2 text-light" data-intro="Gas"><?= lang('Global.Gases') ?></h1>
            <div id="gas-content">
                <?php foreach ($gases as $gas) : ?>
                    <div class="my-1 mx-n2 shadow px-3 rounded" style="border:5px solid RGB(124,122,243);background: RGBA(124,122,243,0.7);">
                        <div class="">
                            <div class="py-0 font-weight-bold text-light">
                                <span class="h5"><?= $gas->caption_id ?></span>
                                <small style="color:#FFFF00;font-size: smaller">(<i <?php if ($gas->default_unit == "µg/m<sup>3") : ?> class="switch-unit"> <?php endif ?><?= $gas->default_unit ?></i>)</small>
                            </div>
                            <span class="py-0 small font-weight-bold sensor d-none text-light" id="svalue_<?= $gas->code ?>">0</span>
                        </div>
                        <div class="m-0 d-flex justify-content-center">
                            <div class="d-flex align-items-center">
                                <h3 class="h3 mr-1 text-light" id="value_<?= $gas->code ?>">0</h3>
                            </div>
                        </div>
                        <div class="row text-right text-light d-none p-0 m-0">
                            <small class="col-12 xtimestamp" id="time_<?= $gas->code ?>" style="font-size: 10px"><?=date("Y-m-d H:i:s")?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-sm mx-2">
            <h1 class="h4 mt-2 text-light" data-intro="Cuaca"><?= lang('Global.Meteorology') ?></h1>
            <div id="meteorologi-content">
                <?php foreach ($weathers as $wheather) : ?>
                    <div class="my-1 mx-n2 shadow px-3 rounded" style="border:5px solid RGB(99,173,252);background: RGBA(99,173,252,0.7);">
                        <span class="h6 font-weight-bold text-light"><?= $wheather->caption_id ?></span>
                        <div class="m-0 d-flex justify-content-center text-light">
                            <div class="d-flex align-items-center">
                                <h3 class="h4 mr-1 text-light" id="value_<?= $wheather->code ?>">0</h3>
                                <?= $wheather->default_unit ?>
                            </div>
                        </div>
                        <div class="row text-right text-light d-none p-0 m-0">
                            <small class="col-12 xtimestamp" id="time_<?= $wheather->code ?>" style="font-size: 10px"><?=date("Y-m-d H:i:s")?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 my-2">
            <div class="px-3 mb-md-0 mb-3 overflow-hidden">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center align-sm-items-start">
                    <div id="location">
                        <div id="location">
                            <h2 class="h4 text-light p-0 m-0" style="display:inline-block;" style="cursor: pointer;" unselectable="on" onselectstart="return false;" onmousedown="return false;"><?= $stationname ?? 'AQMS' ?></h2>
                            <h2 class="h6 text-light p-0 m-0" id="date"></h2>
                        </div>

                    </div>
                    <div>
                        <div id="unit" class="my-1 d-flex flex-column flex-md-row justify-content-between align-md-items-center">
                            <div class="mr-3">
                                <h7 class="text-light" style="display:inline-block;"><b><?= lang('Global.Unit') ?></b></h7>
                            </div>
                            <div>
                                <span class="text-light" id="unit-content" style="font-weight:bolder;font-size:18px;">(µg/m<sup>3</sup>)</span>
                                <button type="button" class="btn-dark rounded border border-light btn btn-sm btn-info" id="btn-unit" data-intro="<?= lang('Global.intro_change_unit') ?>">
                                    <?= lang('Global.Switch') ?>
                                </button>
                            </div>
                        </div>
                        <?php if ($pump_interval > 0) : ?>
                            <div id="pump" class="my-1 d-flex flex-column flex-md-row justify-content-between align-md-items-center">
                                <div class="mr-3">
                                    <h7 class="text-light" style="display:inline-block;"><b><?= lang('Global.Pump') ?></b></h7>
                                </div>
                                <div>
                                    <span class="text-light" id="pumpState" style="font-weight:bolder;font-size:20px;"><i class="fas fa-spinner fa-spin"></i></span>
                                    <span id="pumpTimer" class="small text-light" style="font-weight:bolder;font-size:18px;"><i class="fas fa-spinner fa-spin"></i></span>
                                    <button type="button" id="switch_pump" class="btn btn-dark rounded border border-light btn-sm btn-info" data-intro="<?= lang('Global.intro_change_pump') ?>">

                                        <?= lang('Global.Switch') ?>
                                    </button>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('js') ?>

<script>
    $(document).ready(function() {
        // Setting Global Variable
        var begin = 1;
        var beginUnit = 1;

        setInterval(() => {
            // Realtime Get Data
            $.ajax({
                url: `<?= base_url('measurementlog') ?>`,
                dataType: 'json',
                success: function(data) {
                    if (data?.logs) {
                        data?.logs.map(function(value, index) {
                            try {
                                let param_value = cleanStr(value?.measured);
                                let default_unit = cleanStr(value?.default_unit);
                                let molecular_mass = cleanStr(value?.molecular_mass);
                                let p_type = value?.p_type
                                if (p_type == 'gas' && default_unit == "µg/m<sup>3") {
                                    let unit_id = parseInt(localStorage.getItem('unit_id'));
                                    switch (unit_id) {
                                        case 2:
                                            param_value = calculatePpm(param_value, molecular_mass);
                                            break;
                                        case 3:
                                            param_value = calculatePpm(param_value, molecular_mass) * 1000;
                                            break;
                                        case 1:
                                        default:
                                            param_value = param_value
                                            break;
                                    }
                                }
                                $(`#value_${value?.code}`).html(param_value)
                                $(`#value_${value?.code}`).attr("title",value.xtimestamp)
                                $(`#time_${value?.code}`).html(value.xtimestamp)
                            } catch (err) {
                                console.log(err)
                            }

                        });
                        try {
                            let pump_state = data?.config?.pump_state;
                            let curent = new Date(data?.config?.now);
                            let pump_last = new Date(data?.config?.pump_last);
                            let pump_interval = data?.config?.pump_interval;
                            let pump_state_time = (curent - pump_last) / 1000;
                            let remaining = (pump_interval * 60) - pump_state_time;
                            let h = Math.floor(remaining / 3600);
                            let m = Math.floor((remaining - (h * 3600)) / 60);
                            let s = Math.floor(remaining % 60);
                            let pumpTimer = `${h}:${m}:${s}`;
                            if (pumpTimer == `0:0:0` || parseInt(h) < 0 || parseInt(m) < 0 || parseInt(s) < 0) {
                                $('#switch_pump').click();
                            }
                            $('#pumpTimer').html(pumpTimer);
                            $('#pumpState').html(`(Pump ${Math.floor(parseInt(pump_state)+1)})`)
                        } catch (err) {
                            console.log(err)
                        }
                    }

                },
                error: function(xhr, status, err) {
                    console.log(err);
                }
            })
        }, 1000);

        // Trigger Button Change Unit
        $('#btn-unit').click(function(e) {
            beginUnit++;
            if (beginUnit > 3) {
                beginUnit = 1;
            }
            localStorage.setItem('unit_id', beginUnit)
            switchUnit(beginUnit)
        });

        function switchUnit(id) {
            id = parseInt(id)
            switch (id) {
                case 2: //ppm
                    $('#unit-content').html(`(ppm)`);
                    unit = `ppm`;
                    break;
                case 3: //ppb
                    $('#unit-content').html(`(ppb)`);
                    unit = `ppb`;
                    break;
                case 1: //micro
                default:
                    $('#unit-content').html(`(µg/m<sup>3</sup>)`);
                    unit = `µg/m<sup>3</sup>`;
                    break;
            }
            $('.switch-unit').html(unit)
        }

        if(localStorage.getItem('unit_id')){
            switchUnit(localStorage.getItem('unit_id'))
        }

        // Trigger Switch Pump
        $("#switch_pump").click(function() {
            $.ajax({
                type: 'POST',
                url: '/switch/pump',
                dataType: 'json',
                success: function(data) {
                    if (data?.success) {
                        toastr.success(`Pump switched`);   
                    }
                }
            })
        })

        // Trigger Show Last 
        let is_show_time = 0
        $('#location').click(function(e) {
            is_show_time += 1
            if(is_show_time > 2){
                $('.xtimestamp').parent().removeClass('d-none')
                is_show_time = 0
            }else{
                $('.xtimestamp').parent().addClass('d-none')
            }
        })

        function calculatePpm(ug, molecular_mass) {

            try {
                console.log(ug)
                ug = parseFloat(ug);
                molecular_mass = parseFloat(molecular_mass);
                let value = (ug * 24.45) / (1000 * molecular_mass);
                return value.toFixed(3);
            } catch (err) {
                toastr.error(`Error while calculating ppm`);
                return 0;
            }
        }
    });
</script>
<script>
    function cleanStr(str) {
        try {
            if (str === undefined || str === null || str === "") {
                return `0`;
            }
        } catch (err) {
            return `0`;
        }
        return str;
    }
</script>
<?= $this->endSection() ?>