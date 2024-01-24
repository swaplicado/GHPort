var app = new Vue({
    el: '#personalDataApp',
    data: {
        oData: oServerData,
        config: oServerData.config,
        personalData: oServerData.personalData,
        childs: [],
        childIds: 0,
        lSex: oServerData.lSex,
        lBlood: oServerData.lBlood,
        lCivil: oServerData.lCivil,
        lSchooling: oServerData.lSchooling,
        lState: oServerData.lState,
        lParentesco: oServerData.lParentesco,

        lastName: oServerData.personalData.lastName,
        secondLastName: oServerData.personalData.secondLastName,
        names: oServerData.personalData.names,
        rfc: oServerData.personalData.rfc,
        personalMail: oServerData.personalData.email01,
        companyMail: oServerData.personalData.email02,

        sex: oServerData.personalData.sexId,
        bloodType: oServerData.personalData.bloodId,
        maritalStatus: oServerData.personalData.maritalId,
        schooling: oServerData.personalData.educationId,
        sexSpouce: oServerData.personalData.sexMateId,
        parentesco: oServerData.personalData.parentescoId,
        ext: oServerData.personalData.telExt02,

        personalPhone: oServerData.personalData.telNumber01,
        companyPhone: oServerData.personalData.telNumber02,
        emergencyPhone: oServerData.personalData.emergsTel,
        emergencyContac: oServerData.personalData.emergsCon,
        beneficiary: oServerData.personalData.benefs,
        adress: oServerData.personalData.adress,
        country: oServerData.personalData.country,
        state: oServerData.personalData.fidSta,
        postalCode: oServerData.personalData.zipCode,
        postalCodeFiscal: oServerData.personalData.zipCodeFiscal,
        municipality: oServerData.personalData.county,
        locality: oServerData.personalData.locality,
        colony: oServerData.personalData.neighborhood,
        street: oServerData.personalData.street,
        outsideNumber: oServerData.personalData.streetNumExt,
        insideNumber: oServerData.personalData.streetNumInt,
        reference: oServerData.personalData.reference,
        spouse: oServerData.personalData.mate,
        birthdaySpouce: oServerData.personalData.dtBirMate,

        id_add: oServerData.personalData.idAdd,
        id_con: oServerData.personalData.idCon,
        id_bpb: oServerData.personalData.idBpb,
    },
    mounted(){
        self = this;

        $('.select2-class').select2({});
        
        $('#selSex').select2({
            data: self.lSex,
            placeholder: 'Selecciona sexo',
        }).on('select2:select', function(e) {
            self.sex = e.params.data.id;
        });

        $('#selSex').val(self.sex).trigger('change');

        $('#selBlood').select2({
            data: self.lBlood,
            placeholder: 'Selecciona tipo de sangre',
        }).on('select2:select', function(e) {
            self.bloodType = e.params.data.id;
        });

        $('#selBlood').val(self.bloodType).trigger('change');

        $('#selCivl').select2({
            data: self.lCivil,
            placeholder: 'Selecciona estado civil',
        }).on('select2:select', function(e) {
            self.maritalStatus = e.params.data.id;
        });

        $('#selCivl').val(self.maritalStatus).trigger('change');

        $('#selSchooling').select2({
            data: self.lSchooling,
            placeholder: 'Selecciona nivel de estudios',
        }).on('select2:select', function(e) {
            self.schooling = e.params.data.id;
        });

        $('#selSchooling').val(self.schooling).trigger('change');

        $('#SelEmergencyContac').select2({
            data: self.lParentesco,
            placeholder: 'Selecciona parentesco',
        }).on('select2:select', function(e) {
            self.parentesco = e.params.data.id;
        });

        $('#SelEmergencyContac').val(self.parentesco).trigger('change');

        $('#selSexSpouce').select2({
            data: self.lSex,
            placeholder: 'Selecciona sexo',
        }).on('select2:select', function(e) {
            self.sexSpouce = e.params.data.id;
        });

        $('#selSexSpouce').val(self.sexSpouce).trigger('change');

        $('#selState').select2({
            data: self.lState,
            placeholder: 'Selecciona estado',
        }).on('select2:select', function(e) {
            self.state = e.params.data.id;
        });

        $('#selState').val(self.state).trigger('change');

        if(this.personalData.son1 != ""){
            this.addChild(this.personalData.son1, this.personalData.dtBirSon1, this.personalData.sexSonId1);
        }
        if(this.personalData.son2 != ""){
            this.addChild(this.personalData.son2, this.personalData.dtBirSon2, this.personalData.sexSonId2);
        }
        if(this.personalData.son3 != ""){
            this.addChild(this.personalData.son3, this.personalData.dtBirSon3, this.personalData.sexSonId3);
        }
        if(this.personalData.son4 != ""){
            this.addChild(this.personalData.son4, this.personalData.dtBirSon4, this.personalData.sexSonId4);
        }
        if(this.personalData.son5 != ""){
            this.addChild(this.personalData.son5, this.personalData.dtBirSon5, this.personalData.sexSonId5);
        }
    },
    methods: {
        updatePersonalData(){
            SGui.showWaiting();   
        },

        addChild(name = "", birthday = "", sex = ""){
            let hijos = document.getElementsByClassName('hijoColab');
            let cantidad = hijos.length;

            if(cantidad >= this.config.limitChilds){
                return;
            }

            this.childIds++;
            let contenedor = document.getElementById('contenedor_hijos'); // Reemplaza 'contenedor' con el ID real del contenedor

            let html = `
                <div class="row hijoColab" id="hijo`+this.childIds+`">
                    <div class="col-md-2 label-container">
                        <label for="">Nombre completo</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="nombre" value="`+name+`" class="my-form-control" placeholder="Nombre completo del hijo(a)">
                    </div>
                    <div class="col-md-5">
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="">Nacimiento</label>
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="edad" value="`+birthday+`" class="my-form-control">
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="">Sexo</label>
                            </div>
                            <div class="col-md-4">
                                <select name="sexo" id="selSexChild" class="my-form-control select2-class" style="width: 100%;">`;
                                    if(sex == 2){
                                        html = html +`<option value="2" selected>Masculino</option>
                                                        <option value="3">Femenino</option>`
                                    }else if(sex == 3){
                                        html = html +`<option value="2">Masculino</option>
                                                        <option value="3" selected>Femenino</option>`
                                    }else{
                                        html = html +`<option value="2">Masculino</option>
                                                        <option value="3">Femenino</option>`
                                    }
                            html = html +`</select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button id="btn_eliminar" type="button" class="btnRound btn-danger" 
                            style="display: inline-block;" title="Eliminar renglon" onclick="app.delChild('hijo`+this.childIds+`')">
                            <span class="bx bx-minus"></span>
                        </button>
                    </div>
                </div>
            `;

            // Insertar el HTML en el contenedor
            contenedor.insertAdjacentHTML('beforeend', html);
        },

        delChild(id){
            let child = document.getElementById(id);
            child.remove();
        },

        setChilds(){
            this.childs = [];
            let elementosRow = document.getElementsByClassName('hijoColab');

            for (let i = 0; i < elementosRow.length; i++) {
                let inputName = elementosRow[i].querySelector('input[name="nombre"]');
                let inputAge = elementosRow[i].querySelector('input[name="edad"]');
                let inputSex = elementosRow[i].querySelector('select[name="sexo"]');
                this.childs.push({
                    'id': i,
                    'name': inputName.value,
                    'birthday': inputAge.value,
                    'sex': inputSex.value,
                });
            }

        },

        update(){
            SGui.showWaiting();
            this.setChilds();
            let route = this.oData.updateRoute;
            axios.post(route, {
                'id_add': this.id_add,
                'id_con': this.id_con,
                'id_bpb': this.id_bpb,
                'lastName': this.lastName,
                'secondLastName': this.secondLastName,
                'names': this.names,
                'rfc': this.rfc,
                'zip_code_fiscal': this.postalCodeFiscal,
                'zip_code': this.postalCode,
                'personalMail': this.personalMail,
                'companyMail': this.companyMail,
                'sex': this.sex,
                'bloodType': this.bloodType,
                'maritalStatus': this.maritalStatus,
                'schooling': this.schooling,
                'personalPhone': this.personalPhone,
                'companyPhone': this.companyPhone,
                'emergencyPhone': this.emergencyPhone,
                'emergencyContac': this.emergencyContac,
                'parentesco': this.parentesco,
                'ext': this.ext,
                'beneficiary': this.beneficiary,
                'state': this.state,
                'municipality': this.municipality,
                'locality': this.locality,
                'colony': this.colony,
                'street': this.street,
                'outsideNumber': this.outsideNumber,
                'insideNumber': this.insideNumber,
                'reference': this.reference,
                'spouse': this.spouse,
                'birthdaySpouce': this.birthdaySpouce,
                'sexSpouce': this.sexSpouce,
                'childs': this.childs,
            })
            .then(function (response) {
                let data = response.data;
                if (data.success) {
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function (error) {
                console.log(error);
                SGui.showMessage('', 'Error al actualizar los datos', 'error');
            });
        }
    }
});