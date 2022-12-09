/**
 * Necesario declarar la variable global dateRangePickerArrayApplications = [] en la vista.
 */

class SDateRangePicker {
    /**
     * Metodo para crear el oobjeto calendario dateRangePicker con valores a usar de una dataTable,
     * es importante que esta dataTable contenga el campo payment_frec_id del usuario.
     * Los inputs date del calendario deben estar separador por " a ", en la forma: input1 a input2
     * @param {*} container_id 
     * @param {*} table_id 
     * @param {*} first_input_date_id 
     * @param {*} second_input_date_id 
     * @param {*} payment_frec_index 
     * @param {*} const_QUINCENA 
     */
    setDateRangePickerWithSelectDataTable(
        container_id,
        table_id,
        first_input_date_id,
        second_input_date_id,
        payment_frec_index,
        const_QUINCENA,
        lHolidays
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
            'default-default': ''
        };
    
        $('#'+container_id).dateRangePicker(
        {
            // startDate: oServerData.initialCalendarDate,
            inline:true,
            container: '#'+container_id,
            alwaysOpen:true,
            language: 'es',
            separator : ' a ',
            showShortcuts: false,
            beforeShowDay: function(t)
            {
                var valid = false;
                var _class = '';
                var _tooltip = '';
                if(table[table_id].row('.selected').data() != undefined){
                    if(table[table_id].row('.selected').data()[payment_frec_index] == const_QUINCENA){
                        if(t.getDay() == 0 || t.getDay() == 6){
                            _class = 'restDay';
                            _tooltip = 'Inhabil';
                        }else if(lHolidays.includes(moment(t.getTime()).format('YYYY-MM-DD'))){
                            _class = 'holiday';
                            _tooltip = 'Festivo';
                        }else if(dateRangePickerArrayApplications.includes(moment(t.getTime()).format('YYYY-MM-DD'))){
                            _class = 'requestedVac';
                            _tooltip = 'Solicitud de vacaciones';
                        }
                    } else {
                        if(t.getDay() == 0){
                            _class = 'restDay';
                            _tooltip = 'Inhabil';
                        }else if(lHolidays.includes(moment(t.getTime()).format('YYYY-MM-DD'))){
                            _class = 'holiday';
                            _tooltip = 'Festivo';
                        }else if(dateRangePickerArrayApplications.includes(moment(t.getTime()).format('YYYY-MM-DD'))){
                            _class = 'requestedVac';
                            _tooltip = 'Solicitud de vacaciones';
                        }
                    }
                }
     
                return [valid,_class,_tooltip];
            },
            getValue: function(){
                if ($('#'+first_input_date_id).val() && $('#'+second_input_date_id).val() ){
                    return $('#'+first_input_date_id).val() + ' a ' + $('#'+second_input_date_id).val();
                }
                else{
                    return '';
                }
            },
            setValue: function(s,s1,s2){
                $('#'+first_input_date_id).val(s1);
                $('#'+second_input_date_id).val(s2);
            }
        });
    }

    /**
     * Necesario declarar la funcion global dateRangePickerSetValue.
     * Necesario declarar la funcion global dateRangePickerGetValue.
     * Necesario declarar la funcion global dateRangePickerClearValue.
     * Necesario declarar la variable global dateRangePickerValid.
     * @param {*} container_id 
     * @param {*} initialCalendarDate 
     * @param {*} user_payment_frec_id 
     * @param {*} const_QUINCENA 
     * @param {*} first_input_date_id 
     * @param {*} second_input_date_id 
     * @param {*} clear_btn_id 
     * @param {*} lHolidays 
     */
    setDateRangePicker(
        container_id,
        initialCalendarDate,
        user_payment_frec_id,
        const_QUINCENA,
        first_input_date_id,
        second_input_date_id,
        clear_btn_id,
        lHolidays
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
            startDate: initialCalendarDate,
            inline:true,
            container: '#'+container_id,
            alwaysOpen:true,
            language: 'es',
            separator : ' a ',
            beforeShowDay: function(t)
            {
                var valid = dateRangePickerValid;
                var _class = '';
                var _tooltip = '';
                if(user_payment_frec_id == const_QUINCENA){
                    if(t.getDay() == 0 || t.getDay() == 6){
                        _class = 'restDay';
                        _tooltip = 'Inhabil';
                    }else if(lHolidays.includes(moment(t.getTime()).format('YYYY-MM-DD'))){
                        _class = 'holiday';
                        _tooltip = 'Festivo';
                    }else if(dateRangePickerArrayApplications.includes(moment(t.getTime()).format('YYYY-MM-DD'))){
                        _class = 'requestedVac';
                        _tooltip = 'Solicitud de vacaciones';
                    }
                } else {
                    if(t.getDay() == 0){
                        _class = 'restDay';
                        _tooltip = 'Inhabil';
                    }else if(lHolidays.includes(moment(t.getTime()).format('YYYY-MM-DD'))){
                        _class = 'holiday';
                        _tooltip = 'Festivo';
                    }else if(dateRangePickerArrayApplications.includes(moment(t.getTime()).format('YYYY-MM-DD'))){
                        _class = 'requestedVac';
                        _tooltip = 'Solicitud de vacaciones';
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
                $('#'+first_input_date_id).val(s1);
                $('#'+second_input_date_id).val(s2);
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