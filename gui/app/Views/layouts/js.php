<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= base_url('js/moment-with-locales.min.js') ?>"></script>
<script src="<?= base_url('js/html2canvas.min.js') ?>"></script>
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
        setInterval(() => {
            const pingUrl = 'https://ipv4.icanhazip.com';
            fetch(`${pingUrl}?_t=` + parseInt(Math.random() * 10000)).then((result) => {
                $('#connect').html(connected);
            }).catch((err) => {
                if (err.message.indexOf('Failed to fetch') !== -1) {
                    $('#connect').html(disconnect);
                }
            });
        }, 3000);
    });
</script>
<script>
    /* Date & Time */
    try {
        setInterval(function() {
            moment.locale('<?= @session()->get('web_lang') ? session()->get('web_lang') : 'en' ?>');
            let momentNow = moment();
            let date = ` ${momentNow.format('dddd')}, ${momentNow.format('DD MMMM YYYY')}`;
            let time = momentNow.format('hh:mm:ss A');
            $('#date').html(`${date} | ${time}`);
        }, 100);
    } catch (err) {
        console.error(err);
    }
</script>
<script>
    try {
        /* request to server get eventTime pump */
        let eventTime = moment().add(6, 'h').unix(); /* Dummy data */
        let curentTime = moment().unix();
        let diffTime = eventTime - curentTime;
        let duration = moment.duration(diffTime * 1000, 'milliseconds');
        let interval = 1000;
        setInterval(() => {
            duration = moment.duration(duration - interval, 'milliseconds');
            $('#pumpTimer').html(`${duration.hours()}:${duration.minutes()}:${duration.seconds()}`)
        }, interval);

    } catch (err) {
        console.error(err);
    }
</script>
<script>
    $('form').submit(function(e) {
        e.preventDefault();
        let loader = `<i class="fas fa-spinner fa-spin"></i> Saving`;
        $('#btn-save').html(loader);
        try {
            let action = $(this).attr('action');
            let method = $(this).attr('method');
            let serializeData = $(this).serialize();
            $.ajax({
                url: action,
                type: method,
                data: serializeData,
                dataType: 'json',
                success: function(data) {
                    setTimeout(() => {
                        $('#btn-save').html('Save Changes');
                        toastr.success(data?.message);
                    }, 1000);
                },
                error: function(xhr, status, err) {
                    toastr.error(err.toString());
                    $('#btn-save').html('Save Changes');
                }
            })


        } catch (err) {
            toastr.error(err.toString());
            $('#btn-save').html('Save Changes');
        }

    })
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