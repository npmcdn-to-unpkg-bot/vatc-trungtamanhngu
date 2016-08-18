function showPopup(cla, title, message) {
    $(".popup-area").show();
    $(".popup-main").hide();
    $(".popup-" + cla + " .text-popup-header").html(title);
    $(".popup-" + cla + " .text-popup-content").html(message);
    $(".popup-" + cla).fadeIn();

}
function closePopup() {
    $(".popup-area").fadeOut();
}
function changeProvince(val, $cla) {
    if (val != -1) {
        $.ajax({
            type: "POST",
            url: rootUrl + 'user/handle',
            data: {method: "provinceZone", data: val},
            success: function (response) {
                var result = $.parseJSON(response);

                if (result.status == 1) {
                    $("#" + $cla).html('');
                    $.each(result.data, function (key, val) {
                        var html = '<option value="' + val.zd_id + '">' + val.zd_name + '</option>';
                        $("#" + $cla).append(html);
                    });
                }
            }
            ,
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
}