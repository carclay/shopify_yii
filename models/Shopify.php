<?

namespace app\models;

use Yii;

class Shopify
{
    const SITE = "https://carclay.site/";
    const API_KEY = "bfd35002987481f02faf1141d0ea22dc";
    const SECRET = "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";
    const TOKEN_URI = "/admin/oauth/access_token";
    const APP_CODE = "product-view-checker";

    public $token;
    public $shop;

    private $scopes = [
        "read_products",
        "read_script_tags",
        "write_script_tags"
    ];

    private $request;
    public $curl;

    /**
     * Shopify constructor.
     */
    public function __construct()
    {
        if(php_sapi_name() !== 'cli'){
            $this->request = Yii::$app->request;
            $this->session = Yii::$app->session;

            if(!$shop = !empty($this->request->get('shop')) ? $this->request->get('shop') : $this->session->get('shop')){
                throw new \Exception('shop is undefined');
            }
            $this->setShop($shop);
            $this->getToken();
        }

        $this->curl = new Curl();
    }

    /**
     * @param $shop
     * @return $this
     */
    public function setShop($shop){
        $this->shop = $shop;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstallUrl()
    {
        $redirect_endpoint = "token/";
        return "https://" . $this->shop . "/admin/oauth/authorize?client_id=" . static::API_KEY . "&scope=" . implode(",", $this->scopes) . "&redirect_uri=" . static::SITE . urlencode($redirect_endpoint);
    }

    /**
     * @param $endpoint
     * @param array $query
     * @param string $method
     * @param array $arHeaders
     * @return mixed
     */
    public function request($endpoint, $query = [], $method = "GET", $arHeaders = [])
    {

        $url = "https://" . $this->shop . $endpoint;
        if (!is_null($query) && in_array($method, ["GET", "DELETE"])) {
            $url .= "?" . http_build_query($query);
        }

        $this->curl->setUrl($url);
        $this->curl->setopt(CURLOPT_CUSTOMREQUEST, $method);
        $this->getToken();
        if(!is_null($this->token)){
            $arHeaders[] = "X-Shopify-Access-Token: ".$this->token;
        }

        $this->curl->setHeaders($arHeaders);

        if($method !== 'GET' && in_array($method, ["PUT", "POST"])){
            if(is_array($query)){
                $query = http_build_query($query);
            }
            $this->curl->setopt(CURLOPT_POSTFIELDS, $query);
        }

        try{
            $response = $this->curl->request();
        }catch (\Exception $e){
            dump($e->getMessage());
        }

        return json_decode($response, true);
    }

    /**
     * @return mixed
     */
    public function getToken(){
        if(is_null($this->token)){
            $this->token = Tokens::find()->where(["shop_name" => $this->shop])->one()["token"];
        }
        return $this->token;
    }

    /**
     * @param $token
     * @return bool
     */
    public function saveToken($token){
        if(!$rsToken = Tokens::find()->where([
            "shop_name" => htmlspecialchars($_REQUEST["shop"])
        ])->one()){
            $rsToken = new Tokens();
        }
        $rsToken->shop_name = htmlspecialchars($_REQUEST["shop"]);
        $rsToken->token = $token;

        // save company

        if(!$rsShop = Shops::find()->where([
            "shop" => htmlspecialchars($_REQUEST["shop"])
        ])->one()){
            $rsShop = new Shops();
        }
        $rsShop->shop = htmlspecialchars($_REQUEST["shop"]);
        $rsShop->save();
        return $rsToken->save();
    }

    public function goToShop(){
        \Yii::$app->response->redirect("https://".htmlspecialchars($_REQUEST["shop"])."/admin/apps/".static::APP_CODE, 301)->send();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function generateToken()
    {
        $params = $_GET;
        $hmac = $params["hmac"];
        unset($params["hmac"]);
        unset($params["PHPSESSID"]);
        unset($params["_csrf"]);

        $computed_hmac = hash_hmac('sha256', http_build_query($params), static::SECRET);

        if (!hash_equals($hmac, $computed_hmac)) {
            throw new \Exception("hash not equal");
        }

        $url = "https://" . $params["shop"] . static::TOKEN_URI;
        $query = [
            "client_id" => static::API_KEY,
            "client_secret" => static::SECRET,
            "code" => $params["code"]
        ];
        $this->curl->setUrl($url);
        $this->curl->setopt(CURLOPT_POST, true);
        $this->curl->setopt(CURLOPT_POSTFIELDS, $query);

        $response = json_decode($this->curl->request(), true);

        if (!isset($response["access_token"]) && empty($response["access_token"])) {
            throw new \Exception("token was not received");
        }

        return $response["access_token"];
    }
}