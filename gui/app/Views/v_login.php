<?= $this->extend('layouts/layouts') ?>
<?= $this->section('content') ?>
<div class="container-md py-3">
    <form action="<?= base_url('login') ?>" id="formLogin" method="POST" autocomplete="off">
        <div class="row">
            <div class="col-md-6 col-12 mx-auto">
                <div class="card">
                    <div class="card-body login-card-body">
                        <input type="hidden" name="url_direction" value="<?= @$url_direction; ?>">
                        <div class="input-group mb-3">
                            <input required name="username" class="form-control" placeholder="Username">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input required type="password" name="password" class="form-control" placeholder="Password">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-block">Login</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script>
    $(document).ready(function(){
        $('#formLogin').submit(function(e){
            e.preventDefault()
            $.ajax({
                url: $(this).attr('action'),
                type:'post',
                dataType:'json',
                data:$(this).serialize(),
                success:function(data){
                    if(data?.success){
                        toastr.success(data?.message)
                        const url = `<?=base_url()?>${data?.url_direction}`
                        window.location = url
                    }
                },
                error:function(xhr,status,error){
                    return toastr.error(xhr.responseJSON?.message)
                }
            })
        })

    })
</script>
<?= $this->endSection() ?>