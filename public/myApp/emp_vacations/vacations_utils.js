class vacationUtils{
    getTakedDays(lHolidays, payment_frec_id, startDate, endDate, constants, take_rest_days, take_holidays){
        if((startDate == null || startDate == "" || typeof startDate == undefined) ||
            (endDate == null || endDate == "" || typeof endDate == undefined)){
            return [null, 0];
        }
        var takedDays = 0;
        var diffDays = moment(endDate).diff(moment(startDate), 'days');
        var oDate = moment(startDate);
        var returnDate = moment(endDate).add('1', 'days');
        var lDays = [];
        for(var i = 0; i < 31; i++){
            switch (returnDate.weekday()) {
                case 5:
                    if(payment_frec_id == constants.QUINCENA){
                        returnDate.add('2', 'days');
                    }
                    break;
                case 6:
                        returnDate.add('1', 'days');
                    break;
                default:
                    break;
            }
            
            if(!lHolidays.includes(returnDate.format('YYYY-MM-DD'))){
                returnDate = returnDate.format('YYYY-MM-DD');
                break;
            }else{
                returnDate.add('1', 'days');
            }
        }

        for (var i = 0; i <= diffDays; i++) {
            if(payment_frec_id == constants.QUINCENA){
                if(
                    (!take_rest_days ? (oDate.weekday() != 5 && oDate.weekday() != 6) : true) &&
                    (!take_holidays ? (!lHolidays.includes(oDate.format('YYYY-MM-DD'))) : true)
                ){
                    takedDays = takedDays + 1;
                    lDays.push(oDate.format('YYYY-MM-DD'));
                }
                oDate.add('1', 'days');
            }else{
                if(
                    (!take_rest_days ? (oDate.weekday() != 6) : true) &&
                    (!take_holidays ? (!lHolidays.includes(oDate.format('YYYY-MM-DD'))) : true)
                ){
                    takedDays = takedDays + 1;
                    lDays.push(oDate.format('YYYY-MM-DD'));
                }
                oDate.add('1', 'days');
            }


        }

        return [returnDate, takedDays, lDays];
    }
}