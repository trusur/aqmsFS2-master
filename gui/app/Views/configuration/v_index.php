<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <form action="<?= base_url('configuration') ?>" method="POST">
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
                        <form action="<?=base_url("configuration/update")?>" method="post">
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
            <div class="col-md-6 mx-auto d-none" id="config-automation">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title h6 p-0 m-0">Automation</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?=base_url("configuration/update")?>" method="post">
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
                                <form action="<?=base_url("configuration/update")?>" method="post">
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
                                <form action="<?=base_url("configuration/update")?>" method="post">
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
         <!-- <div class="row">
           <div class="col-md-6">
                <div class="card">
                    <div class="bg-light px-3 py-2">
                        <h2 class="h4">AQMS INFO</h2>
                        <div class="form-group">
                            <label><?= lang('Global.Station Name') ?></label>
                            <input type="text" name="nama_stasiun" placeholder="<?= lang('Global.Station Name') ?>" value="<?= $__this->findConfig('nama_stasiun') ?>" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Station ID</label>
                                    <input type="text" name="id_stasiun" placeholder="Station ID" value="<?= $__this->findConfig('id_stasiun') ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?= lang('Global.City') ?></label>
                                    <input type="text" name="city" value="<?= $__this->findConfig('city') ?>" placeholder="<?= lang('Global.City') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?= lang('Global.Province') ?></label>
                                    <input type="text" name="province" value="<?= $__this->findConfig('province') ?>" placeholder="<?= lang('Global.Province') ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?= lang('Global.Full Address') ?></label>
                            <textarea name="address" rows="2" placeholder="<?= lang('Global.Full Address') ?>" class="form-control"><?= $__this->findConfig('address') ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Latitude</label>
                                    <input type="text" name="latitude" placeholder="Latitude" value="<?= $__this->findConfig('latitude') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Longitude</label>
                                    <input type="text" name="longitude" placeholder="Longitude" value="<?= $__this->findConfig('longitude') ?>" class="form-control">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="bg-light px-3 py-2">
                        <h2 class="h4">AUTOMATION</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small"><?= lang('Global.pump_speed') ?> <small>(%)</small></label>
                                    <input type="text" name="pump_speed" value="<?= $__this->findConfig('pump_speed') ?>" placeholder="<?= lang('Global.pump_speed') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small"><?= lang('Global.Interval') ?> <?= lang('Global.Pump') ?> <small>(<?= lang('Global.Minutes') ?>)</small></label>
                                    <input type="text" name="pump_interval" value="<?= $__this->findConfig('pump_interval') ?>" placeholder="<?= lang('Global.Interval') ?> <?= lang('Global.Pump') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small"><?= lang('Global.Collect Data Interval') ?> <small>(<?= lang('Global.Minutes') ?>)</small></label>
                                    <input type="text" name="data_interval" value="<?= $__this->findConfig('data_interval') ?>" placeholder="<?= lang('Global.Collect Data Interval') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small"><?= lang('Global.Graphic Refresh Interval') ?> <small>(<?= lang('Global.Minutes') ?>)</small></label>
                                    <input type="text" name="graph_interval" value="<?= $__this->findConfig('graph_interval') ?>" placeholder="<?= lang('Global.Graphic Refresh Interval') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">Is Auto Restart? (0 => No ; 1 => Yes)</label>
                                    <input type="text" name="is_auto_restart" value="<?= $__this->findConfig('is_auto_restart') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">Restart Schedule</label>
                                    <input type="time" name="restart_schedule" value="<?= $__this->findConfig('restart_schedule') ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card mt-3">
                    <div class="bg-light px-3 py-2">
                        <h2 class="h4">SERVERS</h2>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">Is Sent To KLHK? (0 => No ; 1 => Yes)</label>
                                    <input type="text" name="is_sentto_klhk" value="<?= $__this->findConfig('is_sentto_klhk') ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">KLHK API Server</label>
                                    <input type="text" name="klhk_api_server" value="<?= $__this->findConfig('klhk_api_server') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">KLHK API Key</small></label>
                                    <input type="text" name="klhk_api_key" value="<?= $__this->findConfig('klhk_api_key') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">KLHK API Username</small></label>
                                    <input type="text" name="klhk_api_username" value="<?= $__this->findConfig('klhk_api_username') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">KLHK API Password</small></label>
                                    <input type="text" name="klhk_api_password" value="<?= $__this->findConfig('klhk_api_password') ?>" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">Is Sent To TRUSUR? (0 => No ; 1 => Yes)</label>
                                    <input type="text" name="is_sentto_trusur" value="<?= $__this->findConfig('is_sentto_trusur') ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">TRUSUR API Server</label>
                                    <input type="text" name="trusur_api_server" value="<?= $__this->findConfig('trusur_api_server') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">TRUSUR API Key</small></label>
                                    <input type="text" name="trusur_api_key" value="<?= $__this->findConfig('trusur_api_key') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">TRUSUR API Username</small></label>
                                    <input type="text" name="trusur_api_username" value="<?= $__this->findConfig('trusur_api_username') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">TRUSUR API Password</small></label>
                                    <input type="text" name="trusur_api_password" value="<?= $__this->findConfig('trusur_api_password') ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="bg-light px-3 py-2">
                        <h2 class="h4">DEVICE</h2>
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between">
                                <h3 class="h6">PORTS:</h3>
                                <span class="btn-show-all" style="cursor: pointer;" data-toggle="collapse" data-target="#collapse-sensor" aria-expanded="true" aria-controls="collapse-sensor">
                                    Show All
                                </span>
                            </div>
                            <?php foreach ($sensor_readers as $key => $sensor_reader) : ?>
                                <?php if ($key > 0) : ?>
                                    <div id="collapse-sensor" class="collapse">
                                        <p class="mb-1 small">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-copy" data-id="<?= $key ?>"><i class="fas fa-xs fa-copy"></i></button>
                                            <?= "<span data-id='{$key}'>{$sensor_reader->sensor_code}</span>  => " . str_replace(".py", "", $sensor_reader->driver) ?>
                                        </p>
                                    </div>
                                <?php else : ?>
                                    <p class="mb-1 small">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-copy" data-id="<?= $key ?>"><i class="fas fa-xs fa-copy"></i></button>
                                        <?= "<span data-id='{$key}'>{$sensor_reader->sensor_code}</span>  => " . str_replace(".py", "", $sensor_reader->driver) ?>
                                    </p>
                                <?php endif; ?>
                            <?php endforeach ?>
                        </div>
                        <table id="export-tbl" class="table stripped">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Port</th>
                                    <th>Baud Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sensor_readers as $sensor_reader) : ?>
                                    <tr>
                                        <td><b><?= $sensor_reader->driver; ?></b></td>
                                        <td>
                                            <input type="text" name="sensor_code[<?= $sensor_reader->id ?>]" placeholder="Port" class="form-control" value="<?= $sensor_reader->sensor_code ?>">
                                        </td>
                                        <td>
                                            <select name="baud_rate[<?= $sensor_reader->id ?>]" class="form-control">
                                                <option value="">Baud Rate</option>
                                                <option value="110" <?= $sensor_reader->baud_rate == 110 ? 'selected' : '' ?>> 110 </option>
                                                <option value="300" <?= $sensor_reader->baud_rate == 300 ? 'selected' : '' ?>> 300 </option>
                                                <option value="1200" <?= $sensor_reader->baud_rate == 1200 ? 'selected' : '' ?>> 1200 </option>
                                                <option value="2400" <?= $sensor_reader->baud_rate == 2400 ? 'selected' : '' ?>> 2400 </option>
                                                <option value="4800" <?= $sensor_reader->baud_rate == 4800 ? 'selected' : '' ?>> 4800 </option>
                                                <option value="9600" <?= $sensor_reader->baud_rate == 9600 ? 'selected' : '' ?>> 9600 </option>
                                                <option value="19200" <?= $sensor_reader->baud_rate == 19200 ? 'selected' : '' ?>> 19200 </option>
                                                <option value="38400" <?= $sensor_reader->baud_rate == 38400 ? 'selected' : '' ?>> 38400 </option>
                                                <option value="57600" <?= $sensor_reader->baud_rate == 57600 ? 'selected' : '' ?>> 57600 </option>
                                                <option value="115200" <?= $sensor_reader->baud_rate == 115200 ? 'selected' : '' ?>> 115200 </option>
                                                <option value="230400" <?= $sensor_reader->baud_rate == 230400 ? 'selected' : '' ?>> 230400 </option>
                                                <option value="460800" <?= $sensor_reader->baud_rate == 460800 ? 'selected' : '' ?>> 460800 </option>
                                                <option value="921600" <?= $sensor_reader->baud_rate == 921600 ? 'selected' : '' ?>> 921600 </option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="bg-light px-3 py-2">
                        <h2 class="h4">CALIBRATION</h2>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small">With Auto Zero Valve (0 => No ; 1 => Yes)</label>
                                    <input type="text" name="is_valve_calibrator" value="<?= $__this->findConfig('is_valve_calibrator') ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small"><?= lang('Global.zerocal_schedule') ?></label>
                                    <input type="text" name="zerocal_schedule" value="<?= $__this->findConfig('zerocal_schedule') ?>" placeholder="<?= lang('Global.zerocal_schedule') ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small"><?= lang('Global.zerocal_duration') ?> <small>(<?= lang('Global.Seconds') ?>)</small></label>
                                    <input type="number" name="zerocal_duration" value="<?= $__this->findConfig('zerocal_duration') ?>" placeholder="<?= lang('Global.zerocal_duration') ?>" class="form-control" min="60">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> 
        </div>-->
        <div class="position-fixed" style="z-index: 999;right:11vw;bottom:20px;">
            <button type="submit" name="Save" class="btn btn-info" id="btn-save"><?= lang('Global.Save Changes') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('css') ?>
<?= $this->endSection() ?>
<?= $this->section('js') ?>

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
    })
</script>
<?= $this->endSection() ?>