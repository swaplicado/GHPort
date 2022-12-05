<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <div class="sidebar-brand d-flex align-items-center justify-content-center">
        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
            <div class="sidebar-brand-text">Portal GH</div>
        </a>
        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </div>
    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('home') }}" onclick="showPageWaiting()">
        <i class="bx bxs-home bx-sm"></i>
        <span>HOME</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    {{-- <div class="sidebar-heading">
        Interface
    </div> --}}

    <!-- Nav Item - Pages Collapse Menu -->
    {{-- <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fw fa-folder"></i>
            <span>Pages</span>
        </a>
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Login</a>
                <a class="collapse-item" href="#">Register</a>
                <a class="collapse-item" href="#">Forgot Password</a>
                <a class="collapse-item" href="#">404 Page</a>
                <a class="collapse-item" href="#">Blank Page</a>
            </div>
        </div>
    </li> --}}

    <!-- Nav Item - Tables -->
    {{-- <li class="nav-item">
        <a class="nav-link" href="{{ route('orgChart') }}">
            <i class="fas fa-fw fa-table"></i>
            <span>Organigrama</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('assignArea') }}">
            <i class="fas fa-fw fa-table"></i>
            <span>Areas funcionales</span></a>
    </li> --}}
    {!! session()->has('menu') ? session('menu') : "" !!}

</ul>
<!-- End of Sidebar -->