function JSProductsTable(params) {
    this.params = JSON.parse(params) || {};
    this.init = function () {
        this.initComponent();
        console.log(this.params);
    };

    this.initComponent = function () {
        let _this = this;
        this.component = new Vue({
            data: {
                arItems: _this.params.arItems,
                nav: _this.params.nav,
                arTypes: _this.params.arTypes,
                arTags: _this.params.arTags,
                searchForm: {
                    title: '',
                    types: [],
                    tags: [],
                },
            },
            methods: {
                search: function(){
                    let urlParams = getAllUrlParams(document.location.href),
                        url,
                        _this = this;

                    if(
                        this.searchForm.title.length <= 0 &&
                        this.searchForm.types.length <= 0 &&
                        this.searchForm.tags.length <= 0
                    ){
                        return false;
                    }

                    urlParams = this.clearQuery(urlParams) || {};

                    urlParams['page'] = 1;
                    urlParams['search'] = this.searchForm;

                    url = document.location.pathname + '?' + $.param(urlParams);
                    $.ajax({
                        url: url,
                        type: 'post',
                        dataType: 'json',
                        success: function (resp) {
                            history.pushState(false, false, url);
                            _this.arItems = resp.arItems;
                            _this.nav = resp.nav;
                        }
                    });
                },
                goToPage: function (e) {
                    let page = e.target.closest('li').dataset['page'],
                        urlParts = document.location.href.split("?"),
                        urlParams = getAllUrlParams(document.location.href),
                        url,
                        _this = this;

                    urlParams = this.clearQuery(urlParams);

                    if (page === urlParams.page || (!urlParams.page && page === '1')) {
                        return false;
                    }

                    urlParams['page'] = page;
                    urlParams['per_page'] = e.target.closest('nav').dataset['limit'];

                    url = urlParts[0];
                    if (Object.keys(urlParams).length > 0) {
                        url += '?' + $.param(urlParams);
                    }

                    $.ajax({
                        url: url,
                        type: 'post',
                        dataType: 'json',
                        success: function (resp) {
                            history.pushState(false, false, url);
                            _this.arItems = resp.arItems;
                            _this.nav = resp.nav;
                        }
                    });
                },
                reset: function(){
                    document.location.href = document.location.pathname;
                },
                clearQuery: function (query) {
                    delete query.hmac;
                    delete query.locale;
                    delete query.session;
                    delete query.timestamp;

                    return query;
                },
            },
            el: '#product_node',
            template: '#product_node_template'
        });
    };
}

