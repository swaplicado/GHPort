var app = new Vue({
    el: '#orgchart',
    data: {
        oData: oServerData,
        name: null,
        area: null,
        jobs: null,
        img: null,
    },
    mounted(){

    },
    methods: {
        showModal(id, name, area, jobs){
            this.name = name;
            this.area = area;
            this.jobs = jobs;
            this.img = document.getElementById('img_'+id).src
            $('#modal_OrgChart').modal('show');
        }
    },
})