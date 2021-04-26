<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AQM</title>
    <?php include 'inc/css.php'; ?>

</head>

<body>
    <!-- Navbar -->
    <?php include 'inc/navbar.php'; ?>

    <!-- End of Navar -->
    <div class="container-md py-5">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h2 text-light">Parameter</h1>
            <div>
                <a href="#" onclick="return window.history.go(-1);" class="btn btn-sm btn-primary">
                    <i class="fas fa-xs fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <form action="">
            <div class="row">
                <?php for ($i = 1; $i <= 6; $i++) : ?>
                    <div class="col-md-4 my-3">
                        <div class="card">
                            <div class="bg-light px-3 py-2">
                                <h1 class="h4">ID: SO2</h1>
                                <div class="d-flex justify-content-between">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="is_view[<?= $i ?>]" value="1">
                                        <label class="form-check-label" for="is_view[<?= $i ?>]">Show Data</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="is_graph[<?= $i ?>]" value="1">
                                        <label class="form-check-label" for="is_graph[<?= $i ?>]">Show Graphic</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Caption</label>
                                    <input type="text" name="caption" placeholder="Caption" class="form-control">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Unit</label>
                                            <input type="text" name="unit" placeholder="Unit" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Molecular Weight</label>
                                            <input type="text" name="molecular_mass" placeholder="Molecular Weight" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Formula</label>
                                            <input type="text" name="molecular_mass" placeholder="Formula" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
            <div class="position-fixed" style="z-index: 999;right:11vw;bottom:20px;">
                <button class="btn btn-sm btn-info" id="btn-save">Save Changes</button>
            </div>
        </form>

    </div>

    <?php include 'inc/js.php'; ?>
    <script>
        $('form').submit(function(e) {
            e.preventDefault();
            let loader = `<i class="fas fa-spinner fa-spin"></i> Saving`;
            $('#btn-save').html(loader);
            try {
                // if success
                setTimeout(() => {
                    $('#btn-save').html('Save Changes');
                }, 1000);

            } catch (err) {
                alert(err);
            }

        })
    </script>
</body>

</html>