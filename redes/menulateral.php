<?php

  // TRAE EL PERFIL DEL USUARIO EN SISTEMA ARTISTAS
  $userartistas = "SELECT * FROM artistas.usuarios WHERE cuitUsuario=$cuit";
  $userart = mysqli_query($condb,$userartistas);
  $rowuser=mysqli_fetch_array($userart);

  $tipouserart = $rowuser['perfilUsuario'];

  $organismo = $_SESSION["tipoOrganismo"]." ".$_SESSION["nombreOrganismo"];

?>
  <nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
    <div class="container-fluid">
      <!-- Toggler -->
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- Brand -->
      <a class="navbar-brand pt-0" href="./">
        <img src="../images/<?php echo $_SESSION["logoOrganismo"]; ?>" class="navbar-brand-img" alt="<?php echo $organismo ?>">
      </a>
		  <span class="badge badge-dark">REDES MUNICIPALES</span>

      <!-- Collapse -->
      <div class="collapse navbar-collapse" id="sidenav-collapse-main">
        <!-- Collapse header -->
        <div class="navbar-collapse-header d-md-none">
          <div class="row">
            <div class="col-6 collapse-brand">
              <a href="index.php">
                <img src="../images/<?php echo $_SESSION["logoOrganismo"]; ?>">
              </a>
            </div>
            <div class="col-6 collapse-close">
              <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                <span></span>
                <span></span>
              </button>
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="./">
              <i class="ni ni-tv-2 text-primary"></i> Inicio
            </a>
          </li>
          <li class="nav-item">
            	<a class="nav-link" href="../gestion.php">
              <i class="ni ni-bold-left text-primary"></i> Volver al Menú
            </a>
          </li>
				</ul>	
      </div>
    </div>
  </nav>