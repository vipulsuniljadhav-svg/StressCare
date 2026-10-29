<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/includes/recommendation_engine.php";

requireLogin();

$uid = (int) $_SESSION['user_id'];

$assessmentId = (int) ($_GET['assessment_id'] ?? 0);

$assessment = null;


/* =========================================================
   GET REQUESTED ASSESSMENT
   ========================================================= */

if ($assessmentId > 0) {

    $stmt = $conn->prepare("
        SELECT *
        FROM stress_assessments
        WHERE id = ?
        AND user_id = ?
        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $assessmentId,
        $uid
    );

    $stmt->execute();

    $assessment =
        $stmt->get_result()->fetch_assoc();

    $stmt->close();
}


/* =========================================================
   GET LATEST ASSESSMENT IF NO VALID ID
   ========================================================= */

if (!$assessment) {

    $stmt = $conn->prepare("
        SELECT *
        FROM stress_assessments
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $assessment =
        $stmt->get_result()->fetch_assoc();

    $stmt->close();
}


/* =========================================================
   NO ASSESSMENT
   ========================================================= */

if (!$assessment) {

    header(
        "Location: assessment.php"
    );

    exit();
}


/* =========================================================
   ASSESSMENT DATA
   ========================================================= */

$stress =
    (int) ($assessment['stress_level'] ?? 0);

$emotion =
    trim(
        $assessment['emotion']
        ?? $assessment['mood']
        ?? "Neutral"
    );

$situation =
    trim(
        $assessment['situation']
        ?? "General Stress"
    );

$sleep =
    (float) ($assessment['sleep_hours'] ?? 0);

$analysis =
    $assessment['analysis']
    ?? "Stress Assessment";

$activities =
    trim(
        $assessment['activities']
        ?? ""
    );

$notes =
    trim(
        $assessment['notes']
        ?? ""
    );


/* =========================================================
   STRESS CATEGORY
   ========================================================= */

if ($stress <= 3) {

    $stressCategory =
        "Low Stress";

    $stressDescription =
        "Your responses indicate relatively low stress at this time.";

    $stressClass =
        "low";

} elseif ($stress <= 6) {

    $stressCategory =
        "Moderate Stress";

    $stressDescription =
        "Your responses suggest that you are experiencing some pressure that may benefit from healthy coping strategies.";

    $stressClass =
        "moderate";

} elseif ($stress <= 8) {

    $stressCategory =
        "High Stress";

    $stressDescription =
        "Your responses suggest significant pressure. Consider giving yourself time for rest, support and manageable next steps.";

    $stressClass =
        "high";

} else {

    $stressCategory =
        "Very High Stress";

    $stressDescription =
        "Your responses indicate a high level of current pressure. Consider prioritizing support, rest and practical coping strategies.";

    $stressClass =
        "very-high";
}


/* =========================================================
   EMOTION ICONS
   ========================================================= */

$emotionIcons = [

    "Happy" =>
        "😊",

    "Calm" =>
        "😌",

    "Sad" =>
        "😔",

    "Angry" =>
        "😠",

    "Anxious" =>
        "😰",

    "Lonely" =>
        "😞",

    "Fearful" =>
        "😨",

    "Hurt" =>
        "💔",

    "Disappointed" =>
        "😕",

    "Frustrated" =>
        "😣",

    "Confused" =>
        "😵",

    "Tired" =>
        "😴",

    "Stressed" =>
        "😫",

    "Overwhelmed" =>
        "🤯",

    "Neutral" =>
        "😐"

];

$emotionIcon =
    $emotionIcons[$emotion]
    ?? "💚";


/* =========================================================
   RECOMMENDATIONS
   ========================================================= */

$recommendations = [];

try {

    $recommendations =
        getRecommendations(
            $conn,
            $emotion,
            $situation,
            $stress,
            8
        );

} catch (Throwable $e) {

    $recommendations = [];

}


/* =========================================================
   FALLBACK RECOMMENDATIONS
   ========================================================= */

if (!$recommendations) {

    $recommendations = [

        [
            "title" =>
                "Take One Step at a Time",

            "description" =>
                "You do not need to solve everything at once. Focus on one manageable concern and work through it gradually.",

            "activity" =>
                "Write down your biggest concern and choose one small action you can complete today.",

            "category" =>
                "Mindset"
        ],

        [
            "title" =>
                "Give Yourself a Short Reset",

            "description" =>
                "A short break can give your mind an opportunity to slow down and approach the situation more calmly.",

            "activity" =>
                "Try five slow breaths. Inhale for approximately 4 seconds and exhale for approximately 6 seconds.",

            "category" =>
                "Relaxation"
        ],

        [
            "title" =>
                $sleep < 6
                ? "Prioritize Your Sleep Tonight"
                : "Protect Your Sleep Routine",

            "description" =>
                $sleep < 6
                ? "Getting enough rest may make it easier to think clearly and manage everyday pressure."
                : "Maintaining a consistent sleep routine can support recovery and daily functioning.",

            "activity" =>
                "Reduce screen use before bed and give yourself enough time to rest.",

            "category" =>
                "Sleep"
        ],

        [
            "title" =>
                "Talk to Someone You Trust",

            "description" =>
                "You do not always have to handle difficult situations alone. A supportive conversation can make a problem feel more manageable.",

            "activity" =>
                "Talk to a trusted friend, family member, teacher, mentor or another supportive person.",

            "category" =>
                "Support"
        ],

        [
            "title" =>
                "Do Something That Helps You Recharge",

            "description" =>
                "A small enjoyable activity can give your mind a break from constant worrying or overthinking.",

            "activity" =>
                "Take a walk, listen to music, exercise lightly, read or spend time doing something you enjoy.",

            "category" =>
                "Wellness"
        ],

        [
            "title" =>
                "Be Patient With Yourself",

            "description" =>
                "Experiencing stress does not mean that you are failing. Difficult periods can be managed gradually.",

            "activity" =>
                "Write down one thing you handled well today.",

            "category" =>
                "Self Care"
        ]

    ];
}


/* =========================================================
   ADD SITUATION-SPECIFIC FALLBACKS
   ========================================================= */

$specificRecommendations = [

    "Friends / Friendship" => [

        "title" =>
            "Communicate Before Assuming",

        "description" =>
            "Friendship problems can become more stressful when thoughts and assumptions remain unspoken.",

        "activity" =>
            "If appropriate, calmly explain how a situation made you feel instead of assuming what the other person meant.",

        "category" =>
            "Friendship"

    ],

    "Relationship / Love" => [

        "title" =>
            "Give Your Feelings Space",

        "description" =>
            "Relationship uncertainty can create repeated overthinking. Giving yourself time to understand your own feelings can help.",

        "activity" =>
            "Write down what you feel, what you know for certain and what you are only assuming.",

        "category" =>
            "Relationships"

    ],

    "Family Problems" => [

        "title" =>
            "Choose Calm Communication",

        "description" =>
            "Family disagreements can become more manageable when conversations happen after emotions have settled.",

        "activity" =>
            "Choose a calm moment to discuss one specific issue instead of trying to solve everything at once.",

        "category" =>
            "Family"

    ],

    "College / Studies" => [

        "title" =>
            "Break Your Work Into Smaller Goals",

        "description" =>
            "Academic pressure often feels larger when many tasks are viewed together.",

        "activity" =>
            "Choose your most important academic task and work on it for one focused study session.",

        "category" =>
            "Studies"

    ],

    "Work / Career" => [

        "title" =>
            "Separate Today From the Future",

        "description" =>
            "Career uncertainty can cause you to worry about problems that have not happened yet.",

        "activity" =>
            "Write down one career action you can realistically take this week.",

        "category" =>
            "Career"

    ],

    "Financial Problems" => [

        "title" =>
            "Create One Clear Financial Step",

        "description" =>
            "Financial worries can feel overwhelming when everything is considered at once.",

        "activity" =>
            "Write down your most important expense or financial priority and focus on that first.",

        "category" =>
            "Financial Wellbeing"

    ],

    "Social Pressure" => [

        "title" =>
            "Focus on Your Own Progress",

        "description" =>
            "Constant comparison can make your own progress difficult to recognize.",

        "activity" =>
            "Write down three things you have improved at without comparing yourself with anyone else.",

        "category" =>
            "Confidence"

    ],

    "Breakup / Loss" => [

        "title" =>
            "Allow Yourself Time to Process",

        "description" =>
            "Loss can bring sadness, memories and uncertainty. Recovery does not have to happen immediately.",

        "activity" =>
            "Give yourself permission to experience your emotions without judging yourself for having them.",

        "category" =>
            "Emotional Recovery"

    ],

    "Loneliness" => [

        "title" =>
            "Make One Small Connection",

        "description" =>
            "You do not need to suddenly become highly social. One genuine interaction can be a useful first step.",

        "activity" =>
            "Message or speak with one person you feel comfortable around.",

        "category" =>
            "Connection"

    ],

    "Conflict / Anger" => [

        "title" =>
            "Pause Before Responding",

        "description" =>
            "Creating a short pause before reacting can help you respond rather than react emotionally.",

        "activity" =>
            "When you notice anger rising, step away briefly and take several slow breaths before continuing the conversation.",

        "category" =>
            "Emotional Control"

    ],

    "Self Confidence" => [

        "title" =>
            "Challenge One Negative Thought",

        "description" =>
            "Self-doubt can make mistakes feel larger than they really are.",

        "activity" =>
            "Write down one negative thought and then write one piece of evidence that shows it may not be completely true.",

        "category" =>
            "Confidence"

    ],

    "Time Management" => [

        "title" =>
            "Choose Your Top Three Tasks",

        "description" =>
            "Trying to complete everything at once can increase mental pressure.",

        "activity" =>
            "Choose only three important tasks for today and complete them one at a time.",

        "category" =>
            "Productivity"

    ],

    "Sleep Problems" => [

        "title" =>
            "Create a Calmer Bedtime Routine",

        "description" =>
            "A consistent and calmer pre-sleep routine can help create a better environment for rest.",

        "activity" =>
            "Reduce stimulating activities before bed and keep your bedtime routine consistent.",

        "category" =>
            "Sleep"

    ],

    "Social Media" => [

        "title" =>
            "Create a Short Digital Break",

        "description" =>
            "Constant online activity can increase comparison, distraction and mental fatigue.",

        "activity" =>
            "Take a 30-minute break from social media and use that time for an offline activity.",

        "category" =>
            "Digital Wellbeing"

    ],

    "General Stress" => [

        "title" =>
            "Identify Your Biggest Pressure",

        "description" =>
            "General stress can feel vague when many different responsibilities are mixed together.",

        "activity" =>
            "Write down everything currently worrying you and circle the one issue you can influence first.",

        "category" =>
            "Stress Management"

    ]

];


/* =========================================================
   ADD SPECIFIC RECOMMENDATION
   ========================================================= */

if (
    isset(
        $specificRecommendations[$situation]
    )
) {

    array_unshift(
        $recommendations,
        $specificRecommendations[$situation]
    );
}


/* =========================================================
   LIMIT TO 8
   ========================================================= */

$recommendations =
    array_slice(
        $recommendations,
        0,
        8
    );


/* =========================================================
   POSITIVE MESSAGE
   ========================================================= */

if ($stress <= 3) {

    $message =
        "You are doing well. Keep building healthy habits and give yourself credit for your progress.";

} elseif ($stress <= 6) {

    $message =
        "You may be carrying some pressure right now. Small, consistent steps can help you feel more balanced.";

} elseif ($stress <= 8) {

    $message =
        "You are dealing with significant pressure. Be kind to yourself and focus on one manageable step at a time.";

} else {

    $message =
        "You are dealing with a lot right now. You do not have to solve everything at once. Start with one small step and consider reaching out to someone you trust.";
}


/* =========================================================
   CATEGORY ICONS
   ========================================================= */

$categoryIcons = [

    "Mindset" =>
        "fa-brain",

    "Relaxation" =>
        "fa-wind",

    "Sleep" =>
        "fa-moon",

    "Support" =>
        "fa-hand-holding-heart",

    "Wellness" =>
        "fa-leaf",

    "Self Care" =>
        "fa-heart",

    "Friendship" =>
        "fa-users",

    "Relationships" =>
        "fa-heart",

    "Family" =>
        "fa-house",

    "Studies" =>
        "fa-graduation-cap",

    "Career" =>
        "fa-briefcase",

    "Financial Wellbeing" =>
        "fa-wallet",

    "Confidence" =>
        "fa-star",

    "Emotional Recovery" =>
        "fa-seedling",

    "Connection" =>
        "fa-comments",

    "Emotional Control" =>
        "fa-scale-balanced",

    "Productivity" =>
        "fa-list-check",

    "Digital Wellbeing" =>
        "fa-mobile-screen",

    "Stress Management" =>
        "fa-spa"

];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Personalized Recommendations | StressCare
</title>


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

    background: #f4faf7;

    padding-bottom: 100px;

}


/* =========================================================
   NAVBAR
   ========================================================= */

.navbar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 6%;

}

.nav-links {

    display: flex;

    gap: 10px;

    align-items: center;

}

.nav-btn {

    display: inline-flex;

    align-items: center;

    padding: 10px 15px;

    border-radius: 10px;

    background: #e9f8f2;

    color: #087f5b;

    font-size: 12px;

    font-weight: 800;

}


/* =========================================================
   HERO
   ========================================================= */

.recommendation-hero {

    background:

        radial-gradient(
            circle at 85% 15%,
            rgba(139,207,57,.18),
            transparent 30%
        ),

        linear-gradient(
            135deg,
            #ffffff,
            #effaf5
        );

    border:
        1px solid #dfeee8;

    border-radius: 25px;

    padding: 32px;

    margin-bottom: 24px;

    box-shadow:
        0 15px 40px rgba(8,127,91,.07);

}

.hero-icon {

    width: 55px;

    height: 55px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 16px;

    background:

        linear-gradient(
            135deg,
            #087f5b,
            #8bcf39
        );

    color: white;

    font-size: 26px;

    margin-bottom: 15px;

}

.recommendation-hero h1 {

    font-size:
        clamp(25px,4vw,38px);

    margin-bottom: 8px;

}

.recommendation-hero p {

    color: #71847c;

    font-size: 13px;

    max-width: 720px;

    line-height: 1.7;

}


/* =========================================================
   SUMMARY
   ========================================================= */

.summary-grid {

    display:
        grid;

    grid-template-columns:
        repeat(4,1fr);

    gap: 15px;

    margin-bottom: 25px;

}

.summary-card {

    background: white;

    border:
        1px solid #e2eee8;

    border-radius: 17px;

    padding: 21px;

    text-align: center;

    box-shadow:
        0 8px 25px rgba(8,127,91,.05);

}

.summary-icon {

    width: 44px;

    height: 44px;

    margin:
        0 auto 10px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        #e9f8f2;

    font-size: 22px;

}

.summary-label {

    font-size: 10px;

    color: #7a8b84;

    font-weight: 700;

}

.summary-value {

    font-size: 15px;

    font-weight: 800;

    margin-top: 4px;

    word-break: break-word;

}


/* =========================================================
   STRESS
   ========================================================= */

.stress-value {

    font-size: 26px;

    font-weight: 900;

}

.stress-low {
    color: #16834f;
}

.stress-moderate {
    color: #a97700;
}

.stress-high {
    color: #c55a14;
}

.stress-very-high {
    color: #c0392b;
}


/* =========================================================
   ANALYSIS
   ========================================================= */

.analysis-card {

    background:
        #edf9f3;

    border:
        1px solid #d8eee1;

    border-radius: 18px;

    padding: 22px;

    display: flex;

    gap: 14px;

    align-items: flex-start;

    margin-bottom: 30px;

}

.analysis-icon {

    width: 43px;

    height: 43px;

    border-radius: 12px;

    background:
        #087f5b;

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

}

.analysis-card h3 {

    font-size: 15px;

    margin-bottom: 5px;

}

.analysis-card p {

    color: #71847c;

    font-size: 12px;

    line-height: 1.7;

}


/* =========================================================
   SECTION
   ========================================================= */

.section-header {

    margin-bottom: 18px;

}

.section-header h2 {

    font-size: 23px;

    margin-bottom: 5px;

}

.section-header p {

    color: #71847c;

    font-size: 12px;

}


/* =========================================================
   RECOMMENDATION GRID
   ========================================================= */

.recommendation-grid {

    display:
        grid;

    grid-template-columns:
        repeat(2,1fr);

    gap: 18px;

    margin-bottom: 28px;

}

.recommendation-card {

    background: white;

    border:
        1px solid #e2eee8;

    border-radius: 18px;

    padding: 22px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 8px 25px rgba(8,127,91,.05);

    transition:
        transform .2s,
        box-shadow .2s;

}

.recommendation-card:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 15px 35px rgba(8,127,91,.1);

}

.recommendation-card::before {

    content: "";

    position: absolute;

    left: 0;

    top: 0;

    bottom: 0;

    width: 4px;

    background:

        linear-gradient(
            180deg,
            #087f5b,
            #8bcf39
        );

}

.recommendation-top {

    display: flex;

    align-items: flex-start;

    gap: 13px;

}

.recommendation-icon {

    width: 45px;

    height: 45px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        #e9f8f2;

    color:
        #087f5b;

    flex-shrink: 0;

}

.recommendation-title {

    font-size: 14px;

    font-weight: 800;

    line-height: 1.4;

    margin-bottom: 3px;

}

.recommendation-category {

    color:
        #087f5b;

    font-size: 10px;

    font-weight: 800;

}

.recommendation-description {

    color:
        #71847c;

    font-size: 11px;

    line-height: 1.7;

    margin:
        16px 0;

}

.try-box {

    background:
        #f4faf7;

    border:
        1px solid #e2eee8;

    border-radius: 11px;

    padding: 12px;

    font-size: 10px;

    color: #62766e;

    line-height: 1.6;

}

.try-title {

    display: block;

    color:
        #087f5b;

    font-size: 10px;

    font-weight: 900;

    margin-bottom: 4px;

}


/* =========================================================
   FINAL MESSAGE
   ========================================================= */

.final-message {

    text-align: center;

    background:

        linear-gradient(
            135deg,
            #edf9f3,
            #f8fcfa
        );

    border:
        1px solid #dcefe5;

    border-radius: 20px;

    padding: 28px;

    margin:
        10px 0 25px;

}

.final-message-icon {

    font-size: 30px;

    margin-bottom: 8px;

}

.final-message h3 {

    font-size: 17px;

    margin-bottom: 5px;

}

.final-message p {

    color:
        #71847c;

    font-size: 12px;

    max-width: 650px;

    margin:
        0 auto;

    line-height: 1.7;

}


/* =========================================================
   BUTTONS
   ========================================================= */

.action-buttons {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 10px;

    flex-wrap: wrap;

}

.action-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    padding:
        12px 18px;

    border-radius: 11px;

    background:
        #087f5b;

    color: white;

    font-size: 12px;

    font-weight: 800;

    transition: .2s;

}

.action-btn:hover {

    background:
        #056044;

    transform:
        translateY(-1px);

}

.action-btn.secondary {

    background:
        #edf4f1;

    color:
        #087f5b;

}


/* =========================================================
   GENERAL WELLNESS SIDEBAR
   ========================================================= */

.wellness-sidebar {

    position: fixed;

    right: 18px;

    top: 50%;

    transform:
        translateY(-50%);

    width: 245px;

    background:
        rgba(255,255,255,.97);

    backdrop-filter:
        blur(14px);

    border:
        1px solid #dcece4;

    border-radius: 20px;

    padding: 18px;

    box-shadow:
        0 15px 45px rgba(8,127,91,.14);

    z-index: 900;

    animation:
        sidebarFloat 5s ease-in-out infinite;

}

@keyframes sidebarFloat {

    0%,100% {

        transform:
            translateY(-50%);

    }

    50% {

        transform:
            translateY(
                calc(-50% - 5px)
            );

    }

}

.wellness-sidebar-header {

    display: flex;

    align-items: center;

    gap: 10px;

    padding-bottom: 13px;

    border-bottom:
        1px solid #e8f1ec;

    margin-bottom: 13px;

}

.wellness-sidebar-icon {

    width: 38px;

    height: 38px;

    border-radius: 11px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        #e9f8f2;

    color:
        #087f5b;

    font-size: 17px;

}

.wellness-sidebar-header h3 {

    margin: 0;

    font-size: 13px;

    color: #173c2d;

}

.wellness-sidebar-header p {

    margin:
        3px 0 0;

    font-size: 9px;

    color: #82938b;

}

.wellness-item {

    display: flex;

    align-items: center;

    gap: 10px;

    padding:
        10px 8px;

    border-radius: 11px;

    margin-bottom: 5px;

    transition: .2s;

}

.wellness-item:hover {

    background:
        #f0faf5;

    transform:
        translateX(-3px);

}

.wellness-item-icon {

    width: 31px;

    height: 31px;

    border-radius: 9px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        #f0f8f4;

    color:
        #087f5b;

    flex-shrink: 0;

    font-size: 12px;

}

.wellness-item-text {

    font-size: 10px;

    color: #50675d;

    line-height: 1.45;

}

.wellness-item-text strong {

    display: block;

    color: #173c2d;

    font-size: 10px;

    margin-bottom: 2px;

}

.sidebar-note {

    margin-top: 10px;

    padding: 11px;

    border-radius: 11px;

    background:

        linear-gradient(
            135deg,
            #edf9f3,
            #f8fcfa
        );

    color: #638077;

    font-size: 9px;

    line-height: 1.6;

    text-align: center;

}


/* =========================================================
   MOBILE SIDEBAR BUTTON
   ========================================================= */

.wellness-toggle {

    display: none;

    position: fixed;

    right: 15px;

    bottom: 90px;

    width: 48px;

    height: 48px;

    border: none;

    border-radius: 50%;

    background:

        linear-gradient(
            135deg,
            #087f5b,
            #8bcf39
        );

    color: white;

    box-shadow:
        0 10px 30px rgba(8,127,91,.25);

    z-index: 901;

    cursor: pointer;

    font-size: 17px;

}


/* =========================================================
   ANIMATED BOTTOM WELLNESS BAR
   ========================================================= */

.wellness-bottom-bar {

    position: fixed;

    left: 50%;

    bottom: 18px;

    transform:
        translateX(-50%);

    width:
        min(720px, calc(100% - 40px));

    min-height: 55px;

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        10px 18px;

    background:

        linear-gradient(
            135deg,
            rgba(8,127,91,.97),
            rgba(16,151,102,.97)
        );

    color: white;

    border-radius: 18px;

    box-shadow:
        0 15px 40px rgba(8,127,91,.25);

    z-index: 950;

    overflow: hidden;

    backdrop-filter:
        blur(12px);

}

.wellness-bottom-icon {

    width: 36px;

    height: 36px;

    border-radius: 11px;

    background:
        rgba(255,255,255,.16);

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    font-size: 17px;

}

.wellness-bottom-content {

    flex: 1;

    min-width: 0;

}

.wellness-bottom-label {

    font-size: 8px;

    text-transform: uppercase;

    letter-spacing: 1px;

    opacity: .75;

    margin-bottom: 2px;

}

.wellness-bottom-message {

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

    transition:
        opacity .4s ease,
        transform .4s ease;

}

.wellness-bottom-message.fade-out {

    opacity: 0;

    transform:
        translateY(8px);

}

.wellness-bottom-progress {

    position: absolute;

    left: 0;

    bottom: 0;

    height: 3px;

    width: 100%;

    background:
        rgba(255,255,255,.22);

}

.wellness-bottom-progress::after {

    content: "";

    position: absolute;

    left: 0;

    top: 0;

    height: 100%;

    width: 0%;

    background:
        rgba(255,255,255,.9);

    animation:
        wellnessProgress 6s linear infinite;

}

@keyframes wellnessProgress {

    from {
        width: 0%;
    }

    to {
        width: 100%;
    }

}


/* =========================================================
   CLOSE BUTTON
   ========================================================= */

.wellness-close {

    width: 25px;

    height: 25px;

    border: none;

    border-radius: 50%;

    background:
        rgba(255,255,255,.12);

    color: white;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 10px;

    flex-shrink: 0;

}

.wellness-close:hover {

    background:
        rgba(255,255,255,.25);

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width:1150px) {

    .wellness-sidebar {

        display: none;

    }

    .wellness-sidebar.active {

        display: block;

        right: 12px;

        top: auto;

        bottom: 90px;

        transform: none;

        animation: none;

    }

    .wellness-toggle {

        display: flex;

        align-items: center;

        justify-content: center;

    }

}

@media(max-width:850px) {

    .summary-grid {

        grid-template-columns:
            repeat(2,1fr);

    }

    .recommendation-grid {

        grid-template-columns:
            1fr;

    }

}

@media(max-width:550px) {

    body {

        padding-bottom: 85px;

    }

    .summary-grid {

        grid-template-columns:
            1fr;

    }

    .recommendation-hero {

        padding:
            23px;

    }

    .analysis-card {

        flex-direction:
            column;

    }

    .nav-links {

        gap: 5px;

    }

    .nav-btn {

        padding:
            8px 10px;

        font-size:
            10px;

    }

    .wellness-bottom-bar {

        bottom: 10px;

        width:
            calc(100% - 20px);

        min-height: 52px;

        padding:
            9px 12px;

    }

    .wellness-bottom-message {

        font-size: 10px;

    }

    .wellness-bottom-icon {

        width: 32px;

        height: 32px;

    }

    .wellness-sidebar.active {

        width:
            calc(100% - 30px);

        right: 15px;

        bottom: 75px;

    }

}

</style>

</head>


<body>


<!-- =========================================================
     GENERAL WELLNESS SIDEBAR
     ========================================================= -->

<aside
    class="wellness-sidebar"
    id="wellnessSidebar"
>


    <div class="wellness-sidebar-header">

        <div class="wellness-sidebar-icon">

            <i class="fa-solid fa-spa"></i>

        </div>

        <div>

            <h3>
                General Wellness
            </h3>

            <p>
                Small things that can help
            </p>

        </div>

    </div>


    <!-- PEACEFUL MUSIC -->

    <div class="wellness-item">

        <div class="wellness-item-icon">

            <i class="fa-solid fa-music"></i>

        </div>

        <div class="wellness-item-text">

            <strong>
                Peaceful Music
            </strong>

            Listen to a song that makes you feel calm.

        </div>

    </div>


    <!-- MEDITATION -->

    <div class="wellness-item">

        <div class="wellness-item-icon">

            <i class="fa-solid fa-om"></i>

        </div>

        <div class="wellness-item-text">

            <strong>
                Meditation
            </strong>

            Sit quietly and focus on your breathing.

        </div>

    </div>


    <!-- YOGA -->

    <div class="wellness-item">

        <div class="wellness-item-icon">

            <i class="fa-solid fa-person"></i>

        </div>

        <div class="wellness-item-text">

            <strong>
                Gentle Yoga
            </strong>

            Stretch your body and release some tension.

        </div>

    </div>


    <!-- FAMILY -->

    <div class="wellness-item">

        <div class="wellness-item-icon">

            <i class="fa-solid fa-people-roof"></i>

        </div>

        <div class="wellness-item-text">

            <strong>
                Family Time
            </strong>

            Sit, talk or share a meal with your family.

        </div>

    </div>


    <!-- FRIENDS -->

    <div class="wellness-item">

        <div class="wellness-item-icon">

            <i class="fa-solid fa-user-group"></i>

        </div>

        <div class="wellness-item-text">

            <strong>
                Talk to a Friend
            </strong>

            A genuine conversation can make things feel lighter.

        </div>

    </div>


    <!-- NATURE -->

    <div class="wellness-item">

        <div class="wellness-item-icon">

            <i class="fa-solid fa-leaf"></i>

        </div>

        <div class="wellness-item-text">

            <strong>
                Spend Time Outside
            </strong>

            Take a walk or sit somewhere peaceful.

        </div>

    </div>


    <!-- HAPPINESS -->

    <div class="wellness-item">

        <div class="wellness-item-icon">

            <i class="fa-solid fa-face-smile"></i>

        </div>

        <div class="wellness-item-text">

            <strong>
                Do What Makes You Happy
            </strong>

            Enjoy a hobby or activity you genuinely like.

        </div>

    </div>


    <!-- SPIRITUAL / FOLK -->

    <div class="wellness-item">

        <div class="wellness-item-icon">

            <i class="fa-solid fa-hands-praying"></i>

        </div>

        <div class="wellness-item-text">

            <strong>
                Spiritual / Folk Music
            </strong>

            Listen to music that gives you peace and connection.

        </div>

    </div>


    <div class="sidebar-note">

        🌱 You don't have to do everything.

        <br>

        Choose one small thing that feels good today.

    </div>

</aside>


<!-- =========================================================
     MOBILE WELLNESS BUTTON
     ========================================================= -->

<button
    class="wellness-toggle"
    id="wellnessToggle"
    type="button"
    aria-label="Open wellness tips"
>

    <i class="fa-solid fa-spa"></i>

</button>


<!-- =========================================================
     NAVBAR
     ========================================================= -->

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
            Dashboard
        </a>

        <a
            class="nav-btn"
            href="history.php"
        >
            History
        </a>

    </div>

</header>


<main class="container">


<!-- =========================================================
     HERO
     ========================================================= -->

<section class="recommendation-hero">

    <div class="hero-icon">
        💚
    </div>

    <h1>
        You are taking a positive step.
    </h1>

    <p>
        Thank you for checking in with yourself.
        Your assessment helps StressCare understand
        your current situation and provide practical,
        personalized wellbeing suggestions.
    </p>

</section>


<!-- =========================================================
     SUMMARY
     ========================================================= -->

<section class="summary-grid">


    <!-- EMOTION -->

    <div class="summary-card">

        <div class="summary-icon">

            <?= $emotionIcon; ?>

        </div>

        <div class="summary-label">
            CURRENT EMOTION
        </div>

        <div class="summary-value">

            <?= htmlspecialchars(
                $emotion
            ); ?>

        </div>

    </div>


    <!-- CONCERN -->

    <div class="summary-card">

        <div class="summary-icon">
            🎯
        </div>

        <div class="summary-label">
            MAIN CONCERN
        </div>

        <div class="summary-value">

            <?= htmlspecialchars(
                $situation
            ); ?>

        </div>

    </div>


    <!-- STRESS -->

    <div class="summary-card">

        <div class="summary-icon">
            💗
        </div>

        <div class="summary-label">
            STRESS LEVEL
        </div>

        <div
            class="
                stress-value
                stress-<?= $stressClass; ?>
            "
        >

            <?= $stress; ?>/10

        </div>

        <div
            style="
                font-size:10px;
                color:#71847c;
                margin-top:2px;
            "
        >

            <?= htmlspecialchars(
                $stressCategory
            ); ?>

        </div>

    </div>


    <!-- SLEEP -->

    <div class="summary-card">

        <div class="summary-icon">
            😴
        </div>

        <div class="summary-label">
            AVERAGE SLEEP
        </div>

        <div class="summary-value">

            <?= htmlspecialchars(
                number_format($sleep, 1)
            ); ?>

            hours

        </div>

    </div>


</section>


<!-- =========================================================
     ANALYSIS
     ========================================================= -->

<section class="analysis-card">

    <div class="analysis-icon">

        <i class="fa-solid fa-chart-line"></i>

    </div>

    <div>

        <h3>
            Your Assessment Result
        </h3>

        <p>

            <?= htmlspecialchars(
                $stressDescription
            ); ?>

        </p>

    </div>

</section>


<!-- =========================================================
     RECOMMENDATIONS
     ========================================================= -->

<section class="section-header">

    <h2>
        Recommendations For You
    </h2>

    <p>
        These suggestions are based on your
        selected concern, emotion and stress level.
    </p>

</section>


<div class="recommendation-grid">


<?php foreach (
    $recommendations
    as $recommendation
): ?>


<?php

$category =
    $recommendation['category']
    ?? "Wellness";

$icon =
    $categoryIcons[$category]
    ?? "fa-lightbulb";

?>


<article class="recommendation-card">


    <div class="recommendation-top">


        <div class="recommendation-icon">

            <i
                class="
                    fa-solid
                    <?= htmlspecialchars(
                        $icon
                    ); ?>
                "
            ></i>

        </div>


        <div>

            <div class="recommendation-title">

                <?= htmlspecialchars(
                    $recommendation['title']
                    ?? "Wellbeing Recommendation"
                ); ?>

            </div>


            <div class="recommendation-category">

                <?= htmlspecialchars(
                    $category
                ); ?>

            </div>

        </div>


    </div>


    <div class="recommendation-description">

        <?= htmlspecialchars(
            $recommendation['description']
            ?? ""
        ); ?>

    </div>


    <div class="try-box">

        <span class="try-title">

            <i class="fa-solid fa-check"></i>

            TRY THIS

        </span>

        <?= htmlspecialchars(
            $recommendation['activity']
            ?? "Take one small positive step today."
        ); ?>

    </div>


</article>


<?php endforeach; ?>


</div>


<!-- =========================================================
     POSITIVE MESSAGE
     ========================================================= -->

<section class="final-message">

    <div class="final-message-icon">
        🌱
    </div>

    <h3>
        A Little Reminder
    </h3>

    <p>

        <?= htmlspecialchars(
            $message
        ); ?>

    </p>

</section>


<!-- =========================================================
     ACTIONS
     ========================================================= -->

<div class="action-buttons">


    <a
        class="action-btn"
        href="assessment.php"
    >

        <i class="fa-solid fa-rotate"></i>

        Take Another Assessment

    </a>


    <a
        class="action-btn secondary"
        href="history.php"
    >

        <i class="fa-solid fa-clock-rotate-left"></i>

        View My History

    </a>


    <a
        class="action-btn secondary"
        href="dashboard.php"
    >

        <i class="fa-solid fa-house"></i>

        Dashboard

    </a>


</div>


</main>


<!-- =========================================================
     ANIMATED BOTTOM WELLNESS BAR
     ========================================================= -->

<div
    class="wellness-bottom-bar"
    id="wellnessBottomBar"
>

    <div class="wellness-bottom-icon">

        <span id="wellnessBottomIcon">
            🌿
        </span>

    </div>


    <div class="wellness-bottom-content">

        <div class="wellness-bottom-label">
            StressCare • Gentle Reminder
        </div>

        <div
            class="wellness-bottom-message"
            id="wellnessBottomMessage"
        >
            Take a few minutes to listen to peaceful music.
        </div>

    </div>


    <button
        class="wellness-close"
        id="wellnessClose"
        type="button"
        aria-label="Close reminder"
    >

        <i class="fa-solid fa-xmark"></i>

    </button>


    <div class="wellness-bottom-progress"></div>

</div>


<script>

/* =========================================================
   STRESSCARE GENERAL WELLNESS ANIMATION
   ========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* -------------------------------------------------
           WELLNESS MESSAGES
           ------------------------------------------------- */

        const wellnessMessages = [

            {
                icon: "🎵",
                text:
                    "Take a few minutes to listen to peaceful music and let your mind slow down."
            },

            {
                icon: "🧘",
                text:
                    "Try 5–10 minutes of meditation. Sit quietly and focus on your breathing."
            },

            {
                icon: "🧘‍♂️",
                text:
                    "Gentle yoga or stretching can help you release physical tension."
            },

            {
                icon: "❤️",
                text:
                    "Spend some time with your family. You don't always need to talk about problems."
            },

            {
                icon: "👥",
                text:
                    "Talk to a friend you trust. Sometimes being heard is enough."
            },

            {
                icon: "🌿",
                text:
                    "Step outside for a while. A peaceful walk can give your mind some space."
            },

            {
                icon: "😊",
                text:
                    "Do something that genuinely makes you happy, even if it is something small."
            },

            {
                icon: "🎶",
                text:
                    "Listen to a folk, traditional or spiritual song that makes you feel peaceful and connected."
            },

            {
                icon: "📵",
                text:
                    "Put your phone away for a little while and give your mind a break."
            },

            {
                icon: "🎨",
                text:
                    "Try something creative such as drawing, writing, photography, music or cooking."
            },

            {
                icon: "🌳",
                text:
                    "Spend a few quiet minutes in nature without constantly checking your phone."
            },

            {
                icon: "💚",
                text:
                    "Be patient with yourself. You do not have to solve everything today."
            },

            {
                icon: "☕",
                text:
                    "Give yourself a peaceful moment with a warm drink, calm music or quiet time."
            },

            {
                icon: "😌",
                text:
                    "Take five slow breaths and give yourself permission to pause."
            },

            {
                icon: "🤝",
                text:
                    "Human connection matters. Spend time with someone who makes you feel comfortable."
            }

        ];


        let currentMessage = 0;


        const messageElement =
            document.getElementById(
                "wellnessBottomMessage"
            );

        const iconElement =
            document.getElementById(
                "wellnessBottomIcon"
            );


        /* -------------------------------------------------
           CHANGE MESSAGE
           ------------------------------------------------- */

        function showNextWellnessMessage() {

            if (
                !messageElement ||
                !iconElement
            ) {

                return;

            }


            messageElement.classList.add(
                "fade-out"
            );


            setTimeout(
                function () {

                    currentMessage++;

                    if (
                        currentMessage >=
                        wellnessMessages.length
                    ) {

                        currentMessage = 0;

                    }


                    const item =
                        wellnessMessages[
                            currentMessage
                        ];


                    iconElement.textContent =
                        item.icon;


                    messageElement.textContent =
                        item.text;


                    messageElement.classList.remove(
                        "fade-out"
                    );

                },
                400
            );

        }


        /*
         * Message changes every 6 seconds.
         */

        setInterval(
            showNextWellnessMessage,
            6000
        );


        /* -------------------------------------------------
           CLOSE BOTTOM BAR
           ------------------------------------------------- */

        const closeButton =
            document.getElementById(
                "wellnessClose"
            );

        const bottomBar =
            document.getElementById(
                "wellnessBottomBar"
            );


        if (
            closeButton &&
            bottomBar
        ) {

            closeButton.addEventListener(
                "click",
                function () {

                    bottomBar.style.transition =
                        "all .4s ease";

                    bottomBar.style.opacity =
                        "0";

                    bottomBar.style.transform =
                        "translate(-50%, 100px)";


                    setTimeout(
                        function () {

                            bottomBar.style.display =
                                "none";

                        },
                        400
                    );

                }
            );

        }


        /* -------------------------------------------------
           MOBILE SIDEBAR
           ------------------------------------------------- */

        const sidebar =
            document.getElementById(
                "wellnessSidebar"
            );

        const toggle =
            document.getElementById(
                "wellnessToggle"
            );


        if (
            toggle &&
            sidebar
        ) {

            toggle.addEventListener(
                "click",
                function () {

                    sidebar.classList.toggle(
                        "active"
                    );

                }
            );

        }


        /* -------------------------------------------------
           CLOSE SIDEBAR OUTSIDE CLICK
           ------------------------------------------------- */

        document.addEventListener(
            "click",
            function (event) {

                if (
                    !sidebar ||
                    !toggle
                ) {

                    return;

                }


                if (

                    sidebar.classList.contains(
                        "active"
                    )

                    &&

                    !sidebar.contains(
                        event.target
                    )

                    &&

                    !toggle.contains(
                        event.target
                    )

                ) {

                    sidebar.classList.remove(
                        "active"
                    );

                }

            }
        );


    }
);

</script>


</body>

</html>