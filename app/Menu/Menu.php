<?php

namespace App\Menu;

class Menu {
    public static function createMenu($oUser = null)
    {
        $element = 1;
        $list =2;
        if ($oUser == null) {
            return "";
        }

        switch ($oUser->rol_id) {
            //Estándar
            case '1':
                $lMenus = [
                    (object) ['type' => $element, 'route' => route('orgChart'), 'icon' => 'bx bx-sitemap bx-sm', 'name' => 'Organigrama'],
                    (object) ['type' => $element, 'route' => route('myVacations'), 'icon' => 'bx bx-calendar bx-sm', 'name' => 'Mis vacaciones'],
                    (object) ['type' => $element, 'route' => route('mailLog'), 'icon' => 'bx bx-envelope bx-sm', 'name' => 'Registro e-mails'],
                ];
                break;

            //Jefe
            case '2':
                $lMenus = [
                    (object) ['type' => $element, 'route' => route('orgChart'), 'icon' => 'bx bx-sitemap bx-sm', 'name' => 'Organigrama'],
                    (object) ['type' => $list, 'list' => [
                                                    ['route' => route('myEmplVacations'), 'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Mis colaboradores'],
                                                    ['route' => route('allEmplVacations'), 'icon' => 'bx bxs-group bx-sm', 'name' => 'Todos mis colaboradores']
                                                ],
                                                'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Vac. colaboradores', 'id' => 'vac_colabs'
                            ],
                    (object) ['type' => $element, 'route' => route('myVacations'), 'icon' => 'bx bx-calendar bx-sm', 'name' => 'Mis vacaciones'],
                    (object) ['type' => $element, 'route' => route('requestVacations'), 'icon' => 'bx bxs-archive bx-sm', 'name' => 'Solicitudes vacaciones'],
                    (object) ['type' => $element, 'route' => route('mailLog'), 'icon' => 'bx bx-envelope bx-sm', 'name' => 'Registro e-mails'],
                ];
                break;

            //GH
            case '3':
                $lMenus = [
                    (object) ['type' => $element, 'route' => route('orgChart'), 'icon' => 'bx bx-sitemap bx-sm', 'name' => 'Organigrama'],
                    (object) ['type' => $element, 'route' => route('assignArea'), 'icon' => 'bx bxs-grid bx-sm', 'name' => 'Areas funcionales'],
                    (object) ['type' => $list, 'list' => [
                                                    ['route' => route('myEmplVacations'), 'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Mis colaboradores'],
                                                    ['route' => route('allEmplVacations'), 'icon' => 'bx bxs-group bx-sm', 'name' => 'Todos mis colaboradores']
                                                ],
                                                'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Vac. colaboradores', 'id' => 'vac_colabs'
                            ],
                    (object) ['type' => $element, 'route' => route('myVacations'), 'icon' => 'bx bx-calendar bx-sm', 'name' => 'Mis vacaciones'],
                    (object) ['type' => $element, 'route' => route('allVacations'), 'icon' => 'bx bxs-contact bx-sm', 'name' => 'Reporte Vacaciones'],
                    (object) ['type' => $element, 'route' => route('mailLog'), 'icon' => 'bx bx-envelope bx-sm', 'name' => 'Registro e-mails'],
                ];
                break;

            //Administrador Sistema
            case '4':
                $lMenus = [
                    (object) ['type' => $element, 'route' => route('orgChart'), 'icon' => 'bx bx-sitemap bx-sm', 'name' => 'Organigrama'],
                    (object) ['type' => $element, 'route' => route('assignArea'), 'icon' => 'bx bxs-grid bx-sm', 'name' => 'Areas funcionales'],
                    (object) ['type' => $list, 'list' => [
                                                    ['route' => route('myEmplVacations'), 'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Mis colaboradores'],
                                                    ['route' => route('allEmplVacations'), 'icon' => 'bx bxs-group bx-sm', 'name' => 'Todos mis colaboradores']
                                                ],
                                                'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Vac. colaboradores', 'id' => 'vac_colabs'
                             ],
                    (object) ['type' => $element, 'route' => route('myVacations'), 'icon' => 'bx bx-calendar bx-sm', 'name' => 'Mis vacaciones'],
                    (object) ['type' => $element, 'route' => route('allVacations'), 'icon' => 'bx bxs-contact bx-sm', 'name' => 'Reporte Vacaciones'],
                    (object) ['type' => $element, 'route' => route('mailLog'), 'icon' => 'bx bx-envelope bx-sm', 'name' => 'Registro e-mails'],
                    (object) ['type' => $element, 'route' => route('vacationPlans'), 'icon' => 'bx bxs-notepad bx-sm', 'name' => 'Plan vacaciones'],
                    (object) ['type' => $element, 'route' => route('bitacoras'), 'icon' => 'bx bxs-archive bx-sm', 'name' => 'Bitacoras'],
                    (object) ['type' => $element, 'route' => route('specialSeasonTypes'), 'icon' => 'bx bx-calendar-exclamation bx-sm', 'name' => 'Temporadas especiales'],
                ];
                break;
            
            default:
                $lMenus = [];
                break;
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
                    <div id="'.$id.'" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                        <div class="py-2 collapse-inner rounded">';
        
        foreach($list as $l){
            $str = $str.'<a onclick="showPageWaiting()" class="collapse-item" href="'.$l['route'].'"><i class="'.$l['icon'].'"></i>'.$l['name'].'</a>';
        }
                    
        $str = $str.'</div></div></li>';

        return $str;
    }
}