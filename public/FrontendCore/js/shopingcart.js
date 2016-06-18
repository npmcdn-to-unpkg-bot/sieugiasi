var click = true;
$(document).ready(function () {
    addCart();
    quatityShoppingCart();
    changeQuatityShoppingCart();
    removeProductShoppingCart();
    //chose Color
    $("body").on("click", ".chose-color", function () {
        $(".chose-color").removeClass("selected");
        $(this).addClass("selected");
        var id = $(this).attr("data-id");
        var pr_id = $(this).attr("data-rel");
        $.ajax({
            type: "POST",
            url: rootUrl + 'product/change-color',
            data: {id_color: id, id_product: pr_id},
            success: function (response) {
                $("#buy-product").html(response);
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    });
});
function addCart() {
    //Add cart
    $("body").on('click', '.btn-add-cart', function () {
            postAddCart();
        })
    ;
}
function checkout() {
    var idForm = $("input[name='chose_address_order']:checked").val();
    var data_address = $("#" + idForm).serializeArray();
    var option_payment = $("input[name='option_payment']:checked").val();
    var bankcode = $("input[name='bankcode']:checked").val();
    $.ajax({
        type: "POST",
        url: rootUrl + 'checkout/handle',
        data: {
            method: 'payment',
            data_address: data_address,
            option_payment: option_payment,
            bankcode: bankcode,
            form_id: idForm
        },
        success: function (response) {
            var result = $.parseJSON(response);
            if (result.status == 1) {
                document.location.href = result.url;
            }
            else {
                showPopup('error', 'Thất Bại', result.message);
            }
        }
        ,
        error: function (xhr, ajaxOptions, thrownError) {
        }
    });
}
function popupChoseSizeColor(id) {
    $.ajax({
        type: "POST",
        url: rootUrl + 'shoping-cart/get-size-color/' + id,
        success: function (response) {
            $(".popup-chose-size-color-order .content-popup").html(response);
            showPopup('chose-size-color-order');

        },
        error: function (xhr, ajaxOptions, thrownError) {
        }
    });
}
function postAddCart() {
    $.ajax({
        type: "POST",
        url: rootUrl + 'shoping-cart/handle',
        data: {method: "addCart"},
        success: function (response) {
            var result = $.parseJSON(response);
            if (result.status == 1) {
                showPopup('add-cart', 'Thành Công', result.message);
            } else {
                showPopup('error', 'Thất Bại', result.message);
                return false;
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }
    });

}
function quatityShoppingCart() {
    if (click == true) {
        click = false;
        $("body").on("click", ".minus", function () {
            var id_product = $(this).attr("data-id");
            var id_size = $(this).attr("data-size");
            var id_color = $("#current-color").attr("data-id");
            var val = $(this).parent(".input-number-sc").find(".input-number").val();
            val--;
            if (val >= 0) {
                changeNumberProduct(val, id_product, id_size,id_color, $(this));

            }
        });
        $("body").on("click", ".plus", function () {
            var id_product = $(this).attr("data-id");
            var id_size = $(this).attr("data-size");
            var id_color = $("#current-color").attr("data-id");
            var val = $(this).parent(".input-number-sc").find(".input-number").val();
            var price_product = $(this).parent(".input-number-sc").parent("td").parent("tr").find(".price-total");
            val++;
            // if (val <= 5) {
                changeNumberProduct(val, id_product, id_size,id_color, $(this));
            // }
        });
    }
}
function changeQuatityShoppingCart() {
    // if (click == true) {
    //     click = false;
        $("body").on("click", ".minus-cart", function () {
            var id = $(this).attr("data-rel");
            var val = $(this).parent(".input-number-sc").find(".input-number").val();
            var price_product = $(this).parent(".input-number-sc").parent("td").parent("tr").find(".price-total");
            val--;
            if (val >= 0) {
                changeNumberCart(id, val, price_product, $(this));

            }
        });
        $("body").on("click", ".plus-cart", function () {
            var $this = $(this);
            var id = $(this).attr("data-rel");
            var val = $(this).parent(".input-number-sc").find(".input-number").val();
            var price_product = $(this).parent(".input-number-sc").parent("td").parent("tr").find(".price-total");
            val++;
            // if (val <= 5) {
                changeNumberCart(id, val, price_product, $(this));
            // }
        });
    // }
}
function changeNumberProduct($quantity,$id_product, $id_size,$id_color, $this,$status) {
    $.ajax({
        type: "POST",
        url: rootUrl + 'product/buy-product',
        data: {id_product: $id_product,id_size:$id_size,id_color:$id_color, quantity: $quantity,status:$status},
        success: function (response) {
            $("#buy-product").html(response);
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }
    });
}
function changeNumberCart($id, $quantity, price_product, $this) {
    $.ajax({
        type: "POST",
        url: rootUrl + 'shoping-cart/handle',
        data: {method: "changeNumberCart", id: $id, quantity: $quantity},
        success: function (response) {
            var result = $.parseJSON(response);
            if (result.status == 1) {
                click = true;
                window.location.reload();
                price_product.text(result.total_price);
                $(".temp-price").text(result.total_price_product);
                $(".coupon-discount").text(result.coupon);
                $(".fee-price").text(result.fee_drive);
                $(".total-price").text(result.total_price_order);
                $this.parent(".input-number-sc").find(".input-number").val($quantity);
            } else {
                showPopup('error', 'Thất Bại', result.message);
                return false;
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }
    });
}
function removeProductShoppingCart() {
    $("body").on('click', '.btn-remove-pr-shoppingcart', function () {
        $this = $(this);
        var id = $(this).attr("data-rel");
        $.ajax({
            type: "POST",
            url: rootUrl + 'shoping-cart/handle',
            data: {method: "removeProductCart", id: id},
            success: function (response) {
                var result = $.parseJSON(response);
                if (result.status == 1) {
                    window.location.reload();
                    // $this.parent(".item").remove();
                    // $(".temp-price").text(result.total_price_product);
                    // $(".coupon-discount").text(result.coupon);
                    // $(".fee-price").text(result.fee_drive);
                    // $(".total-price").text(result.total_price_order);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    });
}
function addCoupon() {
    var val = $("input[name='co_code']").val();
    $.ajax({
        type: "POST",
        url: rootUrl + 'shoping-cart/handle',
        data: {method: "addCoupon", code: val},
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