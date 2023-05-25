var appPrinciaplNotifications = new Vue({
    el: '#navNotifications',
    data:  {
        lNotifications: oGlobalDataNotification.lNotifications,
        showNotificationAlert: false
    },
    mounted(){
        var self = this;
        this.test();
    },
    methods: {
        sleep(milliseconds) {
            return new Promise((resolve) => setTimeout(resolve, milliseconds));
        },

        async test(){
            while(true){
                this.getlNotifications();
                await this.sleep(60000);
            }
        },

        getlNotifications(){
            axios.get(self.oGlobalDataNotification.notifications_getNotificationsRoute, {

            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lNotifications = data.lNotifications;
                    this.showNotificationAlert = data.showNotificationAlert;
                }
            })
            .catch( function(error){
                console.log(error);
            });
        },

        cleanNumberOfNotifications(){
            this.showNotificationAlert = false;
            axios.post(self.oGlobalDataNotification.notifications_cleanPendetNotificationRoute, {
                'lNotifications': this.lNotifications,
            })
            .then( result => {

            })
            .catch( function(error){

            });
        },

        revisedNotification(url, oNotify){
            // axios.post(self.oGlobalDataNotification.notifications_revisedNotificationRoute, {
            //     'oNotify': oNotify,
            // })
            // .then( result => {

            // })
            // .catch( function(error){
                
            // });
            window.location.href = url;
        }
    }
});