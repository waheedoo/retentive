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
use api\models\User as User;

class UserController extends \api\components\ActiveController
{
    /* @var $modelClass \api\models\User */
    public $modelClass = '\api\models\User';
    public $viewAction = 'view';

    /*overriding the default actionCreate*/
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view']);
        unset($actions['update']);
        return $actions;
    }

    public function accessRules()
    {
        return [
            [
                'allow'		=> true,
                'actions'	=> ['anonymous', 'register', 'forgotpassword', 'resetpassword'],
                'roles' 	=> ['?'],
            ],
            [
                'allow' => true,
                'actions' => [
                    'view',
                    'create',
                    'delete',
                    'newman',
                    'update',
                ],
                'roles' => ['@', User::ROLE_USER], //'@' with User role must go together, otherwise the token with default scope will be accepted for the corresponding role
            ],
            [
                'allow' => true,
                'actions' => ['custom'],
                'roles' => ['@', User::ROLE_USER],
                'scopes' => ['custom'],
            ],
            [
                'allow' => true,
                'actions' => ['protected'],
                'roles' => ['@', User::ROLE_USER],
                'scopes' => ['protected'],
            ],
            [
                'allow' => true,
                'actions' => ['administrator'],
                'roles' => [User::ROLE_ADMIN],
            ],
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


    public function actionView($id)
    {
        //access Identity object of the current user.
        $user = null;

        if(Yii::$app->user->identity && ($id == 'me' OR Yii::$app->user->identity->getId() == $id) ) {
            $user = Yii::$app->user->identity;
        } else {
            /* @var $modelClass \api\models\User */
            $user =  User::findIdentity((int) $id);
        }

        $response = Yii::$app->getResponse();
        if($user instanceof User) {
            $response->setStatusCode(200);
            $response->data = [
                'id' => $user->getId(),
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ];
        } else {
            $response->setStatusCode(404);
            $response->data = [
                                'name'=> 'Not Found!',
                                'message' => '',
                                'status'  => '404',
                              ];
        }

        return $response;
    }

    public function actionUpdate()
    {
        $user = Yii::$app->user->identity;
        $response = Yii::$app->getResponse();
        //@todo
        //get the new values to be updated, and then save the changes, and return the new data
        return ['test' => 1];
    }

    public function actionForgotpassword()
    {
        $response = Yii::$app->getResponse();
        $request = Yii::$app->request;
        if($request->post('email')) {
            $email = $request->post('email');
            $user = User::findByEmail($email);
            if($user instanceof User) {
                $user->generatePasswordResetToken();
                $link = $url = Url::toRoute(['v1/users/resetPassword', 'resetToken' => $user->password_reset_token]);
                Yii::$app->mailer->compose()
                    ->setFrom('noreply@retentive.app')
                    ->setTo($user->email)
                    ->setSubject(Yii::t('api', 'Reset Password'))
                    ->setTextBody('please click on the link to reset your password: '.$link)
                    ->setHtmlBody('<b>Hello!</b><br> please click on the link to reset your password: '.$link)
                    ->send();
                $response->setStatusCode(200);
                $response->data = ['message' => Yii::t('api', 'Reset password link sent!')];
            }
            else {
                $response->setStatusCode(404);
                $response->data = ['message' => Yii::t('api', "Email doesn't exist")];
            }
        } else {
            $response->setStatusCode(400);
            $response->data = ['message' => Yii::t('api', 'Missing parameters')];
        }

        return $response;
    }

    public function actionResetpassword()
    {
        $response = Yii::$app->getResponse();
        $request = Yii::$app->request;
        $resetToken = $request->get('resetToken');
        $password = $request->post('password');
        $passwordRepeat = $request->post('password_repeat');
        if($resetToken && $password && $passwordRepeat) {
            $user = User::findByPasswordResetToken($resetToken);
            if($user instanceof User) {
                $user->password = $password;
                $user->password_repeat = $passwordRepeat;
                $user->scenario = 'reset';
                if($user->save()) {
                    $user->removePasswordResetToken();
                    $response->setStatusCode(200);
                    $response->data = ['message' => Yii::t('api', 'password has been reset successfully!')];
                } else {
                    $response->setStatusCode(400);
                    $response->data = ['errors' => $user->getErrors()];
                }
            } else {
                $response->setStatusCode(400);
                $response->data = ['message' => Yii::t('api', 'Invalid link!')];
            }
        } else {
            $response->setStatusCode(400);
            $response->data = ['message' => Yii::t('api', 'Missing parameters')];
        }
    }
}