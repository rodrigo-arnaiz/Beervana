<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beervana Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/estilos.css') }}">
<style>
         body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            margin: 0;
            background-image: url('assets/beervana_background.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .sidebar {
            width: 300px;
            min-height: 100vh;
            background-color: #343a40;
        }

.sidebar .sidebar-header a{
    color: #F2D797; 
    text-decoration: none;
}

.sidebar .sidebar-header a:hover {
    color: #fff; /* o cualquier color al hacer hover */
}
        .sidebar .nav-link {
            color: #F2D797;
        }

        .sidebar .nav-link:hover {
            background-color: #D09F58;
            color: #F2D797;
        }

        .navbar-title {
            width: 300px;
        }

        .navbar-title .inner {
            background-color: #343a40;
            color: white;
        }
</style>
