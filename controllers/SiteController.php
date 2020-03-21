<?php

namespace app\controllers;

use app\models\Shopify;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * @param $action
     * @return bool
     * @throws \Exception
     */
    public function beforeAction($action)
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        if(!$session->get('shop') && !empty($request->get('shop'))){
            $session->set('shop', $request->get('shop'));
        }
        // сначала получаем список скриптов, и если в нем нет нужного докидываем. иначе будут дублиться
        $shopify = new Shopify();
        $script = [
            "script_tag" => [
                "event" => "onload",
                "src" => "https://carclay.site/js/script.js"
            ]
        ];

        $response = $shopify->request("/admin/api/2020-01/script_tags.json");
        $needLoad = true;

        foreach ($response["script_tags"] as $item) {
            if ($item['src'] == $script["script_tag"]["src"]) {
                $needLoad = false;
            }
        }

        if ($needLoad) {
            $shopify->request("/admin/api/2020-01/script_tags.json", $script, "POST");
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     * @throws \Exception
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;

        $hmac = $request->get('hmac');
        $shop = $request->get('shop');
        $locale = $request->get('locale');
        $session = $request->get('session');

        if (strlen($hmac) > 0 && strlen($shop) > 0 && !$locale && !$session) {
            $model = new Shopify();
            return $this->redirect($model->getInstallUrl());
        }
        return $this->render('index');
    }

    /**
     * @throws \Exception
     */
    public function actionToken()
    {
        $model = new Shopify();
        $model->saveToken($model->generateToken());
        $model->goToShop();
    }

    public function actionApi()
    {
        Header('Access-Control-Allow-Origin: *');
        dump($_REQUEST);
    }

    public function actionImport(){
        return $this->render('import');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
