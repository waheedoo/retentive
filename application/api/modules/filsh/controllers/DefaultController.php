<?php

namespace api\modules\filsh\controllers;

use api\common\models\UserLogin;
use api\models\User;
use filsh\yii2\oauth2server\controllers\DefaultController as BaseDefaultController;

class DefaultController extends BaseDefaultController
{

    
    public function actionToken()
    {
        //echo 'Newman! Extended';exit;
        return parent::actionToken();
    }

    public function actionLogin()
    {
        /* @var $server \OAuth2\Server */
        $server = $this->module->getServer();
        /* @var $request \OAuth2\Request */
        $request = $this->module->getRequest();
        $response = $server->handleTokenRequest($request);

        if($response->getStatusCode() == 200) {
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

        return $response->getParameters();
    }

    public function actionLogout()
    {

    }

    public function actionTokenInfo()
    {

    }

}