var app = new Vue({
    el: '#curriculumLogsApp',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        lUser: oServerData.lUser,
        lastDateUpdateDP: oServerData.lastDateUpdateDP,
        lastDateUpdateCV: oServerData.lastDateUpdateCV,
    },
    mounted(){
        self = this;
    },
    methods: {
    }
})