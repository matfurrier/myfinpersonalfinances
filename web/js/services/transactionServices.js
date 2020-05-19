"use strict";

var TransactionServices = {
    getAllTransactions: (successCallback, errorCallback) => {
        var pageUrl = REST_SERVER_PATH + "trxs/"
        $.ajax({
            async: true,
            type: "GET",
            dataType: "json",
            cache: false,
            headers: {
                authusername: Cookies.get("username"),
                sessionkey: Cookies.get("sessionkey"),
            },
            data: {
            },
            url: pageUrl,
            success: function (response) {
                if (successCallback) successCallback(response)
            },
            error: function (response) {
                if (errorCallback) errorCallback(response)
            }
        });
    },
    getAddTransactionStep0: (successCallback, errorCallback) => {
        var pageUrl = REST_SERVER_PATH + "trxs/step0"

        $.ajax({
            async: true,
            type: "POST",
            dataType: "json",
            cache: false,
            headers: {
                authusername: Cookies.get("username"),
                sessionkey: Cookies.get("sessionkey")
            },
            data: {},
            url: pageUrl,
            success: function (response) {
                if (successCallback) successCallback(response)
            },
            error: function (response) {
                if (errorCallback) errorCallback(response)
            }
        })
    },
    addTransaction: (amount, type, description, entity_id, account_from_id, account_to_id, category_id, date_timestamp, successCallback, errorCallback) => {
        var pageUrl = REST_SERVER_PATH + "trxs/step1"

        $.ajax({
            async: true,
            type: "POST",
            dataType: "json",
            cache: false,
            headers: {
                authusername: Cookies.get("username"),
                sessionkey: Cookies.get("sessionkey"),
            },
            data: {
                amount,
                type,
                description,
                entity_id,
                account_from_id,
                account_to_id,
                category_id,
                date_timestamp
            },
            url: pageUrl,
            success: function (response) {
                if (successCallback) successCallback(response)
            },
            error: function (response) {
                if (errorCallback) errorCallback(response)
            }
        });
    },
    editTransaction: (trxID, new_amount, new_type, new_description, new_entity_id, new_account_from_id, new_account_to_id, new_category_id, new_date_timestamp, successCallback, errorCallback) => {
        var pageUrl = REST_SERVER_PATH + "trxs/"

        $.ajax({
            async: true,
            type: "PUT",
            dataType: "json",
            cache: false,
            headers: {
                authusername: Cookies.get("username"),
                sessionkey: Cookies.get("sessionkey"),
            },
            data: {
                transaction_id: trxID,
                new_amount,
                new_type,
                new_description,
                new_entity_id,
                new_account_from_id,
                new_account_to_id,
                new_category_id,
                new_date_timestamp
            },
            url: pageUrl,
            success: function (response) {
                if (successCallback) successCallback(response)
            },
            error: function (response) {
                if (errorCallback) errorCallback(response)
            }
        });
    },
    removeTransaction: (trxID, successCallback, errorCallback) => {
        var pageUrl = REST_SERVER_PATH + "trxs/"

        $.ajax({
            async: true,
            type: "DELETE",
            dataType: "json",
            cache: false,
            headers: {
                authusername: Cookies.get("username"),
                sessionkey: Cookies.get("sessionkey"),
            },
            data: {
                transaction_id: trxID,
            },
            url: pageUrl,
            success: function (response) {
                if (successCallback) successCallback(response)
            },
            error: function (response) {
                if (errorCallback) errorCallback(response)
            }
        });
    },
    // TODO
    editEntity: (entID, newName, successCallback, errorCallback) => {
        var pageUrl = REST_SERVER_PATH + "entities/"

        $.ajax({
            async: true,
            type: "PUT",
            dataType: "json",
            cache: false,
            headers: {
                authusername: Cookies.get("username"),
                sessionkey: Cookies.get("sessionkey"),
            },
            data: {
                entity_id: entID,
                new_name: newName,
            },
            url: pageUrl,
            success: function (response) {
                if (successCallback) successCallback(response)
            },
            error: function (response) {
                if (errorCallback) errorCallback(response)
            }
        });
    },

}

//# sourceURL=js/actions/transactionServices.js