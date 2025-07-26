<?php
    require 'avatar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<style>
body {
    display: block !important;
    position: static !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: auto !important;
}
</style>

<body>

    <?php
            require('partials/header.php');
            $accType = $_SESSION['acc_type'] ?? 'passenger';
            if ($accType === 'company') {
                require('partials/nav_C.php');
                require('addServices.php');
            } else {
                require('partials/nav_P.php');
                require('servicePassenger.php');
            }
        ?>

    <?php require('partials/footer.php'); ?>
    <script>
    const userAccountType = '<?php echo $accType; ?>';

    if (window.location.hash && window.location.hash === '#_=_') {
        // Remove hash without reloading
        history.replaceState(null, null, window.location.href.split('#')[0]);
    }

    $(document).on("click", ".company-card", function() {
        const serviceId = $(this).data("service-id");
        if (serviceId) {

            if (userAccountType === 'passenger') {
                window.location.href = `/serviceDescription?service_id=${serviceId}`;
            }
        }
    });
    </script>
</body>

</html>