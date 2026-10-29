<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/session.php";

requireLogin();

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? "User";

/* =========================================================
   HELPER FUNCTIONS
========================================================= */

function stressCategory($stress)
{
    if ($stress <= 3) {
        return "Low";
    } elseif ($stress <= 6) {
        return "Moderate";
    } elseif ($stress <= 8) {
        return "High";
    }

    return "Very High";
}

function stressClass($stress)
{
    if ($stress <= 3) {
        return "low";
    } elseif ($stress <= 6) {
        return "moderate";
    } elseif ($stress <= 8) {
        return "high";
    }

    return "very-high";
}

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* =========================================================
   GET ALL ASSESSMENTS
========================================================= */

$assessments = [];

$stmt = $conn->prepare("
    SELECT
        id,
        stress_level,
        mood,
        emotion,
        situation,
        sleep_hours,
        activities,
        notes,
        analysis,
        created_at
    FROM stress_assessments
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $assessments[] = $row;
}

$stmt->close();

/* =========================================================
   BASIC STATISTICS
========================================================= */

$totalAssessments = count($assessments);

$totalStress = 0;
$highestStress = 0;
$lowestStress = 10;

$totalSleep = 0;
$sleepRecords = 0;

$emotionCounts = [];
$situationCounts = [];

foreach ($assessments as $assessment) {

    $stress = (int)$assessment['stress_level'];

    $totalStress += $stress;

    if ($stress > $highestStress) {
        $highestStress = $stress;
    }

    if ($stress < $lowestStress) {
        $lowestStress = $stress;
    }

    $sleep = (float)$assessment['sleep_hours'];

    if ($sleep > 0) {
        $totalSleep += $sleep;
        $sleepRecords++;
    }

    $emotion = trim($assessment['emotion'] ?? '');

    if ($emotion !== '') {
        if (!isset($emotionCounts[$emotion])) {
            $emotionCounts[$emotion] = 0;
        }

        $emotionCounts[$emotion]++;
    }

    $situation = trim($assessment['situation'] ?? '');

    if ($situation !== '') {
        if (!isset($situationCounts[$situation])) {
            $situationCounts[$situation] = 0;
        }

        $situationCounts[$situation]++;
    }
}

$averageStress = $totalAssessments > 0
    ? round($totalStress / $totalAssessments, 1)
    : 0;

$averageSleep = $sleepRecords > 0
    ? round($totalSleep / $sleepRecords, 1)
    : 0;

/* =========================================================
   WELLNESS SCORE
   Higher = better
========================================================= */

if ($totalAssessments === 0) {

    $wellnessScore = 0;

} else {

    $stressScore = max(0, 100 - ($averageStress * 10));

    if ($averageSleep > 0) {

        if ($averageSleep >= 7 && $averageSleep <= 9) {
            $sleepScore = 100;
        } elseif ($averageSleep >= 6) {
            $sleepScore = 80;
        } elseif ($averageSleep >= 5) {
            $sleepScore = 60;
        } else {
            $sleepScore = 40;
        }

    } else {

        $sleepScore = 70;
    }

    $wellnessScore = round(($stressScore * 0.7) + ($sleepScore * 0.3));
}

/* =========================================================
   WELLNESS STATUS
========================================================= */

if ($wellnessScore >= 80) {

    $wellnessStatus = "Excellent";
    $wellnessMessage = "You're maintaining a healthy balance. Keep taking care of yourself.";

} elseif ($wellnessScore >= 65) {

    $wellnessStatus = "Good";
    $wellnessMessage = "You're doing fairly well. A few healthy habits can make your routine even stronger.";

} elseif ($wellnessScore >= 45) {

    $wellnessStatus = "Needs Attention";
    $wellnessMessage = "Your recent responses suggest that some areas of your wellbeing may need attention.";

} else {

    $wellnessStatus = "Take Care";
    $wellnessMessage = "Your recent stress levels appear elevated. Consider taking some time for rest and recovery.";
}

/* =========================================================
   CURRENT / LATEST ASSESSMENT
========================================================= */

$latestAssessment = $totalAssessments > 0
    ? $assessments[0]
    : null;

$currentStress = $latestAssessment
    ? (int)$latestAssessment['stress_level']
    : 0;

$currentStressCategory = $latestAssessment
    ? stressCategory($currentStress)
    : "Not Available";

$currentStressClass = $latestAssessment
    ? stressClass($currentStress)
    : "neutral";

/* =========================================================
   MOST COMMON EMOTION
========================================================= */

$topEmotion = "Not enough data";

if (!empty($emotionCounts)) {

    arsort($emotionCounts);

    $topEmotion = array_key_first($emotionCounts);
}

/* =========================================================
   MOST COMMON SITUATION
========================================================= */

$topSituation = "Not enough data";

if (!empty($situationCounts)) {

    arsort($situationCounts);

    $topSituation = array_key_first($situationCounts);
}

/* =========================================================
   TREND DATA
========================================================= */

$trendAssessments = array_reverse(array_slice($assessments, 0, 7));

$chartLabels = [];
$chartValues = [];

foreach ($trendAssessments as $assessment) {

    $chartLabels[] = date(
        "d M",
        strtotime($assessment['created_at'])
    );

    $chartValues[] = (int)$assessment['stress_level'];
}

/* =========================================================
   STRESS DISTRIBUTION
========================================================= */

$lowCount = 0;
$moderateCount = 0;
$highCount = 0;
$veryHighCount = 0;

foreach ($assessments as $assessment) {

    $stress = (int)$assessment['stress_level'];

    if ($stress <= 3) {
        $lowCount++;
    } elseif ($stress <= 6) {
        $moderateCount++;
    } elseif ($stress <= 8) {
        $highCount++;
    } else {
        $veryHighCount++;
    }
}

/* =========================================================
   PERSONALIZED MESSAGE
========================================================= */

if ($totalAssessments === 0) {

    $personalMessage =
        "Start with your first stress assessment to understand your current wellbeing.";

} elseif ($currentStress <= 3) {

    $personalMessage =
        "Your latest stress level is low. Keep maintaining the habits that help you feel balanced.";

} elseif ($currentStress <= 6) {

    $personalMessage =
        "Your latest stress level is moderate. Small changes in sleep, routine and relaxation may help.";

} elseif ($currentStress <= 8) {

    $personalMessage =
        "Your latest stress level is high. Consider giving yourself some time to rest and work through the main source of stress.";

} else {

    $personalMessage =
        "Your latest stress level is very high. Consider taking immediate time for rest, support and healthy coping strategies.";
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

    <title>Dashboard | StressCare</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {

            --green: #087f5b;
            --green-dark: #056044;
            --green-light: #e9f8f2;

            --lime: #8bcf39;

            --text: #123c32;
            --muted: #71847c;

            --background: #f4faf7;
            --white: #ffffff;

            --border: #e3eee9;

            --shadow:
                0 12px 35px rgba(8, 127, 91, 0.08);

            --radius: 20px;
        }

        body {

            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Arial,
                sans-serif;

            background: var(--background);

            color: var(--text);

            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* =====================================================
           NAVBAR
        ===================================================== */

        .navbar {

            height: 76px;

            background: rgba(255,255,255,0.95);

            border-bottom: 1px solid var(--border);

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 6%;

            position: sticky;

            top: 0;

            z-index: 100;

            backdrop-filter: blur(12px);
        }

        .brand {

            display: flex;

            align-items: center;

            gap: 12px;
        }

        .brand-icon {

            width: 42px;

            height: 42px;

            border-radius: 13px;

            background: linear-gradient(
                135deg,
                var(--green),
                var(--lime)
            );

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            font-size: 20px;

            font-weight: 800;

            box-shadow:
                0 8px 20px rgba(8,127,91,0.2);
        }

        .brand-text strong {

            display: block;

            font-size: 20px;

            color: var(--green-dark);
        }

        .brand-text span {

            display: block;

            font-size: 11px;

            color: var(--muted);

            margin-top: -3px;
        }

        .nav-right {

            display: flex;

            align-items: center;

            gap: 12px;
        }

        .nav-link {

            padding: 10px 15px;

            border-radius: 10px;

            font-size: 14px;

            font-weight: 600;

            color: var(--muted);

            transition: .2s;
        }

        .nav-link:hover,
        .nav-link.active {

            background: var(--green-light);

            color: var(--green);
        }

        .nav-user {

            display: flex;

            align-items: center;

            gap: 10px;

            padding-left: 15px;

            border-left: 1px solid var(--border);
        }

        .avatar {

            width: 38px;

            height: 38px;

            border-radius: 50%;

            background: var(--green);

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-weight: 800;
        }

        .user-name {

            font-size: 13px;

            font-weight: 700;
        }

        .logout {

            color: #c0392b;

            font-size: 13px;

            font-weight: 700;
        }

        /* =====================================================
           CONTAINER
        ===================================================== */

        .container {

            width: min(1400px, 92%);

            margin: auto;

            padding: 38px 0 70px;
        }

        /* =====================================================
           HERO
        ===================================================== */

        .welcome {

            background:
                radial-gradient(
                    circle at 85% 20%,
                    rgba(139,207,57,0.18),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #ffffff,
                    #effaf5
                );

            border: 1px solid var(--border);

            border-radius: 28px;

            padding: 36px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 30px;

            box-shadow: var(--shadow);

            margin-bottom: 25px;
        }

        .welcome h1 {

            font-size: clamp(27px, 4vw, 40px);

            line-height: 1.15;

            margin-bottom: 10px;
        }

        .welcome h1 span {

            color: var(--green);
        }

        .welcome p {

            color: var(--muted);

            max-width: 650px;

            font-size: 15px;
        }

        .welcome-action {

            flex-shrink: 0;
        }

        .primary-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 14px 21px;

            background: var(--green);

            color: white;

            border-radius: 12px;

            font-weight: 700;

            font-size: 14px;

            box-shadow:
                0 10px 25px rgba(8,127,91,0.2);

            transition: .2s;
        }

        .primary-btn:hover {

            background: var(--green-dark);

            transform: translateY(-2px);
        }

        /* =====================================================
           MAIN GRID
        ===================================================== */

        .main-grid {

            display: grid;

            grid-template-columns: 1.35fr .65fr;

            gap: 25px;

            margin-bottom: 25px;
        }

        /* =====================================================
           WELLNESS CARD
        ===================================================== */

        .wellness-card {

            background: white;

            border: 1px solid var(--border);

            border-radius: var(--radius);

            padding: 28px;

            box-shadow: var(--shadow);
        }

        .card-heading {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;
        }

        .card-heading h2 {

            font-size: 19px;
        }

        .card-heading span {

            font-size: 12px;

            color: var(--muted);
        }

        .wellness-content {

            display: flex;

            align-items: center;

            gap: 35px;
        }

        .score-circle {

            width: 170px;

            height: 170px;

            border-radius: 50%;

            background:
                conic-gradient(
                    var(--green) <?= $wellnessScore ?>%,
                    #e7f0ec <?= $wellnessScore ?>%
                );

            display: flex;

            align-items: center;

            justify-content: center;

            position: relative;

            flex-shrink: 0;
        }

        .score-circle::before {

            content: "";

            position: absolute;

            width: 132px;

            height: 132px;

            border-radius: 50%;

            background: white;
        }

        .score-number {

            position: relative;

            text-align: center;
        }

        .score-number strong {

            display: block;

            font-size: 40px;

            line-height: 1;

            color: var(--green-dark);
        }

        .score-number span {

            color: var(--muted);

            font-size: 12px;
        }

        .wellness-info h3 {

            font-size: 25px;

            margin-bottom: 8px;
        }

        .wellness-info p {

            color: var(--muted);

            font-size: 14px;

            max-width: 520px;
        }

        .wellness-status {

            display: inline-flex;

            margin-top: 15px;

            padding: 7px 12px;

            border-radius: 20px;

            background: var(--green-light);

            color: var(--green);

            font-size: 12px;

            font-weight: 800;
        }

        /* =====================================================
           CURRENT STRESS
        ===================================================== */

        .current-card {

            background: linear-gradient(
                145deg,
                var(--green),
                var(--green-dark)
            );

            border-radius: var(--radius);

            padding: 28px;

            color: white;

            position: relative;

            overflow: hidden;

            box-shadow:
                0 18px 40px rgba(8,127,91,0.2);
        }

        .current-card::after {

            content: "";

            position: absolute;

            width: 170px;

            height: 170px;

            border-radius: 50%;

            border: 30px solid rgba(255,255,255,.08);

            right: -70px;

            bottom: -70px;
        }

        .current-card small {

            opacity: .75;

            font-size: 12px;
        }

        .current-card h2 {

            margin-top: 12px;

            font-size: 18px;
        }

        .current-number {

            font-size: 64px;

            font-weight: 800;

            line-height: 1;

            margin: 15px 0 5px;
        }

        .current-number span {

            font-size: 17px;

            font-weight: 500;

            opacity: .7;
        }

        .stress-pill {

            display: inline-block;

            padding: 6px 12px;

            background: rgba(255,255,255,.15);

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;
        }

        /* =====================================================
           STAT CARDS
        ===================================================== */

        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 25px;
        }

        .stat-card {

            background: white;

            border: 1px solid var(--border);

            border-radius: 17px;

            padding: 22px;

            box-shadow: 0 8px 25px rgba(8,127,91,0.05);
        }

        .stat-top {

            display: flex;

            justify-content: space-between;

            align-items: center;
        }

        .stat-icon {

            width: 40px;

            height: 40px;

            border-radius: 12px;

            background: var(--green-light);

            display: flex;

            align-items: center;

            justify-content: center;

            color: var(--green);

            font-size: 18px;
        }

        .stat-card h3 {

            font-size: 27px;

            margin-top: 14px;
        }

        .stat-card p {

            color: var(--muted);

            font-size: 12px;

            margin-top: 2px;
        }

        /* =====================================================
           ANALYTICS GRID
        ===================================================== */

        .analytics-grid {

            display: grid;

            grid-template-columns: 1.5fr 1fr;

            gap: 25px;

            margin-bottom: 25px;
        }

        .panel {

            background: white;

            border: 1px solid var(--border);

            border-radius: var(--radius);

            padding: 25px;

            box-shadow: var(--shadow);
        }

        .panel-title {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 20px;
        }

        .panel-title h2 {

            font-size: 18px;
        }

        .panel-title span {

            color: var(--muted);

            font-size: 12px;
        }

        /* =====================================================
           CHART
        ===================================================== */

        .chart-area {

            height: 250px;

            position: relative;

            display: flex;

            align-items: flex-end;

            gap: 18px;

            padding:
                15px
                5px
                30px;
        }

        .chart-grid {

            position: absolute;

            inset: 15px 0 30px;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

            pointer-events: none;
        }

        .chart-line {

            border-top: 1px dashed #dce9e4;

            width: 100%;
        }

        .bars {

            width: 100%;

            height: 100%;

            display: flex;

            align-items: flex-end;

            justify-content: space-around;

            position: relative;

            z-index: 2;
        }

        .bar-wrap {

            height: 100%;

            flex: 1;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: flex-end;

            gap: 8px;

            max-width: 70px;
        }

        .bar {

            width: 26px;

            min-height: 5px;

            border-radius: 8px 8px 3px 3px;

            background:
                linear-gradient(
                    180deg,
                    var(--lime),
                    var(--green)
                );

            transition: .3s;
        }

        .bar:hover {

            transform: scaleY(1.04);
        }

        .bar-value {

            font-size: 10px;

            color: var(--green-dark);

            font-weight: 800;
        }

        .bar-label {

            font-size: 10px;

            color: var(--muted);
        }

        /* =====================================================
           INSIGHT PANEL
        ===================================================== */

        .insights {

            display: grid;

            gap: 13px;
        }

        .insight {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 14px;

            border-radius: 13px;

            background: #f8fcfa;

            border: 1px solid #edf5f1;
        }

        .insight-icon {

            width: 40px;

            height: 40px;

            border-radius: 11px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: var(--green-light);

            color: var(--green);

            font-weight: 800;

            flex-shrink: 0;
        }

        .insight strong {

            display: block;

            font-size: 13px;
        }

        .insight span {

            display: block;

            color: var(--muted);

            font-size: 11px;

            margin-top: 2px;
        }

        /* =====================================================
           DISTRIBUTION
        ===================================================== */

        .distribution {

            display: grid;

            gap: 17px;
        }

        .distribution-row {

            display: grid;

            grid-template-columns: 95px 1fr 35px;

            align-items: center;

            gap: 12px;
        }

        .distribution-label {

            font-size: 12px;

            font-weight: 700;
        }

        .progress {

            height: 8px;

            background: #edf3f0;

            border-radius: 20px;

            overflow: hidden;
        }

        .progress-fill {

            height: 100%;

            border-radius: inherit;

            background: var(--green);
        }

        .distribution-number {

            text-align: right;

            font-size: 12px;

            font-weight: 800;

            color: var(--muted);
        }

        /* =====================================================
           RECENT ASSESSMENTS
        ===================================================== */

        .recent-panel {

            background: white;

            border: 1px solid var(--border);

            border-radius: var(--radius);

            overflow: hidden;

            box-shadow: var(--shadow);

            margin-bottom: 25px;
        }

        .recent-header {

            padding: 22px 25px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            border-bottom: 1px solid var(--border);
        }

        .recent-header h2 {

            font-size: 18px;
        }

        .view-all {

            color: var(--green);

            font-size: 12px;

            font-weight: 800;
        }

        .assessment-row {

            display: grid;

            grid-template-columns:
                1.3fr
                .8fr
                .8fr
                .8fr
                .8fr;

            align-items: center;

            gap: 15px;

            padding: 17px 25px;

            border-bottom: 1px solid #edf3f0;
        }

        .assessment-row:last-child {

            border-bottom: none;
        }

        .assessment-date strong {

            display: block;

            font-size: 13px;
        }

        .assessment-date span {

            color: var(--muted);

            font-size: 11px;
        }

        .row-label {

            color: var(--muted);

            font-size: 10px;

            display: block;

            margin-bottom: 3px;
        }

        .row-value {

            font-size: 12px;

            font-weight: 700;
        }

        .stress-badge {

            display: inline-flex;

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: 800;
        }

        .stress-badge.low {

            background: #e8f7ef;

            color: #16834f;
        }

        .stress-badge.moderate {

            background: #fff6dc;

            color: #a97700;
        }

        .stress-badge.high {

            background: #fff0e5;

            color: #c55a14;
        }

        .stress-badge.very-high {

            background: #ffe9e9;

            color: #c0392b;
        }

        .view-btn {

            display: inline-flex;

            padding: 7px 10px;

            border-radius: 9px;

            background: var(--green-light);

            color: var(--green);

            font-size: 10px;

            font-weight: 800;
        }

        /* =====================================================
           BOTTOM ACTIONS
        ===================================================== */

        .bottom-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr 1fr;

            gap: 18px;
        }

        .action-card {

            background: white;

            border: 1px solid var(--border);

            border-radius: 17px;

            padding: 22px;

            transition: .2s;
        }

        .action-card:hover {

            transform: translateY(-3px);

            box-shadow: var(--shadow);

            border-color: #cce7dc;
        }

        .action-icon {

            width: 43px;

            height: 43px;

            border-radius: 12px;

            background: var(--green-light);

            display: flex;

            align-items: center;

            justify-content: center;

            color: var(--green);

            font-size: 18px;

            margin-bottom: 15px;
        }

        .action-card h3 {

            font-size: 15px;

            margin-bottom: 5px;
        }

        .action-card p {

            font-size: 12px;

            color: var(--muted);

            margin-bottom: 15px;
        }

        .action-link {

            color: var(--green);

            font-size: 12px;

            font-weight: 800;
        }

        /* =====================================================
           EMPTY STATE
        ===================================================== */

        .empty {

            text-align: center;

            padding: 45px 20px;
        }

        .empty-icon {

            width: 60px;

            height: 60px;

            border-radius: 18px;

            margin: 0 auto 15px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: var(--green-light);

            color: var(--green);

            font-size: 25px;
        }

        .empty h3 {

            font-size: 18px;

            margin-bottom: 5px;
        }

        .empty p {

            color: var(--muted);

            font-size: 13px;

            margin-bottom: 18px;
        }

        /* =====================================================
           FOOTER
        ===================================================== */

        footer {

            text-align: center;

            color: var(--muted);

            font-size: 11px;

            padding: 25px 0;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .main-grid,
            .analytics-grid {

                grid-template-columns: 1fr;
            }

            .stats-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .bottom-grid {

                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 760px) {

            .navbar {

                padding: 0 4%;
            }

            .nav-link {

                display: none;
            }

            .user-name {

                display: none;
            }

            .welcome {

                flex-direction: column;

                align-items: flex-start;

                padding: 25px;
            }

            .wellness-content {

                flex-direction: column;

                align-items: flex-start;
            }

            .stats-grid {

                grid-template-columns: 1fr 1fr;
            }

            .assessment-row {

                grid-template-columns:
                    1fr
                    1fr;

                gap: 12px;
            }

            .bottom-grid {

                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 500px) {

            .container {

                width: 94%;

                padding-top: 22px;
            }

            .stats-grid {

                grid-template-columns: 1fr;
            }

            .score-circle {

                width: 145px;

                height: 145px;
            }

            .score-circle::before {

                width: 113px;

                height: 113px;
            }

            .score-number strong {

                font-size: 33px;
            }

            .chart-area {

                gap: 5px;
            }

            .bar {

                width: 20px;
            }
        }

    </style>

</head>

<body>

<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar">

    <a href="index.php" class="brand">

        <div class="brand-icon">
            S
        </div>

        <div class="brand-text">

            <strong>StressCare</strong>

            <span>Wellbeing Management</span>

        </div>

    </a>

    <div class="nav-right">

        <a
            href="dashboard.php"
            class="nav-link active"
        >
            Dashboard
        </a>

        <a
            href="assessment.php"
            class="nav-link"
        >
            Assessment
        </a>

        <a
            href="history.php"
            class="nav-link"
        >
            History
        </a>

        <div class="nav-user">

            <div class="avatar">

                <?= strtoupper(substr($userName, 0, 1)); ?>

            </div>

            <div class="user-name">

                <?= escape($userName); ?>

            </div>

            <a
                href="logout.php"
                class="logout"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<main class="container">

<!-- =========================================================
     WELCOME
========================================================= -->

<section class="welcome">

    <div>

        <h1>
            Welcome back,
            <span><?= escape($userName); ?></span>
        </h1>

        <p>
            <?= escape($personalMessage); ?>
            Your StressCare dashboard gives you a clear view
            of your recent emotional wellbeing and stress patterns.
        </p>

    </div>

    <div class="welcome-action">

        <a
            href="assessment.php"
            class="primary-btn"
        >
            + New Assessment
        </a>

    </div>

</section>


<!-- =========================================================
     WELLNESS + CURRENT STRESS
========================================================= -->

<section class="main-grid">

    <div class="wellness-card">

        <div class="card-heading">

            <h2>Overall Wellness Score</h2>

            <span>
                Based on your recent responses
            </span>

        </div>

        <div class="wellness-content">

            <div class="score-circle">

                <div class="score-number">

                    <strong>
                        <?= $wellnessScore; ?>
                    </strong>

                    <span>
                        / 100
                    </span>

                </div>

            </div>

            <div class="wellness-info">

                <h3>
                    <?= escape($wellnessStatus); ?>
                </h3>

                <p>
                    <?= escape($wellnessMessage); ?>
                </p>

                <div class="wellness-status">

                    Wellness Status:
                    <?= escape($wellnessStatus); ?>

                </div>

            </div>

        </div>

    </div>


    <div class="current-card">

        <small>
            LATEST ASSESSMENT
        </small>

        <h2>
            Current Stress Level
        </h2>

        <?php if ($latestAssessment): ?>

            <div class="current-number">

                <?= $currentStress; ?>

                <span>/ 10</span>

            </div>

            <div class="stress-pill">

                <?= escape($currentStressCategory); ?>

            </div>

            <p style="
                margin-top:18px;
                font-size:12px;
                opacity:.78;
            ">

                <?= date(
                    "d M Y, h:i A",
                    strtotime($latestAssessment['created_at'])
                ); ?>

            </p>

        <?php else: ?>

            <div class="current-number">
                —
            </div>

            <div class="stress-pill">
                No assessment yet
            </div>

        <?php endif; ?>

    </div>

</section>


<!-- =========================================================
     STATISTICS
========================================================= -->

<section class="stats-grid">

    <div class="stat-card">

        <div class="stat-top">

            <div>

                <h3>
                    <?= $totalAssessments; ?>
                </h3>

                <p>Total Assessments</p>

            </div>

            <div class="stat-icon">
                ✓
            </div>

        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">

            <div>

                <h3>
                    <?= $averageStress; ?>
                </h3>

                <p>Average Stress</p>

            </div>

            <div class="stat-icon">
                %
            </div>

        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">

            <div>

                <h3>
                    <?= $averageSleep > 0
                        ? $averageSleep . "h"
                        : "—"; ?>
                </h3>

                <p>Average Sleep</p>

            </div>

            <div class="stat-icon">
                Z
            </div>

        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">

            <div>

                <h3>
                    <?= $highestStress ?: "—"; ?>
                </h3>

                <p>Highest Stress</p>

            </div>

            <div class="stat-icon">
                !
            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     ANALYTICS
========================================================= -->

<section class="analytics-grid">

    <!-- STRESS TREND -->

    <div class="panel">

        <div class="panel-title">

            <h2>Stress Trend</h2>

            <span>
                Last 7 assessments
            </span>

        </div>

        <?php if (!empty($chartValues)): ?>

            <div class="chart-area">

                <div class="chart-grid">

                    <div class="chart-line"></div>
                    <div class="chart-line"></div>
                    <div class="chart-line"></div>
                    <div class="chart-line"></div>
                    <div class="chart-line"></div>

                </div>

                <div class="bars">

                    <?php foreach ($chartValues as $index => $value): ?>

                        <div class="bar-wrap">

                            <span class="bar-value">
                                <?= $value; ?>
                            </span>

                            <div
                                class="bar"
                                style="
                                    height:
                                    <?= max(5, $value * 10); ?>%;
                                "
                            ></div>

                            <span class="bar-label">

                                <?= escape(
                                    $chartLabels[$index]
                                ); ?>

                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php else: ?>

            <div class="empty">

                <div class="empty-icon">
                    +
                </div>

                <h3>
                    No trend data yet
                </h3>

                <p>
                    Complete your first assessment
                    to start tracking your stress pattern.
                </p>

                <a
                    href="assessment.php"
                    class="primary-btn"
                >
                    Start Assessment
                </a>

            </div>

        <?php endif; ?>

    </div>


    <!-- INSIGHTS -->

    <div class="panel">

        <div class="panel-title">

            <h2>Personal Insights</h2>

            <span>Based on your data</span>

        </div>

        <div class="insights">

            <div class="insight">

                <div class="insight-icon">
                    ♥
                </div>

                <div>

                    <strong>
                        Most Common Emotion
                    </strong>

                    <span>
                        <?= escape($topEmotion); ?>
                    </span>

                </div>

            </div>


            <div class="insight">

                <div class="insight-icon">
                    !
                </div>

                <div>

                    <strong>
                        Main Stress Source
                    </strong>

                    <span>
                        <?= escape($topSituation); ?>
                    </span>

                </div>

            </div>


            <div class="insight">

                <div class="insight-icon">
                    Z
                </div>

                <div>

                    <strong>
                        Sleep Pattern
                    </strong>

                    <span>

                        <?php if ($averageSleep >= 7): ?>

                            Your average sleep is within
                            a healthy range.

                        <?php elseif ($averageSleep > 0): ?>

                            Your average sleep may need
                            some improvement.

                        <?php else: ?>

                            No sleep data available yet.

                        <?php endif; ?>

                    </span>

                </div>

            </div>


            <div class="insight">

                <div class="insight-icon">
                    ↑
                </div>

                <div>

                    <strong>
                        Current Status
                    </strong>

                    <span>
                        <?= escape($currentStressCategory); ?>
                        stress level
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     STRESS DISTRIBUTION
========================================================= -->

<section class="panel" style="margin-bottom:25px;">

    <div class="panel-title">

        <h2>Stress Distribution</h2>

        <span>
            All recorded assessments
        </span>

    </div>

    <div class="distribution">

        <?php

        $distribution = [

            [
                "label" => "Low",
                "count" => $lowCount,
                "class" => "low"
            ],

            [
                "label" => "Moderate",
                "count" => $moderateCount,
                "class" => "moderate"
            ],

            [
                "label" => "High",
                "count" => $highCount,
                "class" => "high"
            ],

            [
                "label" => "Very High",
                "count" => $veryHighCount,
                "class" => "very-high"
            ]

        ];

        foreach ($distribution as $item):

            $percentage =
                $totalAssessments > 0
                ? round(
                    ($item['count'] / $totalAssessments) * 100
                )
                : 0;

        ?>

            <div class="distribution-row">

                <div class="distribution-label">

                    <?= escape($item['label']); ?>

                </div>

                <div class="progress">

                    <div
                        class="progress-fill"
                        style="
                            width: <?= $percentage; ?>%;
                        "
                    ></div>

                </div>

                <div class="distribution-number">

                    <?= $item['count']; ?>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</section>


<!-- =========================================================
     RECENT ASSESSMENTS
========================================================= -->

<section class="recent-panel">

    <div class="recent-header">

        <h2>
            Recent Assessments
        </h2>

        <a
            href="history.php"
            class="view-all"
        >
            View Full History →
        </a>

    </div>


    <?php if (!empty($assessments)): ?>

        <?php foreach (
            array_slice($assessments, 0, 5)
            as $assessment
        ): ?>

            <div class="assessment-row">

                <div class="assessment-date">

                    <strong>

                        <?= date(
                            "d M Y",
                            strtotime(
                                $assessment['created_at']
                            )
                        ); ?>

                    </strong>

                    <span>

                        <?= date(
                            "h:i A",
                            strtotime(
                                $assessment['created_at']
                            )
                        ); ?>

                    </span>

                </div>


                <div>

                    <span class="row-label">
                        Stress
                    </span>

                    <span
                        class="
                            stress-badge
                            <?= stressClass(
                                (int)$assessment['stress_level']
                            ); ?>
                        "
                    >

                        <?= (int)$assessment['stress_level']; ?>/10

                    </span>

                </div>


                <div>

                    <span class="row-label">
                        Emotion
                    </span>

                    <span class="row-value">

                        <?= escape(
                            $assessment['emotion']
                            ?: $assessment['mood']
                            ?: "Not specified"
                        ); ?>

                    </span>

                </div>


                <div>

                    <span class="row-label">
                        Concern
                    </span>

                    <span class="row-value">

                        <?= escape(
                            $assessment['situation']
                            ?: "General"
                        ); ?>

                    </span>

                </div>


                <div>

                    <a
                        href="
                            recommendations.php
                            ?assessment_id=
                            <?= (int)$assessment['id']; ?>
                        "
                        class="view-btn"
                    >
                        View Recommendations
                    </a>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty">

            <div class="empty-icon">
                +
            </div>

            <h3>
                Your dashboard is ready
            </h3>

            <p>
                Complete your first stress assessment
                to unlock personalized insights.
            </p>

            <a
                href="assessment.php"
                class="primary-btn"
            >
                Take First Assessment
            </a>

        </div>

    <?php endif; ?>

</section>


<!-- =========================================================
     QUICK ACTIONS
========================================================= -->

<section class="bottom-grid">

    <a
        href="assessment.php"
        class="action-card"
    >

        <div class="action-icon">
            +
        </div>

        <h3>
            New Assessment
        </h3>

        <p>
            Record your current stress,
            emotions, sleep and concerns.
        </p>

        <span class="action-link">
            Start Assessment →
        </span>

    </a>


    <a
        href="recommendations.php<?= $latestAssessment ? '?assessment_id=' . (int)$latestAssessment['id'] : ''; ?>"
        class="action-card"
    >

        <div class="action-icon">
            ♥
        </div>

        <h3>
            Personalized Guidance
        </h3>

        <p>
            View recommendations based on
            your latest assessment.
        </p>

        <span class="action-link">
            View Recommendations →
        </span>

    </a>


    <a
        href="report.php"
        class="action-card"
    >

        <div class="action-icon">
            ↗
        </div>

        <h3>
            Wellness Report
        </h3>

        <p>
            Review your overall stress
            and wellbeing information.
        </p>

        <span class="action-link">
            View Report →
        </span>

    </a>

</section>


<footer>

    StressCare © <?= date("Y"); ?>
    · Personal Stress Management System

</footer>

</main>

</body>

</html>