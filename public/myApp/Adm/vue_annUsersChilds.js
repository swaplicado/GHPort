var app = new Vue({
    el: '#annUsers',
    data: {
        oData: oServerData,
        lannUsersChilds: oServerData.lannUsersChilds,
        oDateUtils: new SDateUtils(),
    },
})