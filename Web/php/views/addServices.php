<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="Web/css/addServices.css?v=1.0" />
    <link href="https://cdn.boxicons.com/fonts/basic/boxicons.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div class="company-grid">
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

    </div>

    <script src="Web/scripts/addServices.js?v=1.0"></script>
</body>

</html>