class RuleApplicabilityResolver{
    // tipos percepción

    static PERCEPTION = {
        VACATIONS: 1,
        INCIDENCE: 2,
        PERSONAL_PERMIT: 3,
        WORK_PERMIT: 4
    }

    static INCIDENT = {
        INCIDENT: 2,
        ADMIN_INCIDENT: 3,
        LEAVE_WITHOUT_PAY: 4,
        LEAVE_WITH_PAY: 5,
        PATERNITY_LEAVE: 6,
        MEDICAL_PRESCRIPTION: 7,
        WORK_OUTSIDE: 8,
        HOLIDAY: 9,
        HOURS_PERMIT: 10,
        DEATH_PERMIT: 11

    }

    ruleApply(perceptionType, incidentType = null){
        switch (perceptionType) {
            case this.VACATIONS:
                return true;
        }
    }
}