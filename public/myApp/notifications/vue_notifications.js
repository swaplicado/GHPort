var appPrinciaplNotifications = new Vue({
    el: '#navNotifications',
    data:  {
        numberOfNotifications: oGlobalDataNotification.numberOfNotifications,
        lNotifications: oGlobalDataNotification.lNotifications,
    },
    mounted(){

    },
    methods: {
        cleanNumberOfNotifications(){
            this.numberOfNotifications = null;
            axios.post(oGlobalDataNotification.notifications_cleanRoute, {

            })
            .then( result => {

            })
            .catch(function(e){
                console.log(e);
            })
        }
    }
});