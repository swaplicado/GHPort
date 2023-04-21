<?php 
namespace App\Constants;

class SysConst {
    /**
     * Constantes de la tabla cat_incidence_cls
     */
    public const CLASS_VACACIONES = 1;

    /**
     * Constantes de la tabla cat_incidence_tps
     */
    public const TYPE_VACACIONES = 1;
    public const TYPE_INASISTENCIA = 2;
    public const TYPE_INASISTENCIA_ADMINISTRATIVA = 3;
    public const TYPE_CUMPLEAÑOS = 4;

    /**
     * JSON para los tipos de incidencia
     */
    public const lTypes = [
        'VACACIONES' => 1,
        'INASISTENCIA' => 2,
        'INASISTENCIA_ADMINISTRATIVA' => 3,
        'CUMPLEANOS' => 4,
        'PERMISO_SIN_GOCE' => 5,
        'PERMISO_CON_GOCE' => 6,
        'PERMISO_PATERNIDAD' => 7,
        'PRESCRIPCIÓN_MÉDICA' => 8,
        'TEMA_LABORAL' => 9,
        'Riesgo_de_trabajo' => 10,
        'Enfermedad_en_general' => 11,
        'Maternidad' => 12,
        'Licencia_por_cuidados_medicos_de_hijos_diagnosticados_con_cancer' => 13,
    ];

    public const lTypesCodes = [
        'INASISTENCIA' => 'INA',
        'INASISTENCIA_ADMINISTRATIVA' => 'IAD',
        'PERMISO_SIN_GOCE' => 'PSG',
        'PERMISO_CON_GOCE' => 'PCG',
        'ERMISO_PATERNIDAD' => 'PPA',
        'PRESCRIPCIÓN_MÉDICA' => 'PME',
        'TEMA_LABORAL' => 'TLA',
        'CUMPLEANOS' => 'CUM',
        'VACACIONES' => 'VAC',
        'Riesgo_de_trabajo' => 'RIE',
        'Enfermedad_en_general' => 'ENF',
        'Maternidad' => 'MAT',
        'Licencia_por_cuidados_medicos_de_hijos_diagnosticados_con_cancer' => 'LIC',
    ];

    /**
     * Constantes de la tabla adm_rol
     */
    public const ESTANDAR = 1;
    public const JEFE = 2;
    public const GH = 3;
    public const ADMINISTRADOR = 4;

    /**
     * Constantes de la tabla cat_payment_frecs
     */
    public const SEMANA =  1;
    public const QUINCENA =  2;

    /**
     * Constantes de la tabla sys_applications_sts
     */
    public const APPLICATION_CREADO = 1;
    public const APPLICATION_ENVIADO = 2;
    public const APPLICATION_APROBADO = 3;
    public const APPLICATION_RECHAZADO = 4;
    public const APPLICATION_CONSUMIDO = 5;

    /**
     * Constantes de la tabla sys_mails_sts
     */
    public const MAIL_EN_PROCESO = 1;
    public const MAIL_ENVIADO = 2;
    public const MAIL_NO_ENVIADO = 3;

    /**
     * Constantes de la tabla cat_mails_tps
     */
    public const MAIL_SOLICITUD_VACACIONES = 1;
    public const MAIL_ACEPT_RECH_SOLICITUD = 2;
}
?>