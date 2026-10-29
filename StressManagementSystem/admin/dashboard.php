<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();


/* =========================================================
   BASIC STATISTICS
   ========================================================= */

$users = 0;
$assessments = 0;
$averageStress = 0;
$highStress = 0;
$veryHighStress = 0;
$averageSleep = 0;


/* Total users */

$result = $conn->query("
    SELECT COUNT(*) AS n
    FROM users
");

if ($result) {
    $users = (int) $result->fetch_assoc()['n'];
}


/* Total assessments */

$result = $conn->query("
    SELECT COUNT(*) AS n
    FROM stress_assessments
");

if ($result) {
    $assessments = (int) $result->fetch_assoc()['n'];
}


/* Average stress */

$result = $conn->query("
    SELECT COALESCE(AVG(stress_level), 0) AS n
    FROM stress_assessments
");

if ($result) {
    $averageStress = (float) $result->fetch_assoc()['n'];
}


/* High stress */

$result = $conn->query("
    SELECT COUNT(*) AS n
    FROM stress_assessments
    WHERE stress_level >= 7
");

if ($result) {
    $highStress = (int) $result->fetch_assoc()['n'];
}


/* Very high stress */

$result = $conn->query("
    SELECT COUNT(*) AS n
    FROM stress_assessments
    WHERE stress_level >= 9
");

if ($result) {
    $veryHighStress = (int) $result->fetch_assoc()['n'];
}


/* Average sleep */

$result = $conn->query("
    SELECT COALESCE(AVG(sleep_hours), 0) AS n
    FROM stress_assessments
");

if ($result) {
    $averageSleep = (float) $result->fetch_assoc()['n'];
}


/* =========================================================
   STRESS DISTRIBUTION
   ========================================================= */

$lowStress = 0;
$moderateStress = 0;
$highStressCount = 0;
$veryHighStressCount = 0;

$result = $conn->query("
    SELECT stress_level
    FROM stress_assessments
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $level = (int) $row['stress_level'];

        if ($level <= 3) {

            $lowStress++;

        } elseif ($level <= 6) {

            $moderateStress++;

        } elseif ($level <= 8) {

            $highStressCount++;

        } else {

            $veryHighStressCount++;
        }
    }
}


/* =========================================================
   MOST COMMON EMOTION
   ========================================================= */

$topEmotion = "No data";

$result = $conn->query("
    SELECT
        CASE
            WHEN emotion IS NOT NULL AND emotion != ''
                THEN emotion
            ELSE mood
        END AS emotion_name,
        COUNT(*) AS total
    FROM stress_assessments
    GROUP BY emotion_name
    ORDER BY total DESC
    LIMIT 1
");

if ($result && $result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $topEmotion =
        $row['emotion_name']
        ?: "Neutral";
}


/* =========================================================
   MOST COMMON CONCERN
   ========================================================= */

$topSituation = "No data";

$result = $conn->query("
    SELECT
        COALESCE(NULLIF(situation, ''), 'General Stress') AS situation_name,
        COUNT(*) AS total
    FROM stress_assessments
    GROUP BY situation_name
    ORDER BY total DESC
    LIMIT 1
");

if ($result && $result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $topSituation =
        $row['situation_name'];
}


/* =========================================================
   RECENT ASSESSMENTS
   ========================================================= */

$recentAssessments = [];

$result = $conn->query("
    SELECT
        sa.id,
        sa.user_id,
        sa.stress_level,
        sa.emotion,
        sa.mood,
        sa.situation,
        sa.sleep_hours,
        sa.created_at,
        u.name,
        u.email
    FROM stress_assessments sa
    INNER JOIN users u
        ON u.id = sa.user_id
    ORDER BY sa.created_at DESC
    LIMIT 8
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $recentAssessments[] = $row;
    }
}


/* =========================================================
   RECENT USERS
   ========================================================= */

$recentUsers = [];

$result = $conn->query("
    SELECT
        id,
        name,
        email,
        created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $recentUsers[] = $row;
    }
}


/* =========================================================
   STRESS CATEGORY
   ========================================================= */

function getStressCategory($stress)
{
    $stress = (int) $stress;

    if ($stress <= 3) {
        return "Low";
    }

    if ($stress <= 6) {
        return "Moderate";
    }

    if ($stress <= 8) {
        return "High";
    }

    return "Very High";
}


/* =========================================================
   STRESS CLASS
   ========================================================= */

function getStressClass($stress)
{
    $stress = (int) $stress;

    if ($stress <= 3) {
        return "low";
    }

    if ($stress <= 6) {
        return "moderate";
    }

    if ($stress <= 8) {
        return "high";
    }

    return "very-high";
}


/* =========================================================
   EMOTION ICON
   ========================================================= */

function emotionIcon($emotion)
{
    $icons = [

        "Happy" => "😊",
        "Calm" => "😌",
        "Sad" => "😔",
        "Angry" => "😠",
        "Anxious" => "😰",
        "Lonely" => "😞",
        "Fearful" => "😨",
        "Hurt" => "💔",
        "Disappointed" => "😕",
        "Frustrated" => "😣",
        "Confused" => "😵",
        "Tired" => "😴",
        "Stressed" => "😫",
        "Overwhelmed" => "🤯",
        "Neutral" => "😐"

    ];

    return $icons[$emotion] ?? "💚";
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Admin Dashboard | StressCare</title>

<link
    rel="stylesheet"
    href="../assets/css/style.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<style>

/* =========================================================
   BASE
   ========================================================= */

body {

    background:
        linear-gradient(
            135deg,
            #f4faf7,
            #ffffff
        );
}

.container {

    max-width: 1250px;

    margin: auto;

    padding:
        30px 20px 60px;
}


/* =========================================================
   ADMIN HERO
   ========================================================= */

.admin-hero {

    background:
        linear-gradient(
            135deg,
            #075d40,
            #0b9665
        );

    color: white;

    border-radius: 25px;

    padding: 34px;

    margin-bottom: 25px;

    box-shadow:
        0 16px 40px rgba(8,117,79,.15);
}

.admin-hero h1 {

    margin: 0 0 8px;

    font-size: 29px;
}

.admin-hero p {

    margin: 0;

    opacity: .92;

    line-height: 1.7;

    font-size: 13px;
}


/* =========================================================
   STAT CARDS
   ========================================================= */

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 16px;

    margin-bottom: 28px;
}

.stat-card {

    background: white;

    border:
        1px solid #e1eee7;

    border-radius: 19px;

    padding: 20px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.035);
}

.stat-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 15px;
}

.stat-icon {

    width: 43px;

    height: 43px;

    border-radius: 13px;

    background: #eaf8f0;

    color: #08754f;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 17px;
}

.stat-number {

    color: #173c2d;

    font-size: 27px;

    font-weight: 900;
}

.stat-title {

    color: #64786e;

    font-size: 11px;

    font-weight: 700;
}

.stat-sub {

    color: #9aa9a2;

    font-size: 9px;

    margin-top: 4px;
}


/* =========================================================
   SECTION TITLE
   ========================================================= */

.section-title {

    margin:
        30px 0 17px;
}

.section-title h2 {

    margin: 0;

    font-size: 20px;

    color: #173c2d;
}

.section-title p {

    margin:
        5px 0 0;

    color: #7b8d85;

    font-size: 11px;
}


/* =========================================================
   ANALYTICS GRID
   ========================================================= */

.analytics-grid {

    display: grid;

    grid-template-columns:
        1.3fr .7fr;

    gap: 18px;

    margin-bottom: 25px;
}

.analytics-card {

    background: white;

    border:
        1px solid #e1eee7;

    border-radius: 20px;

    padding: 23px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.035);
}

.analytics-card h3 {

    margin: 0;

    color: #173c2d;

    font-size: 15px;
}

.analytics-card p {

    color: #83928c;

    font-size: 10px;

    margin:
        5px 0 20px;
}


/* =========================================================
   DISTRIBUTION
   ========================================================= */

.distribution {

    display: flex;

    flex-direction: column;

    gap: 14px;
}

.distribution-row {

    display: grid;

    grid-template-columns:
        105px 1fr 40px;

    align-items: center;

    gap: 10px;
}

.distribution-label {

    font-size: 10px;

    color: #657970;

    font-weight: 700;
}

.progress {

    height: 9px;

    background: #edf3ef;

    border-radius: 20px;

    overflow: hidden;
}

.progress-bar {

    height: 100%;

    border-radius: 20px;

    background: #08754f;
}

.progress-bar.moderate {

    background: #c69a2e;
}

.progress-bar.high {

    background: #d46b24;
}

.progress-bar.very-high {

    background: #b12c2c;
}

.distribution-number {

    text-align: right;

    font-size: 10px;

    color: #52665d;

    font-weight: 800;
}


/* =========================================================
   INSIGHT BOXES
   ========================================================= */

.insight-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 12px;
}

.insight-box {

    background: #f6faf7;

    border-radius: 14px;

    padding: 15px;
}

.insight-label {

    color: #83938c;

    font-size: 9px;

    text-transform: uppercase;
}

.insight-value {

    color: #173c2d;

    font-size: 14px;

    font-weight: 800;

    margin-top: 5px;
}


/* =========================================================
   QUICK ACTIONS
   ========================================================= */

.quick-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 16px;

    margin-bottom: 28px;
}

.quick-card {

    background: white;

    border:
        1px solid #e1eee7;

    border-radius: 18px;

    padding: 20px;

    text-decoration: none;

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}

.quick-card:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 13px 30px rgba(8,117,79,.09);
}

.quick-icon {

    width: 43px;

    height: 43px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eaf8f0;

    color: #08754f;

    margin-bottom: 12px;
}

.quick-card h3 {

    margin: 0;

    color: #173c2d;

    font-size: 14px;
}

.quick-card p {

    margin:
        6px 0 0;

    color: #7b8d85;

    font-size: 10px;

    line-height: 1.6;
}


/* =========================================================
   TABLE
   ========================================================= */

.table-card {

    background: white;

    border:
        1px solid #e1eee7;

    border-radius: 20px;

    padding: 20px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.035);

    overflow-x: auto;
}

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 850px;
}

th {

    text-align: left;

    color: #7b8d85;

    font-size: 9px;

    text-transform: uppercase;

    letter-spacing: .5px;

    padding:
        12px 10px;

    border-bottom:
        1px solid #e7efea;
}

td {

    padding:
        14px 10px;

    border-bottom:
        1px solid #edf2ef;

    color: #53675e;

    font-size: 10px;
}

.user-name {

    color: #173c2d;

    font-weight: 800;

    font-size: 11px;
}

.user-email {

    color: #899790;

    font-size: 9px;

    margin-top: 3px;
}


/* =========================================================
   BADGES
   ========================================================= */

.badge {

    display: inline-block;

    padding:
        5px 9px;

    border-radius: 20px;

    font-size: 9px;

    font-weight: 800;
}

.badge.low {

    background: #e9f8ef;

    color: #08754f;
}

.badge.moderate {

    background: #fff6df;

    color: #987000;
}

.badge.high {

    background: #fff0e5;

    color: #b55b00;
}

.badge.very-high {

    background: #ffeaea;

    color: #b12c2c;
}


/* =========================================================
   FOOTER
   ========================================================= */

.admin-footer {

    text-align: center;

    margin-top: 35px;

    color: #8a9892;

    font-size: 10px;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width: 950px) {

    .stats-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .analytics-grid {

        grid-template-columns: 1fr;
    }

    .quick-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }

}

@media(max-width: 550px) {

    .container {

        padding:
            20px 14px 45px;
    }

    .admin-hero {

        padding: 25px 20px;
    }

    .admin-hero h1 {

        font-size: 24px;
    }

    .stats-grid {

        grid-template-columns: 1fr;
    }

    .quick-grid {

        grid-template-columns: 1fr;
    }

    .insight-grid {

        grid-template-columns: 1fr;
    }

}

</style>

</head>


<body>


<!-- =======================================================
     NAVBAR
     ======================================================= -->

<header class="navbar">

    <a
        class="logo"
        href="dashboard.php"
    >

        <div class="logo-icon">
            🌿
        </div>

        <span>
            Stress<span>Care</span>
        </span>

    </a>


    <div class="nav-links">

        <span class="nav-btn">

            <i class="fa-solid fa-user-shield"></i>

            Admin:
            <?= htmlspecialchars(
                $_SESSION['admin_username'] ?? 'admin'
            ) ?>

        </span>


        <a
            class="nav-btn"
            href="logout.php"
        >
            Logout
        </a>

    </div>

</header>



<main class="container">


<!-- =======================================================
     HERO
     ======================================================= -->

<section class="admin-hero">

    <h1>
        Admin Dashboard
    </h1>

    <p>
        Monitor StressCare users, assessment activity,
        stress patterns and emotional wellbeing trends
        from one centralized dashboard.
    </p>

</section>



<!-- =======================================================
     MAIN STATISTICS
     ======================================================= -->

<div class="stats-grid">


    <!-- USERS -->

    <div class="stat-card">

        <div class="stat-top">

            <div class="stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <div class="stat-number">
                <?= $users ?>
            </div>

        </div>

        <div class="stat-title">
            Registered Users
        </div>

        <div class="stat-sub">
            Total accounts in StressCare
        </div>

    </div>



    <!-- ASSESSMENTS -->

    <div class="stat-card">

        <div class="stat-top">

            <div class="stat-icon">

                <i class="fa-solid fa-clipboard-check"></i>

            </div>

            <div class="stat-number">
                <?= $assessments ?>
            </div>

        </div>

        <div class="stat-title">
            Total Assessments
        </div>

        <div class="stat-sub">
            Completed wellbeing check-ins
        </div>

    </div>



    <!-- AVERAGE -->

    <div class="stat-card">

        <div class="stat-top">

            <div class="stat-icon">

                <i class="fa-solid fa-chart-line"></i>

            </div>

            <div class="stat-number">

                <?= number_format(
                    $averageStress,
                    1
                ) ?>/10

            </div>

        </div>

        <div class="stat-title">
            Average Stress
        </div>

        <div class="stat-sub">
            Application-based indicator
        </div>

    </div>



    <!-- HIGH -->

    <div class="stat-card">

        <div class="stat-top">

            <div class="stat-icon">

                <i class="fa-solid fa-heart-pulse"></i>

            </div>

            <div class="stat-number">
                <?= $highStress ?>
            </div>

        </div>

        <div class="stat-title">
            High Stress Records
        </div>

        <div class="stat-sub">
            Stress level 7 or above
        </div>

    </div>


</div>



<!-- =======================================================
     ANALYTICS
     ======================================================= -->

<div class="section-title">

    <h2>
        Wellbeing Analytics
    </h2>

    <p>
        Overall patterns from completed assessments.
    </p>

</div>



<div class="analytics-grid">


    <!-- STRESS DISTRIBUTION -->

    <section class="analytics-card">

        <h3>
            Stress Distribution
        </h3>

        <p>
            Number of assessment records in each stress range.
        </p>


        <?php

        $distributionTotal =
            max(
                1,
                $lowStress +
                $moderateStress +
                $highStressCount +
                $veryHighStressCount
            );

        ?>


        <div class="distribution">


            <!-- LOW -->

            <div class="distribution-row">

                <div class="distribution-label">
                    Low
                </div>

                <div class="progress">

                    <div
                        class="progress-bar"
                        style="width:<?= ($lowStress / $distributionTotal) * 100 ?>%"
                    ></div>

                </div>

                <div class="distribution-number">
                    <?= $lowStress ?>
                </div>

            </div>


            <!-- MODERATE -->

            <div class="distribution-row">

                <div class="distribution-label">
                    Moderate
                </div>

                <div class="progress">

                    <div
                        class="progress-bar moderate"
                        style="width:<?= ($moderateStress / $distributionTotal) * 100 ?>%"
                    ></div>

                </div>

                <div class="distribution-number">
                    <?= $moderateStress ?>
                </div>

            </div>


            <!-- HIGH -->

            <div class="distribution-row">

                <div class="distribution-label">
                    High
                </div>

                <div class="progress">

                    <div
                        class="progress-bar high"
                        style="width:<?= ($highStressCount / $distributionTotal) * 100 ?>%"
                    ></div>

                </div>

                <div class="distribution-number">
                    <?= $highStressCount ?>
                </div>

            </div>


            <!-- VERY HIGH -->

            <div class="distribution-row">

                <div class="distribution-label">
                    Very High
                </div>

                <div class="progress">

                    <div
                        class="progress-bar very-high"
                        style="width:<?= ($veryHighStressCount / $distributionTotal) * 100 ?>%"
                    ></div>

                </div>

                <div class="distribution-number">
                    <?= $veryHighStressCount ?>
                </div>

            </div>


        </div>

    </section>



    <!-- EMOTIONAL INSIGHTS -->

    <section class="analytics-card">

        <h3>
            Emotional Insights
        </h3>

        <p>
            Most frequently reported patterns.
        </p>


        <div class="insight-grid">


            <div class="insight-box">

                <div class="insight-label">
                    Common Emotion
                </div>

                <div class="insight-value">

                    <?= emotionIcon($topEmotion) ?>

                    <?= htmlspecialchars(
                        $topEmotion
                    ) ?>

                </div>

            </div>


            <div class="insight-box">

                <div class="insight-label">
                    Common Concern
                </div>

                <div class="insight-value">

                    <?= htmlspecialchars(
                        $topSituation
                    ) ?>

                </div>

            </div>


            <div class="insight-box">

                <div class="insight-label">
                    Average Sleep
                </div>

                <div class="insight-value">

                    <?= number_format(
                        $averageSleep,
                        1
                    ) ?>

                    hours

                </div>

            </div>


            <div class="insight-box">

                <div class="insight-label">
                    Very High Stress
                </div>

                <div class="insight-value">

                    <?= $veryHighStress ?>

                    records

                </div>

            </div>


        </div>

    </section>


</div>



<!-- =======================================================
     QUICK ACTIONS
     ======================================================= -->

<div class="section-title">

    <h2>
        Quick Actions
    </h2>

    <p>
        Manage and review the StressCare system.
    </p>

</div>



<div class="quick-grid">


    <a
        class="quick-card"
        href="assessments.php"
    >

        <div class="quick-icon">

            <i class="fa-solid fa-clipboard-list"></i>

        </div>

        <h3>
            Assessments
        </h3>

        <p>
            View and analyze all user assessment records.
        </p>

    </a>



    <a
        class="quick-card"
        href="users.php"
    >

        <div class="quick-icon">

            <i class="fa-solid fa-users"></i>

        </div>

        <h3>
            Users
        </h3>

        <p>
            View registered users and their account information.
        </p>

    </a>



    <a
        class="quick-card"
        href="recommendations.php"
    >

        <div class="quick-icon">

            <i class="fa-solid fa-lightbulb"></i>

        </div>

        <h3>
            Recommendations
        </h3>

        <p>
            Manage personalized recommendation rules and content.
        </p>

    </a>


</div>



<!-- =======================================================
     RECENT ASSESSMENTS
     ======================================================= -->

<div class="section-title">

    <h2>
        Recent Assessments
    </h2>

    <p>
        Latest wellbeing check-ins submitted by users.
    </p>

</div>



<div class="table-card">


<?php if (count($recentAssessments) > 0): ?>


<table>

<thead>

<tr>

    <th>
        User
    </th>

    <th>
        Emotion
    </th>

    <th>
        Concern
    </th>

    <th>
        Stress
    </th>

    <th>
        Sleep
    </th>

    <th>
        Date
    </th>

</tr>

</thead>


<tbody>


<?php foreach ($recentAssessments as $item): ?>


<?php

$userEmotion =
    trim(
        $item['emotion']
        ?: $item['mood']
        ?: 'Neutral'
    );

$userSituation =
    trim(
        $item['situation']
        ?: 'General Stress'
    );

$itemStress =
    (int) $item['stress_level'];

$itemClass =
    getStressClass($itemStress);

?>


<tr>


    <td>

        <div class="user-name">

            <?= htmlspecialchars(
                $item['name']
            ) ?>

        </div>

        <div class="user-email">

            <?= htmlspecialchars(
                $item['email']
            ) ?>

        </div>

    </td>


    <td>

        <?= emotionIcon(
            $userEmotion
        ) ?>

        <?= htmlspecialchars(
            $userEmotion
        ) ?>

    </td>


    <td>

        <?= htmlspecialchars(
            $userSituation
        ) ?>

    </td>


    <td>

        <span
            class="badge <?= $itemClass ?>"
        >

            <?= $itemStress ?>/10

            ·

            <?= getStressCategory(
                $itemStress
            ) ?>

        </span>

    </td>


    <td>

        <?= htmlspecialchars(
            $item['sleep_hours']
        ) ?>

        hrs

    </td>


    <td>

        <?= date(
            'd M Y, h:i A',
            strtotime(
                $item['created_at']
            )
        ) ?>

    </td>


</tr>


<?php endforeach; ?>


</tbody>

</table>


<?php else: ?>


<div style="
    text-align:center;
    padding:35px;
    color:#7b8d85;
    font-size:12px;
">

    🌱

    <br><br>

    No assessments have been completed yet.

</div>


<?php endif; ?>


</div>



<!-- =======================================================
     RECENT USERS
     ======================================================= -->

<div class="section-title">

    <h2>
        Recently Registered Users
    </h2>

    <p>
        Latest users who joined StressCare.
    </p>

</div>



<div class="table-card">


<?php if (count($recentUsers) > 0): ?>


<table>

<thead>

<tr>

    <th>
        Name
    </th>

    <th>
        Email
    </th>

    <th>
        Registered
    </th>

</tr>

</thead>


<tbody>


<?php foreach ($recentUsers as $user): ?>


<tr>

    <td>

        <div class="user-name">

            <?= htmlspecialchars(
                $user['name']
            ) ?>

        </div>

    </td>


    <td>

        <?= htmlspecialchars(
            $user['email']
        ) ?>

    </td>


    <td>

        <?= date(
            'd M Y, h:i A',
            strtotime(
                $user['created_at']
            )
        ) ?>

    </td>

</tr>


<?php endforeach; ?>


</tbody>

</table>


<?php else: ?>


<div style="
    text-align:center;
    padding:30px;
    color:#7b8d85;
">

    No registered users yet.

</div>


<?php endif; ?>


</div>



<!-- =======================================================
     FOOTER
     ======================================================= -->

<div class="admin-footer">

    StressCare Admin Panel ·
    Application-based wellbeing management system

</div>


</main>

</body>

</html>