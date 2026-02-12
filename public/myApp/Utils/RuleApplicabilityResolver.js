class RuleApplicabilityResolver {

    // Tipos de percepción
    static PERCEPTION = {
        VACATIONS: 1,
        INCIDENCE: 2,
        PERSONAL_PERMIT: 3,
        WORK_PERMIT: 4
    }

    // Tipos de incidencia
    static INCIDENT = {
        INCIDENT: 2,
        ADMIN_INCIDENT: 3,
        LEAVE_WITHOUT_PAY: 4,
        LEAVE_WITH_PAY: 5,
        PATERNITY_LEAVE: 6,
        MEDICAL_PRESCRIPTION: 7,
        WORK_OUTSIDE: 8,
        HOLIDAY: 9,
        HOMEOFFICE: 10,
        HOURS_PERMIT: 11,
        DEATH_PERMIT: 12
    }

    /**
     * Retorna true si la regla de diferencia máxima aplica
     * Retorna false si NO debe tomarse en cuenta
     */
    ruleApply(perceptionType, incidentType = null) {

        switch (perceptionType) {

            // Vacaciones siempre aplican regla
            case RuleApplicabilityResolver.PERCEPTION.VACATIONS:
                return true;

            // Incidencias dependen del tipo
            case RuleApplicabilityResolver.PERCEPTION.INCIDENCE:
                return this.incidentApplies(incidentType);

            // Permisos personales
            case RuleApplicabilityResolver.PERCEPTION.PERSONAL_PERMIT:
                return true;

            // Permisos laborales
            case RuleApplicabilityResolver.PERCEPTION.WORK_PERMIT:
                return false;

            default:
                return false;
        }
    }

    /**
     * Define qué incidencias sí aplican la regla
     */
    incidentApplies(incidentType) {

        const APPLY_RULE = [
            RuleApplicabilityResolver.INCIDENT.INCIDENT,
            RuleApplicabilityResolver.INCIDENT.ADMIN_INCIDENT,
            RuleApplicabilityResolver.INCIDENT.LEAVE_WITHOUT_PAY,
            RuleApplicabilityResolver.INCIDENT.LEAVE_WITH_PAY,
            RuleApplicabilityResolver.INCIDENT.PATERNITY_LEAVE,
            RuleApplicabilityResolver.INCIDENT.WORK_OUTSIDE,
            RuleApplicabilityResolver.INCIDENT.HOLIDAY,
            RuleApplicabilityResolver.INCIDENT.HOURS_PERMIT,
            RuleApplicabilityResolver.INCIDENT.DEATH_PERMIT,
            RuleApplicabilityResolver.INCIDENT.MEDICAL_PRESCRIPTION
        ];

        return APPLY_RULE.includes(incidentType);
    }

    getBusinessDays(startDate, endDate) {
        const start = moment(startDate).startOf('day');
        const end = moment(endDate).startOf('day');
        
        // Si la fecha de inicio es posterior a la fecha de fin, no hay días hacia atrás
        if (start.isAfter(end)) {
            return 0;
        }
        
        let businessDays = 0;
        let current = start.clone();
        
        // Iterar hasta el día anterior al endDate (no incluir hoy)
        while (current.isBefore(end)) {
            const dayOfWeek = current.day(); // 0 = domingo, 6 = sábado
            // Si no es fin de semana, cuenta como día hábil
            if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                businessDays++;
            }
            current.add(1, 'days');
        }
        
        return businessDays;
    }
}