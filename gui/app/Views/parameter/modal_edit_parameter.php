<div class="modal" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="modal-edit-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h2 class="modal-title p-0 my-0 h6">Edit Parameter</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-edit" action="<?= base_url('parameter/edit') ?>" class="row">
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label>Code</label>
                            <input type="hidden" name="id">
                            <input type="text" name="code" value="<?= old('code') ?>" placeholder="Name" class="form-control form-contorl-sm">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label>Caption</label>
                            <input type="text" name="caption_id" value="<?= old('caption_id') ?>" placeholder="Caption" class="form-control form-contorl-sm">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-1">
                            <label>Molecular Mass</label>
                            <input type="number" inputmode="numeric" id="molecular_mass" name="molecular_mass" value="<?= old('molecular_mass') ?>" placeholder="Molecular Mass" class="form-control form-contorl-sm">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label>Status</label>
                            <select id="is_view" name="is_view" class="form-control form-contorl-sm">
                                <option value="" selected disabled>Select Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-1">
                            <label>Sensor Value</label>
                            <select id="sensor_value_id" name="sensor_value_id" class="form-control">
                                <option value="" selected disabled>Select Sensor Value</option>
                            </select>
                            <div class="invalid-feedback">

                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-1">
                            <label>Current Sensor Value</label>
                            <div class="input-group">
                                <textarea type="text" id="voltage" class="form-control" readonly></textarea>
                            </div>
                            <input type="hidden" name="" id="sensor_pin">
                            <input type="hidden" name="" id="sensor_reader_id">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-1">
                            <label>Formula</label>
                            <textarea name="formula" id="formula" rows="3" placeholder="Formula" class="form-control form-control-sm"></textarea>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-sm btn-success">Save Changes</button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>