var app = new Vue({
    el: '#curriculumApp',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        curriculumOptions: oServerData.curriculumOptions,
        workExperienceLength: 0,
        educationLength: 0,
        skillsLength:  0,
        languageLength: 0,
        aspectLength: 0,
        minWorkExperience: oServerData.minWorkExperience,
        minEducation: oServerData.minEducation,
        minSkills: oServerData.minSkills,
        minLanguage: oServerData.minLanguage,
        minAspect: oServerData.minAspect,
        maxWorkExperience: oServerData.maxWorkExperience,
        maxEducation: oServerData.maxEducation,
        maxSkills: oServerData.maxSkills,
        maxLanguage: oServerData.maxLanguage,
        maxAspect: oServerData.maxAspect,
        arrayWorkExperience: [],
        arrayEducation: [],
        arraySkills: [],
        arrayLanguage: [],
        arrayAspect: [],
        enabledEdition: oServerData.enabledEdition,
        professional_objective: '',
        o_curriculum: oServerData.o_curriculum,
        enabledEdition: oServerData.enabledEdition,
        full_name: oServerData.full_name,
        birthday: oServerData.birthday,
    },
    mounted(){
        self = this;

        $('.select2-class').select2({});

        this.birthday = this.oDateUtils.formatDate(this.birthday);

        if (this.o_curriculum != null) {
            this.professional_objective = this.o_curriculum.professional_objective;
            this.arrayWorkExperience = this.o_curriculum.work_experience;
            this.arrayEducation = this.o_curriculum.education;
            this.arraySkills = this.o_curriculum.skills;
            this.arrayLanguage = this.o_curriculum.languages;
            this.arrayAspect = this.o_curriculum.aditional_aspects;
        }

        this.workExperienceLength = this.arrayWorkExperience.length;
        this.educationLength = this.arrayEducation.length;
        this.skillsLength = this.arraySkills.length;
        this.languageLength = this.arrayLanguage.length;
        this.aspectLength = this.arrayAspect.length;
        
        if (this.arrayEducation.length < this.minEducation) {
            this.addEducation();
        }
    },
    methods: {
        addWorkExperience() {
            if (this.workExperienceLength < this.maxWorkExperience) {
                this.workExperienceLength++;
                this.arrayWorkExperience.push(
                    {
                        'company': '',
                        'period': '',
                        'position': '',
                        'activities': '',
                        'achievements': '',
                    }
                );
            }
        },

        lessWorkExperience() {
            if(this.workExperienceLength > 0){
                this.workExperienceLength--;
                this.arrayWorkExperience.pop();
            }
        },

        addEducation() {
            if (this.educationLength < this.maxEducation) {
                this.educationLength++;
                this.arrayEducation.push(
                    {
                        'level': '',
                        'institution': '',
                        'period': '',
                        'program': '',
                        'document': ''
                    }
                );
            }
        },

        lessEducation() {
            if(this.educationLength > 1){
                this.educationLength--;
                this.arrayEducation.pop();
            }
        },

        addSkill() {
            if (this.skillsLength < this.maxSkills) {
                this.skillsLength++;
                this.arraySkills.push(
                    {
                        'skill': ''
                    }
                );
            }
        },

        lessSkill() {
            if(this.skillsLength > 0){
                this.skillsLength--;
                this.arraySkills.pop();
            }
        },

        addLanguage() {
            if (this.languageLength < this.maxLanguage) {
                this.languageLength++;
                this.arrayLanguage.push(
                    {
                        'language': '',
                        'level': ''
                    }
                );
            }
        },

        lessLanguage() {
            if(this.languageLength > 0){
                this.languageLength--;
                this.arrayLanguage.pop();
            }
        },

        addAspect() {
            if (this.aspectLength < this.maxAspect) {
                this.aspectLength++;
                this.arrayAspect.push(
                    {
                        'type': '',
                        'description': ''
                    }
                );
            }
        },

        lessAspect() {
            if(this.aspectLength > 0){
                this.aspectLength--;
                this.arrayAspect.pop();
            }
        },

        checkValues() {
            if (!this.professional_objective) {
                SGui.showMessage('', 'Debe introducir el campo "Objetivo profesional"', 'warning');
                return false;
            }

            if (this.arrayWorkExperience.length > 0) {
                let incompleteExperience = this.arrayWorkExperience.some(item => {
                    if (!item.company || !item.period || !item.position || !item.activities || !item.achievements) {
                        return true;
                    }
                    return false;
                });
                
                if (incompleteExperience) {
                    SGui.showMessage('', 'Debe completar todos los campos de "Experiencia laboral"', 'warning');
                    return false;
                }
            }

            if (this.arrayEducation.length > 0) {
                let incompleteEducation = this.arrayEducation.some(item => {
                    if (!item.level || !item.institution || !item.period || !item.program || !item.document) {
                        return true;
                    }
                    return false;
                });

                if (incompleteEducation) {
                    SGui.showMessage('', 'Debe completar todos los campos de "Educación"', 'warning');
                    return false;
                }
            }

            if (this.arraySkills.length > 0) {
                let incompleteSkills = this.arraySkills.some(item => {
                    if (!item.skill) {
                        return true;
                    }
                    return false;
                });

                if (incompleteSkills) {
                    SGui.showMessage('', 'Debe completar todos los campos de "Habilidades y aptitudes"', 'warning');
                    return false;
                }
            }

            if (this.arrayLanguage.length > 0) {
                let incompleteLanguage = this.arrayLanguage.some(item => {
                    if (!item.language || !item.level) {
                        return true;
                    }
                    return false;
                });

                if (incompleteLanguage) {
                    SGui.showMessage('', 'Debe completar todos los campos de "Idiomas"', 'warning');
                    return false;
                }
            }

            if (this.arrayAspect.length > 0) {
                let incompleteAspect = this.arrayAspect.some(item => {
                    if (!item.type || !item.description) {
                        return true;
                    }
                    return false;
                });

                if (incompleteAspect) {
                    SGui.showMessage('', 'Debe completar todos los campos de "Aspectos adicionales"', 'warning');
                    return false;
                }
            }

            return true;
        },

        saveCurriculum() {
            if (!this.checkValues()) return;
            SGui.showWaiting();
            let route = this.oData.saveCurriculumRoute;
            axios.post(route, {
                'workExperience': this.arrayWorkExperience,
                'education': this.arrayEducation,
                'skills': this.arraySkills,
                'language': this.arrayLanguage,
                'aspect': this.arrayAspect,
                'professional_objective': this.professional_objective,
            })
            .then(function (response) {
                let data = response.data;
                if (data.success) {
                    SGui.showOk();
                    location.reload();
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
})