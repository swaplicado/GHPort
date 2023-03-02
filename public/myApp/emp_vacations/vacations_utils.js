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
                }
                lDays.push( 
                    {
                        date: oDateUtils.formatDate( oDate.format('YYYY-MM-DD'), 'ddd DD-MMM-YYYY'),
                        bussinesDay: (oDate.weekday() != 5 && oDate.weekday() != 6 && !lHolidays.includes(oDate.format('YYYY-MM-DD'))),
                        taked: (oDate.weekday() != 5 && oDate.weekday() != 6 && !lHolidays.includes(oDate.format('YYYY-MM-DD'))),
                        isOptional: (oDate.weekday() == 5 || oDate.weekday() == 6 || lHolidays.includes(oDate.format('YYYY-MM-DD'))),
                    }
                );

                oDate.add('1', 'days');
            }else{
                if(
                    (!take_rest_days ? (oDate.weekday() != 6) : true) &&
                    (!take_holidays ? (!lHolidays.includes(oDate.format('YYYY-MM-DD'))) : true)
                ){
                    takedDays = takedDays + 1;
                }
                lDays.push( 
                    {
                        date: oDateUtils.formatDate( oDate.format('YYYY-MM-DD'), 'ddd DD-MMM-YYYY'),
                        bussinesDay: (oDate.weekday() != 6 && !lHolidays.includes(oDate.format('YYYY-MM-DD'))),
                        taked: (oDate.weekday() != 6 && !lHolidays.includes(oDate.format('YYYY-MM-DD'))),
                        isOptional: (oDate.weekday() == 6 || lHolidays.includes(oDate.format('YYYY-MM-DD'))),
                    }
                );
                oDate.add('1', 'days');
            }
        }

        return [returnDate, takedDays, lDays, totCalendarDays, lNoBussinesDay];
    }

    createClass(lTemp){
        let myStyle = document.getElementById('myStyle');
        if(myStyle != undefined){
            myStyle.parentNode.removeChild(myStyle);
        }

        for (let index = 0; index < lTemp.length; index++) {
            var style = document.createElement('style');
            style.id = 'myStyle';
            style.type = 'text/css';
            style.innerHTML = '.priority_' + lTemp[index].priority + '{ background-color: ' + lTemp[index].color + ';' + '}';
            document.getElementsByTagName('head')[0].appendChild(style);
        }
    }

    //No se usan para nada pero esta bien tenerlas xD
    existClass(stylesheet, selector){
        let found = false;
        selector = selector.toLowerCase();
        for(var i = 0; i < stylesheet.cssRules.length; i++) {
            var rule = stylesheet.cssRules[i];
            if(rule.selectorText === selector) {
                rule.style[property] = value;
                return;
            }
        }
        return found;
    }

    changeStylesheetRule(stylesheet, selector, property, value) {
        // Make the strings lowercase
        selector = selector.toLowerCase();
        property = property.toLowerCase();
        value = value.toLowerCase();
        
        // Change it if it exists
        for(var i = 0; i < stylesheet.cssRules.length; i++) {
            var rule = stylesheet.cssRules[i];
            if(rule.selectorText === selector) {
                rule.style[property] = value;
                return;
            }
        }
      
        // Add it if it does not
        stylesheet.insertRule(selector + " { " + property + ": " + value + "; }", 0);
    }
    
    browserSupportsCssMedia(selector, property, value) {
        var styleSheets = document.styleSheets;
        for (var i = 0; i < styleSheets.length; i++) {
            
            var rules = styleSheets[i].cssRules || styleSheets[i].rules;
            try {
                if (rules.length > 0) {
                    // rules[0].media;
                    this.changeStylesheetRule(styleSheets[i], selector, property, value);
                }
            } catch (e) {
                return false;
            }
        }
        return true;
    }
}