var province = 0;
var district = 0;
var ward = 0;
$(document).ready(function () {

    if ($(".daterange-single").length) {
        $('.daterange-single').daterangepicker({
            singleDatePicker: true,
            setStartDate: false,
            locale: {
                format: 'YYYY-MM-DD'
            },
        });
    }
    if ($("#anytime-month-numeric").length) {
        $("#anytime-month-numeric").AnyTime_picker({
            format: "%Z-%m-%d"
        });
    }
    if ($(".select-search").length) {
        $('.select-search').select2();
    }
    if ($(".select-icons").length) {
        $(".select-icons").select2({
            templateResult: iconFormat,
            minimumResultsForSearch: Infinity,
            templateSelection: iconFormat,
            escapeMarkup: function (m) {
                return m;
            }
        });
    }
    if ($(".datatable-highlight").length) {
        $(".datatable-highlight").DataTable();
    }

    if ($("#description_ckeditor").length) {
        var editor = CKEDITOR.replace('description_ckeditor', {
            height: '300px',
            extraPlugins: 'forms'
        });
        CKFinder.setupCKEditor(editor, rootUrl + 'public/BackendMD/plugins/ckfinder/');
    }
    var validator = $(".form-horizontal").validate();
    //normal
    $("body").on('click', '.btn-submit-content', function () {
        if (validator.form()) {
            showLoading();
            var data = $(".form-horizontal").serializeArray();
            var x;
            var temArray = {};
            for (x in data) {
                if (data[x]['name'] == 'pi_image_link') {
                    //get option product
                    if (typeof temArray[data[x]['name']] === 'undefined' || temArray[data[x]['name']] === null) {
                        temArray[data[x]['name']] = {};
                    }
                    temArray[data[x]['name']][x] = data[x]['value'];

                } else {
                    temArray[data[x]['name']] = data[x]['value'];
                }
            }
            if ($("#description_ckeditor").length) {
                temArray['description'] = CKEDITOR.instances['description_ckeditor'].getData();
            }
            $.ajax({
                type: "POST",
                url: queryUrl + '/handle',
                data: {method: "newEditContent", data: JSON.stringify(temArray)},
                success: function (response) {
                    var result = $.parseJSON(response);
                    hideLoading();
                    if (result.status == 1) {
                        SuccessNotice('Thành Công', result.message);
                        setTimeout(function () {
                            document.location.reload();
                        }, 1300);

                    }
                    else {
                        WarningNotice('Lỗi', result.message);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    DangerNotice("Nguy Hiểm", "Lỗi hệ thồng");
                }
            });
        }
        else {
            return false;
        }
    });
    // product
    $("body").on('click', '.btn-submit-content-product', function () {
        if (validator.form()) {
            showLoading();
            var data = $(".form-horizontal").serializeArray();
            var x;
            var temArray = {};
            for (x in data) {
                if (data[x]['name'] == 'Color' || data[x]['name'] == 'Size' || data[x]['name'] == 'pi_image_link') {
                    //get option product
                    if (typeof temArray[data[x]['name']] === 'undefined' || temArray[data[x]['name']] === null) {
                        temArray[data[x]['name']] = {};
                    }
                    temArray[data[x]['name']][x] = data[x]['value'];

                } else {
                    temArray[data[x]['name']] = data[x]['value'];
                }
            }
            //get decription ckeditor
            if ($("#description_ckeditor").length) {
                temArray['description'] = CKEDITOR.instances['description_ckeditor'].getData();
            }
            //
            //get attribute product
            var attribute = document.getElementsByClassName("ckeditor-text");
            if (attribute.length != 0) {
                $.each(attribute, function (key, val) {
                    var data_rel = val.dataset.rel;
                    var data_description = CKEDITOR.instances[val.id].getData();
                    var data_title = $("input[name='hpa_name_" + data_rel + "']").val();

                    if (typeof temArray['attribute'] === 'undefined' || temArray['attribute'] === null) {
                        temArray['attribute'] = {};
                    }
                    if (data_description != '' && data_title != '') {
                        if (typeof temArray['attribute'][key] === 'undefined' || temArray['attribute'][key] === null) {
                            temArray['attribute'][key] = {};
                        }

                        temArray['attribute'][key]['hpa_description'] = data_description;
                        temArray['attribute'][key]['hpa_name'] = data_title;
                    }
                });
            }
            //end get attribute product

            //get price product
            var price = document.getElementsByClassName("tab_price");
            if (price.length != 0) {
                $.each(price, function (key, val) {
                    var data_price = $(val).find("input[name='hqr_price']").val();
                    var data_description = $(val).find("textarea[name='hqr_decription']").val();
                    var quantityTo = $(val).find("input[name='hqr_quantity_to']").val();
                    var quantityFrom = $(val).find("input[name='hqr_quantity_from']").val();

                    if (typeof temArray['price'] === 'undefined' || temArray['price'] === null) {
                        temArray['price'] = {};
                    }
                    if (data_price != '' && quantityFrom != '') {
                        if (typeof temArray['price'][key] === 'undefined' || temArray['price'][key] === null) {
                            temArray['price'][key] = {};
                        }
                        temArray['price'][key]['hqr_quantity_from'] = quantityFrom;
                        temArray['price'][key]['hqr_quantity_to'] = quantityTo;
                        temArray['price'][key]['hqr_price'] = data_price;
                        temArray['price'][key]['hqr_decription'] = data_description;
                    }
                });
            }
            //end get price product

            $.ajax({
                type: "POST",
                url: queryUrl + '/handle',
                data: {method: "newEditContent", data: JSON.stringify(temArray)},
                success: function (response) {
                    var result = $.parseJSON(response);
                    hideLoading();
                    if (result.status == 1) {
                        SuccessNotice('Thành Công', result.message);
                        setTimeout(function () {
                            window.close();
                        }, 1300);

                    }
                    else {
                        WarningNotice('Lỗi', result.message);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    DangerNotice("Nguy Hiểm", "Lỗi hệ thồng");
                }
            });
        }
        else {
            return false;
        }
    });
    $("body").on('click', '.btn-delete-content', function () {
        var id = $(this).attr("data-rel");
        var notice = new PNotify({
            title: "Thông Báo",
            text: "Bạn có chắc chắn muốn xóa dòng này",
            hide: false,
            type: 'warning',
            confirm: {
                confirm: true,
                buttons: [
                    {
                        text: 'Yes',
                        addClass: 'btn-sm'
                    },
                    {
                        addClass: 'btn-sm'
                    }
                ]
            },
            buttons: {
                closer: false,
                sticker: false
            },
            history: {
                history: false
            }
        })
        // On confirm
        notice.get().on('pnotify.confirm', function () {
            showLoading();
            $.ajax({
                type: "POST",
                url: queryUrl + '/handle',
                data: {method: "deleteContent", data: id},
                success: function (response) {
                    var result = $.parseJSON(response);
                    hideLoading();
                    if (result.status == 1) {
                        SuccessNotice('Thành Công', result.message);
                        setTimeout(function () {
                            document.location.reload();
                        }, 1300);

                    }
                    else {
                        WarningNotice('Lỗi', result.message);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    DangerNotice("Nguy Hiểm", "Lỗi hệ thồng");
                }
            });
        });
    });
    var validator_login = $(".form-login").validate();
    $("body").on('click', '.btn-login-access', function () {
        if (validator_login.form()) {
            var data = $(".form-login").serializeArray();
            var x;
            var temArray = {};
            for (x in data) {
                temArray[data[x]['name']] = data[x]['value'];
            }
            $.ajax({
                type: "POST",
                url: queryUrl + '/handle',
                data: {method: "login", data: JSON.stringify(temArray)},
                success: function (response) {
                    var result = $.parseJSON(response);

                    if (result.status == 1) {
                        SuccessNotice('Thành Công', result.message);
                        setTimeout(function () {
                            window.location.href = rootUrlModule;
                        }, 1300);

                    }
                    else {
                        WarningNotice('Lỗi', result.message);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    DangerNotice("Nguy Hiểm", "Lỗi hệ thồng");
                }
            });
        }
        else {
            return false;
        }
    });
    $(".btn-test").click(function () {
        window.open(rootUrlModule + "test", '_blank');
    });
    //add status order
    $("body").on('click', '.btn-add-historyorder', function () {
        var data = $(".status-form").serializeArray();
        var x;
        var temArray = {};
        for (x in data) {
            temArray[data[x]['name']] = data[x]['value'];
        }
        var notice = new PNotify({
            title: "Thông Báo",
            text: "Bạn có chắc chắn muốn thêm",
            hide: false,
            type: 'info',
            confirm: {
                confirm: true,
                buttons: [
                    {
                        text: 'Yes',
                        addClass: 'btn-sm'
                    },
                    {
                        addClass: 'btn-sm'
                    }
                ]
            },
            buttons: {
                closer: false,
                sticker: false
            },
            history: {
                history: false
            }
        })
        // On confirm
        notice.get().on('pnotify.confirm', function () {
            showLoading();
            $.ajax({
                type: "POST",
                url: queryUrl + '/handle',
                data: {method: "addHistoryOrder", data: JSON.stringify(temArray)},
                success: function (response) {
                    var result = $.parseJSON(response);
                    hideLoading();
                    if (result.status == 1) {
                        SuccessNotice('Thành Công', result.message);
                        setTimeout(function () {
                            document.location.reload();
                        }, 1300);

                    }
                    else {
                        WarningNotice('Lỗi', result.message);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    DangerNotice("Nguy Hiểm", "Lỗi hệ thồng");
                }
            });
        });
    });

    //change Manufacture

    $("#select-manufacturer").change(function () {
        showLoading();
        var id = $(this).val();
        $.ajax({
            type: "POST",
            url: queryUrl + '/change-manufacture',
            data: {id: id},
            success: function (response) {
                hideLoading();
                $(".select-category-product-area").html(response);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                DangerNotice("Nguy Hiểm", "Lỗi hệ thồng");
            }
        });


    });
    //chose Collection

    $(".chose-product-collection").change(function () {
        showLoading();
        var id = $(this).val();
        $.ajax({
            type: "POST",
            url: queryUrl + '/chose-product',
            data: {id: id},
            success: function (response) {
                hideLoading();
                $(".product-append").html(response);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                DangerNotice("Nguy Hiểm", "Lỗi hệ thồng");
            }
        });


    });


});
function DangerNotice(title, text) {
    new PNotify({
        title: title,
        text: text,
        icon: 'icon-blocked',
        type: 'error'
    });
}

// Success notification
function SuccessNotice(title, text) {
    new PNotify({
        title: title,
        text: text,
        icon: 'icon-checkmark3',
        type: 'success'
    });
}

// Warning notification
function WarningNotice(title, text) {
    new PNotify({
        title: title,
        text: text,
        icon: 'icon-warning2',
        type: 'warning'
    });
}
;

// Info notification
function InfoNotice(title, text) {
    new PNotify({
        title: title,
        text: text,
        icon: 'icon-info22',
        type: 'info'
    });
}
;
// Confirm
function ConfirmNotice(title, text) {
    var notice = new PNotify({
        title: title,
        text: text,
        hide: false,
        type: 'warning',
        confirm: {
            confirm: true,
            buttons: [
                {
                    text: 'Yes',
                    addClass: 'btn-sm'
                },
                {
                    addClass: 'btn-sm'
                }
            ]
        },
        buttons: {
            closer: false,
            sticker: false
        },
        history: {
            history: false
        }
    })
    // On confirm
    notice.get().on('pnotify.confirm', function () {
        return 1;
    });

    // On cancel
    notice.get().on('pnotify.cancel', function () {
        return 0;
    });
}
;
function resetForm() {
    document.getElementsByClassName("form-horizontal").reset();
}

function BrowseServer(link, link_hide) {
    // You can use the "CKFinder" class to render CKFinder in a page:
    var finder = new CKFinder();
    // The path for the installation of CKFinder (default = "/ckfinder/").
    finder.basePath = rootUrl + '/public/BackendMD/plugins/ckfinder/';
    //Startup path in a form: "Type:/path/to/directory/"
    finder.startupPath = 'Images:/';
    // Name of a function which is called when a file is selected in CKFinder.
    finder.selectActionFunction = function (fileUrl, data) {
        $("input[name='" + link_hide + "']").val(fileUrl);
        $("input[name='" + link + "']").attr("src", fileUrl);
    };
    // Additional data to be passed to the selectActionFunction in a second argument.
    // We'll use this feature to pass the Id of a field that will be updated.
    finder.selectActionData = 'xImagePath';
    // Launch CKFinder
    finder.popup();
}
function BrowseServerGallery(link, link_hide) {
    // You can use the "CKFinder" class to render CKFinder in a page:
    var finder = new CKFinder();
    // The path for the installation of CKFinder (default = "/ckfinder/").
    finder.basePath = rootUrl + '/public/BackendMD/plugins/ckfinder/';
    //Startup path in a form: "Type:/path/to/directory/"
    finder.startupPath = 'Images:/';
    // Name of a function which is called when a file is selected in CKFinder.
    finder.selectActionFunction = function (fileUrl, data) {
        console.log("======");
        console.log(fileUrl);
        console.log(data);
    };
    // Additional data to be passed to the selectActionFunction in a second argument.
    // We'll use this feature to pass the Id of a field that will be updated.
    finder.selectActionData = 'xImagePath';
    // Launch CKFinder
    finder.popup();
}

function showPopup(id) {
    if (id) {
        $.ajax({
            type: "POST",
            url: queryUrl + '/handle',
            data: {method: 'detailContent', id: id},
            success: function (response) {
                var result = $.parseJSON(response);
                if (result) {
                    $.each(columns, function (key, val) {
                        switch (val) {
                            case 'zone':
                                switch (key) {
                                    case 'zp_id':
                                        province = result[key];
                                        var cateID = result[key];
                                        $("select[name='" + key + "'] option").each(function () {
                                            var valueOp = $(this).val();
                                            if (valueOp == cateID) {
                                                $(this).attr("selected", "true");
                                            } else {
                                                $(this).removeAttr("selected");
                                            }

                                        });
                                        $("select[name='" + key + "']").select2();
                                        changeProvince(result[key]);
                                        break;
                                    case 'zd_id':
                                        district = result[key];
                                        var cateID = result[key];
                                        $("select[name='" + key + "'] option").each(function () {
                                            var valueOp = $(this).val();
                                            if (valueOp == cateID) {
                                                $(this).attr("selected", "true");
                                            } else {
                                                $(this).removeAttr("selected");
                                            }

                                        });
                                        $("select[name='" + key + "']").select2();
                                        changeDistrict(result[key]);
                                        break;
                                    case 'zw_id':
                                        setTimeout(function () {
                                            $("select[name='zd_id'] option").each(function () {
                                                if (district == cateID) {
                                                    $(this).attr("selected", "true");
                                                } else {
                                                    $(this).removeAttr("selected");
                                                }

                                            });
                                            $("select[name='zd_id']").select2();
                                        }, 1000);
                                        setTimeout(function () {
                                            var cateID = result[key];
                                            $("select[name='" + key + "'] option").each(function () {
                                                var valueOp = $(this).val();
                                                if (valueOp == cateID) {
                                                    $(this).attr("selected", "true");
                                                } else {
                                                    $(this).removeAttr("selected");
                                                }

                                            });
                                            $("select[name='" + key + "']").select2();
                                        }, 2000);
                                        break;
                                }

                                break;
                            case 'checkbox-switch':
                                if (result[key] == 0) {
                                    $("input[name='" + key + "']").bootstrapSwitch('state', false);
                                } else {
                                    $("input[name='" + key + "']").bootstrapSwitch('state', true);
                                }
                                break;
                            case 'image':
                                $("input[name='" + key + "_img']").attr("src", result[key]);
                                $("input[name='" + key + "']").val(result[key]);
                                break;
                            case 'select':
                                var cateID = result[key];
                                $("select[name='" + key + "'] option").each(function () {
                                    var valueOp = $(this).val();
                                    if (valueOp == cateID) {
                                        $(this).attr("selected", "true");
                                    } else {
                                        $(this).removeAttr("selected");
                                    }

                                });
                                $("select[name='" + key + "']").select2();
                                break;
                            case 'textarea':
                                $("textarea[name='" + key + "']").val(result[key]);
                                break;
                            case 'ckeditor':
                                CKEDITOR.instances['description_ckeditor'].setData(result[key]);
                                break;
                            case 'color':
                                $(".colorpicker-input-initial").spectrum({
                                    preferredFormat: "hex3",
                                    color: result[key],
                                    showInitial: true,
                                    showInput: true,
                                    hide: function (c) {
                                        $(".show-color").val(c.toHexString());
                                    }
                                });
                                $("input[name='" + key + "']").val(result[key]);
                                break;
                            default:
                                $("input[name='" + key + "']").val(result[key]);
                        }
                    });

                }
            }
            ,
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    } else {
        $("textarea").val('');
        $("input").val('');
        $("input[type=image]").attr("src", '');
        if ($("#description_ckeditor").length) {
            CKEDITOR.instances['description_ckeditor'].setData('');
        }
    }
}
function iconFormat(icon) {
    var originalOption = icon.element;
    if (!icon.id) {
        return icon.text;
    }
    var $icon = "<i style='display: inline-block;width: 10px;height: 10px;background-color: " + $(icon.element).data('icon') + " ' ></i>" + icon.text;

    return $icon;
}
function createAttributeProduct() {
    var html = '<div class="form-group attribute_' + attr_id + '" >\n\
                    <div class="col-sm-3">\n\
                        <label >Title</label>\n\
                        <input type="text"  name="hpa_name_' + attr_id + '"  class="form-control title-attribute">\n\
                    </div>\n\
                    <div class="col-sm-8">\n\
                        <label >Decription</label>\n\
                        <textarea data-rel="' + attr_id + '" id="description_ckeditor_' + attr_id + '" rows="5" cols="5" class="form-control ckeditor-text" ></textarea>\n\
                    </div>\n\
                    <div class="col-sm-1">\n\
                        <button type="button" onclick="removeAttributeProduct($(this))" class="btn border-danger text-danger-600 btn-flat btn-icon btn-rounded btn-remove-attribute"><i class="icon-minus3"></i></button>\n\
                    </div>\n\
                    <script type="text/javascript">\n\
                        $(document).ready(function () {\n\
                            var editor' + attr_id + ' = CKEDITOR.replace("description_ckeditor_' + attr_id + '", {\n\
                                                height: "300px",\n\
                                                extraPlugins: "forms"\n\
                                            });\n\
                            CKFinder.setupCKEditor(editor' + attr_id + ', rootUrl + "public/BackendMD/plugins/ckfinder/");\n\
                        });\n\
                    </script>\n\
                </div>';
    attr_id++;
    $(".product-attribute .modal-body").append(html);
    $("html, body").animate({scrollTop: $(document).height()}, 1000);
}
function createPriceProduct() {
    var html = '<div  class="tab_price form-group price_' + price + '" >\n\
    <div class="col-sm-2">\n\
                         <label>Quantity From</label>\n\
                         <input type="text" value="" name="hqr_quantity_from" class="form-control ">\n\
                    </div>\n\
                    <div class="col-sm-2">\n\
                        <label>Quantity To</label>\n\
                        <input type="text"  name="hqr_quantity_to" class="form-control ">\n\
                    </div>\n\
                    <div class="col-sm-2">\n\
                         <label>Price</label>\n\
                         <input data-rel="' + price + '" type="text" value="" name="hqr_price" class="form-control ">\n\
                    </div>\n\
                    <div class="col-sm-5">\n\
                         <label>Decription</label>\n\
                         <textarea name="hqr_decription" data-rel="" rows="5" cols="5" class="form-control"></textarea>\n\
                    </div>\n\
                    <div class="col-sm-1">\n\
                         <button type="button "  onclick="removeAttributeProduct($(this))"\n\
                            class="btn border-danger text-danger-600 btn-flat btn-icon btn-rounded btn-remove-attribute">\n\
                         <i class="icon-minus3"></i></button>\n\
                    </div>\n\
                </div>';
    price++;
    $(".product-price .modal-body").append(html);
    $("html, body").animate({scrollTop: $(document).height()}, 1000);
}
function removeAttributeProduct($this) {
    var notice = new PNotify({
        title: "Thông Báo",
        text: "Bạn có chắc chắn muốn xóa dòng này",
        hide: false,
        type: 'warning',
        confirm: {
            confirm: true,
            buttons: [
                {
                    text: 'Yes',
                    addClass: 'btn-sm'
                },
                {
                    addClass: 'btn-sm'
                }
            ]
        },
        buttons: {
            closer: false,
            sticker: false
        },
        history: {
            history: false
        }
    })
    // On confirm
    notice.get().on('pnotify.confirm', function () {
        $this.parents(".form-group").remove();
    })


}
function searchFilter(cla) {
    var validatorSearch = $(".search-form").validate();
    if (validatorSearch.form()) {
        var data = $(".search-form").serializeArray();
        var x;
        var temArray = {};
        for (x in data) {
            temArray[data[x]['name']] = data[x]['value'];
        }
        showLoading();
        $.ajax({
            type: "POST",
            url: queryUrl + '/search',
            data: {method: "search", data: JSON.stringify(temArray)},
            success: function (response) {
                hideLoading();
                $(".filter-search").empty();
                $(".filter-search").html(response);
                $(".datatable-highlight").DataTable();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                DangerNotice("Nguy Hiểm", "Lỗi hệ thồng");
            }
        });
    }
    else {
        return false;
    }
}
function changeProvince(val) {
    if (!val) {
        var val = document.getElementById("province").value;
    }
    if (val != -1) {
        $.ajax({
            type: "POST",
            url: queryUrl + '/handle',
            data: {method: "provinceZone", data: val},
            success: function (response) {
                var result = $.parseJSON(response);

                if (result.status == 1) {
                    $("#district").html('');
                    $.each(result.data, function (key, val) {
                        var html = '<option value="' + val.zd_id + '">' + val.zd_name + '</option>';
                        $("#district").append(html);
                    });
                    $("#district").select2();
                }
            }
            ,
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
}
function changeDistrict(val) {
    if (!val) {
        var val = document.getElementById("district").value;
    }
    if (val != -1) {
        showLoading();
        $.ajax({
            type: "POST",
            url: queryUrl + '/handle',
            data: {method: "districtZone", data: val},
            success: function (response) {
                var result = $.parseJSON(response);
                hideLoading();
                if (result.status == 1) {
                    $("#ward").html('');
                    $.each(result.data, function (key, val) {
                        var html = '<option value="' + val.zw_id + '">' + val.zw_name + '</option>';
                        $("#ward").append(html);
                    });
                    $("#ward").select2();
                }
            }
            ,
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
}
function sendNewsletter(id) {
    var notice = new PNotify({
        title: "Thông Báo",
        text: "Bạn có muốn gửi Email đến cho tất cả khách hàng?",
        hide: false,
        type: 'info',
        confirm: {
            confirm: true,
            buttons: [
                {
                    text: 'Yes',
                    addClass: 'btn-sm'
                },
                {
                    addClass: 'btn-sm'
                }
            ]
        },
        buttons: {
            closer: false,
            sticker: false
        },
        history: {
            history: false
        }
    })
    // On confirm
    notice.get().on('pnotify.confirm', function () {
        showLoading();
        SuccessNotice('Sending...', "Đang gửi Email Newsletter...");
        var myVar = setInterval(function () {
            SuccessNotice('Sending...', "Đang gửi Email Newsletter...");
        }, 3000);
        $.ajax({
            type: "POST",
            url: queryUrl + '/handle',
            data: {method: "sendNewsletter", data: id},
            success: function (response) {
                var result = $.parseJSON(response);
                clearInterval(myVar);
                hideLoading();
                if (result.status == 1) {
                    SuccessNotice('Thành Công', result.message);
                } else {
                    WarningNotice('Lỗi', result.message);
                }
            }
            ,
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    });


}
function showLoading() {
    $(".loading-page").show();
}
function hideLoading() {
    $(".loading-page").hide();
}