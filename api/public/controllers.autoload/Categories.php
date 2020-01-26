<?php

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

require_once 'consts.php';

class Categories
{
    const DEBUG_MODE = true; // USE ONLY WHEN DEBUGGING THIS SPECIFIC CONTROLLER (this skips sessionkey validation)

    public static function getAllCategoriesForUser(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int) Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN);
            } else {
                $mobile = false;
            }

            if (array_key_exists('type', $request->getQueryParams())) {
                $type = Input::validate($request->getQueryParams()['type'], Input::$STRICT_STRING);
            }


            /* Auth - token validation */
            if (!self::DEBUG_MODE) AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);

            /* Execute Operations */
            /* $db = new EnsoDB(true);
            
            $db->getDB()->beginTransaction(); */

            /* echo "1";
            die(); */
            $userID = UserModel::getUserIdByName($authusername, false);

            if (isset($type)) {
                $catsArr = CategoryModel::getWhere(
                    ["users_user_id" => $userID, "type" => $type],
                    ["category_id", "name", "type", "description"]

                );
            } else {
                $catsArr = CategoryModel::getWhere(
                    ["users_user_id" => $userID],
                    ["category_id", "name", "type", "description"]

                );
            }


            /* $db->getDB()->commit(); */

            return sendResponse($response, EnsoShared::$REST_OK, $catsArr);
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }

    public static function addCategory(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int) Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN);
            } else {
                $mobile = false;
            }


            $name = Input::validate($request->getParsedBody()['name'], Input::$STRING);
            $description = Input::validate($request->getParsedBody()['description'], Input::$STRING);
            $type = Input::validate($request->getParsedBody()['type'], Input::$STRICT_STRING);


            /* Auth - token validation */
            if (!self::DEBUG_MODE) AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);

            /* Execute Operations */
            /* $db = new EnsoDB(true);
            
            $db->getDB()->beginTransaction(); */

            /* echo "1";
            die(); */
            $userID = UserModel::getUserIdByName($authusername, false);

            CategoryModel::insert([
                "name" => $name,
                "type" => $type,
                "description" => $description,
                "users_user_id" => $userID
            ], false);


            /* $db->getDB()->commit(); */

            return sendResponse($response, EnsoShared::$REST_OK, "New category added!");
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }
}

$app->get('/cats/', 'Categories::getAllCategoriesForUser');
$app->post('/cats/', 'Categories::addCategory');