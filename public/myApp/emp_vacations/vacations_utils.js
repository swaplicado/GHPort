class vacationUtils{
    getTakedDays(lHolidays, payment_frec_id, startDate, endDate, constants, take_rest_days, take_holidays){
        if((startDate == null || startDate == "" || startDate == undefined || startDate == "Fecha inválida") ||
            (endDate == null || endDate == "" || endDate == undefined || endDate == "Fecha inválida")){
            return [null, 0, [], 0];
        }
        var takedDays = 0;
        var totCalendarDays = (moment(endDate, 'YYYY-MM-DD').diff(moment(startDate, 'YYYY-MM-DD'), 'days') + 1);
        var diffDays = moment(endDate).diff(moment(startDate), 'days');
        var oDate = moment(startDate);
        var obDate = moment(startDate);
        var returnDate = moment(endDate).add('1', 'days');
        var lDays = [];
        var lNoBussinesDay = [];
        let oDateUtils = new SDateUtils();
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
                    lDays.push( oDateUtils.formatDate( oDate.format('YYYY-MM-DD'), 'ddd DD-MMM-YYYY') );
                }else{
                    lNoBussinesDay.push(oDateUtils.formatDate( oDate.format('YYYY-MM-DD'), 'ddd DD-MMM-YYYY'));
                }
                oDate.add('1', 'days');
            }else{
                if(
                    (!take_rest_days ? (oDate.weekday() != 6) : true) &&
                    (!take_holidays ? (!lHolidays.includes(oDate.format('YYYY-MM-DD'))) : true)
                ){
                    takedDays = takedDays + 1;
                    lDays.push( oDateUtils.formatDate(oDate.format('YYYY-MM-DD'), 'ddd DD-MMM-YYYY') );
                }else{
                    lNoBussinesDay.push(oDateUtils.formatDate( oDate.format('YYYY-MM-DD'), 'ddd DD-MMM-YYYY'));
                }
                oDate.add('1', 'days');
            }
        }

        return [returnDate, takedDays, lDays, totCalendarDays, lNoBussinesDay];
    }
}