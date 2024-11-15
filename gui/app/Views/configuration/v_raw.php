<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
   <div class="card">
    <div class="card-header py-1">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h6 card-title my-0">Configurations</h1>
            <div>
                <button type="button" data-toggle="modal" data-target="#modal-add" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add New</button>
                <a href="<?= base_url('configuration')?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="width: 100%; overflow-x: hidden">
            <table id="table-configuration" style="font-size: small;" class="table table-sm table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Content</th>
                    </tr>
                </thead>
                <tbody><tbody>
            </table>
        </div>
    </div>
   </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('modal') ?>
<div class="modal" id="modal-add" tabindex="-1" role="dialog" aria-labelledby="modal-addLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-addLabel">Add New Configuration</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-add">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required placeholder="Name" class="form-control form-control-sm">
            </div>
            <div class="form-group">
                <label>Content</label>
                <input type="text" name="content" required placeholder="Content" class="form-control form-control-sm">
            </div>
            <button type="reset" class="d-none btn-reset">Reset</button>
            <button type="submit" class="btn btn-sm btn-success w-100">Add New Configuration</button>
        </form>

      </div>
    </div>
  </div>
</div>
<div class="modal" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="modal-addLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-addLabel">Edit Configuration</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-edit">
            <div class="form-group">
                <label id="label-name">Content</label>
                <input type="text" id="content" name="" required placeholder="Content" class="form-control form-control-sm">
            </div>
            <button type="reset" class="d-none btn-reset">Reset</button>
            <button type="submit" class="btn btn-sm btn-success w-100">Save Changes</button>
        </form>

      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="<?=base_url("plugins/datatables-bs4/css/dataTables.bootstrap4.min.css")?>">
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="<?=base_url("plugins/datatables/jquery.dataTables.min.js")?>"></script>
<script src="<?=base_url("plugins/datatables-bs4/js/dataTables.bootstrap4.min.js")?>"></script>
<script>
    $(document).ready(function() {
        const table = $('#table-configuration').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('configuration/raw/datatable') ?>',
            },
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    render: function (data, type, row) {
                        return `<span data-name="${row.name}" data-content="${row.content}" class="btn-edit badge badge-primary p-1" style="cursor:pointer"><i class="fas fa-pen"></i<</button>`
                    }
                },
                {data: 'name', name: 'name'},
                {data: 'content', name: 'content'},
            ]

        })

        $(document).delegate(".btn-edit", "click", function() {
            $('#modal-edit').modal("show")
            let name = $(this).data('name')
            let content = $(this).data('content')
            $('#label-name').html(name)
            $('#content').attr("name",`name[${name}]`)
            $('#content').val(content)
        })
        $("#form-add").submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?= base_url('configuration/raw/add') ?>",
                data: $(this).serialize(),
                dataType:'json',
                success: function(data) {
                    if(data?.success){
                        table.ajax.reload()
                        $('#modal-add').modal('hide')
                        $(this).find('.btn-reset').trigger('click')
                        return toastr.success(`Configuration added!`)
                    }
                    return toastr.error(`Failed when adding configuration!`)
                },
                error: function(xhr, status, err) {
                    return toastr.error(xhr.responseJSON?.message)
                }
            })
        })
        $("#form-edit").submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?= base_url('configuration/update') ?>",
                data: $(this).serialize(),
                dataType:'json',
                success: function(data) {
                    if(data?.success){
                        table.ajax.reload()
                        $('#modal-edit').modal('hide')
                        $(this).find('.btn-reset').trigger('click')
                        return toastr.success(`Configuration updated!`)
                    }
                    return toastr.error(`Failed when update configuration!`)
                },
                error: function(xhr, status, err) {
                    return toastr.error(xhr.responseJSON?.message)
                }
            })
        })
    })
</script>
<?= $this->endSection() ?>