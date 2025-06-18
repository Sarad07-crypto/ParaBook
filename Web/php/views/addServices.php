<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="Web/css/addServices.css" />
    <link href="https://cdn.boxicons.com/fonts/basic/boxicons.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="add-company-wrapper">
        <div class="add-company-card" id="loadServiceForm">
            <div class="add-company-icon">+</div>
            <div class="add-company-text">Add Service</div>
        </div>
    </div>
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <div id="serviceFormContainer">Loading...</div>
        </div>
    </div>
    <script>
    // Load the modal and form content
    $(document).on('click', '#loadServiceForm', function() {
        $('body').css('overflow', 'hidden');
        $('#serviceModal').addClass('show'); // Use .show instead of fadeIn

        $('#serviceFormContainer').html('<div class="loading-spinner">Loading form...</div>');

        $.ajax({
            url: 'Web/php/views/serviceForm.php',
            type: 'GET',
            success: function(data) {
                $('#serviceFormContainer').html(data);
                // Load the script only once per click
                $.getScript('Web/scripts/serviceForm.js', function() {
                    initServiceForm();
                });
            },
            error: function() {
                $('#serviceFormContainer').html('<p>Failed to load form.</p>');
            }
        });
    });


    // Close modal by clicking close button
    $(document).on('click', '#closeModal', function() {
        $('#serviceModal').removeClass('show');
        $('body').css('overflow', 'auto');
        $('#serviceFormContainer').empty();
    });

    // Close modal by clicking outside modal-content
    $(window).on('click', function(e) {
        if ($(e.target).is('#serviceModal')) {
            $('#serviceModal').removeClass('show');
            $('body').css('overflow', 'auto');
            $('#serviceFormContainer').empty();
        }
    });
    </script>
</body>

</html>