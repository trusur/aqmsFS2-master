<script src="<?= base_url('js/jquery-3.6.0.min.js') ?>"></script>
<script src="<?= base_url('bootstrap/js/bootstrap.min.js')?>"></script>
<script src="<?= base_url('toastr/toastr.min.js') ?>"></script>
<script src="<?= base_url('js/moment-with-locales.min.js') ?>"></script>
<script src="<?= base_url('js/test-connection.js') ?>"></script>
<script>
    $(document).ready(function(){
        // Navbar menu show when user click 4 times
        let showHiddenMenuCount = 0;
        function showHiddenMenu() {
            showHiddenMenuCount++;
            if (showHiddenMenuCount > 4 && !localStorage.getItem("showHiddenMenu")) {
                showHiddenMenuCount = 0
                localStorage.setItem("showHiddenMenu", "true");
                $("#parameters_nav").removeClass("d-none");
                // $("#calibrations_nav").removeClass("d-none");
            }
            if(showHiddenMenuCount > 4 && localStorage.getItem("showHiddenMenu")) {
                localStorage.removeItem("showHiddenMenu");
                $("#parameters_nav").addClass("d-none");
                // $("#calibrations_nav").addClass("d-none");
            }
        }
        <?php if(session()->get("loggedin")): ?>
            if(localStorage.getItem("showHiddenMenu") == "true") {
                $("#parameters_nav").removeClass("d-none");
                // $("#calibrations_nav").removeClass("d-none");
            }
        <?php endif;?>
        // Trigger when user click on logo 4 times
        $('#logo-text').click(showHiddenMenu);

        /*Realtime Date & Time */
        setInterval(function() {
            try {
                moment.locale('<?= session()->get('web_lang') ?? 'en' ?>');
                let momentNow = moment();
                let date = ` ${momentNow.format('dddd').substr(0,3)}, ${momentNow.format('DD')} ${momentNow.format('MMM').substr(0,3)} ${momentNow.format('YYYY')}`;
                let time = momentNow.format('hh:mm:ss A');
                $('#date').html(`${date} | ${time}`);
            } catch (err) {
                console.error(err);
            }
        }, 1000);

        
    })
</script>