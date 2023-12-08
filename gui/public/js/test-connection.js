/* Check Connection */
$(document).ready(function() {
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
                    toastr.error(`Failed to check ISP`);
                    $('#regionName').html(`-`);
                    $('#timezone').html(`-`);
                    $('#isp').html(`-`);
                    $('#ipAddress').html(`-`)
                    $('#status').html(`<span class="badge badge-danger">Disconnect</span>`);
                },
            });
        } catch (err) {
        }
    })
    
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