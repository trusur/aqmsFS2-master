<div class="modal fade" id="paramModal" tabindex="-1" role="dialog" aria-labelledby="paramModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form action="<?= base_url('parameter') ?>" method="post">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Edit Parameter</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="hidden" name="id">
                                <input type="text" name="code" value="<?= old('code') ?>" placeholder="Name" class="form-control">
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Caption</label>
                                <input type="text" name="caption_id" value="<?= old('caption_id') ?>" placeholder="Caption" class="form-control">
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Molecular Mass</label>
                                <input type="text" id="molecular_mass" name="molecular_mass" value="<?= old('molecular_mass') ?>" placeholder="Molecular Mass" class="form-control">
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group  'is-invalid' : '' ?>">
                                <label class="d-block">View</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" name="is_view" type="radio" id="showed" value="1" <?= ((int) old('is_view')) == 1 ? 'checked="checked"' : null ?>">
                                    <label class="form-check-label text-success" for="showed">Aktif</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" name="is_view" type="radio" id="hidden" value="0" <?= ((int) old('is_view')) == 0 ? 'checked="checked"' : null ?>>
                                    <label class="form-check-label text-danger" for="hidden">Tidak Aktif</label>
                                </div>
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group  'is-invalid' : '' ?>">
                                <label class="d-block">Graph</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" name="is_graph" type="radio" id="showed-graph" value="1" <?= ((int) old('is_graph', @$parameter->is_graph)) == 1 ? 'checked="checked"' : null ?>>
                                    <label class="form-check-label text-success" for="showed-graph">Aktif</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" name="is_graph" type="radio" id="hidden-graph" value="0" <?= ((int) old('is_graph', @$parameter->is_graph)) == 0 ? 'checked="checked"' : null ?>>
                                    <label class="form-check-label text-danger" for="hidden-graph">Tidak Aktif</label>
                                </div>
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sensor Value</label>
                                <select id="sensor_value_id" name="sensor_value_id" class="form-control">
                                    <option value="" selected disabled>Select Sensor Value</option>
                                </select>
                                <div class="invalid-feedback">

                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Current Sensor Value</label>
                                <div class="input-group">
                                    <textarea type="text" id="voltage" class="form-control" readonly></textarea>
                                    <!--span class="input-group-btn">
                                        <button type="button" class="btn btn-info btn-flat" onclick="$('#voltage1').val($('#voltage').val());">Set V1</button>
                                        <button type="button" class="btn btn-info btn-flat" onclick="$('#voltage2').val($('#voltage').val());">Set V2</button>
                                    </span-->
                                </div>
                                <input type="hidden" name="" id="sensor_pin">
                                <input type="hidden" name="" id="sensor_reader_id">
                            </div>
                        </div>
                    </div>
                    <div id="content-formula">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="small">Voltage 1</label>
                                    <input type="text" id="voltage1" name="voltage1" value="<?= old('voltage1', @$parameter->voltage1) ?>" placeholder="Voltage 1" class="form-control">
                                    <div class="invalid-feedback">

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="small">Concentration 1</label>
                                    <input type="text" id="concentration1" name="concentration1" value="<?= old('concentration1', @$parameter->concentration1) ?>" placeholder="Concentration 1" class="form-control">
                                    <div class="invalid-feedback">

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="small">Voltage 2</label>
                                    <input type="text" id="voltage2" name="voltage2" value="<?= old('voltage2', @$parameter->voltage2) ?>" placeholder="Voltage 2" class="form-control">
                                    <div class="invalid-feedback">

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="small">Concentration 2</label>
                                    <input type="text" id="concentration2" name="concentration2" value="<?= old('concentration2', @$parameter->concentration2) ?>" placeholder="Concentration 2" class="form-control">
                                    <div class="invalid-feedback">

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Formula</label>
                                <div class="input-group">
                                    <input type="text" id="formula" name="formula" value="<?= old('formula', @$parameter->formula) ?>" placeholder="Formula" class="form-control">
                                    <div class="invalid-feedback">

                                    </div>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-info btn-flat" id="btnGenerate">Generate Formula</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <div class="d-flex justify-content-end">
                        <button name="Save" type="submit" class="btn btn-sm btn-primary mr-1">Save</button>
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>