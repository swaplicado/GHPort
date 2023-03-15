var app = new Vue({
    el: '#mailLogs',
    data: {
        oData: oServerData,
        lMails: oServerData.lMails,
        year: oServerData.year,
        indexes: oServerData.indexes,
        constants: oServerData.constants
    },
    mounted(){
        
    },
    methods: {
        sendRegistry(data){
            let message = '¿Desea enviar el e-mail?';
            if(data[this.indexes.sys_mail_st_id] == this.constants.MAIL_ENVIADO){
                message = 'El correo ya ha sido enviado, ¿desea reenviarlo?';
            }
            Swal.fire({
                title: message,
                html: data[this.indexes.Type_mail] + '<br>' + '<b>Fecha:</b> ' +  data[this.indexes.date_mail],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.sendMail(data[this.indexes.id]);
                }
            })
        },

        sendMail(mailLog_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.sendMailRoute, {
                'id_mailLog': mailLog_id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showMessage('', data.message, data.icon);
                    this.reDrawMailsTable(data.lMails);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },

        reDrawMailsTable(data){
            var dataMails = [];
            for(let mail of data){
                dataMails.push(
                    [
                        mail.id_mail_log,
                        mail.sys_mails_st_id,
                        mail.date_log,
                        mail.mail_st_name,
                        mail.mail_tp_name,
                        mail.full_name_ui,
                    ]
                );
            }
            table['table_mails'].clear().draw();
            table['table_mails'].rows.add(dataMails).draw();
        },

        deleteRegistry(data){
            if(data[this.indexes.sys_mail_st_id] == this.constants.MAIL_ENVIADO){
                SGui.showMessage('','Solo se pueden eliminar e-mails con el estatus NO ENVIADO o EN PROCESO', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Desea eliminar el registro?',
                html: '<b>Fecha e-mail:</b> ' + data[this.indexes.date_mail],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteMailLog(data[this.indexes.id]);
                }
            })
        },

        deleteMailLog(mailLog_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.deleteMailRoute, {
                'id_mailLog': mailLog_id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showMessage('', data.message, data.icon);
                    this.reDrawMailsTable(data.lMails);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },

        filterYear(){
            SGui.showWaiting(5000);
            axios.post(this.oData.filterYearRoute, {
                'year': this.year,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showOk();
                    this.reDrawMailsTable(data.lMails);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },
    },
})