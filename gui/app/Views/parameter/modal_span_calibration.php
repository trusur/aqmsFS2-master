<div class="modal fade" id="spanModal" tabindex="-1" role="dialog" aria-labelledby="spanModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form action="" method="post">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="spanModalTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-warning text-danger">
                        <i class="fas fa-info-circle fa-xs mr-1"></i> Pastikan aliran gas kalibrasi span <span id="spanGas"></span> sudah terpasang dengan benar pada saluran sampling dengan laju alir 0.9 lpm
                    </p>
                    <p class="alert alert-warning text-danger">
                        <i class="fas fa-info-circle fa-xs mr-1"></i> Kesalahan penggunaan gas kalibrasi dapat mempengaruhi daya akurasi pengukuran pada sensor Gas
                    </p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cylinder Gas No</label>
                                <input type="hidden" name="id">
                                <input type="text" name="cylinder_gas_no" value="<?= old('cylinder_gas_no') ?>" placeholder="Cylinder Gas No" class="form-control">
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Span Concetration</label>
                                <input type="text" name="span_concetration" value="<?= old('span_concetration') ?>" placeholder="Span Concetration" class="form-control">
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" value="<?= old('username') ?>" placeholder="Username" class="form-control">
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" value="<?= old('password') ?>" placeholder="Password" class="form-control">
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <div class="d-flex justify-content-end">
                        <button name="Save" type="submit" class="btn btn-sm btn-primary mr-1">Start</button>
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>