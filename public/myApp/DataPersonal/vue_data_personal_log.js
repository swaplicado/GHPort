var app = new Vue({
    el: '#allDataPersonalLog',
    data: {
        DataUser:oServerData.users, startDate:oServerData.startDate, endDate:oServerData.endDate,
        oDateUtils: new SDateUtils(),
    },
    mounted(){
        console.log(this.DataUser);
    },
    methods: {
       
        
    },
})