var validator;
$(document).ready(function () {
    validator = $(".form-horizontal").validate();
    jQuery.extend(jQuery.validator.messages, {
        required: "Không được để trống.",
        remote: "Please fix this field.",
        email: "Địa chỉ Email không hợp lệ",
        url: "Please enter a valid URL.",
        date: "Please enter a valid date.",
        dateISO: "Please enter a valid date (ISO).",
        number: "Phải là số.",
        digits: "Please enter only digits.",
        creditcard: "Please enter a valid credit card number.",
        equalTo: "Please enter the same value again.",
        accept: "Please enter a value with a valid extension.",
        maxlength: jQuery.validator.format("Không được quá {0} ký tự."),
        minlength: jQuery.validator.format("Tối thiểu {0} ký tự."),
        rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
        range: jQuery.validator.format("Please enter a value between {0} and {1}."),
        max: jQuery.validator.format("Giá trị lớn nhất là {0}."),
        min: jQuery.validator.format("Giá trị nhỏ nhất là {0}.")
    });
});
function registerUser() {
    var regietrvalidator = $(".register-form").validate();
    if (regietrvalidator.form()) {
        var data = $(".register-form").serializeArray();
        var x;
        var temArray = {};
        for (x in data) {
            temArray[data[x]['name']] = data[x]['value'];
        }
        $.ajax({
            type: "POST",
            url: rootUrl + 'user/handle',
            data: {method: "register", data: JSON.stringify(temArray)},
            success: function (response) {
                var result = $.parseJSON(response);

                if (result.status == 1) {
                    showPopup('success', 'Thành Công', result.message);
                    //redirect if has param redirect
                    var current_param = getUrlVars();
                    setTimeout(function () {
                        if (current_param['redirect'] != undefined) {
                            document.location.href = rootUrl + current_param['redirect'];
                        } else {
                            document.location.href = rootUrl;
                        }
                    }, 1300);
                }
                else {
                    showPopup('error', 'Thất Bại', result.message);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
    else {
        return false;
    }
}
function loginUser() {
    if (validator.form()) {
        var data = $("#login-form").serializeArray();
        var x;
        var temArray = {};
        for (x in data) {
            temArray[data[x]['name']] = data[x]['value'];
        }
        $.ajax({
            type: "POST",
            url: rootUrl + 'user/handle',
            data: {method: "login", data: JSON.stringify(temArray)},
            success: function (response) {
                var result = $.parseJSON(response);

                if (result.status == 1) {
                    showPopup('success', 'Thành Công', result.message);
                    //redirect if has param redirect
                    var current_param = getUrlVars();
                    setTimeout(function () {
                        if (current_param['redirect'] != undefined) {
                            document.location.href = rootUrl + current_param['redirect'];
                        } else {
                            document.location.href = rootUrl;
                        }
                    }, 1300);
                }
                else {
                    showPopup('error', 'Thất Bại', result.message);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
    else {
        return false;
    }
}
//Login FaceBook
function facebookLogin() {
    FB.getLoginStatus(function (response) {
        console.log(response);
        if (response.status === 'connected') {
            submitLoginFacebook(response);
        } else if (response.status === 'not_authorized') {
            FB.login(function (response) {
                submitLoginFacebook(response);
            }, {scope: 'public_profile,email'});
        } else {
            FB.login(function (response) {
                submitLoginFacebook(response);
            }, {scope: 'public_profile,email'});
        }
    });
}
function submitLoginFacebook(response) {
    if (typeof (response.authResponse) != null) {
        $.ajax({
            type: "POST",
            url: rootUrl + "user/login-facebook",
            data: {accesstoken: response.authResponse.accessToken}
        }).done(function (msg) {
            var result = $.parseJSON(msg);
            if (result.status === 1) {
                showPopup('success', 'Thành Công', result.message);
                setTimeout(function () {
                    document.location.href = rootUrl;
                }, 1300);
            }
            else {
                showPopup('error', 'Thất Bại', result.message);
            }
        }).error(function (err) {
            showPopup('error', 'Thất Bại', "Đăng Nhập Facebook Không Thành Công");
        });
    }
}
//Login Facebook / End

//Login Google
function googleLogin() {
    gapi.client.setApiKey(appGGKey);
    gapi.auth.authorize({
        client_id: appGGId,
        scope: scopes,
        immediate: false,
        response_type: 'code'
    }, GoogleHandleAuthResult);
    return false;
}
function GoogleHandleAuthResult(authResult) {
    if (authResult && !authResult.error) {
        submitLoginGoogle(authResult);
    }
    else {
        showPopup('error', 'Thất Bại', "Đăng Nhập Google Không Thành Công");
    }
}
function makeApiCall() {
    gapi.client.load('plus', 'v1', function () {
        var request = gapi.client.plus.people.get({
            'userId': 'me'
        });
        request.execute(function (resp) {
            showPopup('error', 'Thất Bại', "Đăng Nhập Google Không Thành Công");
        });
    });
}
function submitLoginGoogle(response) {
    $.ajax({
        type: "POST",
        url: rootUrl + "user/google-callback",
        data: {code: response.code}
    }).done(function (msg) {
        var result = $.parseJSON(msg);
        if (result.status === 1) {
            showPopup('success', 'Thành Công', result.message);
            setTimeout(function () {
                document.location.href = rootUrl;
            }, 1300);
        }
        else {
            showPopup('error', 'Thất Bại', result.message);
        }
    }).error(function (err) {
        showPopup('error', 'Thất Bại', "Đăng Nhập Google Không Thành Công");
    });
}
//Login Google / End
function forgotPasswordUser() {
    if (validator.form()) {
        var data = $("#forgot-form").serializeArray();
        var x;
        var temArray = {};
        for (x in data) {
            temArray[data[x]['name']] = data[x]['value'];
        }
        showPopup('loading');
        $.ajax({
            type: "POST",
            url: rootUrl + 'user/handle',
            data: {method: "forgotPassword", data: JSON.stringify(temArray)},
            success: function (response) {
                var result = $.parseJSON(response);
                if (result.status == 1) {
                    showPopup('success', 'Thành Công', result.message);
                }
                else {
                    showPopup('error', 'Thất Bại', result.message);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
    else {
        return false;
    }
}
function registerNewsletter() {
    if (validator.form()) {
        var data = $(".form-horizontal").serializeArray();
        var x;
        var temArray = {};
        for (x in data) {
            temArray[data[x]['name']] = data[x]['value'];
        }
        $.ajax({
            type: "POST",
            url: rootUrl + 'user/handle',
            data: {method: "registerNewsletter", data: JSON.stringify(temArray)},
            success: function (response) {
                var result = $.parseJSON(response);

                if (result.status == 1) {
                    showPopup('success', 'Thành Công', result.message);
                    setTimeout(function () {
                        document.location.reload();
                    }, 1300);
                }
                else {
                    showPopup('error', 'Thất Bại', result.message);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
    else {
        return false;
    }
}
function contactMessage() {
    if (validator.form()) {
        var data = $(".form-horizontal").serializeArray();
        var x;
        var temArray = {};
        for (x in data) {
            temArray[data[x]['name']] = data[x]['value'];
        }
        $.ajax({
            type: "POST",
            url: rootUrl + 'contact/handle',
            data: {method: "contactMessage", data: JSON.stringify(temArray)},
            success: function (response) {
                var result = $.parseJSON(response);

                if (result.status == 1) {
                    showPopup('success', 'Thành Công', result.message);
                    setTimeout(function () {
                        document.location.reload();
                    }, 1300);
                }
                else {
                    showPopup('error', 'Thất Bại', result.message);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
    else {
        return false;
    }
}
function getUrlVars() {
    var vars = {}, hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        // vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
function myPage($method, $id) {
    if (validator.form()) {
        var data = $("#" + $id).serializeArray();
        var x;
        var temArray = {};
        for (x in data) {
            temArray[data[x]['name']] = data[x]['value'];
        }
        $.ajax({
            type: "POST",
            url: rootUrl + 'user/handle',
            data: {method: $method, data: JSON.stringify(temArray)},
            success: function (response) {
                var result = $.parseJSON(response);

                if (result.status == 1) {
                    showPopup('success', 'Thành Công', result.message);
                    setTimeout(function () {
                        document.location.reload();
                    }, 1300);
                }
                else {
                    showPopup('error', 'Thất Bại', result.message);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
    else {
        return false;
    }
}