<script src="<?= base_url('js/jquery-3.6.0.min.js') ?>"></script>
<script src="<?= base_url('bootstrap/js/bootstrap.min.js')?>"></script>
<script src="<?= base_url('toastr/toastr.min.js') ?>"></script>
<script src="<?= base_url('js/moment-with-locales.min.js') ?>"></script>
<script>
    $('#connect').click(function() {
        $('#regionName').html(`<i class='fa fas fa-xs fa-spin fa-spinner'></i>`);
        $('#timezone').html(`<i class='fa fas fa-xs fa-spin fa-spinner'></i>`);
        $('#isp').html(`<i class='fa fas fa-xs fa-spin fa-spinner'></i>`);
        $('#ipAddress').html(`<i class='fa fas fa-xs fa-spin fa-spinner'></i>`)
        $('#status').html(`<i class='fa fas fa-xs fa-spin fa-spinner'></i>`);
        $('#ispModal').modal('show');
        try {
            $.ajax({
                url: 'http://ip-api.com/json',
                dataType: 'json',
                success: function(data) {
                    let regionName = data?.regionName;
                    let timezone = data?.timezone;
                    let isp = data?.isp;
                    let ipAddress = data?.query;
                    let asIsp = data?.as;
                    $('#regionName').html(regionName);
                    $('#timezone').html(timezone);
                    $('#isp').html(`${isp} (${asIsp})`);
                    $('#ipAddress').html(ipAddress)
                    $('#status').html(`<span class="badge badge-success">Connected</span>`);
                },
                error: function(xhr, status, err) {
                    $('#regionName').html(`-`);
                    $('#timezone').html(`-`);
                    $('#isp').html(`-`);
                    $('#ipAddress').html(`-`)
                    $('#status').html(`<span class="badge badge-danger">Disconnect</span>`);
                },
            });
        } catch (err) {
            console.err(err);
        }
    });
</script>
<script>
    /* Check Connection */
    $(document).ready(function() {
        let connected = `<span class="badge badge-sm badge-success" title="Internet Connected">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wifi" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            <path d="M9.172 15.172a4 4 0 0 1 5.656 0"></path>
                            <path d="M6.343 12.343a8 8 0 0 1 11.314 0"></path>
                            <path d="M3.515 9.515c4.686 -4.687 12.284 -4.687 17 0"></path>
                        </svg>
                    </span>`;
        let disconnect = `<span class="badge badge-sm badge-danger" title="Internet Not Connected">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wifi-off" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            <path d="M9.172 15.172a4 4 0 0 1 5.656 0"></path>
                            <path d="M6.343 12.343a7.963 7.963 0 0 1 3.864 -2.14m4.163 .155a7.965 7.965 0 0 1 3.287 2"></path>
                            <path d="M3.515 9.515a12 12 0 0 1 3.544 -2.455m3.101 -.92a12 12 0 0 1 10.325 3.374"></path>
                            <line x1="3" y1="3" x2="21" y2="21"></line>
                        </svg>
                    </span>`;
        $('#connect').html(disconnect);

        function testInternet() {
            const pingUrl = 'https://api.trusur.tech/api/is_connect.php';
            fetch(`${pingUrl}?_t=` + parseInt(Math.random() * 10000)).then((result) => {
                $('#connect').html(connected);
            }).catch((err) => {
                if (err.message.indexOf('Failed to fetch') !== -1) {
                    $('#connect').html(disconnect);
                }
            });
        }
        testInternet()
        setInterval(() => {
            testInternet()

        }, 10000); //1 menit
    });
</script>
<script>
    $.ajax({
        url: "<?= base_url(); ?>/login/status",
        dataType: 'json',
        success: function(data) {
            setTimeout(() => {
                if (data?.loggedin == true)
                    $("#logout_nav").removeClass("d-none");
            }, 100);
        }
    })

    /* Date & Time */
    try {
        setInterval(function() {
            moment.locale('<?= @session()->get('web_lang') ? session()->get('web_lang') : 'en' ?>');
            let momentNow = moment();
            let date = ` ${momentNow.format('dddd').substr(0,3)}, ${momentNow.format('DD')} ${momentNow.format('MMM').substr(0,3)} ${momentNow.format('YYYY')}`;
            let time = momentNow.format('hh:mm:ss A');
            $('#date').html(`${date} | ${time}`);
        }, 100);
    } catch (err) {
        console.error(err);
    }
</script>
<script>
    // $('form').submit(function(e) {
    //     e.preventDefault();
    //     let loader = `<i class="fas fa-spinner fa-spin"></i> Saving`;
    //     $('#btn-save').html(loader);
    //     try {
    //         let action = $(this).attr('action');
    //         let method = $(this).attr('method');
    //         let serializeData = $(this).serialize();
    //         $.ajax({
    //             url: action,
    //             type: method,
    //             data: serializeData,
    //             dataType: 'json',
    //             success: function(data) {
    //                 setTimeout(() => {
    //                     $('#btn-save').html('Save Changes');
    //                     if(!data?.success){
    //                         toastr.error(data?.message);
    //                     }else{
    //                         toastr.success(data?.message);
    //                     }
    //                     if (data?.url_direction != undefined)
    //                         window.location = "<?= base_url(); ?>/" + data?.url_direction;
    //                 }, 1000);
    //             },
    //             error: function(xhr, status, err) {
    //                 toastr.error(err.toString());
    //                 $('#btn-save').html('Save Changes');
    //             }
    //         })


    //     } catch (err) {
    //         console.log(err);
    //         toastr.error(err.toString());
    //         $('#btn-save').html('Save Changes');
    //     }

    // })
</script>
<script>
    /* Capture */
    $(document).ready(function() {
        $(window).scroll(function(event) {
            let scrollValue = $(window).scrollTop();
            if (scrollValue > 0) {
                $('#btn-capture').fadeOut(500);
            } else {
                $('#btn-capture').fadeIn(500);
            }
        });
    })
    $('#btn-capture').click(function() {
        $('#btn-capture').hide();
        $('#btn-save').hide();
        html2canvas(document.querySelector('#capture-body')).then(canvas => {
            $('#capture-result').html(canvas);
            $('#captureModal').modal('show');
            $('#btn-capture').show();
            $('#btn-save').show();
        });
    })
</script>