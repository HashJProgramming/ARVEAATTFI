function fetchSettingsAndDistance() {
    // Fetch the settings (container height and volume) from the server
    $.get("assets/php/getSettings.php", function (response) {
        const settings = JSON.parse(response);

        if (settings.status !== "success" || !settings.data) {
            console.log("Failed to fetch container settings.");
            return;
        }

        const containerHeight = parseFloat(settings.data.height);
        const containerVolume = parseFloat(settings.data.volume);

        if (isNaN(containerHeight) || isNaN(containerVolume) || containerHeight <= 0 || containerVolume <= 0) {
            console.log("Invalid settings received from the server.");
            return;
        }

        const baseArea = containerVolume / containerHeight; // Cross-sectional area of the tank in cm²

        // Fetch the distance value from the sensor
        fetchDistance(containerHeight, containerVolume, baseArea);
        // console.log(containerHeight, containerVolume, baseArea);
    }).fail(function () {
        console.log("Failed to fetch container settings. Ensure the server is reachable.");
    });
}

function fetchDistance(containerHeight, containerVolume, baseArea) {
    // Fetch the distance value from the server
    $.get("http://10.0.0.1/distance", function (data) {
        const distance = parseFloat(data); // Parse the distance from the response

        if (isNaN(distance) || distance > containerHeight) {
            console.log("Invalid distance value received.");
            return;
        }

        const waterLevel = containerHeight - distance; // Water level in cm
        const waterVolume = waterLevel * baseArea; // Volume in cm³
        const waterPercentage = (waterVolume / containerVolume) * 100; // Percentage

        // Update water height
        $("#distance").text(`${waterLevel.toFixed(2)} cm`);

        // Update water volume
        $("#volume").text(`${(waterVolume / 1000).toFixed(2)} L`); // Convert to liters
        $("#percentage").text(`${waterPercentage.toFixed(2)}%`);

        // Update progress bar
        $(".progress-bar")
            .css("width", `${waterPercentage.toFixed(2)}%`)
            .attr("aria-valuenow", waterPercentage.toFixed(2))
            .find(".visually-hidden")
            .text(`${waterPercentage.toFixed(2)}%`);
    }).fail(function () {
        console.log("Failed to fetch the distance. Ensure the server is reachable.");
    });
}

function relayOn(relayIndex) {
    // Send a request to turn on the specified relay
    $.get(`http://10.0.0.1/on?relay=${relayIndex}`, function (data) {
        showAlert(data); // Use SweetAlert2 to show the message
        $(`#relay-${relayIndex}-status`).text("ON").addClass("text-success").removeClass("text-danger");
    }).fail(function () {
        console.log("Failed to turn on the relay. Ensure the server is reachable.");
    });
}

function relayOff(relayIndex) {
    // Send a request to turn off the specified relay
    $.get(`http://10.0.0.1/off?relay=${relayIndex}`, function (data) {
        showAlert(data); // Use SweetAlert2 to show the message
        $(`#relay-${relayIndex}-status`).text("OFF").addClass("text-danger").removeClass("text-success");
    }).fail(function () {
        console.log("Failed to turn off the relay. Ensure the server is reachable.");
    });
}

function showAlert(message) {
    // Display the SweetAlert2 message
    Swal.fire({
        title: "Relay Status",
        text: message, // Use the message from the API response
        icon: "info",
        timer: 1000, // Auto-close after 3 seconds
        timerProgressBar: true,
        showConfirmButton: false
    });
}

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


function submitForm(formId) {
    console.log(formId);
    $(formId).submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var url = "assets/php/updateSettings.php";
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

// Attach event listeners to buttons
$(document).ready(function () {
    $("#relay-1-on").click(function () {
        relayOn(0); // Relay 1 corresponds to index 0
    });

    $("#relay-1-off").click(function () {
        relayOff(0); // Relay 1 corresponds to index 0
    });

    $('#dataTable-1').DataTable({
        ajax: {
            url: 'assets/php/getwaterLogs.php'
        },
        processing: true,
        serverSide: true
    });

    // Fetch the distance every 5 seconds
    setInterval(fetchSettingsAndDistance, 1000);
    // Fetch once immediately on page load
    fetchDistance();


    $('a[data-bs-target="#settings"]').on("click", function () {
        $.get("assets/php/getSettings.php", function (response) {
            // Ensure the response is parsed
            if (typeof response === "string") {
                response = JSON.parse(response);
            }

            if (response.status === "success" && response.data) {
                var settings = response.data;
                console.log("Settings:", settings);

                // Update the input for tankHeight
                $('#tankHeight').val(settings.height);

                // Update the input for tankVolume
                $('#tankVolume').val(settings.volume);

                // Update the select for wateringSelect
                $('#wateringSelect').val(settings.duration);

                console.log(settings.height, settings.volume, settings.duration);
            } else {
                console.error("Error: Data retrieval failed or no data available.");
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Request failed:", textStatus, errorThrown);
        });

    });
    submitForm("#update-form");
});

