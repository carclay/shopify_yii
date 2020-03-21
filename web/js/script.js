$(document).ready(function () {
    function JSViewCheck(params) {
        this.params = params || {};

        this.setView = function () {
            if(typeof meta == 'undefined' || meta.page.pageType !== 'product' || meta.product.id.length <= 0){
                return false;
            }

            $.ajax({
                url: 'https://carclay.site/api/?shop=' + document.location.host + '&productId=' + meta.product.id,
                success: function (resp) {}
            });

        };
    }

    if (typeof window.jsViewCheck == 'undefined') {
        window.jsViewCheck = new JSViewCheck();
    }

    window.jsViewCheck.setView();
});