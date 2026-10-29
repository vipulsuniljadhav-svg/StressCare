<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

requireLogin();

$uid = (int) $_SESSION['user_id'];


/* =========================================================
   GET USER ASSESSMENT HISTORY
   ========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM stress_assessments
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $uid);
$stmt->execute();

$result = $stmt->get_result();


/* =========================================================
   STRESS CATEGORY FUNCTION
   ========================================================= */

function getStressCategory($stress)
{
    $stress = (int) $stress;

    if ($stress <= 3) {
        return "Low Stress";
    }

    if ($stress <= 6) {
        return "Moderate Stress";
    }

    if ($stress <= 8) {
        return "High Stress";
    }

    return "Very High Stress";
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

function getEmotionIcon($emotion)
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


/* =========================================================
   TOTAL ASSESSMENTS
   ========================================================= */

$totalAssessments = 0;

$countStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM stress_assessments
    WHERE user_id = ?
");

$countStmt->bind_param("i", $uid);
$countStmt->execute();

$countResult = $countStmt->get_result()->fetch_assoc();

$totalAssessments = (int) $countResult['total'];

$countStmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Assessment History | StressCare</title>

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<style>

/* =========================================================
   PAGE
   ========================================================= */

body {
    background:
        linear-gradient(
            135deg,
            #f5fbf7,
            #ffffff
        );
}

.container {
    max-width: 1200px;
    margin: auto;
    padding: 30px 20px 60px;
}


/* =========================================================
   HERO
   ========================================================= */

.history-hero {

    background:
        linear-gradient(
            135deg,
            #08754f,
            #0b9665
        );

    color: white;

    border-radius: 24px;

    padding: 32px;

    margin-bottom: 25px;

    box-shadow:
        0 15px 40px rgba(8,117,79,.14);
}

.history-hero h1 {

    margin: 0 0 8px;

    font-size: 28px;
}

.history-hero p {

    margin: 0;

    line-height: 1.7;

    opacity: .92;

    max-width: 750px;

    font-size: 13px;
}


/* =========================================================
   STATS
   ========================================================= */

.history-stats {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 16px;

    margin-bottom: 28px;
}

.stat-card {

    background: white;

    border:
        1px solid #e2eee7;

    border-radius: 18px;

    padding: 20px;

    display: flex;

    align-items: center;

    gap: 14px;

    box-shadow:
        0 7px 25px rgba(0,0,0,.035);
}

.stat-icon {

    width: 45px;
    height: 45px;

    border-radius: 13px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #eaf8f0;

    color: #08754f;

    font-size: 18px;
}

.stat-label {

    color: #7b8d85;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .5px;
}

.stat-value {

    color: #173c2d;

    font-size: 20px;

    font-weight: 800;

    margin-top: 3px;
}


/* =========================================================
   SECTION TITLE
   ========================================================= */

.section-title {

    margin-bottom: 18px;
}

.section-title h2 {

    margin: 0;

    color: #173c2d;

    font-size: 21px;
}

.section-title p {

    margin: 6px 0 0;

    color: #7b8d85;

    font-size: 12px;
}


/* =========================================================
   HISTORY CARD
   ========================================================= */

.history-list {

    display: flex;

    flex-direction: column;

    gap: 16px;
}

.history-card {

    background: white;

    border:
        1px solid #e2eee7;

    border-radius: 20px;

    padding: 22px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 8px 25px rgba(0,0,0,.035);

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}

.history-card:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 13px 32px rgba(8,117,79,.09);
}


/* =========================================================
   CARD TOP
   ========================================================= */

.history-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;

    margin-bottom: 20px;
}

.history-date {

    color: #173c2d;

    font-size: 15px;

    font-weight: 800;
}

.history-time {

    color: #87968f;

    font-size: 11px;

    margin-top: 4px;
}


/* =========================================================
   STRESS BADGE
   ========================================================= */

.stress-badge {

    padding: 7px 12px;

    border-radius: 30px;

    font-size: 10px;

    font-weight: 800;

    white-space: nowrap;
}

.stress-badge.low {

    background: #e9f8ef;

    color: #08754f;
}

.stress-badge.moderate {

    background: #fff6df;

    color: #9a7000;
}

.stress-badge.high {

    background: #fff0e5;

    color: #b55b00;
}

.stress-badge.very-high {

    background: #ffeaea;

    color: #b12c2c;
}


/* =========================================================
   INFORMATION GRID
   ========================================================= */

.history-info {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 12px;

    margin-bottom: 18px;
}

.info-box {

    background: #f7fbf8;

    border-radius: 13px;

    padding: 14px;
}

.info-label {

    color: #82928b;

    font-size: 9px;

    text-transform: uppercase;

    letter-spacing: .5px;
}

.info-value {

    color: #173c2d;

    font-size: 13px;

    font-weight: 800;

    margin-top: 5px;

    word-break: break-word;
}


/* =========================================================
   EMOTION
   ========================================================= */

.emotion-value {

    display: flex;

    align-items: center;

    gap: 7px;
}


/* =========================================================
   ANALYSIS
   ========================================================= */

.analysis-box {

    background:
        linear-gradient(
            135deg,
            #edf9f3,
            #f8fffb
        );

    border:
        1px solid #dceee4;

    border-radius: 14px;

    padding: 15px;

    margin-bottom: 18px;
}

.analysis-title {

    color: #08754f;

    font-size: 11px;

    font-weight: 800;

    margin-bottom: 5px;
}

.analysis-text {

    color: #61756b;

    font-size: 11px;

    line-height: 1.7;
}


/* =========================================================
   CARD FOOTER
   ========================================================= */

.history-footer {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    padding-top: 15px;

    border-top:
        1px solid #edf2ef;
}

.question-score {

    color: #87958f;

    font-size: 10px;
}

.history-actions {

    display: flex;

    gap: 7px;

    flex-wrap: wrap;
}


/* =========================================================
   EMPTY
   ========================================================= */

.empty-state {

    background: white;

    border:
        1px solid #e2eee7;

    border-radius: 22px;

    text-align: center;

    padding: 55px 25px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.035);
}

.empty-icon {

    font-size: 50px;

    margin-bottom: 12px;
}

.empty-state h3 {

    color: #173c2d;

    margin: 0 0 8px;
}

.empty-state p {

    color: #7b8d85;

    font-size: 12px;

    line-height: 1.7;

    max-width: 500px;

    margin: 0 auto 20px;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width: 850px) {

    .history-stats {

        grid-template-columns:
            1fr 1fr;
    }

    .history-info {

        grid-template-columns:
            1fr 1fr;
    }

}

@media(max-width: 550px) {

    .container {

        padding:
            20px 14px 45px;
    }

    .history-hero {

        padding: 25px 20px;
    }

    .history-hero h1 {

        font-size: 24px;
    }

    .history-stats {

        grid-template-columns: 1fr;
    }

    .history-info {

        grid-template-columns: 1fr 1fr;
    }

    .history-top {

        flex-direction: column;
    }

    .history-footer {

        flex-direction: column;

        align-items: flex-start;
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

        <a
            class="nav-btn"
            href="dashboard.php"
        >
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>

        <a
            class="nav-btn"
            href="assessment.php"
        >
            <i class="fa-solid fa-clipboard-check"></i>
            New Assessment
        </a>

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

<section class="history-hero">

    <h1>
        Your Wellbeing Journey 🌿
    </h1>

    <p>
        Review your previous emotional check-ins, stress levels,
        concerns and personalized recommendations. Looking back
        can help you understand how your wellbeing changes over time.
    </p>

</section>



<!-- =======================================================
     STATS
     ======================================================= -->

<div class="history-stats">


    <div class="stat-card">

        <div class="stat-icon">

            <i class="fa-solid fa-clipboard-check"></i>

        </div>

        <div>

            <div class="stat-label">
                Total Assessments
            </div>

            <div class="stat-value">
                <?= $totalAssessments ?>
            </div>

        </div>

    </div>



    <div class="stat-card">

        <div class="stat-icon">

            <i class="fa-solid fa-heart-pulse"></i>

        </div>

        <div>

            <div class="stat-label">
                Your Check-ins
            </div>

            <div class="stat-value">
                Personal
            </div>

        </div>

    </div>



    <div class="stat-card">

        <div class="stat-icon">

            <i class="fa-solid fa-seedling"></i>

        </div>

        <div>

            <div class="stat-label">
                Purpose
            </div>

            <div class="stat-value">
                Progress
            </div>

        </div>

    </div>


</div>



<!-- =======================================================
     SECTION
     ======================================================= -->

<div class="section-title">

    <h2>
        Previous Assessments
    </h2>

    <p>
        Your most recent check-ins appear first.
    </p>

</div>



<?php if ($result->num_rows > 0): ?>


<div class="history-list">


<?php while ($x = $result->fetch_assoc()): ?>


<?php

$stress =
    (int) ($x['stress_level'] ?? 0);

$emotion =
    trim(
        $x['emotion']
        ?: $x['mood']
        ?: 'Neutral'
    );

$situation =
    trim(
        $x['situation']
        ?: 'General Stress'
    );

$sleep =
    (float) (
        $x['sleep_hours']
        ?? 0
    );

$stressCategory =
    getStressCategory($stress);

$stressClass =
    getStressClass($stress);

$emotionIcon =
    getEmotionIcon($emotion);

$analysis =
    trim(
        $x['analysis']
        ?? ''
    );


/*
 * Extract the raw question score if it exists.
 */

$questionScore = '';

if (
    preg_match(
        '/Question score:\s*(\d+)\s*\/\s*(\d+)/i',
        $x['notes'] ?? '',
        $matches
    )
) {

    $questionScore =
        $matches[1] . '/' . $matches[2];
}

?>


<article class="history-card">


    <!-- CARD TOP -->

    <div class="history-top">

        <div>

            <div class="history-date">

                <?= date(
                    'd M Y',
                    strtotime($x['created_at'])
                ) ?>

            </div>

            <div class="history-time">

                <?= date(
                    'h:i A',
                    strtotime($x['created_at'])
                ) ?>

            </div>

        </div>


        <div class="stress-badge <?= $stressClass ?>">

            <?= htmlspecialchars(
                $stressCategory
            ) ?>

            ·

            <?= $stress ?>/10

        </div>

    </div>



    <!-- INFORMATION -->

    <div class="history-info">


        <!-- EMOTION -->

        <div class="info-box">

            <div class="info-label">
                Emotion
            </div>

            <div class="info-value emotion-value">

                <span>
                    <?= $emotionIcon ?>
                </span>

                <span>
                    <?= htmlspecialchars(
                        $emotion
                    ) ?>
                </span>

            </div>

        </div>


        <!-- CONCERN -->

        <div class="info-box">

            <div class="info-label">
                Main Concern
            </div>

            <div class="info-value">

                <?= htmlspecialchars(
                    $situation
                ) ?>

            </div>

        </div>


        <!-- STRESS -->

        <div class="info-box">

            <div class="info-label">
                Stress Level
            </div>

            <div class="info-value">

                <?= $stress ?>/10

            </div>

        </div>


        <!-- SLEEP -->

        <div class="info-box">

            <div class="info-label">
                Sleep
            </div>

            <div class="info-value">

                <?= htmlspecialchars(
                    $sleep
                ) ?>

                hrs

            </div>

        </div>


    </div>



    <!-- ANALYSIS -->

    <?php if ($analysis !== ''): ?>

    <div class="analysis-box">

        <div class="analysis-title">

            <i class="fa-solid fa-chart-line"></i>

            Assessment Analysis

        </div>

        <div class="analysis-text">

            <?= htmlspecialchars(
                $analysis
            ) ?>

        </div>

    </div>

    <?php endif; ?>



    <!-- FOOTER -->

    <div class="history-footer">


        <div class="question-score">

            <?php if ($questionScore !== ''): ?>

                <i class="fa-solid fa-list-check"></i>

                Question Score:
                <?= htmlspecialchars(
                    $questionScore
                ) ?>

            <?php else: ?>

                <i class="fa-solid fa-chart-simple"></i>

                Application-based assessment

            <?php endif; ?>

        </div>


        <div class="history-actions">

            <a
                class="btn btn-secondary"
                href="recommendations.php?assessment_id=<?= (int) $x['id'] ?>"
            >

                <i class="fa-solid fa-leaf"></i>

                View Recommendations

            </a>

        </div>


    </div>


</article>


<?php endwhile; ?>


</div>


<?php else: ?>


<!-- =======================================================
     EMPTY STATE
     ======================================================= -->

<div class="empty-state">

    <div class="empty-icon">
        🌱
    </div>

    <h3>
        No Assessments Yet
    </h3>

    <p>
        You have not completed your first wellbeing assessment.
        Take a few minutes to check in with yourself and receive
        personalized recommendations.
    </p>

    <a
        class="btn"
        href="assessment.php"
    >

        <i class="fa-solid fa-clipboard-check"></i>

        Start Your First Assessment

    </a>

</div>


<?php endif; ?>


</main>

</body>

</html>