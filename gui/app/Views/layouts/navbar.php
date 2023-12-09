<nav class="sticky-top shadow-lg navbar navbar-expand-lg navbar-dark">
    <div class="container-md">
        <span class="navbar-brand d-flex align-items-center" href="<?= base_url() ?>" style="column-gap:12px;font-weight:bolder;font-size:30px;">
            <a href="<?=base_url()?>">
            <img src="<?= base_url('img/logo.png') ?>" width="50" height="50" class="d-inline-block align-top" alt="Logo TRUSUR"> 
            </a>
            <div id="logo-text" style="cursor:pointer;" class="position-relative d-flex align-items-center">
                <span style="font-size:20px">AQMS</span>
                <div class="position-absolute" style="top: -2px;right: -11px">
                    <small class="badge badge-dark" style="font-size:10px">EFS1</small>
                </div>
            </div>
        </span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto" style="font-weight:bolder;font-size:20px;">
                <?php 
                    if(session()->get("loggedin")):                
                ?>
                 <li class="btn-dark rounded border border-light nav-item mr-2 <?= @strtolower($__routename) == 'configuration' ? 'active' : '' ?>" data-intro="<?= lang('Global.intro_configuration') ?>">
                    <a class="nav-link" href="<?= base_url('configurations') ?>"><?= lang('Global.Configuration') ?></a>
                </li>
                <li id="parameters_nav" class="btn-dark rounded border border-light nav-item mr-2 hide d-none <?= @strtolower($__routename) == 'parameter' ? 'active' : '' ?>" data-intro="<?= lang('Global.intro_parameter') ?>">
                    <a class="nav-link" href="<?= base_url('parameters') ?>">Parameter</a>
                </li>
                <li id="calibrations_nav" class="btn-dark rounded border border-light nav-item mr-2 hide d-none <?= @strtolower($__routename) == 'calibration' ? 'active' : '' ?>" data-intro="<?= lang('Global.intro_calibration') ?>">
                    <a class="nav-link" href="<?= base_url('calibrations') ?>"><?= lang('Global.Calibration') ?></a>
                </li>
                
                <?php else: ?>
                <li class="btn-dark rounded border border-light nav-item mr-2 <?= @strtolower($__routename) == 'rht' ? 'active' : '' ?>" data-intro="<?= lang('Global.intro_extras') ?>">
                    <a class="nav-link" href="<?= base_url('login?url_direction=configuration') ?>"><?= lang('Global.login') ?></a>
                </li>
                <?php endif; ?>
                <li class="btn-dark rounded border border-light nav-item mr-2 <?= @strtolower($__routename) == 'export' ? 'active' : '' ?>" data-intro="<?= lang('Global.intro_export') ?>">
                    <a class="nav-link" href="<?= base_url('exports') ?>"><?= lang('Global.Export') ?></a>
                </li>
                <li class="btn-dark rounded border border-light nav-item mr-2 <?= @strtolower($__routename) == 'rht' ? 'active' : '' ?>" data-intro="<?= lang('Global.intro_extras') ?>">
                    <a class="nav-link" href="<?= base_url('rht') ?>"><?= lang('Global.extras') ?></a>
                </li>
                <?php if(session()->get("loggedin")):?>
                <li id="logout_nav" class="btn-dark rounded border border-light nav-item mr-2" data-intro="Logout">
                    <a class="nav-link" href="<?= base_url('login/logout') ?>">Logout</a>
                </li>
                <?php endif?>
                
            </ul>
            <div class="d-flex justify-content-end align-items-center my-2 ml-md-0">
                <div id="connect" data-intro="<?= lang('Global.intro_connectivity') ?>">
                    <span class="badge badge-sm badge-danger" title="Internet Not Connected">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wifi-off" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            <path d="M9.172 15.172a4 4 0 0 1 5.656 0"></path>
                            <path d="M6.343 12.343a7.963 7.963 0 0 1 3.864 -2.14m4.163 .155a7.965 7.965 0 0 1 3.287 2"></path>
                            <path d="M3.515 9.515a12 12 0 0 1 3.544 -2.455m3.101 -.92a12 12 0 0 1 10.325 3.374"></path>
                            <line x1="3" y1="3" x2="21" y2="21"></line>
                        </svg>
                    </span>
                </div>
                <div class="mx-1" data-intro="<?= lang('Global.intro_lang') ?>">
                    <?php if (@session()->get('web_lang') == 'en') : ?>
                        <a href="<?= base_url('lang/id') ?>" class="btn btn-sm btn-primary" title="Translate to Indonesia">
                            <img src="<?= base_url('img/gb.svg') ?>" alt="EN" height="20vh" width="20vw">
                        </a>
                    <?php else : ?>
                        <a href="<?= base_url('lang/en') ?>" class="btn btn-sm btn-primary" title="Terjemahkan ke Bahasa Inggris">
                            <img src="<?= base_url('img/id.svg') ?>" alt="ID" height="20vh" width="20vw">
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>