class SDateRangePicker {
    /**
     * Metodo para crear el oobjeto calendario dateRangePicker,
     * Los inputs date del calendario deben estar separador por " a ", en la forma: input1 a input2
     * @param {*} container_id
     * @param {*} first_input_date_id
     * @param {*} second_input_date_id
     * @param {*} clear_btn_id
     * @param {*} constants
     * @param {*} sStart_date
     * @param {*} bSingleMonth
     * @param {*} bSingleDate
     * @param {*} payment
     * @param {*} lTemp
     * @param {*} lHolidays
     * @param {*} birthday
     * @param {*} aniversaryDay
     */
    setDateRangePicker(
        container_id,
        first_input_date_id,
        second_input_date_id,
        clear_btn_id,
        constants,
        sStart_date,
        bSingleMonth,
        bSingleDate,
        payment,
        lTemp,
        lHolidays,
        birthday,
        aniversaryDay,
        enable = true,
    ){  
        $.dateRangePickerLanguages['es'] =
        {
            'selected': 'De:',
            'days': 'Dias',
            'apply': 'Cerrar',
            'week-1' : 'Lun',
            'week-2' : 'Mar',
            'week-3' : 'Mie',
            'week-4' : 'Jue',
            'week-5' : 'Vie',
            'week-6' : 'Sab',
            'week-7' : 'Dom',
            'month-name': ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','octubre','Noviembre','Diciembre'],
            'shortcuts' : 'Shortcuts',
            'past': 'Past',
            '7days' : '7 días',
            '14days' : '14 días',
            '30days' : '30 días',
            'previous' : 'Anterior',
            'prev-week' : 'Semana',
            'prev-month' : 'Mes',
            'prev-quarter' : 'Quincena',
            'prev-year' : 'Año',
            'less-than' : 'El rango de fecha debe ser mayor a %d días',
            'more-than' : 'El rango de fecha debe ser menor a %d días',
            'default-more' : 'Selecciona un rango de fecha mayor a %d días',
            'default-less' : 'Selecciona un rango de fecha menor a %d días',
            'default-range' : 'Selecciona un rango de fecha entre %d y %d días',
            'default-default': 'Seleccione rango de fecha'
        };
    
        $('#'+container_id).dateRangePicker(
        {
            startDate: sStart_date,
            inline:true,
            container: '#'+container_id,
            alwaysOpen:true,
            language: 'es',
            separator : ' a ',
            showShortcuts: false,
            singleMonth: bSingleMonth,
            singleDate : bSingleDate,

            beforeShowDay: function(t)
            {
                var valid = enable;
                var _class = '';
                var _tooltip = '';

                if(app.oApplication != undefined){
                    if(app.oApplication.start_date != undefined && app.oApplication.end_date != undefined){
                        if(moment(t.getTime()).format('YYYY-MM-DD') == app.oApplication.start_date){
                            _tooltip = _tooltip + 'Inicio. ';
                        }
        
                        if(moment(t.getTime()).format('YYYY-MM-DD') == app.oApplication.end_date){
                            _tooltip = _tooltip + 'Fin. ';
                        }
                    }
                }

                let index = lTemp.findIndex(({ lDates }) => lDates.includes(moment(t.getTime()).format('YYYY-MM-DD')));
                if(index > -1){
                    _class = 'priority_' + lTemp[index].priority;
                    _tooltip = _tooltip + lTemp[index].name + '. ';
                }
                if(typeof app.lEvents !== 'undefined'){
                    let index = app.lEvents.findIndex(({ lDates }) => lDates.includes(moment(t.getTime()).format('YYYY-MM-DD')));
                    if(index > -1){
                        _class = 'event';
                        _tooltip = _tooltip + app.lEvents[index].name + '. ';
                    }
                }
                if(lHolidays.includes(moment(t.getTime()).format('YYYY-MM-DD'))){
                    _class = 'holiday';
                    _tooltip = _tooltip + 'Festivo. ';
                }
                
                if(moment(aniversaryDay).format('MM-DD') == moment(t.getTime()).format('MM-DD')){
                    _class = 'aniversary';
                    _tooltip = _tooltip + 'Aniversario. ';
                }
                if(moment(birthday).format('MM-DD') == moment(t.getTime()).format('MM-DD')){
                    _class = 'birthDay';
                    _tooltip = _tooltip + 'Cumpleaños. ';
                }

                let endWeek = payment == constants.QUINCENA ? t.getDay() == 0 || t.getDay() == 6 : t.getDay() == 0;

                if(endWeek){
                    _class = 'restDay';
                    _tooltip = _tooltip + 'Inhabil. ';
                }

                if(dateRangePickerArrayApplications != undefined){
                    if(dateRangePickerArrayApplications.length > 0){
                        let indexApplication = dateRangePickerArrayApplications.findIndex(function(evento) {
                            return moment(evento.date).format('YYYY-MM-DD') == moment(t.getTime()).format('YYYY-MM-DD');
                        });
                        if(indexApplication > -1){
                            _class = 'requestedVac';
                            _tooltip = _tooltip + dateRangePickerArrayApplications[indexApplication].name +  
                                        '. ' + dateRangePickerArrayApplications[indexApplication].comments;
                        }
                    }
                }

                if(dateRangePickerArrayIncidences != undefined){
                    if(dateRangePickerArrayIncidences.length > 0){
                        let indexIncidence = dateRangePickerArrayIncidences.findIndex(function(evento) {
                            return moment(evento.date).format('YYYY-MM-DD') == moment(t.getTime()).format('YYYY-MM-DD');
                        });
                        if(indexIncidence > -1){
                            _class = 'incidence';
                            _tooltip = _tooltip + dateRangePickerArrayIncidences[indexIncidence].name +  
                                        '. ' + dateRangePickerArrayIncidences[indexIncidence].comments;
                        }
                    }
                }


                return [valid,_class,_tooltip];
            },
            getValue: function(){
                dateRangePickerGetValue();
                if ($('#'+first_input_date_id).val() && $('#'+second_input_date_id).val() ){
                    return $('#'+first_input_date_id).val() + ' a ' + $('#'+second_input_date_id).val();
                }
                else{
                    return '';
                }
            },
            setValue: function(s,s1,s2){
                if(!bSingleDate){
                    $('#'+first_input_date_id).val(s1);
                    $('#'+second_input_date_id).val(s2);
                }else{
                    $('#'+first_input_date_id).val(s1);
                    $('#'+second_input_date_id).val(s1);
                }
                dateRangePickerSetValue();
            }
        });
        $('#'+clear_btn_id).click(function(evt){
            evt.stopPropagation();
            $('#'+container_id).data('dateRangePicker').clear();
            dateRangePickerClearValue();
        });
    }
}