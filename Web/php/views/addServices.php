<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<style>
.add-company-wrapper {
    height: 600px;
    margin: 50px;
}

.add-company-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 220px;
    height: 160px;
    background: #f5f7fa;
    border: 2px dashed #007BFF;
    border-radius: 12px;
    cursor: pointer;
    margin: 24px auto;
    transition: background 0.2s, border-color 0.2s;
}

.add-company-card:hover {
    background: #e6f0ff;
    border-color: #0056b3;
}

.add-company-icon {
    font-size: 48px;
    color: #007BFF;
    margin-bottom: 12px;
    font-weight: bold;
    line-height: 1;
}

.add-company-text {
    font-size: 18px;
    color: #333;
    font-weight: 500;
}
</style>

<body>
    <div class="add-company-wrapper">
        <div class="add-company-card" onclick="window.location.href='/serviceform'">
            <div class="add-company-icon">+</div>
            <div class="add-company-text">Add Service</div>
        </div>
    </div>


</body>

</html>