<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{
    padding-top:70px;
}

.navbar{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:65px;
    background:#d32f2f; /* RED theme */
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 25px;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
    z-index:1000;
    box-sizing:border-box;
}

.navbar .logo{
    color:white;
    font-size:18px;
    font-weight:700;
    text-decoration:none;
    display:flex;
    align-items:center;
    gap:8px;
}

.navbar .menu{
    display:flex;
    gap:18px;
    margin-left:auto;
}

.navbar .menu a{
    color:white;
    text-decoration:none;
    font-size:14px;
    display:flex;
    align-items:center;
    gap:6px;
    padding:6px 10px;
    border-radius:6px;
}

.navbar .menu a:hover{
    background:rgba(255,255,255,0.2);
}

.logout-btn{
    background:none;
    border:none;
    color:white;
    font-size:20px;
    cursor:pointer;
    margin-left:15px;
}
</style>

<div class="navbar">

    <a href="homepage.php" class="logo">
        <i class="fa-solid fa-chalkboard-user"></i>
        Teacher Panel
    </a>

    <div class="menu">
        <a href="homepage.php"><i class="fa fa-house"></i> Home</a>
        <a href="manage_students.php"><i class="fa fa-users"></i> Students</a>
        <a href="manage_attendance.php"><i class="fa fa-calendar-check"></i> Attendance</a>
        <a href="manage_results.php"><i class="fa fa-pen"></i> Results</a>
        <a href="class_results.php"><i class="fa fa-ranking-star"></i> Ranking</a>
        <a href="reports.php"><i class="fa fa-chart-line"></i> Reports</a>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <button class="logout-btn" onclick="logoutConfirm()">
            <i class="fa fa-right-from-bracket"></i>
        </button>
    <?php endif; ?>

</div>

<script>
function logoutConfirm(){
    if(confirm("Are you sure you want to logout?")){
        window.location.href = "logout.php";
    }
}
</script>