<?php
/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

require_once(dirname(__DIR__) . "/helpers/auth.php");
require_once(ROOT_DIR . "/helpers/lang.php");
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/utils.php");

$auth = new uAuth();
$config = uConfig::getInstance();

$action = uUtils::postString('action');
$login = uUtils::postString('login');
$pass = uUtils::postPass('pass');
$admin = uUtils::postBool('admin', false);

$lang = (new uLang($config))->getStrings();

if (($auth->user && $auth->user->login === $login) || !$auth->isAdmin()) {
  uUtils::exitWithError($lang["notauthorized"]);
}

if (empty($action) || empty($login)) {
  uUtils::exitWithError($lang["servererror"]);
}


$aUser = new uUser($login);
$data = null;

switch ($action) {
  case 'add':
    if ($aUser->isValid) {
      uUtils::exitWithError($lang["userexists"]);
    }
    if (empty($pass) || !$config->validPassStrength($pass) || ($userId = uUser::add($login, $pass, $admin)) === false) {
      uUtils::exitWithError($lang["servererror"]);
    } else {
      $data = [ 'id' => $userId ];
    }
    break;

  case 'update':
    if ($aUser->setAdmin($admin) === false) {
      uUtils::exitWithError($lang["servererror"]);
    }
    if (!empty($pass) && (!$config->validPassStrength($pass) || $aUser->setPass($pass) === false)) {
      uUtils::exitWithError($lang["servererror"]);
    }
    break;

  case 'delete':
    if ($aUser->delete() === false) {
      uUtils::exitWithError($lang["servererror"]);
    }
    break;

  default:
    uUtils::exitWithError($lang["servererror"]);
    break;
}

uUtils::exitWithSuccess($data);

?>
