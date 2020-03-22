<?php


namespace app\models;


use yii\data\Pagination;

class ProductList
{
    private $shopify;
    private $localShopId = false;
    private $limit = 3;
    private $offset;
    public $pages;

    /**
     * ProductList constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->shopify = new Shopify();
        $this->localShopId = Shops::find()->where(['shop' => $this->shopify->shop])->one()->id;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    private function getList(){
        $where = ['and'];
        $where[] = ['shop_id' => $this->localShopId];
        if(isset($_REQUEST["search"]["title"]) && !empty($_REQUEST["search"]["title"])){
            $where[] = ['like', 'title', htmlspecialchars($_REQUEST["search"]["title"])];
        }
        $types = $_REQUEST["search"]["types"];
        if(!empty($types) && $types !== 'reset'){
            $where[] = ['type' => $_REQUEST["search"]["types"]];
        }

        $query = Products::find()->where($where);

        $tags = $_REQUEST["search"]["tags"];
        if(!empty($tags) && current($tags) !== 'reset'){
            $query->innerJoin('tags', "products.product_id = tags.product_id and tags.value = '".htmlspecialchars($_REQUEST["search"]["tags"])."'");
        }

        $countQuery = clone $query;
        $this->pages = new Pagination(['totalCount' => $countQuery->count()]);
        $this->pages->setPageSize($this->limit);
        $arItems = $query->offset($this->pages->offset)
            ->limit($this->pages->limit)
            ->asArray(true)->all();
        $this->pages->getLimit();
        $arProducts = array_column($arItems, "product_id");

        $rsTags = Tags::find()->where(["product_id" => $arProducts])->asArray()->all();
        $arTags = [];
        foreach($rsTags as $tag){
            $arTags[$tag["product_id"]][] = $tag["value"];
        }

        foreach($arItems as $k => $product){
            $arItems[$k]["tags"] = $arTags[$product["product_id"]];
        }

        return $arItems;
    }

    /**
     * @return array
     */
    private function getPagination(){
        return [
            "total" => $this->pages->getPageCount(),
            "offset" => $this->pages->getOffset(),
            "limit" => $this->pages->getLimit(),
        ];
    }

    /**
     * @return array
     */
    private function getTypes(){
        $rs = Products::find()
            ->where([
                "shop_id" => $this->localShopId
            ])
            ->select('type')
            ->groupBy('type')
            ->asArray()
            ->all();
        $arTypes = [];
        foreach($rs as $type){
            $arTypes[] = $type["type"];
        }
        return $arTypes;
    }

    /**
     * @return array
     */
    private function getTags(){
        $rs = Products::find()
            ->where([
                "shop_id" => $this->localShopId
            ])
            ->innerJoin('tags', 'products.product_id = tags.product_id')
            ->select('tags.value')
            ->groupBy('value')
            ->asArray()
            ->all();

        $arTags = [];
        foreach($rs as $tag){
            $arTags[] = $tag["value"];
        }
        return $arTags;
    }

    /**
     * @return array
     */
    public function getResult(){
        return [
            "arItems" => $this->getList(),
            "nav" => $this->getPagination(),
            'arTypes' => $this->getTypes(),
            'arTags' => $this->getTags(),
        ];
    }
}