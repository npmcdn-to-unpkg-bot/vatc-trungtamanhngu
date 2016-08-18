var is_busy = false;
var page = 1;
var stopped = false;
$(document).ready(function () {
    ajaxLoadContent();
});
function ajaxLoadContent() {
    $(window).scroll(function () {
        // Element append nội dung
        $element = $('#category-product');

        // ELement hiển thị chữ loadding
//        $loadding = $('#loadding');

        // Nếu màn hình đang ở dưới cuối thẻ thì thực hiện ajax
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - $(document).height() / 100 * 20) {
            // Nếu đang gửi ajax thì ngưng
            if (is_busy == true) {
                return false;
            }

            // Nếu hết dữ liệu thì ngưng
            if (stopped == true) {
                return false;
            }

            // Thiết lập đang gửi ajax
            is_busy = true;
            // Hiển thị loadding
//            $loadding.removeClass('hidden');
//             var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1);
            // Gửi Ajax
            $.ajax({
                type: "POST",
                url: rootUrl + 'category-product/filter',
                data: {data: arrFilter,page: page},
                success: function (response) {
                    if (response.length == 0) {
                        is_busy = true;
                        stopped = true;
                        return false;

                    }
                    is_busy = false;
                    $element.append(response);
                    $element.find(".productListItem").animate({opacity: '1'}, "slow");
                    page++;

                },
                error: function (xhr, ajaxOptions, thrownError) {
                }
            });
            return false;
        }
    });

}