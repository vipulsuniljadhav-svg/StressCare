<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/session.php";

requireLogin();

$uid = (int) $_SESSION['user_id'];
$err = "";

/*
|--------------------------------------------------------------------------
| QUESTION BANK
|--------------------------------------------------------------------------
| 15 categories × 15 questions = 225 questions
|
| Each question is rated:
| 1 = Not at all
| 2 = A little
| 3 = Sometimes
| 4 = Often
| 5 = Extremely
|--------------------------------------------------------------------------
*/

$q = [

    "Friends / Friendship" => [

        "How often do you feel that your friends do not understand you?",
        "How hurt do you feel when you are ignored or left out?",
        "How often do you worry that a friendship may end?",
        "How comfortable are you expressing your true feelings to your friends?",
        "How often do you overthink something a friend said or did?",
        "How strongly do you feel the need for acceptance from your friends?",
        "How disappointed do you feel when a friend does not support you?",
        "How difficult is it for you to handle misunderstandings with friends?",
        "How often do you compare your friendships with other people's friendships?",
        "How lonely do you feel even when you are around friends?",
        "How much does friendship-related stress affect your concentration?",
        "How much does it affect your sleep or daily routine?",
        "How emotionally exhausted do friendship problems make you feel?",
        "How difficult is it for you to trust people after being hurt by a friend?",
        "How much does the situation affect your overall mood?"
    ],

    "Relationship / Love" => [

        "How much is your relationship affecting your emotional wellbeing?",
        "How often do you worry about losing the person you care about?",
        "How emotionally affected are you by disagreements in the relationship?",
        "How often do you overthink your partner's words or actions?",
        "How secure or insecure do you feel in the relationship?",
        "How difficult is it to express your feelings openly?",
        "How often do you fear being misunderstood or rejected?",
        "How strongly do you feel hurt when your expectations are not met?",
        "How much does uncertainty about the relationship affect you?",
        "How often do you feel emotionally dependent on the relationship?",
        "How difficult is it to concentrate when relationship problems are on your mind?",
        "How much does the relationship affect your confidence?",
        "How much does relationship stress affect your sleep?",
        "How often do you feel emotionally drained after arguments?",
        "How much does the relationship situation affect your overall happiness?"
    ],

    "Family Problems" => [

        "How often do family disagreements affect your mood?",
        "How pressured do you feel by family expectations?",
        "How often do you feel that your family does not understand you?",
        "How difficult is it to express your feelings to your family?",
        "How often do you feel guilty after a family argument?",
        "How strongly does criticism from family members affect you?",
        "How often do you feel emotionally unsupported at home?",
        "How much responsibility do you feel you have to satisfy everyone?",
        "How often do you avoid conversations to prevent conflict?",
        "How difficult is it to stay calm during family disagreements?",
        "How much do family problems affect your concentration?",
        "How much do they affect your sleep?",
        "How emotionally exhausted do family problems make you feel?",
        "How often do you feel helpless about changing the situation?",
        "How much do family problems affect your overall wellbeing?"
    ],

    "College / Studies" => [

        "How worried are you about upcoming exams or deadlines?",
        "How often do you fear getting lower marks than expected?",
        "How much pressure do you feel to perform well academically?",
        "How difficult is it to concentrate while studying?",
        "How often do you compare your academic performance with others?",
        "How disappointed do you feel when your results are below expectations?",
        "How afraid are you of failing an important subject?",
        "How often do unfinished assignments stay on your mind?",
        "How overwhelmed do you feel by your academic workload?",
        "How difficult is it to balance studies with personal life?",
        "How much does academic pressure affect your confidence?",
        "How much does studying late affect your sleep?",
        "How mentally exhausted do you feel after studying?",
        "How often do you procrastinate because the workload feels overwhelming?",
        "How much do academic concerns affect your overall happiness?"
    ],

    "Work / Career" => [

        "How worried are you about your future career?",
        "How much pressure do you feel to become successful?",
        "How often do you fear making mistakes at work?",
        "How uncertain do you feel about your career direction?",
        "How often do you compare your progress with others?",
        "How much does criticism at work affect your confidence?",
        "How overwhelmed do you feel by your workload?",
        "How often do you worry about job or career stability?",
        "How difficult is it to maintain motivation when work becomes stressful?",
        "How much does work pressure affect your personal life?",
        "How often do you continue thinking about work after working hours?",
        "How much does career uncertainty affect your sleep?",
        "How emotionally exhausted do you feel because of work?",
        "How helpless do you feel when you cannot control work-related situations?",
        "How much does career pressure affect your overall wellbeing?"
    ],

    "Financial Problems" => [

        "How often do you worry about having enough money?",
        "How strongly do financial problems affect your sense of security?",
        "How often do you think about unexpected expenses?",
        "How anxious do you feel about your financial future?",
        "How difficult is it to control unnecessary spending?",
        "How much pressure do you feel to earn more money?",
        "How often do money problems affect your mood?",
        "How embarrassed or uncomfortable do financial difficulties make you feel?",
        "How often do you compare your financial situation with others?",
        "How difficult is it to relax because of financial concerns?",
        "How much do financial worries affect your concentration?",
        "How much do they affect your sleep?",
        "How overwhelmed do you feel when thinking about expenses?",
        "How helpless do you feel about improving your financial situation?",
        "How much do financial concerns affect your overall wellbeing?"
    ],

    "Social Pressure" => [

        "How often do you worry about what other people think about you?",
        "How strongly do you fear being judged by others?",
        "How often do you compare yourself with people around you?",
        "How difficult is it to be yourself around other people?",
        "How often do you change your behavior to gain acceptance?",
        "How strongly does criticism affect your confidence?",
        "How often do you feel pressure to look or behave a certain way?",
        "How worried are you about being rejected by others?",
        "How often do you feel that you need to prove yourself?",
        "How uncomfortable do you feel when you think people are judging you?",
        "How much does social pressure affect your concentration?",
        "How much does it affect your sleep?",
        "How emotionally exhausted do you feel after social situations?",
        "How often do you feel insecure when comparing yourself with others?",
        "How much does social pressure affect your overall happiness?"
    ],

    "Breakup / Loss" => [

        "How often do you think about the person or situation you lost?",
        "How strongly do memories of the loss affect your emotions?",
        "How difficult is it for you to accept what happened?",
        "How often do you feel sadness because of the loss?",
        "How often do you feel lonely after the loss?",
        "How strongly do you miss the person or situation?",
        "How often do you replay past events in your mind?",
        "How much guilt or regret do you experience?",
        "How difficult is it to enjoy things you previously enjoyed?",
        "How difficult is it to focus on your normal responsibilities?",
        "How much does the loss affect your sleep?",
        "How often do you feel emotionally empty?",
        "How difficult is it to imagine moving forward?",
        "How overwhelmed do you feel when memories return?",
        "How much does the loss affect your overall wellbeing?"
    ],

    "Loneliness" => [

        "How often do you feel that you have nobody to talk to?",
        "How strongly do you wish you had someone who understood you?",
        "How often do you feel lonely even when surrounded by people?",
        "How difficult is it to start conversations with others?",
        "How often do you feel disconnected from people around you?",
        "How strongly does loneliness affect your mood?",
        "How often do you feel left out of social activities?",
        "How difficult is it to form meaningful connections?",
        "How often do you avoid social situations because of insecurity?",
        "How much does loneliness affect your motivation?",
        "How much does loneliness affect your concentration?",
        "How much does it affect your sleep?",
        "How often do you overthink why you feel alone?",
        "How emotionally exhausted do you feel because of loneliness?",
        "How much does loneliness affect your overall happiness?"
    ],

    "Conflict / Anger" => [

        "How often do you feel irritated by small things?",
        "How difficult is it to control your anger once it begins?",
        "How often do you regret things you say when angry?",
        "How strongly do conflicts affect your mood?",
        "How often do you raise your voice during disagreements?",
        "How difficult is it to listen when you are angry?",
        "How often do you continue thinking about an argument afterward?",
        "How strongly do you feel misunderstood during conflicts?",
        "How difficult is it to forgive someone after an argument?",
        "How often does anger affect your relationships?",
        "How physically tense do you feel during conflicts?",
        "How much does anger affect your concentration?",
        "How much does conflict affect your sleep?",
        "How emotionally exhausted do you feel after an argument?",
        "How much do anger and conflict affect your overall wellbeing?"
    ],

    "Self Confidence" => [

        "How often do you doubt your own abilities?",
        "How often do you compare yourself negatively with others?",
        "How strongly does criticism affect your self-esteem?",
        "How afraid are you of making mistakes in front of others?",
        "How often do you think that you are not good enough?",
        "How difficult is it to speak confidently in front of people?",
        "How often do you avoid opportunities because of self-doubt?",
        "How strongly do you fear failure?",
        "How often do you need approval from others to feel confident?",
        "How difficult is it to accept compliments?",
        "How often do you focus on your weaknesses rather than strengths?",
        "How much does self-doubt affect your studies or work?",
        "How much does low confidence affect your relationships?",
        "How emotionally tired do negative thoughts make you?",
        "How much does self-doubt affect your overall happiness?"
    ],

    "Time Management" => [

        "How often do you feel that there is not enough time to complete everything?",
        "How often do you postpone important tasks?",
        "How overwhelmed do you feel when looking at your task list?",
        "How difficult is it to decide which task to do first?",
        "How often do deadlines make you anxious?",
        "How often do unfinished tasks remain on your mind?",
        "How guilty do you feel when you procrastinate?",
        "How difficult is it to maintain a consistent routine?",
        "How often do you underestimate how long tasks will take?",
        "How often do you sacrifice sleep to complete work?",
        "How much does poor time management affect your concentration?",
        "How much does it affect your relationships or personal time?",
        "How emotionally exhausted do you feel from constantly rushing?",
        "How often do you feel helpless when too many tasks accumulate?",
        "How much does time pressure affect your overall wellbeing?"
    ],

    "Sleep Problems" => [

        "How difficult is it for you to fall asleep?",
        "How often do you wake up during the night?",
        "How often do you wake up feeling tired?",
        "How difficult is it to maintain a consistent sleep schedule?",
        "How often do thoughts keep you awake?",
        "How worried do you become when you cannot sleep?",
        "How much does poor sleep affect your mood?",
        "How much does poor sleep affect your concentration?",
        "How often do you feel irritated because you are tired?",
        "How much does tiredness reduce your motivation?",
        "How often do you feel sleepy during the day?",
        "How much does your sleep pattern affect your studies or work?",
        "How difficult is it to relax before going to bed?",
        "How emotionally exhausted do you feel because of poor sleep?",
        "How much do sleep problems affect your overall wellbeing?"
    ],

    "Social Media" => [

        "How often do you compare your life with people you see online?",
        "How strongly do other people's posts affect your self-confidence?",
        "How often do you worry about likes, comments, or reactions?",
        "How difficult is it to stop checking social media?",
        "How often do you check your phone without a specific reason?",
        "How often does social media make you feel left out?",
        "How strongly does online criticism affect your emotions?",
        "How often do you feel that others have a better life than you?",
        "How much time do you spend thinking about what others post?",
        "How difficult is it to focus on studies or work after using social media?",
        "How much does social media affect your sleep?",
        "How often do you feel mentally tired after scrolling?",
        "How often do you regret spending too much time online?",
        "How strongly does social media affect your mood?",
        "How much does social media use affect your overall wellbeing?"
    ],

    "General Stress" => [

        "How overwhelmed do you feel by your current responsibilities?",
        "How often do you feel tense without a clear reason?",
        "How often do you find yourself overthinking situations?",
        "How difficult is it to relax after a stressful day?",
        "How often do you worry about things that have not happened yet?",
        "How difficult is it to concentrate when you are stressed?",
        "How often do you feel emotionally exhausted?",
        "How often do small problems feel bigger than they normally would?",
        "How difficult is it to control negative thoughts?",
        "How often do you feel that you have too much to handle?",
        "How much does stress affect your motivation?",
        "How much does stress affect your relationships?",
        "How much does stress affect your sleep?",
        "How often do you feel helpless when dealing with responsibilities?",
        "How much is your current stress affecting your overall wellbeing?"
    ]

];


/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $emotion = trim($_POST['emotion'] ?? '');

    $situation = trim($_POST['situation'] ?? '');

    $sleep = (float) ($_POST['sleep_hours'] ?? 0);

    $activities = trim($_POST['activities'] ?? '');

    $notes = trim($_POST['notes'] ?? '');

    $answers = [];

    /*
     * Collect all 15 answers
     */

    for ($i = 1; $i <= 15; $i++) {

        $answers[$i] =
            (int) ($_POST['q' . $i] ?? 0);
    }


    /*
     * Validation
     */

    if (
        !$emotion ||
        !isset($q[$situation]) ||
        $sleep < 0 ||
        $sleep > 24 ||
        in_array(0, $answers, true)
    ) {

        $err =
            "Please complete all required fields and answer all 15 questions.";

    } else {

        /*
         * Calculate raw score
         *
         * 15 questions × 5 = 75 maximum
         */

        $score = array_sum($answers);

        $maxScore = count($answers) * 5;

        /*
         * Convert 15–75 into 1–10
         */

        $stress = (int) round(
            ($score / $maxScore) * 10
        );

        $stress = max(
            1,
            min(10, $stress)
        );


        /*
         * Stress category
         */

        if ($stress <= 3) {

            $analysis = "Low Stress";

        } elseif ($stress <= 6) {

            $analysis = "Moderate Stress";

        } elseif ($stress <= 8) {

            $analysis = "High Stress";

        } else {

            $analysis = "Very High Stress";
        }


        /*
         * Add raw score to notes
         */

        $notes .=
            ($notes ? "\n\n" : "")
            .
            "Question score: "
            .
            $score
            .
            "/"
            .
            $maxScore;


        /*
         * Existing database uses mood too.
         */

        $mood = $emotion;


        /*
         * Save assessment
         */

        $stmt = $conn->prepare("
            INSERT INTO stress_assessments
            (
                user_id,
                stress_level,
                mood,
                emotion,
                situation,
                activities,
                sleep_hours,
                notes,
                analysis
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");


        if (!$stmt) {

            $err =
                "Database error: Unable to prepare assessment.";

        } else {

            $stmt->bind_param(
                "iissssdss",
                $uid,
                $stress,
                $mood,
                $emotion,
                $situation,
                $activities,
                $sleep,
                $notes,
                $analysis
            );


            if ($stmt->execute()) {

                $assessmentId =
                    $stmt->insert_id;

                $stmt->close();


                /*
                 * Go to recommendation engine
                 */

                header(
                    "Location: recommendations.php?assessment_id="
                    .
                    $assessmentId
                );

                exit();

            } else {

                $err =
                    "Unable to save your assessment. Please try again.";

                $stmt->close();
            }
        }
    }
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

<title>
    Stress Assessment | StressCare
</title>


<style>

/* =========================================================
   GLOBAL
========================================================= */

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

    --border: #e2eee8;

    --danger: #c0392b;

    --shadow:
        0 15px 40px rgba(8,127,91,0.08);
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

    min-height: 100vh;

    line-height: 1.6;
}

a {
    text-decoration: none;
    color: inherit;
}


/* =========================================================
   NAVBAR
========================================================= */

.navbar {

    height: 76px;

    background:
        rgba(255,255,255,0.96);

    border-bottom:
        1px solid var(--border);

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 6%;

    position: sticky;

    top: 0;

    z-index: 100;

    backdrop-filter: blur(12px);
}

.logo {

    display: flex;

    align-items: center;

    gap: 11px;

    font-size: 21px;

    font-weight: 800;

    color: var(--green-dark);
}

.logo-icon {

    width: 43px;

    height: 43px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        linear-gradient(
            135deg,
            var(--green),
            var(--lime)
        );

    color: white;

    font-size: 21px;

    box-shadow:
        0 8px 20px rgba(8,127,91,.2);
}

.logo > span > span {

    color: var(--lime);
}

.nav-btn {

    padding: 10px 16px;

    border-radius: 10px;

    background: var(--green-light);

    color: var(--green);

    font-size: 13px;

    font-weight: 800;

    transition: .2s;
}

.nav-btn:hover {

    background: #dcefe7;
}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.container {

    width:
        min(1050px, 92%);

    margin: auto;

    padding:
        40px 0 70px;
}


/* =========================================================
   PAGE TITLE
========================================================= */

.title {

    margin-bottom: 24px;
}

.title h1 {

    font-size:
        clamp(28px, 4vw, 40px);

    line-height: 1.2;

    margin-bottom: 8px;
}

.title p {

    color: var(--muted);

    font-size: 14px;

    max-width: 700px;
}


/* =========================================================
   ERROR
========================================================= */

.alert {

    padding: 14px 17px;

    border-radius: 13px;

    margin-bottom: 18px;

    font-size: 12px;

    font-weight: 700;
}

.alert.error {

    color: var(--danger);

    background: #fff0ef;

    border: 1px solid #f3cfcb;
}


/* =========================================================
   PROGRESS BAR
========================================================= */

.progress-card {

    background: white;

    border:
        1px solid var(--border);

    border-radius: 18px;

    padding: 18px 21px;

    margin-bottom: 13px;

    box-shadow:
        0 8px 25px rgba(8,127,91,.04);
}

.progress-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 10px;
}

.progress-header strong {

    font-size: 13px;
}

.progress-header span {

    color: var(--muted);

    font-size: 11px;

    font-weight: 700;
}

.progress-track {

    height: 7px;

    width: 100%;

    background: #edf3f0;

    border-radius: 20px;

    overflow: hidden;
}

.progress-fill {

    height: 100%;

    width: 33.33%;

    background:
        linear-gradient(
            90deg,
            var(--green),
            var(--lime)
        );

    border-radius: inherit;

    transition:
        width .35s ease;
}


/* =========================================================
   STEP INDICATORS
========================================================= */

.steps {

    display:
        grid;

    grid-template-columns:
        repeat(3,1fr);

    gap: 10px;

    margin-bottom: 18px;
}

.step-indicator {

    display: flex;

    align-items: center;

    gap: 9px;

    padding: 10px 13px;

    border:
        1px solid var(--border);

    border-radius: 12px;

    background: white;

    color: var(--muted);

    font-size: 11px;

    font-weight: 800;
}

.step-number {

    width: 29px;

    height: 29px;

    border-radius: 50%;

    background: #edf3f0;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;
}

.step-indicator.active {

    background: var(--green-light);

    color: var(--green);

    border-color: #b8ddce;
}

.step-indicator.active
.step-number {

    background: var(--green);

    color: white;
}


/* =========================================================
   MAIN CARD
========================================================= */

.card {

    background: white;

    border:
        1px solid var(--border);

    border-radius: 23px;

    padding: 30px;

    box-shadow: var(--shadow);
}

.step {

    display: none;
}

.step.active {

    display: block;

    animation:
        fadeIn .3s ease;
}

@keyframes fadeIn {

    from {

        opacity: 0;

        transform:
            translateY(8px);
    }

    to {

        opacity: 1;

        transform:
            translateY(0);
    }
}


/* =========================================================
   CARD HEADINGS
========================================================= */

.card-title {

    font-size: 21px;

    margin-bottom: 6px;
}

.card-subtitle {

    color: var(--muted);

    font-size: 12px;

    margin-bottom: 24px;
}


/* =========================================================
   CATEGORY GRID
========================================================= */

.categories {

    display:
        grid;

    grid-template-columns:
        repeat(3,1fr);

    gap: 13px;
}

.category {

    position: relative;

    border:
        2px solid var(--border);

    border-radius: 16px;

    padding: 18px 12px;

    text-align: center;

    cursor: pointer;

    background: #fbfdfc;

    transition: .2s;

    user-select: none;
}

.category:hover {

    transform:
        translateY(-2px);

    border-color:
        #afdac9;

    box-shadow:
        0 8px 20px rgba(8,127,91,.07);
}

.category.selected {

    border-color:
        var(--green);

    background:
        var(--green-light);

    box-shadow:
        0 8px 22px rgba(8,127,91,.1);
}

.category-icon {

    width: 48px;

    height: 48px;

    border-radius: 14px;

    background: white;

    border:
        1px solid var(--border);

    display: flex;

    align-items: center;

    justify-content: center;

    margin:
        0 auto 9px;

    font-size: 22px;
}

.category-name {

    font-size: 12px;

    font-weight: 800;
}

.category-check {

    position: absolute;

    top: 9px;

    right: 9px;

    width: 21px;

    height: 21px;

    border-radius: 50%;

    background:
        var(--green);

    color: white;

    display: none;

    align-items: center;

    justify-content: center;

    font-size: 11px;
}

.category.selected
.category-check {

    display: flex;
}


/* =========================================================
   QUESTION HEADER
========================================================= */

.question-top {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 15px;
}

.selected-category {

    display: inline-flex;

    padding: 7px 12px;

    border-radius: 20px;

    background:
        var(--green-light);

    color:
        var(--green);

    font-size: 11px;

    font-weight: 800;
}

.question-count {

    font-size: 11px;

    color: var(--muted);

    font-weight: 700;
}


/* =========================================================
   QUESTION PROGRESS
========================================================= */

.question-progress {

    height: 5px;

    background: #edf3f0;

    border-radius: 20px;

    overflow: hidden;

    margin:
        15px 0 22px;
}

.question-progress-fill {

    height: 100%;

    width: 6.66%;

    background:
        var(--green);

    border-radius: inherit;

    transition: .3s;
}


/* =========================================================
   QUESTIONS
========================================================= */

.question {

    border:
        1px solid var(--border);

    border-radius: 17px;

    padding: 20px;

    margin-bottom: 14px;

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #fbfdfc
        );

    transition: .2s;
}

.question:hover {

    border-color:
        #c6e3d8;
}

.question-number {

    color:
        var(--green);

    font-size: 10px;

    font-weight: 900;

    letter-spacing: .5px;

    margin-bottom: 7px;
}

.question-text {

    font-size: 14px;

    font-weight: 700;

    line-height: 1.5;

    margin-bottom: 16px;
}

.options {

    display:
        grid;

    grid-template-columns:
        repeat(5,1fr);

    gap: 7px;
}

.option {

    position: relative;
}

.option input {

    position: absolute;

    opacity: 0;

    pointer-events: none;
}

.option label {

    min-height: 62px;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    border:
        1px solid #dce9e3;

    border-radius: 11px;

    padding: 7px 4px;

    cursor: pointer;

    text-align: center;

    color: var(--muted);

    font-size: 9px;

    transition: .2s;
}

.option label strong {

    color:
        var(--text);

    font-size: 15px;

    margin-bottom: 2px;
}

.option label:hover {

    background: #f7fcf9;

    border-color:
        #a6d5c2;
}

.option input:checked
+ label {

    background:
        var(--green-light);

    border-color:
        var(--green);

    color:
        var(--green);

    box-shadow:
        0 5px 15px rgba(8,127,91,.08);
}

.option input:checked
+ label strong {

    color:
        var(--green);
}


/* =========================================================
   FORM
========================================================= */

.form-grid {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap: 18px;
}

.form-group {

    margin-bottom: 20px;
}

.form-group.full {

    grid-column:
        1 / -1;
}

.form-group > label {

    display: block;

    font-size: 12px;

    font-weight: 800;

    margin-bottom: 8px;
}

.form-group input,
.form-group textarea {

    width: 100%;

    border:
        1px solid #dce9e3;

    border-radius: 11px;

    padding: 12px 13px;

    background:
        #fbfdfc;

    color:
        var(--text);

    font-family:
        inherit;

    font-size: 13px;

    outline: none;

    transition: .2s;
}

.form-group input:focus,
.form-group textarea:focus {

    border-color:
        var(--green);

    background:
        white;

    box-shadow:
        0 0 0 3px
        rgba(8,127,91,.08);
}

.form-group textarea {

    min-height: 110px;

    resize: vertical;
}

.hint {

    color:
        var(--muted);

    font-size: 10px;

    margin-top: 5px;
}


/* =========================================================
   EMOTIONS
========================================================= */

.emotions {

    display:
        grid;

    grid-template-columns:
        repeat(5,1fr);

    gap: 8px;
}

.emotion {

    position: relative;
}

.emotion input {

    position: absolute;

    opacity: 0;
}

.emotion label {

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    min-height: 62px;

    padding: 7px 4px;

    border:
        1px solid var(--border);

    border-radius: 11px;

    text-align: center;

    cursor: pointer;

    font-size: 10px;

    transition: .2s;
}

.emotion label:hover {

    background:
        #f7fcf9;
}

.emotion input:checked
+ label {

    background:
        var(--green-light);

    border-color:
        var(--green);

    color:
        var(--green);

    font-weight: 800;
}


/* =========================================================
   INFO BOX
========================================================= */

.info-box {

    display: flex;

    align-items: flex-start;

    gap: 12px;

    padding: 15px;

    margin-bottom: 22px;

    border:
        1px solid #dcefe6;

    background:
        #f2faf6;

    border-radius: 13px;
}

.info-icon {

    width: 30px;

    height: 30px;

    border-radius: 9px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        var(--green);

    color: white;

    flex-shrink: 0;

    font-weight: 800;
}

.info-box p {

    color:
        var(--muted);

    font-size: 11px;

    line-height: 1.6;
}


/* =========================================================
   BUTTONS
========================================================= */

.actions {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 12px;

    margin-top: 25px;

    padding-top: 20px;

    border-top:
        1px solid var(--border);
}

.btn {

    border: none;

    cursor: pointer;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    padding: 13px 20px;

    border-radius: 11px;

    background:
        var(--green);

    color: white;

    font-family:
        inherit;

    font-size: 13px;

    font-weight: 800;

    transition: .2s;
}

.btn:hover {

    background:
        var(--green-dark);

    transform:
        translateY(-1px);
}

.btn-secondary {

    background:
        #edf4f1;

    color:
        var(--green-dark);
}

.btn-secondary:hover {

    background:
        #e1eee9;
}

.btn:disabled {

    opacity: .7;

    cursor:
        not-allowed;

    transform:
        none;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:850px) {

    .categories {

        grid-template-columns:
            repeat(2,1fr);
    }

    .emotions {

        grid-template-columns:
            repeat(3,1fr);
    }
}

@media(max-width:650px) {

    .navbar {

        padding:
            0 4%;
    }

    .container {

        width: 94%;

        padding-top:
            25px;
    }

    .card {

        padding: 20px;
    }

    .steps {

        grid-template-columns:
            1fr;
    }

    .step-indicator {

        padding:
            9px;
    }

    .options {

        grid-template-columns:
            1fr;
    }

    .option label {

        min-height: 45px;

        flex-direction:
            row;

        gap: 8px;
    }

    .form-grid {

        grid-template-columns:
            1fr;
    }

    .form-group.full {

        grid-column:
            auto;
    }

    .emotions {

        grid-template-columns:
            repeat(2,1fr);
    }

    .actions {

        flex-direction:
            column-reverse;

        align-items:
            stretch;
    }

    .actions .btn {

        width:
            100%;
    }
}

@media(max-width:430px) {

    .categories {

        grid-template-columns:
            1fr 1fr;

        gap: 8px;
    }

    .category {

        padding:
            14px 7px;
    }

    .category-name {

        font-size:
            10px;
    }

    .question {

        padding:
            15px;
    }
}

</style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<header class="navbar">

    <a
        href="dashboard.php"
        class="logo"
    >

        <div class="logo-icon">
            🌿
        </div>

        <span>
            Stress<span>Care</span>
        </span>

    </a>


    <a
        href="dashboard.php"
        class="nav-btn"
    >
        ← Dashboard
    </a>

</header>


<main class="container">


<!-- =========================================================
     TITLE
========================================================= -->

<div class="title">

    <h1>
        Personal Stress Assessment
    </h1>

    <p>
        Understand your emotional wellbeing through
        questions about your thoughts, feelings,
        relationships, daily life and current concerns.
    </p>

</div>


<?php if ($err): ?>

<div class="alert error">

    <?= htmlspecialchars($err); ?>

</div>

<?php endif; ?>


<!-- =========================================================
     OVERALL PROGRESS
========================================================= -->

<div class="progress-card">

    <div class="progress-header">

        <strong id="progressTitle">
            Step 1 — Select Concern
        </strong>

        <span id="progressPercent">
            33%
        </span>

    </div>

    <div class="progress-track">

        <div
            class="progress-fill"
            id="progressFill"
        ></div>

    </div>

</div>


<!-- =========================================================
     STEP INDICATORS
========================================================= -->

<div class="steps">

    <div
        class="step-indicator active"
        id="indicator1"
    >

        <div class="step-number">
            1
        </div>

        Select Concern

    </div>


    <div
        class="step-indicator"
        id="indicator2"
    >

        <div class="step-number">
            2
        </div>

        Emotional Assessment

    </div>


    <div
        class="step-indicator"
        id="indicator3"
    >

        <div class="step-number">
            3
        </div>

        Personal Details

    </div>

</div>


<form
    method="post"
    id="assessmentForm"
    autocomplete="off"
>


<input
    type="hidden"
    name="situation"
    id="situation"
>


<!-- =========================================================
     STEP 1
========================================================= -->

<section
    class="card step active"
    id="step1"
>

    <h2 class="card-title">
        What is affecting you?
    </h2>

    <p class="card-subtitle">
        Select the main area that best describes
        what is currently affecting your emotional wellbeing.
    </p>


    <div
        class="categories"
        id="categories"
    ></div>


    <div class="actions">

        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >
            Cancel
        </a>

        <button
            type="button"
            class="btn"
            onclick="goToQuestions()"
        >
            Continue →
        </button>

    </div>

</section>


<!-- =========================================================
     STEP 2
========================================================= -->

<section
    class="card step"
    id="step2"
>

    <div class="question-top">

        <div>

            <h2 class="card-title">
                Emotional Assessment
            </h2>

            <p
                class="card-subtitle"
                style="margin-bottom:0"
            >
                Answer based on how you have genuinely
                felt recently.
            </p>

        </div>

        <div
            class="question-count"
            id="questionCount"
        >
            15 Questions
        </div>

    </div>


    <div
        class="selected-category"
        id="selectedCategory"
    >
        ● Selected concern
    </div>


    <div class="question-progress">

        <div
            class="question-progress-fill"
            id="questionProgress"
        ></div>

    </div>


    <div
        id="questionsContainer"
    ></div>


    <div class="actions">

        <button
            type="button"
            class="btn btn-secondary"
            onclick="goToStep(1)"
        >
            ← Back
        </button>

        <button
            type="button"
            class="btn"
            onclick="goToDetails()"
        >
            Continue →
        </button>

    </div>

</section>


<!-- =========================================================
     STEP 3
========================================================= -->

<section
    class="card step"
    id="step3"
>

    <h2 class="card-title">
        Tell Us a Little More
    </h2>

    <p class="card-subtitle">
        These details help StressCare generate
        more relevant recommendations.
    </p>


    <div class="info-box">

        <div class="info-icon">
            i
        </div>

        <p>
            Your responses are used to calculate an
            application-based stress score and generate
            personalized wellbeing recommendations.
            This system is not a medical diagnosis.
        </p>

    </div>


    <!-- =====================================================
         EMOTION
    ====================================================== -->

    <div class="form-group">

        <label>
            How are you feeling right now?
        </label>


        <div class="emotions">

            <?php

            $emotions = [

                "Happy",
                "Calm",
                "Sad",
                "Angry",
                "Anxious",
                "Lonely",
                "Fearful",
                "Hurt",
                "Disappointed",
                "Frustrated",
                "Confused",
                "Tired",
                "Stressed",
                "Overwhelmed",
                "Neutral"

            ];


            $emotionIcons = [

                "Happy" => "😊",
                "Calm" => "😌",
                "Sad" => "😔",
                "Angry" => "😠",
                "Anxious" => "😟",
                "Lonely" => "🥺",
                "Fearful" => "😨",
                "Hurt" => "💔",
                "Disappointed" => "😞",
                "Frustrated" => "😤",
                "Confused" => "😕",
                "Tired" => "😴",
                "Stressed" => "😣",
                "Overwhelmed" => "😵",
                "Neutral" => "😐"

            ];


            foreach ($emotions as $emotion):

                $safeId =
                    "emotion_" .
                    preg_replace(
                        '/[^a-zA-Z0-9]/',
                        '_',
                        strtolower($emotion)
                    );

            ?>

            <div class="emotion">

                <input
                    type="radio"
                    name="emotion"
                    id="<?= $safeId; ?>"
                    value="<?= htmlspecialchars($emotion); ?>"
                    required
                >

                <label for="<?= $safeId; ?>">

                    <span
                        style="
                            font-size:18px;
                            margin-bottom:3px;
                        "
                    >
                        <?= $emotionIcons[$emotion]; ?>
                    </span>

                    <?= htmlspecialchars($emotion); ?>

                </label>

            </div>

            <?php endforeach; ?>

        </div>

    </div>


    <!-- =====================================================
         SLEEP
    ====================================================== -->

    <div class="form-grid">

        <div class="form-group">

            <label>
                Average sleep per night
            </label>

            <input
                type="number"
                name="sleep_hours"
                id="sleepHours"
                min="0"
                max="24"
                step="0.5"
                placeholder="Example: 7.5"
                required
            >

            <div class="hint">
                Enter your approximate average sleep
                during recent days.
            </div>

        </div>


        <!-- =================================================
             ACTIVITIES
        ================================================== -->

        <div class="form-group">

            <label>
                Activities recently
            </label>

            <input
                type="text"
                name="activities"
                placeholder="Example: Gym, study, walking, music"
            >

            <div class="hint">
                Mention activities that are currently
                part of your routine.
            </div>

        </div>


        <!-- =================================================
             NOTES
        ================================================== -->

        <div class="form-group full">

            <label>
                Tell us more about the problem
                <span
                    style="
                        color:var(--muted);
                        font-weight:500;
                    "
                >
                    (optional)
                </span>
            </label>

            <textarea
                name="notes"
                placeholder="You can briefly describe what has been bothering you..."
            ></textarea>

        </div>

    </div>


    <div class="actions">

        <button
            type="button"
            class="btn btn-secondary"
            onclick="goToStep(2)"
        >
            ← Back
        </button>

        <button
            type="submit"
            class="btn"
            id="submitButton"
        >
            Analyze My Stress →
        </button>

    </div>

</section>


</form>

</main>


<script>

/* =========================================================
   QUESTION DATA FROM PHP
========================================================= */

const questionData =
    <?= json_encode(
        $q,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ); ?>;


/* =========================================================
   CATEGORY ICONS
========================================================= */

const categoryIcons = [

    "👥",
    "❤️",
    "🏠",
    "🎓",
    "💼",
    "💰",
    "🧑‍🤝‍🧑",
    "💔",
    "🧑",
    "🔥",
    "🌟",
    "⏰",
    "🌙",
    "📱",
    "🧠"

];


/* =========================================================
   CATEGORY LIST
========================================================= */

const categories =
    Object.keys(questionData);


const categoryBox =
    document.getElementById(
        "categories"
    );


const situationInput =
    document.getElementById(
        "situation"
    );


/* =========================================================
   CREATE CATEGORY CARDS
========================================================= */

categories.forEach(
    (category, index) => {

        const item =
            document.createElement(
                "div"
            );

        item.className =
            "category";


        item.innerHTML = `

            <div class="category-check">
                ✓
            </div>

            <div class="category-icon">
                ${categoryIcons[index]}
            </div>

            <div class="category-name">
                ${category}
            </div>

        `;


        item.addEventListener(
            "click",
            function() {

                document
                    .querySelectorAll(
                        ".category"
                    )
                    .forEach(
                        element => {

                            element.classList.remove(
                                "selected"
                            );

                        }
                    );


                item.classList.add(
                    "selected"
                );


                situationInput.value =
                    category;

            }
        );


        categoryBox.appendChild(
            item
        );

    }
);


/* =========================================================
   STEP NAVIGATION
========================================================= */

function goToStep(step) {

    document
        .querySelectorAll(".step")
        .forEach(
            element => {

                element.classList.remove(
                    "active"
                );

            }
        );


    document
        .getElementById(
            "step" + step
        )
        .classList.add(
            "active"
        );


    document
        .querySelectorAll(
            ".step-indicator"
        )
        .forEach(
            element => {

                element.classList.remove(
                    "active"
                );

            }
        );


    document
        .getElementById(
            "indicator" + step
        )
        .classList.add(
            "active"
        );


    const progress =
        document.getElementById(
            "progressFill"
        );


    const percent =
        document.getElementById(
            "progressPercent"
        );


    const title =
        document.getElementById(
            "progressTitle"
        );


    if (step === 1) {

        progress.style.width =
            "33.33%";

        percent.textContent =
            "33%";

        title.textContent =
            "Step 1 — Select Concern";

    }


    else if (step === 2) {

        progress.style.width =
            "66.66%";

        percent.textContent =
            "66%";

        title.textContent =
            "Step 2 — Emotional Assessment";

    }


    else {

        progress.style.width =
            "100%";

        percent.textContent =
            "100%";

        title.textContent =
            "Step 3 — Personal Details";

    }


    window.scrollTo({

        top: 0,

        behavior: "smooth"

    });

}


/* =========================================================
   STEP 1 → QUESTIONS
========================================================= */

function goToQuestions() {

    const situation =
        situationInput.value;


    if (!situation) {

        alert(
            "Please select the main concern affecting you."
        );

        return;
    }


    document
        .getElementById(
            "selectedCategory"
        )
        .textContent =
            "● " + situation;


    renderQuestions(
        situation
    );


    goToStep(2);

}


/* =========================================================
   RENDER 15 QUESTIONS
========================================================= */

function renderQuestions(category) {

    const container =
        document.getElementById(
            "questionsContainer"
        );


    container.innerHTML = "";


    const questions =
        questionData[category];


    document.getElementById(
        "questionCount"
    ).textContent =
        questions.length +
        " Questions";


    questions.forEach(
        (question, index) => {

            const number =
                index + 1;


            const questionDiv =
                document.createElement(
                    "div"
                );


            questionDiv.className =
                "question";


            const options = [

                {
                    value: 1,
                    text: "Not at all"
                },

                {
                    value: 2,
                    text: "A little"
                },

                {
                    value: 3,
                    text: "Sometimes"
                },

                {
                    value: 4,
                    text: "Often"
                },

                {
                    value: 5,
                    text: "Extremely"
                }

            ];


            let optionsHTML = "";


            options.forEach(
                option => {

                    const inputId =
                        "q" +
                        number +
                        "_" +
                        option.value;


                    optionsHTML += `

                        <div class="option">

                            <input
                                type="radio"
                                name="q${number}"
                                id="${inputId}"
                                value="${option.value}"
                                required
                            >

                            <label
                                for="${inputId}"
                            >

                                <strong>
                                    ${option.value}
                                </strong>

                                ${option.text}

                            </label>

                        </div>

                    `;

                }
            );


            questionDiv.innerHTML = `

                <div class="question-number">

                    QUESTION
                    ${number}
                    OF
                    ${questions.length}

                </div>

                <div class="question-text">

                    ${question}

                </div>

                <div class="options">

                    ${optionsHTML}

                </div>

            `;


            container.appendChild(
                questionDiv
            );

        }
    );


    /*
     * Update question progress
     */

    const inputs =
        container.querySelectorAll(
            "input[type=radio]"
        );


    inputs.forEach(
        input => {

            input.addEventListener(
                "change",
                updateQuestionProgress
            );

        }
    );

}


/* =========================================================
   QUESTION PROGRESS
========================================================= */

function updateQuestionProgress() {

    const total =
        Object.keys(
            questionData[
                situationInput.value
            ]
        ).length;


    let answered = 0;


    for (
        let i = 1;
        i <= total;
        i++
    ) {

        if (
            document.querySelector(
                `input[name="q${i}"]:checked`
            )
        ) {

            answered++;

        }

    }


    const percentage =
        Math.max(
            6.66,
            (answered / total) * 100
        );


    document.getElementById(
        "questionProgress"
    ).style.width =
        percentage + "%";

}


/* =========================================================
   STEP 2 → STEP 3
========================================================= */

function goToDetails() {

    const total =
        Object.keys(
            questionData[
                situationInput.value
            ]
        ).length;


    for (
        let i = 1;
        i <= total;
        i++
    ) {

        const answer =
            document.querySelector(
                `input[name="q${i}"]:checked`
            );


        if (!answer) {

            alert(
                "Please answer all " +
                total +
                " questions before continuing."
            );


            const question =
                document.querySelector(
                    `.question:nth-child(${i})`
                );


            if (question) {

                question.scrollIntoView({

                    behavior: "smooth",

                    block: "center"

                });

            }


            return;

        }

    }


    goToStep(3);

}


/* =========================================================
   FINAL FORM VALIDATION
========================================================= */

document
    .getElementById(
        "assessmentForm"
    )
    .addEventListener(
        "submit",
        function(event) {


            /*
             * Check emotion
             */

            const emotion =
                document.querySelector(
                    'input[name="emotion"]:checked'
                );


            if (!emotion) {

                event.preventDefault();


                alert(
                    "Please select how you are feeling right now."
                );


                return;

            }


            /*
             * Check sleep
             */

            const sleep =
                document.getElementById(
                    "sleepHours"
                ).value;


            if (
                sleep === "" ||
                Number(sleep) < 0 ||
                Number(sleep) > 24
            ) {

                event.preventDefault();


                alert(
                    "Please enter a valid sleep duration between 0 and 24 hours."
                );


                return;

            }


            /*
             * Prevent duplicate submission
             */

            const button =
                document.getElementById(
                    "submitButton"
                );


            button.disabled =
                true;


            button.innerHTML =
                "Analyzing Your Responses...";

        }
    );

</script>

</body>

</html>