
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Dashboard - ARVEAATTFI</title>
    <meta name="description" content="ARVEAATTFI">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Nunito.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/datatables.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.min.css">
    
</head>
<body style="background: url(assets/img/wall.jpg) center / auto no-repeat;filter: blur(0px);">
    <nav class="navbar navbar-expand bg-white shadow mb-4 topbar">
        <div class="container-fluid">
            <h3 class="text-dark mb-0">ARVEAATTFI</h3>
        </div>
    </nav>
    <section class="position-relative py-4 py-xl-5 mt-5">
        <div class="container mt-5">
            <div class="row d-flex justify-content-center">
                <div class="col-md-6 col-xl-4">
                    <div class="card">
                        <div class="card-body d-flex flex-column align-items-center" style="height: 500px;">
                            <div class="bs-icon-xl bs-icon-circle bs-icon-primary bs-icon my-4">
                                <img src="assets/img/images.png" width="120em">
                            </div>
                            <form id="sign-in-form">
                                <div class="form-floating mb-3">
                                    <input class="form-control" type="text" placeholder="username" name="username" value="<?php echo isset($_COOKIE['username']) ? $_COOKIE['username'] : ''; ?>">
                                    <label class="form-label" for="floatingInput">Username</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input class="form-control" type="password" placeholder="password" name="password" value="<?php echo isset($_COOKIE['password']) ? $_COOKIE['password'] : ''; ?>">
                                    <label class="form-label" for="floatingInput">Password</label>
                                </div>
                                <div class="d-flex justify-content-center form-check mb-4">
                                    <input type="checkbox" class="form-check-input me-2" id="form2Example33" name="remember" <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="form2Example33">Remember Me</label>
                                </div>
                                
                                <button class="btn btn-primary btn-block mb-4 w-100" type="submit">Sign In</button>
        
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/datatables.min.js"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/login.js"></script>
</body>

</html>