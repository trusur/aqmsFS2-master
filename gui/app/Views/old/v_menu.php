<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-navy navbar-dark">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-user-circle"></i>
                <span class="badge badge-warning navbar-badge d-none" style="font-size:.8rem" id="notifications_count"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notification_details">
                <div class="dropdown-divider"></div>
                <?php if ($__session->get("loggedin")) : ?>
                    <a href="<?= base_url(); ?>/logout" class="dropdown-item"><i class="fas fa-key mr-2"></i> Logout</a>
                <?php else : ?>
                    <a href="<?= base_url(); ?>/login" class="dropdown-item"><i class="fas fa-key mr-2"></i> Login</a>
                <?php endif ?>
            </div>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary navbar-navy elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
        <img src="<?= base_url(); ?>/dist/img/egateway_white.png" alt="eGateway" class="brand-image elevation-3">
        <span class="brand-text font-weight-light">&nbsp;</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <?php if ($__session->get("loggedin")) : ?>
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info nav-link w-100">
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                            <li class="nav-item has-treeview menu-open">
                                <a href="#" class="nav-link">
                                    <?php if (file_exists("dist/upload/users_photo/" . @$__users["photo"]) && @$__users["photo"] != "") : ?>
                                        <img src="<?= base_url(); ?>/dist/upload/users_photo/<?= @$__users["photo"]; ?>" height="150" class="rounded">
                                    <?php else : ?>
                                        <i style="font-size:30px;float:left;margin-right:10px;" class="nav-icon far fa-user-circle"></i>
                                    <?php endif ?>
                                    <p><?= $__session->get("user")->name; ?></p>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif ?>


        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                <?php if (isset($__mainmenu) && count($__mainmenu) > 0) : ?>
                    <?php foreach ($__mainmenu as $mainmenu) : ?>
                        <li id="menu_<?= $mainmenu->id; ?>" class="nav-item has-treeview">
                            <a <?= (!@$__submenu[$mainmenu->id] != "") ? "href='" . base_url() . "/" . $mainmenu->url . "'" : "href='#'"; ?> class="nav-link">
                                <i class="nav-icon <?= $mainmenu->icon; ?>"></i>
                                <p>
                                    <?= $mainmenu->name; ?>
                                    <?php if (isset($__submenu[$mainmenu->id])) : ?> <i class="right fas fa-angle-left"></i> <?php endif ?>
                                </p>
                            </a>
                            <?php if (isset($__submenu[$mainmenu->id])) : ?>
                                <ul class="nav nav-treeview">
                                    <?php foreach ($__submenu[$mainmenu->id] as $submenu) : ?>
                                        <?php if (in_array($submenu->id, $__menu_ids)) : ?>
                                            <script>
                                                document.getElementById("menu_<?= $mainmenu->id; ?>").classList.add("menu-open");
                                            </script>
                                        <?php endif ?>
                                        <li class="nav-item">
                                            <a href="<?= base_url(); ?>/<?= $submenu->url; ?>" class="nav-link <?= (in_array($submenu->id, $__menu_ids)) ? "active" : ""; ?>" style="padding-left:50px;">
                                                <i class="<?= $submenu->icon; ?> nav-icon"></i>
                                                <p><?= $submenu->name; ?></p>
                                            </a>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            <?php endif ?>
                        </li>
                    <?php endforeach ?>
                <?php endif ?>
                <?php if ($__session->get("loggedin")) : ?>
                    <li class="nav-item has-treeview">
                        <a href="<?= base_url(); ?>/logout" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i> LOGOUT
                        </a>
                    </li>
                <?php endif ?>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><?= $__modulename; ?>
                    </h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->