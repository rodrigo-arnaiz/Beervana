<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.admin-head')
</head>
<body>
    <div class="wrapper">
        @include('partials.sidebar')
        <div id="content">
            @include('partials.nav')
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>
    @include('partials.admin-scripts')
</body>
</html>
