var click_face = 0;

window.fbAsyncInit = function () {
    FB.init({
        appId: appFbId, // id y thien
        status: true, // check login status
        cookie: true, // enable cookies to allow the server to access the session
        xfbml: true, // parse XFBML
        frictionlessRequests: true,
        oauth: true
    });
    FB.getLoginStatus(function (response) {
        console.log(response);
        if (response.status === 'connected') {
            //user is authorized
            // callback(rootUrl + "user/login-facebook", 'Post', response.authResponse.accessToken, false);
        }
    });
};
function callFBShare() {
    FB.ui({
        method: "feed",
        link: shareLink,
        caption: shareCaption,
        description: shareDescription,
        picture: rootUrl + sharePicture,
        display: "dialog",
        name: shareName
    }, function (response) {
        if (response.post_id) {
            // callback(urlCallBackShare, 'PUT', userFBID, 'share');
        } else {
            //alert("Bạn phải share để có thể nhận được Giftcode");
        }
    });
}
function getUserData() {
    FB.api('/me', function (response) {
        $(".user-id-fb").val(response.id);
    });
}
function getUserFriendData() {

    FB.api('/me/invitable_friends?limit=1000', function (response) {
        var array = response.data;
//            var array = [];
//            var i = 0;
//            for (i = 0; i <= 25; i++) {
//                var r = Math.floor(Math.random() * response.data.length);
//                array[i] = response.data[r];
//                response.data.splice(r, 1);
//            }
        var html = '';
        html += '<ul>';
        $.each(array, function (key, value) {
            html += '<li style="display: inline-block;width: 120px;text-align: center;vertical-align: top">\n\
                        <img src="' + value.picture.data.url + '" alt="">\n\
                <p>' + value.name + '</p>\n\
                <a href="javascript:;" data-rel="' + value.id + '" class="invi-friend" >Invi</a>\n\
                    </li>';
        });
        html += '</ul>';
        $("#friend-list").html(html);
        $(".invi-friend").click(function () {
            var uid = $(this).attr("data-rel");
            invi(uid);
        });
    });
}
function createRan(array) {
}
//load the JavaScript SDK
(function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
function invi(uid) {
    FB.ui({
            method: 'apprequests',
            message: 'Test invi FB chơi game Naruto',
            to: uid,
        }, function (response) {
            if (response) {
                alert('Successfully Invited');
                console.log(response);
            } else {
                alert('Failed To Invite');
            }
        }
    );
}
function FacebookInviteFriends() {
    FB.ui({
            method: 'apprequests',
            message: 'Test invi FB',
//            data: 'send-to-one-42'
        }, function (response) {
            if (response) {
                alert('Successfully Invited');
                console.log(response);
            } else {
                alert('Failed To Invite');
            }
        }
    );
}
function facebookLoginApp() {

    FB.login(function (response) {
        if (response.authResponse) {
            //user just authorized your app
            getUserData();
            callback(rootUrl + "user/login-facebook", 'Post', response.authResponse.accessToken, 'register');
            //callFBShare();
        }
    }, {
        scope: 'email,public_profile,user_friends,manage_pages,read_custom_friendlists,user_likes,publish_actions',
        return_scopes: true
    });
}
function callback(url, method, accessToken, first) {
    $.ajax({
            type: method,
            url: url,
            data: {accessToken: accessToken},
            success: function (response) {
                var result = $.parseJSON(response);
                if (result.status == 1) {
                    if (first == 'register') {
                        window.location.href = rootUrl + "guide";
                        return false;
                    }
                    if (first == 'share') {
                        alert("Chúc mừng bạn đã được + thêm " + result.pointShare + " lượt chơi");
                        window.location.href = rootUrl + "playgame";
                        return false;
                    }
                    if (first == 'login') {

                        window.location.href = rootUrl + "playgame";
                    }
                    //window.top.location=rootUrl + "playgame";
                }
                else {
                    alert("fail");
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        }
    )
    ;
}
$(document).ready(function () {

    $(".btn-get-userinfo").click(function () {
        FB.getLoginStatus(function (response) {
            if (response.status === 'connected') {
                callback(rootUrl + "user/login-facebook", 'Post', response.authResponse.accessToken, 'login');
            } else {
                facebookLoginApp();
            }
        });
    });
});