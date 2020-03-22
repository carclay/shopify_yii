<?php
/* @var $this yii\web\View */

use yii\web\View;
use yii\widgets\LinkPager;

$this->title = 'My Yii Application';
$this->registerJsFile("/js/products_table.js", ['position' => View::POS_HEAD]);
?>

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css"/>

<div class="site-index">
    <div id="product_node"></div>
    <template id="product_node_template">
        <div class="block">
            <div class="container">
                <div class="row-fluid">
                    <div class="form-group">
                        <label for="title">Наименование</label>
                        <input type="text" class="form-control" id="title" v-model="searchForm.title">
                    </div>
                    <div class="form-group">
                        <label for="types">Тип продукта</label>
                        <select class="selectpickers" id="types" data-show-subtext="true" data-live-search="true" v-model="searchForm.types">
                            <option value="reset">ничего не выбрано</option>
                            <option v-for="type in arTypes" :data-subtext="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tags">Теги</label>
                        <select class="selectpickers" id="tags" data-show-subtext="true" data-live-search="true" v-model="searchForm.tags">
                            <option value="reset">ничего не выбрано</option>
                            <option v-for="tag in arTags" :data-subtext="tag" :value="tag">{{ tag }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" @click.prevent="search" class="btn btn-primary">Найти</button>
                        <button type="submit" @click.prevent="reset" class="btn btn-secondary">Сбросить</button>
                    </div>
                </div>
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Идентификатор</th>
                    <th scope="col">Наименование</th>
                    <th scope="col">Тип товара</th>
                    <th scope="col">Теги</th>
                    <th scope="col">Количество просмотров</th>
                </tr>
                </thead>
                <tbody>
                    <tr v-for="item in arItems">
                        <td>{{ item.product_id }}</td>
                        <td>{{ item.title }}</td>
                        <td>{{ item.type }}</td>
                        <td>{{ item.tags ? item.tags.join(', ') : ''}}</td>
                        <td>{{ item.views }}</td>
                    </tr>
                </tbody>
            </table>
            <nav aria-label="Page navigation example" v-if="parseInt(nav.total) > 1" :data-limit="nav.limit">
                <ul class="pagination justify-content-center">
                    <li @click="goToPage" v-for="i in parseInt(nav.total)" class="page-item" :class="{'active' : (i - 1) == parseInt(nav.offset)}" :data-page="i"><a class="page-link" href="javascript: void(0)">{{ i }}</a></li>
                </ul>
            </nav>
        </div>
    </template>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        if(typeof window.jsProductsTable == 'undefined'){
            window.jsProductsTable = new JSProductsTable('<?=json_encode($arResult)?>');
            window.jsProductsTable.init();
        }
    });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>