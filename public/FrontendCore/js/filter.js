$check = true;
$(document).ready(function () {
    $("#narrow-form").focusin(function () {
        $(this).find(".filter-price-c").addClass("price-selected");
        // $(this).find("#filter-submit").show();

    });
    $("#narrow-form").focusout(function () {
        $(this).find(".filter-price-c").removeClass("price-selected");
        // $(this).find("#filter-submit").hide();
    });
});
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
function filterCategoryProduct($type, $val) {
    if ($check) {
        $check = false;
        var url = '';
        if (arrFilter[$type] != undefined && arrFilter[$type] == $val) {
            // $this.removeClass('active');
            delete arrFilter[$type];
        } else {
            // $this.addClass('active');
            arrFilter[$type] = $val;
            // if (arrFilter[$type] == 'all') {
            //     delete arrFilter[$type];
            // }
        }
        $.each(arrFilter, function (key, value) {
            url += key + "=" + value + "&";
        });
        url = url.slice(0, -1);
        window.location.href = rootUrl + "category-product/" + seo + "?" + url;
    }
}