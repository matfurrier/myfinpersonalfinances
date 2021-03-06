<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once 'consts.php';

class Accounts
{
    const DEBUG_MODE = false; // USE ONLY WHEN DEBUGGING THIS SPECIFIC CONTROLLER (this skips sessionkey validation)

    public static function getAllAccountsForUser(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int)Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN, 3);
            } else {
                $mobile = false;
            }

            /* Auth - token validation */
            if (!self::DEBUG_MODE) {
                AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);
            }

            /* Execute Operations */
            $db = new EnsoDB(true);
            $db->getDB()->beginTransaction();


            $userID = UserModel::getUserIdByName($authusername, true);

            $accsArr = AccountModel::getAllAccountsForUserWithAmounts($userID, false, true);

            $db->getDB()->commit();

            return sendResponse($response, EnsoShared::$REST_OK, $accsArr);
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }

    public static function addAccount(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int)Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN, 2);
            } else {
                $mobile = false;
            }

            $name = Input::validate($request->getParsedBody()['name'], Input::$STRING, 3);
            $type = Input::validate($request->getParsedBody()['type'], Input::$STRING, 4);
            $description = Input::validate($request->getParsedBody()['description'], Input::$STRING, 5);
            $status = Input::validate($request->getParsedBody()['status'], Input::$STRING, 6);
            $excludeFromBudgets = (int)Input::validate($request->getParsedBody()['exclude_from_budgets'], Input::$BOOLEAN, 7);
            $currentBalance = Input::convertFloatToIntegerAmount(Input::validate($request->getParsedBody()['current_balance'], Input::$FLOAT, 8));
            $colorGradient = Input::validate($request->getParsedBody()["color_gradient"], Input::$STRICT_STRING, 9);

            if (
                $type !== "CHEAC" && $type !== "SAVAC"
                && $type !== "INVAC" && $type !== "CREAC"
                && $type !== "OTHAC"
                && $type !== "WALLET"
                && $type !== "MEALAC"
            ) {
                throw new BadValidationTypeException("Account type not valid!");
            }

            /* Auth - token validation */ {
                if (!self::DEBUG_MODE) {
                    AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);
                }
            }

            /* Execute Operations */
            $db = new EnsoDB(true);
            $db->getDB()->beginTransaction();


            $userID = UserModel::getUserIdByName($authusername, true);

            $accountID = AccountModel::insert([
                "name" => $name,
                "type" => $type,
                "description" => $description,
                "exclude_from_budgets" => $excludeFromBudgets,
                "status" => $status,
                "users_user_id" => $userID,
                "current_balance" => $currentBalance,
                "created_timestamp" => time(),
                "color_gradient" => $colorGradient,
            ], true);


            $currentMonth = date("n");
            $currentYear = date("Y");


            AccountModel::addBalanceSnapshot($accountID, $currentMonth, $currentYear, true);
            if ($currentMonth < 12) {
                $currentMonth2 = $currentMonth + 1;
                $currentYear2 = $currentYear;
            } else {
                $currentYear2 = $currentYear + 1;
                $currentMonth2 = 1;
            }
            AccountModel::addBalanceSnapshot($accountID, $currentMonth2, $currentYear2, true);
            $db->getDB()->commit();

            return sendResponse($response, EnsoShared::$REST_OK, "New account added!");
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (BadValidationTypeException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->__toString());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }

    public static function removeAccount(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int)Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN, 2);
            } else {
                $mobile = false;
            }

            $accountID = Input::validate($request->getParsedBody()['account_id'], Input::$INT, 3);

            /* Auth - token validation */
            if (!self::DEBUG_MODE) {
                AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);
            }

            /* Execute Operations */
            $db = new EnsoDB(true);
            $db->getDB()->beginTransaction();

            $userID = UserModel::getUserIdByName($authusername, true);

            TransactionModel::delete([
                "accounts_account_from_id" => $accountID
            ]);

            TransactionModel::delete([
                "accounts_account_to_id" => $accountID
            ]);

            AccountModel::removeBalanceSnapshotsForAccount($accountID, true);

            AccountModel::delete([
                "account_id" => $accountID,
                "users_user_id" => $userID,
            ], true);

            $db->getDB()->commit();

            return sendResponse($response, EnsoShared::$REST_OK, "Account Removed!");
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }

    public static function editAccount(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int)Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN, 2);
            } else {
                $mobile = false;
            }

            $accountID = Input::validate($request->getParsedBody()['account_id'], Input::$INT, 3);
            $newName = Input::validate($request->getParsedBody()['new_name'], Input::$STRING, 4);
            $newType = Input::validate($request->getParsedBody()['new_type'], Input::$STRING, 5);
            $newDescription = Input::validate($request->getParsedBody()['new_description'], Input::$STRING, 6);
            $newStatus = Input::validate($request->getParsedBody()['new_status'], Input::$STRING, 7);
            $excludeFromBudgets = (int)Input::validate($request->getParsedBody()['exclude_from_budgets'], Input::$BOOLEAN, 8);
            $currentBalance = Input::convertFloatToIntegerAmount(Input::validate($request->getParsedBody()['current_balance'], Input::$FLOAT, 9));
            $colorGradient = Input::validate($request->getParsedBody()["color_gradient"], Input::$STRICT_STRING, 10);

            if (
                $newType !== "CHEAC" && $newType !== "SAVAC"
                && $newType !== "INVAC" && $newType !== "CREAC"
                && $newType !== "OTHAC"
                && $newType !== "WALLET"
                && $newType !== "MEALAC"
            ) {
                throw new BadValidationTypeException("New account type not valid!");
            }

            /* Auth - token validation */
            if (!self::DEBUG_MODE) {
                AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);
            }

            /* Execute Operations */
            $db = new EnsoDB(true);
            $db->getDB()->beginTransaction();

            $userID = UserModel::getUserIdByName($authusername, false);

            AccountModel::editWhere(
                [
                    "account_id" => $accountID,
                    "users_user_id" => $userID,
                ],
                [
                    "name" => $newName,
                    "type" => $newType,
                    "description" => $newDescription,
                    "exclude_from_budgets" => $excludeFromBudgets,
                    "status" => $newStatus,
                    "current_balance" => $currentBalance,
                    "updated_timestamp" => time(),
                    "color_gradient" => $colorGradient,
                ],
                true
            );

            $currentMonth = date("n");
            $currentYear = date("Y");

            AccountModel::addBalanceSnapshot($accountID, $currentMonth, $currentYear, true);
            if ($currentMonth < 12) {
                $currentMonth2 = $currentMonth + 1;
                $currentYear2 = $currentYear;
            } else {
                $currentYear2 = $currentYear + 1;
                $currentMonth2 = 1;
            }
            AccountModel::addBalanceSnapshot($accountID, $currentMonth2, $currentYear2, true);

            if ($currentMonth < 12) {
                $currentMonth3 = $currentMonth2 + 1;
                $currentYear3 = $currentYear2;
            } else {
                $currentYear3 = $currentYear2 + 1;
                $currentMonth3 = 1;
            }
            AccountModel::addBalanceSnapshot($accountID, $currentMonth3, $currentYear3, true);

            $db->getDB()->commit();

            return sendResponse($response, EnsoShared::$REST_OK, "Account Updated!");
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (BadValidationTypeException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->__toString());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }

    public static function getUserAccountsBalanceSnapshot(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int)Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN, 2);
            } else {
                $mobile = false;
            }


            /* Auth - token validation */
            if (!self::DEBUG_MODE) {
                AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);
            }

            /* Execute Operations */
            $db = new EnsoDB(true);
            $db->getDB()->beginTransaction();

            $userID = UserModel::getUserIdByName($authusername, true);
            $userAccountsCount = count(AccountModel::getWhere(["users_user_id" => $userID]));
            if ($userAccountsCount == 0) {
                return sendResponse($response, EnsoShared::$REST_OK, null);
            }
            $outArr = AccountModel::getBalancesSnapshotForUser($userID, true);

            $db->getDB()->commit();
            return sendResponse($response, EnsoShared::$REST_OK, $outArr);
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (BadValidationTypeException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->__toString());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }

    /**
     * Recalculate all user accounts balance
     */
    public static function
    recalculateAllUserAccountsBalances(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int)Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN, 3);
            } else {
                $mobile = false;
            }

            /* Auth - token validation */
            if (!self::DEBUG_MODE) {
                AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);
            }

            $db = new EnsoDB(false);
            $db->getDB()->beginTransaction();

            $userID = UserModel::getUserIdByName($authusername, false);

            $userAccounts = AccountModel::getWhere(["users_user_id" => $userID], ["account_id"]);


            foreach ($userAccounts as $account) {
                AccountModel::setNewAccountBalance($account["account_id"],
                    AccountModel::recalculateBalanceForAccountIncrementally($account["account_id"], 0, time() + 1, false),
                    false);
            }


            $db->getDB()->commit();

            return sendResponse($response, EnsoShared::$REST_OK, "All balances successfully recalculated!");
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }
}

$app->get('/accounts/', 'Accounts::getAllAccountsForUser');
$app->post('/accounts/', 'Accounts::addAccount');
$app->delete('/accounts/', 'Accounts::removeAccount');
$app->put('/accounts/', 'Accounts::editAccount');

$app->get('/accounts/stats/balance-snapshots/', 'Accounts::getUserAccountsBalanceSnapshot');
$app->get('/accounts/recalculate-balance/all', 'Accounts::recalculateAllUserAccountsBalances');
