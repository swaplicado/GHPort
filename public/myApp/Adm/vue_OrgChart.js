var app = new Vue({
    el: '#orgchart',
    data: {
        oData: oServerData,
        name: null,
        area: null,
        jobs: null,
        img: null,
        users: [],
        orgChart_id: null,
    },
    mounted(){

    },
    methods: {
        showModal(id, name, area, jobs, countUsers){
            this.users = [];
            this.orgChart_id = id;
            if(countUsers > 1){
                this.getlUsers();
            }
            this.name = name;
            this.area = area;
            this.jobs = jobs;
            this.img = document.getElementById('img_'+id).src;
            $('#modal_OrgChart').modal('show');
        },

        getlUsers(){
            SGui.showWaiting(10000);
            axios.post(this.oData.getUsersRoute, {
                'orgChart_id': this.orgChart_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.users = data.lUser;
                    this.$forceUpdate();
                    swal.close();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            })
        }
    },
})