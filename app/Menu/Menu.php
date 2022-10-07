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
                ];
                break;

            //Administrador
            case '2':
                $lMenus = [
                ];
                break;

            //GH
            case '3':
                $lMenus = [
                ];
                break;

            //Administrador Sistema
            case '4':
                $lMenus = [
                    (object) ['route' => route('orgChart'), 'icon' => 'bx bx-grid-alt', 'name' => 'Organigrama'],
                    (object) ['route' => route('assignArea'), 'icon' => 'bx bx-grid-alt', 'name' => 'Areas funcionales'],
                    (object) ['route' => route('myEmplVacations'), 'icon' => 'bx bx-grid-alt', 'name' => 'Vac. mis colaboradores'],
                    (object) ['route' => route('allEmplVacations'), 'icon' => 'bx bx-grid-alt', 'name' => 'Vac. colaboradores'],
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
                        <i class="fas fa-fw fa-table"></i>
                        <span>'.$name.'</span>
                    </a>
                </li>';
    }
}