'use strict';

var InvestmentAssetsTableFunc = {
  renderAssetsTable: (assets, containerId, editAssetCallback, removeAssetCallback) => {
    $(containerId)
      .html(`
      <table id="assets-table" class="display browser-defaults" style="width:100%">
        <thead>
            <tr>
                <th>Ticker</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Broker</th>
                <th>Unidades</th>
                <th>Valor Investido</th>
                <th>Valor Atual</th>
                <th>ROI Atual</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            ${assets.map(asset => InvestmentAssetsTableFunc.renderAssetsRow(asset, editAssetCallback, removeAssetCallback))
        .join('')}
        </tbody>
      </table>
    `);
  },
  renderAssetsRow: (asset, editAssetCallback, removeAssetCallback) => {
    return `
      <tr data-id='${asset.asset_id}'>
        <td>${asset.ticker ? asset.ticker : '-'}</td>
        <td>${asset.name}</td>
        <td>${StringUtils.getInvestingAssetObjectById(asset.type).name}</td>
        <td>${asset.broker ? asset.broker : '-'}</td>
        <td>${asset.units}</td>
        <td>${StringUtils.formatStringToCurrency(asset.invested_value)}</td>
        <td>${StringUtils.formatStringToCurrency(asset.current_value)}</td>
        <td>${StringUtils.formatStringToCurrency(asset.absolute_roi_value)} ${InvestmentAssetsTableFunc.buildRoiPercentage(asset.relative_roi_percentage)}</td>
        <td>
            <i onClick="Investments.${editAssetCallback.name}(${asset.asset_id}, '${asset.ticker ? asset.ticker : ''}', '${asset.name}', '${asset.type}', '${asset.broker ? asset.broker : ''}')" class="material-icons table-action-icons">create</i>
            <i onClick="Investments.${removeAssetCallback.name}(${asset.asset_id})" class="material-icons table-action-icons" style="margin-left:10px">delete</i>
        </td>
      </tr>
    `;
  },
  buildRoiPercentage: (percentage) => {
    let strToReturn = '';

    if (percentage > 0) {
      strToReturn = `<span class='green-text text-accent-4' style="font-size: small;">+${StringUtils.formatStringToPercentage(percentage)}</span>`;
    } else if (percentage < 0) {
      strToReturn = `<span class='pink-text text-accent-1' style="font-size: small;">${StringUtils.formatStringToPercentage(percentage)}</span>`;
    } else {
      strToReturn = `<span class="" style="font-size: small;">${StringUtils.formatStringToPercentage(percentage)}</span>`;
    }

    return `(${strToReturn})`;
  },

};

//# sourceURL=js/funcs/investmentAssetsTableFunc.js