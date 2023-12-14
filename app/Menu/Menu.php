<?php

namespace App\Menu;
use \Session;
class Menu {
    public static function createMenu($oUser = null)
    {
        $element = 1;
        $list =2;
        if ($oUser == null) {
            return "";
        }
        $lMenus = [];
        
        // primer grupo de menus vacaciones 
        // arreglo para ingresar submenus
        $vacaciones = [];

        array_push($vacaciones,['route' => route('myVacations'), 'icon' => 'bx bx-calendar bx-xs', 'name' => 'Mis solicitudes']);
        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($vacaciones,['route' => route('requestVacations'), 'icon' => 'bx bxs-archive bx-xs', 'name' => 'Solicitudes mis colabs.']);    
        }
        //submenu de vacaciones
        $subVacaciones = [];

        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($subVacaciones,['route' => route('myEmplVacations'), 'icon' => 'bx bxs-user-detail bx-xs', 'name' => 'Mis colabs. dir.']);       
        }

        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($subVacaciones,['route' => route('allEmplVacations'), 'icon' => 'bx bxs-group bx-xs', 'name' => 'Todos mis colabs.']);       
        }

        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($vacaciones,['type' => $list, 'list' => $subVacaciones, 'icon' => 'bx bxs-user-detail bx-xs', 'name' => 'Vac. mis colabs.', 'id' => 'Vacmiscolab']);       
        }
        //cierra submenu
        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($vacaciones,['route' => route('recoveredVacations'), 'icon' => 'bx bxs-archive bx-xs', 'name' => 'React. mis colabs.']);    
        }


        $lMenus = [(object) ['type' => $list, 'list' => $vacaciones, 'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Vacaciones', 'id' => 'vacations']];
        // cierra primer grupo de menus

        //segundo grupo de menus incidencias
        //arreglo para ingresar incidencias
        $incidencias = [];

        array_push($incidencias,['route' => route('incidences_index'), 'icon' => 'bx bx-file bx-xs', 'name' => 'Mis solicitudes']);
        
        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($incidencias,['route' => route('requestIncidences_index'), 'icon' => 'bx bx-file bx-xs', 'name' => 'Solicitudes mis colabs.']);
        }

        array_push($incidencias,['route' => route('permission_index', ['id' => 2]), 'icon' => 'bx bx-file bx-xs', 'name' => 'Tema laboral hrs.']);

        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($incidencias,['route' => route('requestPermission_index'), 'icon' => 'bx bx-file bx-xs', 'name' => 'Sol. tema laboral hrs.']);
        }

        array_push($lMenus,(object) ['type' => $list, 'list' =>$incidencias, 'icon' => 'bx bx-shape-circle bx-sm', 'name' => 'Incidencias', 'id' => 'Incidencias']);

        //cierra segundo grupo de menus

        //tercer grupo de menus permisos
        //arreglo paraa ingresar permisos
        $permisos = [];

        array_push($permisos,['route' => route('permission_index', ['id' => 1]), 'icon' => 'bx bx-file bx-xs', 'name' => 'Mis solicitudes']);
        
        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($permisos,['route' => route('requestPersonalPermission'), 'icon' => 'bx bx-file bx-xs', 'name' => 'Solicitudes mis colabs.']);
        }

        array_push($lMenus,(object) ['type' => $list, 'list' =>$permisos, 'icon' => 'bx bx-shape-circle bx-sm', 'name' => 'Permiso personal hrs.', 'id' => 'Permisos']);

        //cierrar tercer grupo de menus

        //cuarto grupo de menus gestión
        $gestion = [];

        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($gestion,['route' => route('delegation'), 'icon' => 'bx bxs-contact bx-xs', 'name' => 'Mis delegaciones']);   
        }

        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($lMenus,(object) ['type' => $list, 'list' =>$gestion, 'icon' => 'bx bxs-briefcase-alt bx-sm', 'name' => 'Gestión', 'id' => 'Gestion']);    
        }

        //cierra cuarto grupo de menus

        //quinto grupo de menus consultas
        $consultas = [];

        array_push($consultas,['route' => route('orgChart'), 'icon' => 'bx bx-sitemap bx-xs', 'name' => 'Organigrama']);
        
        //submenu de festejos
        $festejos = [];

        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($festejos,['route' => route('colab_ann'), 'icon' => 'bx bxs-user-detail bx-xs', 'name' => 'Mis colabs. dir.']);       
        }

        if($oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($festejos,['route' => route('all_colab_ann'), 'icon' => 'bx bxs-group bx-xs', 'name' => 'Todos mis colabs.']);       
        }

        if( $oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($consultas,['type' => $list, 'list' => $festejos, 'icon' => 'bx bxs-cake bx-xs', 'name' => 'Festejos mis colabs.', 'id' => 'aniv_colabs']);       
        }
        //cierra submenu

        if( $oUser->rol_id == 1 || $oUser->rol_id == 2 || $oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($lMenus,(object) ['type' => $list, 'list' =>$consultas, 'icon' => 'bx bx-sitemap bx-sm', 'name' => 'Consultas', 'id' => 'Consultas']);    
        }

        //cierra quinto grupo de menus

        //sexto grupo de menus ayuda
        array_push($lMenus,(object) ['type' => $list, 'list' => [['route' => route('tutorialUsuarios'), 'icon' => 'bx bxs-book bx-xs', 'name' => 'Tutorial solicitudes'], ['route' => route('tutorialLideres'), 'icon' => 'bx bxs-book bx-xs', 'name' => 'Tutorial aprobación']],'icon' => 'bx bx-help-circle bx-sm', 'name' => 'Ayuda', 'id' => 'ayuda']);

        //cierra sexto grupo de menus

        //septimo grupo de menus administración
        $administracion = [];

        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($administracion,['route' => route('directVacations'), 'icon' => 'bx bxs-star bx-xs', 'name' => 'Vac. directas']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($administracion,['route' => route('recoveredVacations_managment'), 'icon' => 'bx bxs-star bx-xs', 'name' => 'Reactivaciones']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($administracion,['route' => route('specialVacations'), 'icon' => 'bx bxs-star bx-xs', 'name' => 'Delegaciones']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($administracion,['route' => route('allVacations'), 'icon' => 'bx bxs-contact bx-xs', 'name' => 'Consulta estatus vacs.']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($administracion,['route' => route('mailLog'), 'icon' => 'bx bx-envelope bx-xs', 'name' => 'Bitacora errores emails']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($administracion,['route' => route('bitacoras'), 'icon' => 'bx bxs-archive bx-xs', 'name' => 'Bitacoras sistema']);    
        }

        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($lMenus,(object) ['type' => $list, 'list' =>$administracion, 'icon' => 'bx bxs-user-pin bx-sm', 'name' => 'Administración', 'id' => 'Administracion']);    
        }

        //cierra septimo grupo de menus

        //octavo grupo de menus configuración
        $configuracion = [];

        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($configuracion,['route' => route('index_user'), 'icon' => 'bx bxs-user bx-xs', 'name' => 'Usuarios']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($configuracion,['route' => route('assignArea'), 'icon' => 'bx bxs-grid bx-xs', 'name' => 'Áreas func.']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($configuracion,['route' => route('empVSArea_index'), 'icon' => 'bx bx-shape-square bx-xs', 'name' => 'Colabs. vs áreas func.']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($configuracion,['route' => route('jobVsOrgChartJob'), 'icon' => 'bx bxs-vector bx-xs', 'name' => 'Puestos vs áreas func.']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($configuracion,['route' => route('vacationPlans'), 'icon' => 'bx bxs-notepad bx-xs', 'name' => 'Plan vacaciones']);    
        }
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($configuracion,['route' => route('specialSeasons'), 'icon' => 'bx bx-calendar-exclamation bx-xs', 'name' => 'Temporadas especiales']);    
        }
        //submenu solicitudes especiales
        $solicitudes = [];

         if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($solicitudes,['route' => route('specialType'), 'icon' => 'bx bx-file bx-xs', 'name' => 'Tipos solicitud']);       
        }
 
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($solicitudes,['route' => route('SpecialTypeVsOrgChart'), 'icon' => 'bx bx-file bx-xs', 'name' => 'Asignación solicitud']);       
        }
 
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($configuracion,['type' => $list, 'list' => $solicitudes, 'icon' => 'bx bxs-archive bx-xs', 'name' => 'Solicitudes Especiales', 'id' => 'solic_esp']);       
        }
         //cierra submenu
        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($configuracion,['route' => route('configAuth'), 'icon' => 'bx bxs-archive bx-xs', 'name' => 'Aut. incidencias']);    
        }

        if($oUser->rol_id == 3 || $oUser->rol_id == 4){
            array_push($lMenus,(object) ['type' => $list, 'list' =>$configuracion, 'icon' => 'bx bx-cog bx-sm', 'name' => 'Configuración', 'id' => 'Configuracion']);    
        }

        array_push($lMenus, (object) ['type' => $element, 'route' => route('synchronize'), 'icon' => 'bx bx-sync bx-sm', 'name' => 'Sincronizar', 'id' => 'sync']);

        //cierra octavo grupo de menus
        
        /*prueba
        array_push($lMenus,(object) ['type' => $list, 'list' => [
            ['route' => route('tutorialUsuarios'), 'icon' => 'bx bxs-book bx-sm', 'name' => 'Tutorial solicitudes'],
            ['route' => route('tutorialLideres'), 'icon' => 'bx bxs-book bx-sm', 'name' => 'Tutorial aprobación'],
            ['type' => $list, 'list' => [
                ['route' => route('tutorialUsuarios'), 'icon' => 'bx bxs-book bx-sm', 'name' => 'Tutorial solicitudes'],
                ['route' => route('tutorialLideres'), 'icon' => 'bx bxs-book bx-sm', 'name' => 'Tutorial aprobación'],
                ],
                'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Tutoriales', 'id' => 'prueba'
            ],
            ],
                'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Tutoriales', 'id' => 'tutorial'
        ],);
        
        cierra prueba*/
        if(!$oUser->changed_password){
            $lMenus = [
                (object) ['type' => $element, 'route' => route('profile'), 'icon' => 'bx bxs-key bx-sm', 'name' => 'Cambiar contraseña']
            ];
        }
        
        $config = \App\Utils\Configuration::getConfigurations();
        if(in_array($oUser->id, $config->special_vacations_access)){
            array_push($lMenus, (object) ['type' => $element, 'route' => route('specialVacations'), 'icon' => 'bx bxs-star bx-sm', 'name' => 'Vac. Dir. General']);
        }

        $sMenu = "";
        foreach ($lMenus as $menu) {
            if ($menu == null) {
                continue;
            }
            if($menu->type == $element){
                $sMenu = $sMenu.Menu::createMenuElement($menu->route, $menu->icon, $menu->name);
            }else if($menu->type == $list){
                $sMenu = $sMenu.Menu::createListMenu($menu->id, $menu->list, $menu->name, $menu->icon);
            }
        }

        return $sMenu;
    }

    private static function createMenuElement($route, $icon, $name)
    {
        return '<li class="nav-item">
                    <a class="nav-link" href="'.$route.'" onclick="showPageWaiting()">
                        <i class="'.$icon.'"></i>
                        <span>'.$name.'</span>
                    </a>
                </li>';
    }

    private static function createListMenu($id, $list, $name, $icon){
        $str = '<li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#'.$id.'"
                        aria-expanded="true" aria-controls="'.$id.'">
                        <i class="'.$icon.'"></i>
                        <span>'.$name.'</span>
                    </a>
                    <div id="'.$id.'" class="collapse" aria-labelledby="headingPages">
                        <div class="py-2 collapse-inner rounded">';
        
        foreach($list as $l){

            if( array_key_exists('list',$l) ){
                $str = $str.Menu::createSubMenu($l['id'],$l['list'],$l['name'],$l['icon']);
            }else{
                if(!isset($l['size'])){
                    $str = $str.'<a style="word-wrap: break-word;" onclick="showPageWaiting()" class="collapse-item" href="'.$l['route'].'"><i class="'.$l['icon'].'"></i> '.$l['name'].'</a>';
                }else{
                    $str = $str.'<a style="word-wrap: break-word;" onclick="showPageWaiting()" class="collapse-item" href="'.$l['route'].'" style="font-size:'.$l['size'].'"><i class="'.$l['icon'].'"></i> '.$l['name'].'</a>';
                }
            }

        }
                    
        $str = $str.'</div></div></li>';

        return $str;
    }

    private static function createSubMenu($id, $list,$name, $icon){
  
        $str = '<ul class="navbar-nav" id="accordionSidebar">
                    <li class="nav-item">
                        <a class="nav-link collapsed" style="width:12rem" href="#" data-toggle="collapse" data-target="#'.$id.'"
                            aria-expanded="true" aria-controls="'.$id.'">
                            <i class="'.$icon.'"></i>
                            <span>'.$name.'</span>
                        </a>
                        <div id="'.$id.'" class="collapse" aria-labelledby="headingPages" >
                        <div class="py-2 collapse-inner rounded">';
        
        foreach($list as $l){

            if( array_key_exists('list',$l) ){
                $str = $str.Menu::createSubMenu($l['id'],$l['list'],$l['name'],$l['icon']);
            }else{
                if(!isset($l['size'])){
                    $str = $str.'<a style="word-wrap: break-word;" onclick="showPageWaiting()" class="collapse-item" href="'.$l['route'].'"><i class="'.$l['icon'].'"></i> '.$l['name'].'</a>';
                }else{
                    $str = $str.'<a style="word-wrap: break-word;" onclick="showPageWaiting()" class="collapse-item" href="'.$l['route'].'" style="font-size:'.$l['size'].'"><i class="'.$l['icon'].'"></i> '.$l['name'].'</a>';
                }
            }
                
            }
        
        $str = $str.'</div></div></li></ul>';

        return $str;
    }
}