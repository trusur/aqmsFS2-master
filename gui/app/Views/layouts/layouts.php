<!DOCTYPE html>
<html lang="<?= session()->get('web_lang') ? session()->get('web_lang') : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRUSUR AQMS FS2</title>
    <?= $this->include('layouts/css') ?>
    <?= $this->renderSection('css') ?>
    <!-- Custom CSS -->
</head>

<body id="capture-body">
    <!-- Navbar -->
    <?= $this->include('layouts/navbar') ?>
    <!-- End of Navar -->
    <div id="layout-content">
        <?= $this->renderSection('content') ?>
    </div>
    <div class="modal" id="ispModal" tabindex="-1" role="dialog" aria-labelledby="ispModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Internet Connection Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-stripped">
                        <tr>
                            <th>Status</th>
                            <td id="status">
                                <span class="badge badge-danger">Disconnect</span>
                            </td>
                        </tr>
                        <tr>
                            <th>ISP</th>
                            <td id="isp">-</td>
                        </tr>
                        <tr>
                            <th>IP Address</th>
                            <td id="ipAddress">-</td>
                        </tr>
                        <tr>
                            <th>Region</th>
                            <td id="regionName">-</td>
                        </tr>
                        <tr>
                            <th>Time Zone</th>
                            <td id="timezone">-</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('layouts/js') ?>
    <?= $this->renderSection('js') ?>
</body>

</html>