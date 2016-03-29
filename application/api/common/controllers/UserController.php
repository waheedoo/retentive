<?php
/**
 * Controller for managing Users
 * Created by PhpStorm.
 * User: waheed
 * Date: 27/03/16
 * Time: 01:40 ص
 */

namespace api\common\controllers;
use \Yii as Yii;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

class UserController extends \api\components\ActiveController
{
    public $modelClass = '\api\models\User';
    public $viewAction = 'view';

    public function accessRules()
    {
        return [
            [
                'allow' => true,
                'roles' => ['?'],
            ],
            [
                'allow' => true,
                'actions' => [
                    'view',
                    'create',
                    'update',
                    'delete'
                ],
                'roles' => ['@'],
            ]
        ];
    }

    public function actionNewman()
    {
        return ['status' => 'ok'];
    }

    public function actionRegister()
    {
        /* @var $model \api\models\User */
        $model = new $this->modelClass([
            'scenario' => 'register',
        ]);

        $response = Yii::$app->getResponse();
        $model->setAttributes(Yii::$app->getRequest()->getBodyParams());

        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $response->data = ['id' => $model->getId(), 'email' => $model->email];
        } elseif ($model->hasErrors()) {
            $response->setStatusCode(400);
            $response->data = ['errors' => $model->getErrors()];
        }else {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $response;
    }

}