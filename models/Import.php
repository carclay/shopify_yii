<?php


namespace app\models;


use phpDocumentor\Reflection\DocBlock\Tag;
use Yii;

class Import
{
    private $shopify;
    private $shop;
    private $localShopId = false;
    public $request;

    /**
     * Import constructor.
     * @param bool $shop
     * @throws \Exception
     */
    public function __construct($shop = false)
    {
        $this->request = Yii::$app->request;
        $this->shop = $shop;
        $this->shopify = new Shopify();
        $this->shopify->setShop($this->shop);
    }

    /**
     *
     */
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

    /**
     * @return bool
     */
    public function isRunning(){
        return !empty(CronTask::find()->where(['status' => 1])->one());
    }

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function run(){
        if(!$rs = CronTask::find()->where(["started" => 0])->one()){
            return false;
        }
        $this->shopify->setShop($rs->shop);
        $this->localShopId = Shops::find()->where(['shop' => $rs->shop])->one()->id;
        $rs->started = 1;
        $rs->save();

        $next = false;
        while($res = $this->executePortion($next, $rs)){
            $next = $res;
            sleep(1); // задержка выполнения для демонстрации прогресс бара при импорте
//            usleep(5000);
        }

        $rs->delete();
    }

    /**
     * @param bool $next
     * @param bool $process
     * @return bool|mixed
     */
    private function executePortion($next = false, $process = false){
        $params = [
            "limit" => 1,
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
            $rsProduct->type = $product["product_type"];
            $rsProduct->shop_id = $this->localShopId;
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
            fwrite(STDOUT, $product["id"]."\n");
            $process->last_id = $product["id"];
            $process->processed += 1;
            $process->save();
        }

        return $this->shopify->curl->getNextHash();
    }

    /**
     * @return array
     */
    public function getStateAjax(){

        if(!$rs = CronTask::find()->where(["shop" => htmlspecialchars($this->shop)])->one()){
            return ["done" => true];
        }
        $total = intval($_REQUEST["total"]);
        $percent = intval($rs->processed) / $total * 100;
        return ["processed" => $rs->processed, "total" => $_REQUEST["total"], 'percent' => $percent];
    }
}