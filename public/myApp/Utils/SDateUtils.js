class SDateUtils {  
    getFormat(d){
        var dateFormats = {
            "iso_int" : "YYYY-MM-DD",
            "short_date" : "DD/MM/YYYY",
            "iso_date_time": "YYYY-MM-DDTHH:MM:SS",
            "iso_date_time_utc": "YYYY-MM-DDTHH:MM:SSZ",
            "primaryFormat": "DD-MMM-YYYY",
            "secondFormat": "ddd DD-MMM-YYYY",
            "thirdFormat": "DD-MM-YYYY"
        }
        
        for (var prop in dateFormats) {
            if(moment(d, dateFormats[prop],true).isValid()){
                return dateFormats[prop];
            }
        }
        return null;
    }

    formatDate(myDate, myFormat){
        let flatten_date;
        let fromFormat = this.getFormat(myDate);
        if(myFormat != null){
            flatten_date = moment(myDate, fromFormat).format(myFormat);
        }else{
            flatten_date = moment(myDate, fromFormat).format('DD-MMM-YYYY');
        }

        return flatten_date;
    }
}