<?php
/**
 * Controller for manage products
 *
 * @author ihor@karas.in.ua
 * Date: 03.04.15
 * Time: 00:35
 */

namespace api\common\controllers;
use \Yii as Yii;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use api\models\User as User;


class ProductController extends \api\components\ActiveController
{
	public $modelClass = '\api\common\models\Product';
	public $viewAction = 'view';


	public function accessRules()
	{
		return [
			[
				'allow'		=> true,
				'actions'	=> ['view'],
				'roles' 	=> ['?'],
			],
			[
				'allow' => true,
				'actions' => [
					'create',
					'update',
					'delete',
					'newman',
				],
				'roles' => [User::ROLE_USER],
			],
			[
				'allow' => true,
				'actions' => ['custom'],
				'roles' => [User::ROLE_USER],
				'scopes' => ['custom'],
			],
			[
				'allow' => true,
				'actions' => ['protected'],
				'roles' => [User::ROLE_USER],
				'scopes' => ['protected'],
			],
			[
				'allow' => true,
				'actions' => ['administrator'],
				'roles' => [User::ROLE_ADMIN],
			],
		];
	}

	public function actionCustom()
	{
		return ['status' => 'ok', 'underScope' => 'custom'];
	}

	public function actionProtected()
	{
		return ['status' => 'ok', 'underScope' => 'protected'];
	}

	public function actionAdministrator()
	{
		return ['status' => 'ok', 'role' => 'admin'];
	}

	public function actionNewman()
	{
		return ['status' => 'ok', 'underScope' => 'public'];
	}

	/*overriding the default actionCreate*/
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['create']);
		return $actions;
	}

	public function actionCreate()
	{
		/* @var $model \api\common\models\Product */
		$model = new $this->modelClass([
			'scenario' => 'default',
		]);

		$model->setAttributes(Yii::$app->getRequest()->getBodyParams());

		if ($model->save()) {
			$response = Yii::$app->getResponse();
			$response->setStatusCode(201);
			$id = implode(',', array_values($model->getPrimaryKey(true)));
			$response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
		} elseif (!$model->hasErrors()) {
			throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
		}

		return $model;
	}
}