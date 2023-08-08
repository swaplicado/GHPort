<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Portal GH</title>

    <!-- Custom fonts for this template-->
    <link rel="icon" href="{{ asset('img/aeth_logo.png') }}" type="image/ico">
    <link href="{{ asset('principal/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <!-- Custom styles for this template-->
    <link href="{{ asset('boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset("datatables/app.css") }}">
    <link rel="stylesheet" href="{{ asset("datatables/datatables.css") }}">
    @yield('headStyles')

    <!-- Head javaScript -->
    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('principal/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('principal/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="{{ asset('principal/jquery-easing/jquery.easing.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vue/vue.js') }}"></script>
    <script src="{{ asset('moment/moment.js')}}"></script>
    <script src="{{ asset('moment/moment-with-locales.js')}}"></script>
    <script src="{{ asset('datatables/dataTables.js')}}"></script>
    <script src="{{ asset('sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('myApp/Utils/SDateUtils.js') }}"></script>
    <script>
        function GlobalDataNotification(){
            this.lNotifications = <?php echo json_encode(session()->get('lNotifications')) ?>;
            this.numberOfNotifications = <?php echo json_encode(session()->get('notificationsToSee')) ?>;
            this.notifications_cleanPendetNotificationRoute = <?php echo json_encode(route('notifications_cleanPendetNotification')) ?>;
            this.notifications_getNotificationsRoute = <?php echo json_encode(route('notifications_getNotifications')) ?>;
            this.notifications_revisedNotificationRoute = <?php echo json_encode(route('notifications_revisedNotification')) ?>;
        }

        var oGlobalDataNotification = new GlobalDataNotification();
    </script>
    @yield('headJs')

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        @include('layouts.aside')

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                @include('layouts.topbar')

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @include('delegations.modal_select_delegations')
                    <!-- Contenido de la pagina -->
                    @yield('content')

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            @include('layouts.footer')

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- ejemplod de Modal -->
    {{--
        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
            Logout
        </a> 
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" href="login.html">Logout</a>
                    </div>
                </div>
            </div>
        </div> 
    --}}

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('myApp/gui/SGui.js') }}"></script>
    <script type="text/javascript" src="{{ asset('axios/axios.min.js') }}"></script>
    <script>
        function showPageWaiting(){
            SGui.showWaiting(120000);
        }
    </script>
    <script type="text/javascript" src="{{ asset('myApp/notifications/vue_notifications.js') }}"></script>
    @yield('scripts')
    <script> axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');</script>
</body>

</html>