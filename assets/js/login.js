function showSweetAlert(title, text, icon, timer) {
    let timerInterval;
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        timer: timer,
        timerProgressBar: true,
        willClose: () => {
            clearInterval(timerInterval);
        },
    });
}

function submitLoginForm(formId) {
    console.log(formId);
    $(formId).submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var url = "assets/php/sign-in.php";
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function (data) {
                console.log(data);
                var result = JSON.parse(data);
                showSweetAlert(
                    result.status.toUpperCase(),
                    result.message,
                    result.status,
                    5000
                );

                if (result.status == "success") {
                    setTimeout(function () {
                        window.location.href = "dashboard.php";
                    }, 500);
                }
            },
            error: function (data) {
                var result = JSON.parse(data);
                showSweetAlert(
                    result.status.toUpperCase(),
                    result.message,
                    result.status,
                    5000
                );
            },
        });
    });
}
$(document).ready(function () {
    submitLoginForm("#sign-in-form");
});