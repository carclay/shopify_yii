<?php


namespace app\models;


use phpDocumentor\Reflection\DocBlock\Tag;

class Import
{
    private $shopify;
    private $shop;

    public function __construct($shop)
    {
        $this->shop = $shop;
        $this->shopify = new Shopify();
        $this->shopify->setShop($this->shop);
    }

    public function setTask(){
        if(!$rsTask = CronTask::find()->where([
            "shop" => htmlspecialchars($this->shop)
        ])->one()){
            $rsTask = new CronTask();
        }
        $rsTask->shop = htmlspecialchars($this->shop);
        $rsTask->status = 1;
        $rsTask->last_id = 0;
        $rsTask->save();
    }

    public function isRunning(){
        return !empty(CronTask::find()->where(['status' => 1])->one());
    }

    public function run(){
        if(!$rs = CronTask::find()->where(["started" => 1])->one()){
            return false;
        }
        $this->shopify->setShop($rs->shop);
        $rs->started = 1;
        $rs->save();

        $next = false;
        while($res = $this->executePortion($next)){
            $next = $res;
        }
    }

    private function executePortion($next = false){
        $params = [
            "limit" => 100,
            "fields" => "id,title,tags,product_type"
        ];
        if($next){
            $params["page_info"] = $next;
        }
        $response = $this->shopify->request("/admin/api/2020-01/products.json", $params);
        if(empty($response["products"])){
            return false;
        }
        foreach($response["products"] as $product){
            if(!$rsProduct = Products::find()->where(["product_id" => $product["id"]])->one()){
                $rsProduct = new Products();
            }

            $rsProduct->product_id = $product["id"];
            $rsProduct->title = $product["title"];
            $rsProduct->type = $product["type"];
            $rsProduct->save();

            if(strlen($product["tags"]) > 0){
                $arTags = explode(",", $product["tags"]);
                foreach($arTags as $tag){
                    $tag = trim(htmlspecialchars($tag));
                    if(!$rsTag = Tags::find()->where(["product_id" => $product["id"], "value" => $tag])->one()){
                        $rsTag = new Tags();
                    }
                    $rsTag->product_id = $product["id"];
                    $rsTag->value = $tag;
                    $rsTag->save();
                }
            }
        }

        return $this->shopify->curl->getNextHash();
    }
}