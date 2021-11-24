'use strict';

var InvestServices = {
  getAllAssets: (successCallback, errorCallback) => {
    const pageUrl = `${REST_SERVER_PATH}invest/assets/`;
    $.ajax({
      async: true,
      type: 'GET',
      dataType: 'json',
      cache: false,
      headers: {
        authusername: Cookies.get('username'),
        sessionkey: Cookies.get('sessionkey'),
      },
      data: {},
      url: pageUrl,
      success: (res) => {
        if (successCallback) successCallback(res);
      },
      error: (err) => {
        if (errorCallback) errorCallback(err);
      }
    });
  },
  addAsset: (name, ticker, type, broker, successCallback, errorCallback) => {
    const pageUrl = `${REST_SERVER_PATH}invest/assets/`;
    $.ajax({
      async: true,
      type: 'POST',
      dataType: 'json',
      cache: false,
      headers: {
        authusername: Cookies.get('username'),
        sessionkey: Cookies.get('sessionkey'),
      },
      data: {
        name,
        ticker,
        type,
        broker,
      },
      url: pageUrl,
      success: (res) => {
        if (successCallback) successCallback(res);
      },
      error: (err) => {
        if (errorCallback) errorCallback(err);
      }
    });
  },
  deleteAsset: (assetId, successCallback, errorCallback) => {
    const pageUrl = `${REST_SERVER_PATH}invest/assets/${assetId}`;
    $.ajax({
      async: true,
      type: 'DELETE',
      dataType: 'json',
      cache: false,
      headers: {
        authusername: Cookies.get('username'),
        sessionkey: Cookies.get('sessionkey'),
      },
      data: {},
      url: pageUrl,
      success: (res) => {
        if (successCallback) successCallback(res);
      },
      error: (err) => {
        if (errorCallback) errorCallback(err);
      }
    });
  },
  editAsset: (assetId, ticker, name, type, broker, successCallback, errorCallback) => {
    const pageUrl = `${REST_SERVER_PATH}invest/assets/${assetId}`;
    $.ajax({
      async: true,
      type: 'PUT',
      dataType: 'json',
      cache: false,
      headers: {
        authusername: Cookies.get('username'),
        sessionkey: Cookies.get('sessionkey'),
      },
      data: {ticker, name, type, broker},
      url: pageUrl,
      success: (res) => {
        if (successCallback) successCallback(res);
      },
      error: (err) => {
        if (errorCallback) errorCallback(err);
      }
    });
  },
};

//# sourceURL=js/services/investServices.js