<?php

namespace api\modules\filsh\controllers;

use api\common\models\UserLogin;
use api\models\User;
use filsh\yii2\oauth2server\controllers\DefaultController as BaseDefaultController;
use filsh\yii2\oauth2server\models\OauthAccessTokens;

class DefaultController extends BaseDefaultController
{
    use \api\components\traits\ControllersCommonTrait;

    public function accessRules()
    {
        return [
            [
                'allow'		=> true,
                'actions'	=> ['login'],
                'roles' 	=> ['?'],
            ],
            [
                'allow' => true,
                'actions' => [
                    'token',
                    'logout'
                ],

                'roles' => ['@', User::ROLE_USER], //'@' with User role must go together, otherwise the token with default scope will be accepted for the corresponding role
            ],
            [
                'allow' => true,
                'actions' => [

                ],
                'roles' => [User::ROLE_ADMIN], //'@' with User role must go together, otherwise the token with default scope will be accepted for the corresponding role
            ],

        ];
    }


//    public function actionToken()
//    {
//        //echo 'Newman! Extended';exit;
//        return parent::actionToken();
//    }

    public function actionLogin()
    {
        /* @var $server \OAuth2\Server */
        $server = $this->module->getServer();
        /* @var $request \OAuth2\Request */
        $request = $this->module->getRequest();
        /* @var $response \OAuth2\Response */
        $response = $server->handleTokenRequest($request);
        $this->logUserLogin($request, $response);

        return $response->getParameters();
    }

    private function logUserLogin(\OAuth2\Request $request, \OAuth2\Response $response)
    {
        if($response->getStatusCode() == 200 && $request->request('grant_type') == 'password') {
            // add record to users_login table:
            $login = new UserLogin();

            $user  = User::findByEmail($request->request('username'));
            $attributes = array(
                'uid' => $user->id,
                'client_id' => $request->request('client_id')
            );

            $login->setAttribute('uid', $user->id);
            $login->setAttribute('client_id', $request->request('client_id'));
            $login->save();
        }
    }

    public function actionLogout()
    {
        /* @var $server \OAuth2\Server */
        $server = $this->module->getServer();

        /* @var $request \OAuth2\Request */
        $request = $this->module->getRequest();
        $tokenData = $server->getAccessTokenData($request);
        /* @var $model OauthAccessTokens */
        $model = OauthAccessTokens::findOne($tokenData['access_token']);
        $response = \Yii::$app->getResponse();
        if($model->delete()) {
            $response->setStatusCode(204);
        }

        return $response;
    }

    public function actionToken()
    {
        /* @var $server \OAuth2\Server */
        $server = $this->module->getServer();

        /* @var $request \OAuth2\Request */
        $request = $this->module->getRequest();

        $tokenData = $server->getAccessTokenData($request);
        $response = array(
            'access_token' => isset($tokenData['access_token']) ? $tokenData['access_token']:'',
            'client_id'    => isset($tokenData['client_id']) ? $tokenData['client_id']:'',
            'user_id'      => isset($tokenData['user_id']) ? $tokenData['user_id']:'',
            'scope'        => isset($tokenData['scope']) ? $tokenData['scope']: '',
            'expires'      => isset($tokenData['expires']) ? date('Y-m-d h:i:s A', $tokenData['expires']):'',
        );

        return $response;
    }

}