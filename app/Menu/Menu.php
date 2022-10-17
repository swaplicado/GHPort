<?php

namespace App\Menu;

class Menu {

    public static function createMenu($oUser = null)
    {
        if ($oUser == null) {
            return "";
        }

        switch ($oUser->rol_id) {
            //Estándar
            case '1':
                $lMenus = [
                    (object) ['route' => route('orgChart'), 'icon' => 'bx bx-sitemap bx-sm', 'name' => 'Organigrama'],
                    (object) ['route' => route('myVacations'), 'icon' => 'bx bx-calendar bx-sm', 'name' => 'Mis vacaciones'],
                ];
                break;

            //Jefe
            case '2':
                $lMenus = [
                    (object) ['route' => route('orgChart'), 'icon' => 'bx bx-sitemap bx-sm', 'name' => 'Organigrama'],
                    (object) ['route' => route('myEmplVacations'), 'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Vac. mis colaboradores'],
                    (object) ['route' => route('allEmplVacations'), 'icon' => 'bx bxs-group bx-sm', 'name' => 'Vac. colaboradores'],
                    (object) ['route' => route('myVacations'), 'icon' => 'bx bx-calendar bx-sm', 'name' => 'Mis vacaciones'],
                    (object) ['route' => route('requestVacations'), 'icon' => 'bx bxs-archive bx-sm', 'name' => 'Solicitudes vacaciones'],
                ];
                break;

            //GH
            case '3':
                $lMenus = [
                    (object) ['route' => route('orgChart'), 'icon' => 'bx bx-sitemap bx-sm', 'name' => 'Organigrama'],
                    (object) ['route' => route('assignArea'), 'icon' => 'bx bxs-grid bx-sm', 'name' => 'Areas funcionales'],
                    (object) ['route' => route('myEmplVacations'), 'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Vac. mis colaboradores'],
                    (object) ['route' => route('allEmplVacations'), 'icon' => 'bx bxs-group bx-sm', 'name' => 'Vac. colaboradores'],
                    (object) ['route' => route('myVacations'), 'icon' => 'bx bx-calendar bx-sm', 'name' => 'Mis vacaciones'],
                ];
                break;

            //Administrador Sistema
            case '4':
                $lMenus = [
                    (object) ['route' => route('orgChart'), 'icon' => 'bx bx-sitemap bx-sm', 'name' => 'Organigrama'],
                    (object) ['route' => route('assignArea'), 'icon' => 'bx bxs-grid bx-sm', 'name' => 'Areas funcionales'],
                    (object) ['route' => route('myEmplVacations'), 'icon' => 'bx bxs-user-detail bx-sm', 'name' => 'Vac. mis colaboradores'],
                    (object) ['route' => route('allEmplVacations'), 'icon' => 'bx bxs-group bx-sm', 'name' => 'Vac. colaboradores'],
                    (object) ['route' => route('myVacations'), 'icon' => 'bx bx-calendar bx-sm', 'name' => 'Mis vacaciones'],
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
            $sMenu = $sMenu.Menu::createMenuElement($menu->route, $menu->icon, $menu->name);
        }

        return $sMenu;
    }

    private static function createMenuElement($route, $icon, $name)
    {
        return '<li class="nav-item">
                    <a class="nav-link" href="'.$route.'">
                        <i class="'.$icon.'"></i>
                        <span>'.$name.'</span>
                    </a>
                </li>';
    }
}