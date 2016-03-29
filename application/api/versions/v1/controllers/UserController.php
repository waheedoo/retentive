<?php
/**
 * UserController v1
 * @author Waheed Al Khateeb <waheed.alkhateeb@gmail.com>
 * Date: 03.04.15
 * Time: 00:35
 */

namespace api\versions\v1\controllers;


class UserController extends \api\common\controllers\UserController
{
    public $modelClass = '\api\versions\v1\models\User';

}