<!-- includes/header.php -->
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth_check.php'; // Proteger todas las páginas que usen este header
require_once 'conexion.php';   // Para obtener datos de la BD como la foto

// Variables para los datos del usuario
$ruta_foto_usuario = 'img/fotos/empleados/usuario01.png'; // Imagen por defecto
$nombre_usuario_display = 'Usuario';
$tipo_usuario_actual = null; // Para determinar si es Admin

if (isset($_SESSION['idusuario'])) {
    if (isset($_SESSION['nombre_usuario'])) {
        $nombre_usuario_display = htmlspecialchars($_SESSION['nombre_usuario']);
    }

    if (isset($_SESSION['idemp']) && $pdo) {
        try {
            $stmt_foto = $pdo->prepare("SELECT rutafoto FROM empleado WHERE idempleado = :idemp AND activo = 1");
            $stmt_foto->bindParam(':idemp', $_SESSION['idemp']);
            $stmt_foto->execute();
            $empleado_info = $stmt_foto->fetch(PDO::FETCH_ASSOC);
            if ($empleado_info && !empty($empleado_info['rutafoto'])) {
                // rutafoto guarda la ruta desde la raíz, ej: img/fotos/empleados/usuario01.png
                // Como header.php está en includes/, necesitamos prefijar con ../
               // $ruta_foto_usuario = '../' . htmlspecialchars($empleado_info['rutafoto']);
                $ruta_foto_usuario = htmlspecialchars($empleado_info['rutafoto']);
                //echo $ruta_foto_usuario; // Para depuración, puedes eliminarlo después
            }
        } catch (PDOException $e) {
            error_log("Error al obtener rutafoto: " . $e->getMessage());
            // Se mantiene la imagen por defecto
        }
    }

    if (isset($_SESSION['tipo_usuario'])) {
        $tipo_usuario_actual = $_SESSION['tipo_usuario'];
       
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicons: Ajustar ruta y nombre de archivo si es diferente -->
     <!-- Favicon Simplificado -->
    <link rel="icon" type="image/png" href="../img/favicon.png" sizes="32x32">
    <!-- <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon.png"/>
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon.png"/>
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon.png"/> -->
   
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Intranet-AMPARA'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- DataTables CSS (si se sigue usando globalmente) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/estilo.css">
   
    <!-- <link rel="stylesheet" href="css/empleados.css"> -->
    <style>
        /* Estilos adicionales para el header si son necesarios */
        body {
            padding-top: 56px; /* Ajuste para el navbar fijo */
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            object-fit: cover;
        }
        .main-content {
            padding-top: 1rem; /* Espacio adicional después del header */
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="img/logo_0.png" alt="Logo" style="height: 50px; margin-right: 10px;">
               AMPARA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavPrincipal" aria-controls="navbarNavPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavPrincipal">
                <!-- Menú Principal (Izquierda) -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="liquidaciones.php">Liquidaciones</a>
                    </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCalendario" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Calendario
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownCalendario">
                            <li><a class="dropdown-item" href="#">Equipo</a></li>
                            <li><a class="dropdown-item" href="#">Individual</a></li>
                            <li><a class="dropdown-item" href="#">Gantt</a></li>
                            <li><a class="dropdown-item" href="#">Normativo</a></li>
                            <li><a class="dropdown-item" href="#"><b>Regulatorio</b></a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReportes" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Reportes
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownReportes">
                             <li><a class="dropdown-item" href="reporte_planificacion_liquidacion.php">Planificación vs. Liquidación</a></li>
                            <li><a class="dropdown-item" href="reporte_progreso_colaboradores.php">General  Colaboradores</a></li>
                            <li><a class="dropdown-item" href="reporte_participacion_planificacion.php">Participación por Plan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Otros General</a></li>
                            <li><a class="dropdown-item" href="#">Individual</a></li>
                            <li><a class="dropdown-item" href="#">Por Cliente</a></li>
                        </ul>
                    </li>
                   
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownNoticias" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Noticias
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownNoticias">
                            <li><a class="dropdown-item" href="#">Alerta Normativa</a></li>
                            <li><a class="dropdown-item" href="#">Boletín Informativo</a></li>
                            <li><a class="dropdown-item" href="#">Comunicados</a></li>
                        </ul>
                    </li>
                      <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAprendizaje" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Aprendizaje
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownAprendizaje">
                            <li><a class="dropdown-item" href="#">Base Normativa</a></li>
                            <li><a class="dropdown-item" href="#">Carpetas Clientes</a></li>
                            <li><a class="dropdown-item" href="#">Expo Temática</a></li>
                        </ul>
                    </li>
                    <?php if ($tipo_usuario_actual == 1): // Solo para Admin ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdministrativo" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Administrativo
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdministrativo">
                            <li><a class="dropdown-item" href="usuarios.php">Usuarios</a></li>
                            <li><a class="dropdown-item" href="clientes.php">Clientes</a></li>
                            <li><a class="dropdown-item" href="empleados.php">Colaboradores</a></li>
                            <li><a class="dropdown-item" href="contratos_clientes.php">Contrato Clientes</a></li>
                             <li><a class="dropdown-item" href="contratoColaboradores.php">Contrato Colaboradores</a></li>
                            <li><a class="dropdown-item" href="empleados.php"><hr></a></li>
                            <li><a class="dropdown-item" href="temas.php">Temas</a></li>
                            <li><a class="dropdown-item" href="#">Anuncios</a></li>
                            <li><a class="dropdown-item" href="#">Distribución Temática</a></li>
                            <li><a class="dropdown-item" href="planificaciones.php">Planificacion</a></li>
                        </ul>
                    </li>
                    
                    <?php endif;?>
                </ul>

                <!-- Zona de Usuario (Derecha) -->
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['idusuario'])): echo $_SESSION['idemp'];?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img  src="<?php echo $ruta_foto_usuario; ?>" alt="Foto de perfil" class="rounded-circle me-2 user-avatar"  style="height: 50px; width: 50px;">
                                <?php echo $nombre_usuario_display; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                                <li><a class="dropdown-item" href="#" id="enlacePerfilUsuario">Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../login.php">Iniciar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Contenedor principal para empujar el contenido debajo del navbar fijo -->

    <!-- Modal Perfil de Usuario -->
    <div class="modal fade" id="modalPerfilUsuario" tabindex="-1" aria-labelledby="modalPerfilUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalPerfilUsuarioLabel"><i class="fas fa-user-circle me-2"></i>Perfil de Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loadingPerfil" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                    <div id="contentPerfil" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img id="perfilFoto" src="" alt="Foto de perfil" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                                <h4 id="perfilNombreCorto" class="text-primary"></h4>
                                <p id="perfilCargo" class="text-muted"></p>
                            </div>
                            <div class="col-md-8">
                                <h5>Datos Personales</h5>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="fw-bold" style="width: 30%;">Nombre Completo:</td>
                                        <td id="perfilNombreCompleto"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">DNI:</td>
                                        <td id="perfilDni"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Correo Personal:</td>
                                        <td id="perfilCorreoPersonal"></td>
                                    </tr>
                                </table>
                                <hr>
                                <h5>Datos Corporativos</h5>
                                <table class="table table-sm table-borderless">
                                     <tr>
                                        <td class="fw-bold" style="width: 30%;">Usuario:</td>
                                        <td id="perfilUsername"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Correo Corporativo:</td>
                                        <td id="perfilCorreoCorp"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Área:</td>
                                        <td id="perfilArea"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid main-content">
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3">
                <?php echo htmlspecialchars($_SESSION['mensaje_exito']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3">
                <?php echo htmlspecialchars($_SESSION['mensaje_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['mensaje_error']); ?>
        <?php endif; ?>
        <!-- El contenido de la página específica se cargará aquí -->
    </div>
