var app = new Vue({
    el: '#annAllUsers',
    data: {
        oData: oServerData,
        lannUsersChilds: oServerData.lannUsersChilds,
        oDateUtils: new SDateUtils(),
    },
})