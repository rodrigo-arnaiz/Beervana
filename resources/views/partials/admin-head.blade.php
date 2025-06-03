<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beervana</title>

<!-- Bootstrap, FontAwesome, Google Fonts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
            font-family: 'Poppins', sans-serif;
            background: #f7f5f2;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        .sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #343a40;
            color: #F2D797;
            position: fixed;
            height: 100vh;
            z-index: 999;
            transition: all 0.3s;
        }

        /* Estilo personalizado para el botón del sidebar */
        .btn-custom-sidebar {
            background-color: #D09F58 !important; /* Color dorado */
            border-color: #D09F58 !important;
            color: white !important;
        }

        .btn-custom-sidebar:hover {
            background-color: #B3894D !important; /* Color dorado oscuro al hover */
            border-color: #B3894D !important;
        }
        .sidebar .sidebar-header {
            padding: 20px;
            background: #212529;
        }

        .sidebar ul li a {
            padding: 10px 20px;
            display: block;
            color: #F2D797;
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li.active > a {
            color: #F2D797!important;
            background: #D09F58;
        }

        #content {
            width: 100%;
            min-height: 100vh;
            padding-left: 250px;
            transition: all 0.3s;
            position: relative;
        }

        /* Toggle styles */
        .sidebar.active {
            margin-left: -250px;
        }

        #content.active {
            padding-left: 0;
        }

/*         /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }

            .sidebar.active {
                margin-left: 0;
            }

            #content {
                padding-left: 0;
            }

            #content.active {
                padding-left: 250px;
            }
        } */
        
    </style>