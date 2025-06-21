<?php
  require 'avatar.php';
  require 'partials/header_C.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bookings Management</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
        color: #333;
    }

    .header h1 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .header p {
        opacity: 0.9;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
    }

    .controls {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .controls-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .control-group {
        display: flex;
        flex-direction: column;
    }

    .control-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #555;
    }

    .control-group input,
    .control-group select {
        padding: 0.75rem;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: border-color 0.3s ease;
    }

    .control-group input:focus,
    .control-group select:focus {
        outline: none;
        border-color: #667eea;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #667eea;
    }

    .stat-label {
        color: #666;
        margin-top: 0.5rem;
    }

    .bookings-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .bookings-header {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .bookings-header h2 {
        color: #333;
    }

    .pagination-info {
        color: #666;
        font-size: 0.9rem;
    }

    .booking-table {
        width: 100%;
        border-collapse: collapse;
    }

    .booking-table th {
        background: #f8f9fa;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #e9ecef;
    }

    .booking-table td {
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: top;
    }

    .booking-row {
        transition: background-color 0.2s ease;
    }

    .booking-row:hover {
        background-color: #f8f9ff;
    }

    .booking-number {
        font-weight: bold;
        color: #667eea;
        font-family: "Courier New", monospace;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-confirmed {
        background: #d4edda;
        color: #155724;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    .flight-type {
        background: #e3f2fd;
        color: #1565c0;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
    }

    .pagination button {
        padding: 0.5rem 1rem;
        border: 1px solid #dee2e6;
        background: white;
        color: #6c757d;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pagination button:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .pagination button.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .pagination button:disabled {
        background: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
    }

    .no-bookings {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .loading {
        text-align: center;
        padding: 2rem;
        color: #667eea;
    }

    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }

        .booking-table {
            font-size: 0.8rem;
        }

        .booking-table th,
        .booking-table td {
            padding: 0.5rem;
        }

        .actions {
            flex-direction: column;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <!-- Controls Section -->
        <div class="controls">
            <div class="controls-grid">
                <div class="control-group">
                    <label for="search">🔍 Search Bookings</label>
                    <input type="text" id="search" placeholder="Search by booking number, email, or location..."
                        onkeyup="filterBookings()" />
                </div>
                <div class="control-group">
                    <label for="statusFilter">📊 Filter by Status</label>
                    <select id="statusFilter" onchange="filterBookings()">
                        <option value="">All Statuses</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="control-group">
                    <label for="dateFrom">📅 From Date</label>
                    <input type="date" id="dateFrom" onchange="filterBookings()" />
                </div>
                <div class="control-group">
                    <label for="dateTo">📅 To Date</label>
                    <input type="date" id="dateTo" onchange="filterBookings()" />
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number" id="totalBookings">0</div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="confirmedBookings">0</div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pendingBookings">0</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="todayBookings">0</div>
                <div class="stat-label">Today's Bookings</div>
            </div>
        </div>

        <!-- Bookings Table Section -->
        <div class="bookings-container">
            <div class="bookings-header">
                <h2>📋 All Bookings</h2>
                <div class="pagination-info" id="paginationInfo">
                    Showing 1-10 of 0 bookings
                </div>
            </div>

            <div id="loadingIndicator" class="loading" style="display: none">
                <p>Loading bookings...</p>
            </div>

            <table class="booking-table" id="bookingsTable">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Customer</th>
                        <th>Flight Date</th>
                        <th>Pickup Location</th>
                        <th>Flight Type</th>
                        <th>Passenger Info</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                    <!-- Bookings will be populated here -->
                </tbody>
            </table>

            <div id="noBookings" class="no-bookings" style="display: none">
                <p>📭 No bookings found matching your criteria.</p>
            </div>

            <div class="pagination" id="paginationControls">
                <button onclick="goToPage(1)" id="firstBtn">« First</button>
                <button onclick="previousPage()" id="prevBtn">‹ Previous</button>
                <span id="pageNumbers"></span>
                <button onclick="nextPage()" id="nextBtn">Next ›</button>
                <button onclick="goToPage(totalPages)" id="lastBtn">Last »</button>
            </div>
        </div>
    </div>

    <script>
    // Sample data - In real application, this would come from your database
    let allBookings = [{
            booking_id: 1,
            booking_no: "FB2024001234",
            user_email: "john.doe@email.com",
            date: "2025-07-15",
            pickup: "Tribhuvan International Airport, Kathmandu",
            flight_type: "Helicopter Tour - Everest Base Camp",
            weight: 75.5,
            age: 32,
            condition: "Vegetarian meal preferred",
            status: "confirmed",
            created_at: "2025-06-20 10:30:00",
        },
        {
            booking_id: 2,
            booking_no: "FB2024001235",
            user_email: "sarah.smith@email.com",
            date: "2025-07-20",
            pickup: "Pokhara Airport",
            flight_type: "Scenic Flight - Annapurna Circuit",
            weight: 62.0,
            age: 28,
            condition: null,
            status: "pending",
            created_at: "2025-06-21 14:15:00",
        },
        {
            booking_id: 3,
            booking_no: "FB2024001236",
            user_email: "mike.wilson@email.com",
            date: "2025-08-01",
            pickup: "Lukla Airport",
            flight_type: "Mountain Flight - Everest View",
            weight: 80.2,
            age: 45,
            condition: "Mild altitude sensitivity",
            status: "confirmed",
            created_at: "2025-06-21 09:45:00",
        },
    ];

    // Add more sample data to simulate thousands of bookings
    function generateSampleBookings() {
        const flightTypes = [
            "Helicopter Tour - Everest Base Camp",
            "Scenic Flight - Annapurna Circuit",
            "Mountain Flight - Everest View",
            "Charter Flight - Mustang",
            "Rescue Flight - Emergency",
            "Cargo Flight - Supply Drop",
        ];

        const locations = [
            "Tribhuvan International Airport, Kathmandu",
            "Pokhara Airport",
            "Lukla Airport",
            "Jomsom Airport",
            "Simara Airport",
            "Biratnagar Airport",
        ];

        const statuses = ["confirmed", "pending", "cancelled"];

        for (let i = 4; i <= 150; i++) {
            const randomDate = new Date();
            randomDate.setDate(
                randomDate.getDate() + Math.floor(Math.random() * 60) - 30
            );

            allBookings.push({
                booking_id: i,
                booking_no: `FB2024${String(i).padStart(6, "0")}`,
                user_email: `user${i}@email.com`,
                date: randomDate.toISOString().split("T")[0],
                pickup: locations[Math.floor(Math.random() * locations.length)],
                flight_type: flightTypes[Math.floor(Math.random() * flightTypes.length)],
                weight: (Math.random() * 40 + 50).toFixed(1),
                age: Math.floor(Math.random() * 50 + 18),
                condition: Math.random() > 0.7 ? "Special dietary requirements" : null,
                status: statuses[Math.floor(Math.random() * statuses.length)],
                created_at: new Date(
                        Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000
                    )
                    .toISOString()
                    .replace("T", " ")
                    .split(".")[0],
            });
        }
    }

    // Initialize sample data
    generateSampleBookings();

    // Pagination variables
    let currentPage = 1;
    let itemsPerPage = 10;
    let filteredBookings = [...allBookings];
    let totalPages = Math.ceil(filteredBookings.length / itemsPerPage);

    // Initialize the dashboard
    function initializeDashboard() {
        updateStatistics();
        renderBookings();
        updatePaginationControls();
    }

    // Update statistics
    function updateStatistics() {
        const total = allBookings.length;
        const confirmed = allBookings.filter(
            (b) => b.status === "confirmed"
        ).length;
        const pending = allBookings.filter(
            (b) => b.status === "pending"
        ).length;
        const today = new Date().toISOString().split("T")[0];
        const todayCount = allBookings.filter((b) => b.date === today).length;

        document.getElementById("totalBookings").textContent = total;
        document.getElementById("confirmedBookings").textContent = confirmed;
        document.getElementById("pendingBookings").textContent = pending;
        document.getElementById("todayBookings").textContent = todayCount;
    }

    // Filter bookings based on search criteria
    function filterBookings() {
        const search = document.getElementById("search").value.toLowerCase();
        const statusFilter = document.getElementById("statusFilter").value;
        const dateFrom = document.getElementById("dateFrom").value;
        const dateTo = document.getElementById("dateTo").value;

        filteredBookings = allBookings.filter((booking) => {
            const matchesSearch = !search ||
                booking.booking_no.toLowerCase().includes(search) ||
                booking.user_email.toLowerCase().includes(search) ||
                booking.pickup.toLowerCase().includes(search) ||
                booking.flight_type.toLowerCase().includes(search);

            const matchesStatus = !statusFilter || booking.status === statusFilter;

            const matchesDateFrom = !dateFrom || booking.date >= dateFrom;
            const matchesDateTo = !dateTo || booking.date <= dateTo;

            return (
                matchesSearch && matchesStatus && matchesDateFrom && matchesDateTo
            );
        });

        currentPage = 1;
        totalPages = Math.ceil(filteredBookings.length / itemsPerPage);
        renderBookings();
        updatePaginationControls();
    }

    // Render bookings table
    function renderBookings() {
        const tableBody = document.getElementById("bookingsTableBody");
        const noBookingsDiv = document.getElementById("noBookings");
        const table = document.getElementById("bookingsTable");

        if (filteredBookings.length === 0) {
            table.style.display = "none";
            noBookingsDiv.style.display = "block";
            return;
        }

        table.style.display = "table";
        noBookingsDiv.style.display = "none";

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageBookings = filteredBookings.slice(start, end);

        tableBody.innerHTML = pageBookings
            .map(
                (booking) => `
                <tr class="booking-row">
                    <td>
                        <div class="booking-number">${booking.booking_no}</div>
                        <small style="color: #666;">${formatDate(
                          booking.created_at
                        )}</small>
                    </td>
                    <td>
                        <div style="font-weight: 600;">${
                          booking.user_email
                        }</div>
                    </td>
                    <td>
                        <div style="font-weight: 600;">${formatDate(
                          booking.date
                        )}</div>
                    </td>
                    <td>
                        <div style="max-width: 200px; word-wrap: break-word;">
                            ${booking.pickup}
                        </div>
                    </td>
                    <td>
                        <span class="flight-type">${booking.flight_type}</span>
                    </td>
                    <td>
                        <div>Age: ${booking.age} years</div>
                        <div>Weight: ${booking.weight} kg</div>
                        ${
                          booking.condition
                            ? `<div style="color: #666; font-size: 0.8rem; margin-top: 0.25rem;">Note: ${booking.condition}</div>`
                            : ""
                        }
                    </td>
                    <td>
                        <span class="status-badge status-${booking.status}">
                            ${booking.status}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="btn btn-primary" onclick="viewBooking(${
                              booking.booking_id
                            })">View</button>
                            <button class="btn btn-secondary" onclick="editBooking(${
                              booking.booking_id
                            })">Edit</button>
                            ${
                              booking.status !== "cancelled"
                                ? `<button class="btn btn-danger" onclick="cancelBooking(${booking.booking_id})">Cancel</button>`
                                : ""
                            }
                        </div>
                    </td>
                </tr>
            `
            )
            .join("");

        updatePaginationInfo();
    }

    // Format date for display
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "2-digit",
        });
    }

    // Update pagination info
    function updatePaginationInfo() {
        const start = (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(
            currentPage * itemsPerPage,
            filteredBookings.length
        );
        const total = filteredBookings.length;

        document.getElementById(
            "paginationInfo"
        ).textContent = `Showing ${start}-${end} of ${total} bookings`;
    }

    // Pagination functions
    function goToPage(page) {
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderBookings();
            updatePaginationControls();
        }
    }

    function nextPage() {
        if (currentPage < totalPages) {
            currentPage++;
            renderBookings();
            updatePaginationControls();
        }
    }

    function previousPage() {
        if (currentPage > 1) {
            currentPage--;
            renderBookings();
            updatePaginationControls();
        }
    }

    function updatePaginationControls() {
        const pageNumbers = document.getElementById("pageNumbers");
        const firstBtn = document.getElementById("firstBtn");
        const prevBtn = document.getElementById("prevBtn");
        const nextBtn = document.getElementById("nextBtn");
        const lastBtn = document.getElementById("lastBtn");

        firstBtn.disabled = currentPage === 1;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        lastBtn.disabled = currentPage === totalPages;

        // Generate page numbers
        let pages = [];
        for (
            let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++
        ) {
            pages.push(i);
        }

        pageNumbers.innerHTML = pages
            .map(
                (page) =>
                `<button onclick="goToPage(${page})" class="${
                page === currentPage ? "active" : ""
              }">${page}</button>`
            )
            .join("");
    }

    // Action functions
    function viewBooking(bookingId) {
        const booking = allBookings.find((b) => b.booking_id === bookingId);
        alert(
            `Viewing booking details for ${booking.booking_no}\n\nCustomer: ${booking.user_email}\nFlight: ${booking.flight_type}\nDate: ${booking.date}`
        );
    }

    function editBooking(bookingId) {
        const booking = allBookings.find((b) => b.booking_id === bookingId);
        alert(
            `Edit booking ${booking.booking_no}\n\nThis would open an edit form for the booking.`
        );
    }

    function cancelBooking(bookingId) {
        if (confirm("Are you sure you want to cancel this booking?")) {
            const booking = allBookings.find((b) => b.booking_id === bookingId);
            booking.status = "cancelled";
            updateStatistics();
            renderBookings();
            alert(`Booking ${booking.booking_no} has been cancelled.`);
        }
    }

    // Initialize dashboard on page load
    document.addEventListener("DOMContentLoaded", initializeDashboard);
    </script>
</body>

</html>

<?php
  require 'partials/footer.php';
?>