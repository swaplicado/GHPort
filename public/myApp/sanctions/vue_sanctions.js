var app = new Vue({
    el: '#admMinutesApp',
    data: {
        oData: oServerData,
        lSanctions: oServerData.lSanctions,
        oUser: oServerData.oUser,
        viewMode: 'empMinutes',
        type: oServerData.type,
        lTypes: oServerData.lTypes,
        lRoles: oServerData.lRoles,
        startDate: oServerData.startDate,
        endDate: oServerData.endDate,
        showAll: true,
    },
    mounted(){
        self = this;
    },
    methods:  {
        setViewMode(mode){
            this.viewMode = mode;

            const btn_ids = ['myMinutes', 'empMinutes'];
            let btn = document.getElementById(mode);
            btn.style.backgroundColor = '#858796';
            btn.style.color = '#fff';

            for (const bt_id of btn_ids) {
                if (bt_id != mode) {
                    let bt = document.getElementById(bt_id);
                    bt.style.backgroundColor = '#fff';
                    bt.style.color = '#858796';
                    bt.style.boxShadow = '0 0 0';
                }
            }
            
            table['minutes_table'].clear().draw();
            this.getMinutes();
        },

        drawMinutesTable(lSanctions){
            let datalSanctions = [];
            for(let san of lSanctions){
                datalSanctions.push(
                    [
                        san.employee_id,
                        san.num,
                        san.startDate,
                        san.endDate,
                        san.title,
                        san.description,
                        san.offender,
                    ]
                );
            }

            table['minutes_table'].clear().draw();
            table['minutes_table'].rows.add(datalSanctions).draw();
        },

        drawSanctionsTable(lSanctions){
            let datalSanctions = [];
            for(let san of lSanctions){
                datalSanctions.push(
                    [
                        san.employee_id,
                        san.num,
                        san.startDate,
                        san.title,
                        san.description,
                        san.offender,
                    ]
                );
            }

            table['sanctions_table'].clear().draw();
            table['sanctions_table'].rows.add(datalSanctions).draw();
        },

        getMinutes(){
            SGui.showWaiting();
            
            let isAll = document.getElementById('checkBoxAllEmployees').checked;

            let route;
            if (this.viewMode == 'empMinutes') {
                if(isAll){
                    route = this.oData.allEmpRoute;
                }else{
                    route = this.oData.myEmpRoute;
                }
            }else{
                route = this.oData.mySanctionRoute;
            }

            axios.post(route, {
                'type': this.type,
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    if(this.type == this.lTypes.ACTA){
                        this.drawMinutesTable(data.lSanctions);
                    }else if(this.type == this.lTypes.SANCION){
                        this.drawSanctionsTable(data.lSanctions);
                    }
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(error => {
                console.log(error);
                SGui.showError(error);
            })
        }
    }
});