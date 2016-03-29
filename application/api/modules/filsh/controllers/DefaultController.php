<?php

namespace api\modules\filsh\controllers;

use filsh\yii2\oauth2server\controllers\DefaultController as BaseDefaultController;

class DefaultController extends BaseDefaultController
{

    
    public function actionToken()
    {
        //echo 'Newman! Extended';exit;
        return parent::actionToken();
    }
}