<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Navbar -->
    <img src="{{asset('img/logo HD.png')}}"  width="200" height="100"/>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item mx-1">
            @if(Session::get('tot_delegations') > 0)
                <a class="nav-link" href="#" role="button" onclick="$('#modal_select_delegation').modal('show');">
                    <i class="fa fa-object-group fa-lg"></i>
                        <span class="badge badge-danger badge-counter">{{ Session::get('tot_delegations') }}</span>
                </a>
            @else
                <a class="nav-link" href="#">
                    <i class="fa fa-object-group fa-lg"></i>
                        <span class="badge badge-danger badge-counter"></span>
                </a>
            @endif
        </li>
            
        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1" id="navNotifications">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-on:click="cleanNumberOfNotifications();">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Alerts -->
                <span class="badge badge-danger badge-counter">@{{numberOfNotifications}}</span>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Centro de notificaciones
                </h6>
                <!-- Ejemplo de notificacion -->
                <template v-for="oNotify in lNotifications">
                    <a class="dropdown-item d-flex align-items-center" :href="oNotify.link">
                        <div class="mr-3">
                            <div class="icon-circle bg-primary">
                                <i v-bind:class="[oNotify.icon]"></i>
                            </div>
                        </div>
                        <div>
                            <!-- <div class="small text-gray-500">December 12, 2019</div> -->
                            <span class="font-weight-bold">@{{oNotify.text}}</span>
                        </div>
                    </a>
                </template>

                <!-- <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 12, 2019</div>
                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                    </div>
                </a> -->
                <!-- <a class="dropdown-item text-center small text-gray-500" href="#">Ver todas las notificaciones</a> -->
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                @if(!Session::get('is_delegation'))
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                        {{ Auth::user()->username }}
                    </span>
                @else
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                        {{ Auth::user()->username }} 
                        <br> 
                        delegación de 
                        <br> 
                        {{\App\Utils\delegationUtils::getUsernameUser()}}
                    </span>
                @endif
                @if(!is_null(Auth::user()->getPhoto()))
                    <img class="img-profile rounded-circle" src="data:image/jpg;base64,{{ Auth::user()->getPhoto() }}">
                @else
                    <img class="img-profile rounded-circle" src="{{ asset('img/avatar/profile2.png') }}">
                @endif
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{route('profile')}}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Perfil
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" target="_blank" href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:portalgh">
                    <i class="fas fa-book fa-sm fa-fw mr-2 text-gray-400"></i>
                    Manual de usuario
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('logout') }}">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Salir
                </a>
            </div>
        </li>

    </ul>

</nav>
<!-- End of Topbar -->