<?php
  $perfil = $_SESSION["perfil"];
?>

    <!-- User -->
        <ul class="navbar-nav align-items-center d-none d-md-flex">
          <li class="nav-item dropdown">
            <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			        <div class="media align-items-center">
                <div class="media-body ml-2 pt-2 d-none d-lg-block">
					        <span class="mb-0 text-sm  font-weight-bold">Hola, <?php echo $_SESSION["nombre"]; ?></span>
                </div>
              </div>
            </a>

            <!-- NAVEGACION DE ADMINISTRADORES PERSONAL -->			
            <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
              <div class=" dropdown-header noti-title">
				        <h6 class="text-overflow m-0">Bienvenido!</h6>
              </div>
              <?php if($perfil > 2) { ?>	              
                <a href="../configuracion.php" class="dropdown-item">
                  <i class="ni ni-settings"></i>
                  <span>Configuración</span>
                </a>
                <?php } ?>
                <a href="../datospersonales.php" class="dropdown-item">
                  <i class="ni ni-single-02"></i>
                  <span>Datos Personales</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="../logout.php" class="dropdown-item">
                  <i class="ni ni-bold-left"></i>
                  <span>Salir</span>
                </a>
            </div>					
          </li>
        </ul>