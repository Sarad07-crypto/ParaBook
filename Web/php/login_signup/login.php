<?php
    require_once ROOT_PATH . '\Parabook\Web\php\google\g-config.php';
    require_once ROOT_PATH . '\Parabook\Web\php\facebook\f-config.php';
    require_once ROOT_PATH . '\Parabook\Web\php\google\core\controller.class.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="/Web/css/login.css" />
    <!-- Boxicons CSS -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <div class="container">
        <div class="sub-container">
            <div class="login-wrapper-cols">

                <h1>Welcome to</h1>
                <h1>ParaBook</h1>
                <div class="img">
                    <img id="slideshow" src="/Assets/Images/01.png" width="500px" height="600px" />
                </div>
            </div>
            <div class="login-wrapper-cols">
                <div class="login-form">
                    <h3>Log In</h3>
                    <p>Welcome back! Please enter your credentials</p>
                    <form class="form" method="post" action="/logincheck">
                        <div class="input-box-wrapper">
                            <div class="input-box">
                                <input type="text" name="email" placeholder="Email"
                                    pattern="^[^\s@]{3,}@[^\s@]{3,}\.[^\s@]{2,}$" required />
                                <span class="error-icon">
                                    <i class='bx bx-error-circle'></i>
                                </span>
                                <span class="check-icon">
                                    <i class='bx bx-check-circle'></i>
                                </span>
                            </div>
                        </div>
                        <div class="input-box">
                            <input type="password" minlength="8" name="password" placeholder="Password" class="pwd"
                                required />
                            <i class="bx bx-hide eye-icon"></i>
                        </div>
                        <div class="check-box">
                            <label>
                                <input type="checkbox" required class="remember-checkbox" /> Remember me
                            </label>
                            <a href="#">Forgot Password?</a>
                        </div>
                        <div class="submit-box">
                            <input type="submit" value="Log In" name="loginfrm" />
                        </div>
                    </form>
                    <div class="separator">
                        <span>or continue with</span>
                    </div>
                    <div class="alt-signup">
                        <a href="/auth/google?provider=google" class="google-btn">Google</a>
                        <a href="<?php echo $fullURL ?>" class="facebook-btn">Facebook</a>
                    </div>
                    <div class="no-account">
                        <p>Don't have an account?</p>
                        <a href="/signup">Sign up</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="foot-para">
            <p>
                ParaBook is a platform that connects paragliding companies with
                passengers, offering a remote booking experience. It empowers
                passengers with the flexibility to compare and choose the best
                paragliding company based on their preferences, while enabling service
                providers to showcase their offerings and reach a wider audience
                efficiently.
            </p>

            <p style="margin-top: 10px">© All copyright reserved by ParaBook</p>
        </div>
        <div class="foot-img">
            <img src="/Assets/Icons/cloud-01.png" />
        </div>
    </footer>
    <script src="/Web/scripts/index.js"></script>
</body>

</html>