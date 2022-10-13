var app = new Vue({
    el: '#myVacations',
    data: {
        oData: oServerData,
        oUser: oServerData.oUser,
        lHolidays: oServerData.lHolidays,
        startDate: null,
        endDate: null,
        returnDate: null,
        comments: null,
        idRequest: null,
        takedDays: 0,
        lDays: []
    },
    mounted(){
        
    },
    methods: {
        showModal(data = null){
            if(data != null){
                this.comments = data[8];
                $('#date-range200').val(data[1]).trigger('change');
			    $('#date-range201').val(data[2]).trigger('change');
            }else{
                this.comments = null;
                this.idRequest = null;
                $('#clear').trigger('click');
            }
            $('#modal_solicitud').modal('show');
        },

        getDataDays(){
            this.getTakedDays();
        },

        getTakedDays(){
            this.takedDays = 0;
            this.lDays = [];
            var diffDays = moment(this.endDate).diff(moment(this.startDate), 'days');
            var oDate = moment(this.startDate);
            if(this.oUser.payment_frec_id == 1){
                if(this.startDate != null && this.startDate != ''){
                    for(var i = 0; i < 31;  i++){
                        switch (moment(this.startDate).weekday()) {
                            case 0:
                                this.startDate = moment(this.startDate).add('1', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }

                        if(!this.lHolidays.includes(this.startDate)){
                            break;
                        }else{
                            this.startDate = moment(this.startDate).add('1', 'days').format('YYYY-MM-DD');
                        }
                    }
                }

                if(this.endDate != null && this.endDate != ''){
                    this.returnDate = moment(this.endDate).add('1', 'days').format('YYYY-MM-DD');
                    for(var i = 0; i < 31; i++){
                        switch (moment(this.returnDate).weekday()) {
                            case 0:
                                this.returnDate = moment(this.returnDate).add('1', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }

                        if(!this.lHolidays.includes(this.returnDate)){
                            break;
                        }else{
                            this.returnDate = moment(this.returnDate).add('1', 'days').format('YYYY-MM-DD');
                        }
                    }

                    for(var i = 0; i < 31; i++){
                        switch (moment(this.endDate).weekday()) {
                            case 0:
                                this.endDate = moment(this.endDate).subtract('1', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }

                        if(!this.lHolidays.includes(this.endDate)){
                            break;
                        }else{
                            this.endDate = moment(this.endDate).subtract('1', 'days').format('YYYY-MM-DD');
                        }
                    }
                }

                for (let i = 0; i <= diffDays; i++) {
                    if(oDate.weekday() != 0 && !this.lHolidays.includes(oDate.format('YYYY-MM-DD'))){
                        this.takedDays = this.takedDays + 1;
                        this.lDays.push(oDate.format('YYYY-MM-DD'));
                    }
                    oDate.add('1', 'days');
                }
            }else{
                if(this.startDate != null && this.startDate != ''){
                    for(var i = 0; i < 31; i++){
                        switch (moment(this.startDate).weekday()) {
                            case 6:
                                this.startDate = moment(this.startDate).add('2', 'days').format('YYYY-MM-DD');
                                break;
                            case 0:
                                this.startDate = moment(this.startDate).add('1', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }

                        if(!this.lHolidays.includes(this.startDate)){
                            break;
                        }else{
                            this.startDate = moment(this.startDate).add('1', 'days').format('YYYY-MM-DD');
                        }
                    }
                }

                if(this.endDate != null && this.endDate != ''){
                    this.returnDate = moment(this.endDate).add('1', 'days').format('YYYY-MM-DD');
                    for(var i = 0; i < 31; i++){
                        switch (moment(this.returnDate).weekday()) {
                            case 6:
                                this.returnDate = moment(this.returnDate).add('2', 'days').format('YYYY-MM-DD');
                                break;
                            case 0:
                                this.returnDate = moment(this.returnDate).add('1', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }
                        if(!this.lHolidays.includes(this.returnDate)){
                            break;
                        }else{
                            this.returnDate = moment(this.returnDate).add('1', 'days').format('YYYY-MM-DD');
                        }
                    }

                    for(var i = 0; i < 31; i++){
                        switch (moment(this.endDate).weekday()) {
                            case 6:
                                this.endDate = moment(this.endDate).subtract('1', 'days').format('YYYY-MM-DD');
                                break;
                            case 0:
                                this.endDate = moment(this.endDate).subtract('2', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }
                        if(!this.lHolidays.includes(this.endDate)){
                            break;
                        }else{
                            this.endDate = moment(this.endDate).subtract('1', 'days').format('YYYY-MM-DD');
                        }
                    }
                }

                for (let i = 0; i <= diffDays; i++) {
                    if(oDate.weekday() != 0 && oDate.weekday() != 6 && !this.lHolidays.includes(oDate.format('YYYY-MM-DD'))){
                        this.takedDays = this.takedDays + 1;
                        this.lDays.push(oDate.format('YYYY-MM-DD'));
                    }
                    oDate.add('1', 'days');
                }
            }
        },

        formatDate(sDate){
            return moment(sDate).format('YYYY-MM-DD');
        }
    },
})