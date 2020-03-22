<?

use \app\models\Shopify;
use \app\models\Import;
use yii\bootstrap\Progress;

$session = Yii::$app->session;
$shop = $session->get('shop');
$shopify = new Shopify();
$import = new Import($shop);

if (Yii::$app->request->get('start_import') == 'Y') {
    $import->setTask();
}
if (!$import->isRunning()):

    /**
     * @todo докидать больше товаров. хотя бы штук 20
     * сделать прогресс
     * сохранение просмотров
     * поиск по товарам и показ статистики
     */
    ?>

    <form action="/import/">
        <input type="hidden" name="shop" value="<?= $shop ?>">
        <button type="submit" class="btn btn-primary" name="start_import" value="Y">Start product import</button>
    </form>
<? else:?>
    <?
    $response = $shopify->request("/admin/api/2020-01/products/count.json");
    ?>
    <h3>В течение ближайшей минуты импорт начнет выполняться</h3>
    <div id="w0" class="progress" style="max-width: 400px;" onclick="updateState()">
        <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"
             style="width: 0;">0 of <?= $response["count"] ?></div>
    </div>
    <script>
        function updateState() {
            $.ajax({
                url: '/ajax/',
                type: 'post',
                data: {shop: '<?=$shop?>', total: '<?=$response["count"]?>'},
                dataType: 'json',
                success: function (resp) {
                    let bar = document.querySelectorAll('.progress-bar')[0];
                    if (resp.done) {
                        bar.innerHTML = '<?=$response["count"]?>' + ' of ' + '<?=$response["count"]?>';
                        bar.style.width = '100%';
                        document.location.href = '/import/';
                    } else {
                        if(bar){
                            bar.innerHTML = resp.processed + ' of ' + resp.total;
                            bar.style.width = resp.percent + '%';
                        }
                        setTimeout(function(){
                            updateState();
                        }, 1000);
                    }
                }
            });
        }
        document.addEventListener("DOMContentLoaded", () => {
            updateState();
        });
    </script>
<? endif; ?>
