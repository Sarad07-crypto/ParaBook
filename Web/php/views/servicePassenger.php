<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="Web/css/views.css" />
    <link rel="stylesheet" href="Web/css/addServices.css?v=1.0" />
    <link rel="stylesheet" href="Web/css/notification.css?v=1.0" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/Web/scripts/views.js"></script>

    <style>
    /*
    Example HTML structure for correct dropdown positioning:
    <div class="heart-container">
        <div id="heart-icon" onclick="toggleHeartNotifications(event)">
            <i class="fas fa-heart"></i>
            <span id="heart-badge">0</span>
        </div>
        <div id="heart-dropdown">
            <div id="heart-list"></div>
            <button id="heart-clear-btn" onclick="clearAllFavorites()">Clear All</button>
        </div>
    </div>
    */
    </style>
</head>

<body>

    <!-- Main content -->
    <div class="company-section">
        <div class="company-header">
            <div>
                <h1>Companies you are looking for</h1>
                <div class="company-results">Loading...</div>
            </div>
            <div class="company-sort">
                <label style="margin-left: 24px;">
                    Sort by:
                    <select class="sort-select">
                        <option value="name">Company Name (A-Z)</option>
                        <option value="price">Price (Low to High)</option>
                        <option value="price-max">Price (High to Low)</option>
                        <option value="reviews">Most Flights</option>
                        <option value="rating">Highest Rating</option>
                    </select>
                </label>
            </div>
        </div>
    </div>
    <div class="company-grid">

    </div>
    <div class="pagination" id="pagination"></div>

    <script src="Web/scripts/favorite.js?v=1.0"></script>
</body>

</html>