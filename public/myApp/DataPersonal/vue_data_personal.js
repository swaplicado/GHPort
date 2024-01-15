var app = new Vue({
    el: '#allDataPersonal',
    data: {
        DataUser:oServerData.users,
    },
    mounted(){
        console.log(this.DataUser);
    },
    methods: {
        download(User_id, isSalary){
            SGui.showWaiting(5000);
            console.log(oServerData);
            console.log(User_id);
            let id_botton = document.getElementById(User_id);
            id_botton.disabled = true;
            axios.post(oServerData.rute_get_work_personal,{
                'employee_id': User_id, 'isSalary':isSalary,
                
            })
            .then(result => {
                let data = result.data;
                if(data.success){
                    var pdfWindow = window.open("");

                    pdfWindow.document.write("<iframe width='100%' height='100%' src='data:application/pdf;base64," 
                    + encodeURI(data.pdf) + "'></iframe>");

                    // this.initDatePicker();
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
                id_botton.disabled = false;
            })
            .catch(function(error){
                console.log(error);
                SGui.showError(error);
                id_botton.disabled = false;

            });
        }
    },
})