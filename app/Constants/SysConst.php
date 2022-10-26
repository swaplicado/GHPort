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