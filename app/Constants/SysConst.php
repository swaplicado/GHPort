<?php 
namespace App\Constants;

class SysConst {
    /**
     * Constantes de la tabla cat_incidence_cls
     */
    public const CLASS_VACACIONES = 1;
    public const CLASS_INASISTENCIA = 2;
    public const CLASS_INCAPACIDAD = 3;

    /**
     * Constantes de la tabla cat_incidence_tps
     */
    public const TYPE_VACACIONES = 1;
    public const TYPE_INASISTENCIA = 2;
    public const TYPE_INASISTENCIA_ADMINISTRATIVA = 3;
    public const TYPE_PERMISO_SIN_GOCE = 4;
    public const TYPE_PERMISO_CON_GOCE = 5;
    public const TYPE_PERMISO_PATERNIDAD = 6;
    public const TYPE_PRESCRIPCIÓN_MEDICA = 7;
    public const TYPE_TEMA_LABORAL = 8;
    public const TYPE_CUMPLEAÑOS = 9;
    public const TYPE_HOMEOFFICE = 10;

    public const TYPE_PERMISO_HORAS = 11; //Este permiso no esta en el catalogo de incidencias, es aparte
    public const TYPE_PERMISO_FALLECIMIENTO = 12;

    /**
     * JSON para los tipos de incidencia
     */
    public const lTypes = [
        'VACACIONES' => 1,
        'INASISTENCIA' => 2,
        'INASISTENCIA_ADMINISTRATIVA' => 3,
        'PERMISO_SIN_GOCE' => 4,
        'PERMISO_CON_GOCE' => 5,
        'PERMISO_PATERNIDAD' => 6,
        'PRESCRIPCIÓN_MEDICA' => 7,
        'TEMA_LABORAL' => 8,
        'CUMPLEAÑOS' => 9,
        'HOMEOFFICE' => 10,
        'PERMISO_HORAS' => 11,
        'PERMISO_FALLECIMIENTO' => 12,
    ];

    public const lTypesCodes = [
        'VACACIONES' => 'VAC',
        'INASISTENCIA' => 'INA',
        'INASISTENCIA_ADMINISTRATIVA' => 'ADM',
        'PERMISO_SIN_GOCE' => 'SGO',
        'PERMISO_CON_GOCE' => 'GOC',
        'PERMISO_PATERNIDAD' => 'PPA',
        'PRESCRIPCIÓN_MEDICA' => 'MED',
        'TEMA_LABORAL' => 'TLA',
        'CUMPLEAÑOS' => 'CUM',
        'HOMEOFFICE' => 'OFF',
        'PERMISO_HORAS' => 'HOR',
        'PERMISO_FALLECIMIENTO' => 'FAL',
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
    public const APPLICATION_CANCELADO = 6;

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
    public const MAIL_SOLICITUD_INCIDENCIA = 3;
    public const MAIL_REVISION_INCIDENCIA = 4;
    public const MAIL_SOLICITUD_PERMISO = 5;
    public const MAIL_REVISION_PERMISO = 6;
    public const MAIL_CANCELACION_VACACIONES = 7;
    public const MAIL_CANCELACION_INCIDENCIA = 8;
    public const MAIL_CANCELACION_PERMISO = 9;

    /**
     * Constantes de la tabla cat_notifications_type
     */
    public const NOTIFICATION_TYPE_VACACIONES = 1;
    public const NOTIFICATION_TYPE_INCIDENCIA = 2;
    public const NOTIFICATION_TYPE_PERMISO = 3;
    public const NOTIFICATION_TYPE_GLOBAL = 4;

    /**
     * Prioridad de tipos de notificaciones
     */
    public const NOTIFICATION_PRIORITY_VACACIONES = 2;
    public const NOTIFICATION_PRIORITY_INCIDENCIA = 2;
    public const NOTIFICATION_PRIORITY_PERMISO = 2;
    public const NOTIFICATION_PRIORITY_GLOBAL = 1;

     /**
      * iconos de notificaciones
      */
    public const NOTIFICATION_ICON_VACACIONES = 'bx bx-calendar-event';
    public const NOTIFICATION_ICON_INCIDENCIA = 'bx bx-calendar-event';
    public const NOTIFICATION_ICON_PERMISO = 'bx bx-calendar-event';
    public const NOTIFICATION_ICON_GLOBAL = 'bx bx-calendar-event';

    /**
     * Id del rol gerente
     */
    public const ORG_CHART_JOB_ID_TO_ADMIN = 2;
}
?>