<?php
/**
 * Created by PhpStorm.
 * @author Ihor Karas <ihor@karas.in.ua>
 */

namespace api\components;

use api\models\User;

class AccessRule extends \yii\filters\AccessRule
{
	/** @var array list of scopes, used for setting scope for controller */
	public $scopes=[];

	/**
	 * @inheritdoc
	 */
	protected function matchRole($user)
	{
		/* @var $model \api\models\User */

		if (empty($this->roles)) {
			return true;
		}

		foreach ($this->roles as $role) {
			if ($role == '?') {
				if ($user->getIsGuest()) {
					return true;
				}
			} elseif ($role == User::ROLE_USER) {
				if (!$user->getIsGuest()) {
					return true;
				}
				// Check if the user is logged in, and the roles match
			} elseif (!$user->getIsGuest() && $role == $user->identity->role) {
				return true;
			}
		}

		return false;
	}

}