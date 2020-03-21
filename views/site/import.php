<?
use \app\models\Shopify;
use \app\models\Import;
$session = Yii::$app->session;
$shop = $session->get('shop');
$shopify = new Shopify();
$import = new Import($shop);

if(Yii::$app->request->get('start_import') == 'Y'){
    $import->setTask();
}
if(!$import->isRunning()):

    /**
     * @todo докидать больше товаров. хотя бы штук 20
     * сделать прогресс
     * сохранение просмотров
     * поиск по товарам и показ статистики
     */
?>

<form action="/import/">
    <input type="hidden" name="shop" value="<?=$shop?>">
    <button type="submit" class="btn btn-primary" name="start_import" value="Y">Start product import</button>
</form>
<?else:?>
    <?
        $response = $shopify->request("/admin/api/2020-01/products/count.json");
        dump($response);
    ?>
<?endif;?>
