function getTodayDate() {
    var today = new Date();
    return today.getFullYear() + "-" + ("0" + (today.getMonth() + 1)).slice(-2) + "-" + ("0" + today.getDate()).slice(-2);
}

function getNowTime() {
    var today = new Date();

    return ("0" + today.getHours()).slice(-2) + ":" + ("0" + Math.ceil(today.getMinutes() / 10) * 10).slice(-2);
}

function getReturnDate(days) {
    days = days > 0 ? days : 1;

    var today = new Date();
    var retDate = new Date(Number(today));
    retDate.setDate(today.getDate() + days);
    return retDate.getFullYear() + "-" + ("0" + (retDate.getMonth() + 1)).slice(-2) + "-" + ("0" + retDate.getDate()).slice(-2);
}

function searchCities(element, listElement) {
    var curSearchText = $(element).val();
    if (curSearchText.length >= 2) {
        var dataString = JSON.stringify({searchText: curSearchText})

        $.ajax({ // FIXME: escape special characters using urlencode
            url: "/search/location",
            type: "POST",
            dataType: "json",
            data: dataString,
            error: function (jqxhr, status, errorThrown) {
                alert("AJAX error: " + jqxhr.responseText);
            }
        }).done(function (cityList) {
            $(listElement).empty();
            $.each(cityList, function (i, item) {
                var option = new Option(item['name'] + "  (" + item['postalCode'] + ")");
                $(listElement).append(option);
            });

        }).always(function () {

        });
    }
}

function reloadMapCoordinate(element) {
    var cityname = $(element).val();
    $.ajax({
        url: "/search/city",
        type: "POST",
        dataType: "json",
        data: JSON.stringify(cityname),
        error: function (jqxhr, status, errorThrown) {
            alert("AJAX error: " + jqxhr.responseText);
        }
    }).done(function (cityInfo) {
        if (cityInfo.length === 0) {
            cityInfo =
        }

    }).always(function () {

    })
}

$(document).ready(function () {
    initGoogleMap();

    $("#pickupSearchText").on("keypress", function (e) {
        if (e.keyCode === 13) {
            reloadMapCoordinate(this);
        }

        searchCities(this, "#pickupLocationList");
    });

    $("#returnSearchText").on("keypress", function () {
        searchCities(this, "#returnLocationList");
    });


    $("#pickupDate").val(getTodayDate);
    $("#pickupTime").val(getNowTime);
    $("#returnDate").val(getReturnDate(Number($("#rentDays").val())));
    $("#returnTime").val(getNowTime);

    $("#returnDate").on("change", function () {
        var retDate = $(this).val();
        var picDate = $("#pickupDate").val();
        if (retDate <= picDate) {
            alert("Return date must be at least one day after pick-up date!");
        }
    })

    $("#rentDays").on("change", function () {
        var days = $(this).val() > 0 ? $("#rentDays").val() : 1;
        $(this).val(days);
        $("#returnDate").val(getReturnDate(Number(days)));
    })

    $("input[name=isDiffLocation]").on("click", function () {
        if ($(this).prop("checked")) {
            //alert("selected");
            $("#returnLocation").show();
        } else {
            $("#returnLocation").hide();
        }
    })


})
$(document).ajaxError(function (event, jqxhr, settings, thrownError) {
    console.log("Ajax error occured on " + settings.url);
    alert("Ajax error occurred");
});


function initMap() {
    var location = {lat: 45.4560, lng: -73.8623};
    var map = new google.maps.Map(document.getElementById("googleMap"), {
        zoom: 8,
        center: location,
        draggable: true
    });

    var icon = {
        url: "../../resources/companyimages/enterprise_icon.jpg",
        scaledSize: new google.maps.Size(50, 50),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(25, 25)
    }

    var marker = new google.maps.Marker({
        position: location,
        map: map,
        icon: icon

    });
}