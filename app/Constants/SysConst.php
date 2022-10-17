<?php 
namespace App\Constants;

class SysConst {
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
    public const QUINCENA =  1;

    /**
     * Constantes de la tabla sys_applications_sts
     */
    public const APPLICATION_CREADO = 1;
    public const APPLICATION_ENVIADO = 2;
    public const APPLICATION_APROBADO = 3;
    public const APPLICATION_RECHAZADO = 4;
    public const APPLICATION_CONSUMIDO = 5;
}
?>