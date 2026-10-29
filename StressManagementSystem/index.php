<?php
require_once __DIR__ . '/config/session.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!doctype html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>StressCare | Stress Management System</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>

        /* =========================================================
           STRESSCARE PREMIUM HOME PAGE
        ========================================================= */

        :root {
            --green: #087f5b;
            --green-dark: #056044;
            --green-light: #e9f8f2;
            --lime: #8bcf39;
            --text: #123c32;
            --muted: #71847c;
            --border: #dcebe5;
            --white: #ffffff;
            --background: #f5faf8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family:
                Inter,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;

            color: var(--text);

            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(139, 207, 57, 0.08),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 90% 15%,
                    rgba(8, 127, 91, 0.08),
                    transparent 30%
                ),
                linear-gradient(
                    180deg,
                    #f9fdfb 0%,
                    #eef8f4 100%
                );

            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        /* =========================================================
           NAVBAR
        ========================================================= */

        .navbar {
            width: 100%;
            min-height: 78px;

            padding: 0 6%;

            display: flex;
            align-items: center;
            justify-content: space-between;

            background: rgba(255, 255, 255, 0.94);

            border-bottom: 1px solid var(--border);

            box-shadow:
                0 8px 30px rgba(8, 127, 91, 0.05);

            position: sticky;
            top: 0;
            z-index: 100;

            backdrop-filter: blur(14px);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;

            color: var(--green);

            font-size: 25px;
            font-weight: 850;
            letter-spacing: -0.5px;
        }

        .logo-icon {
            width: 48px;
            height: 48px;

            border-radius: 15px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: var(--green-light);

            font-size: 25px;

            box-shadow:
                inset 0 0 0 1px rgba(8, 127, 91, 0.04);
        }

        .logo span span {
            color: var(--lime);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-btn {
            padding: 11px 17px;

            border-radius: 12px;

            color: #45645c;

            font-size: 14px;
            font-weight: 700;

            transition: 0.2s ease;
        }

        .nav-btn:hover {
            background: var(--green-light);
            color: var(--green);
        }

        .nav-register {
            color: #ffffff !important;
            background: var(--green);

            box-shadow:
                0 8px 20px rgba(8, 127, 91, 0.18);
        }

        .nav-register:hover {
            background: var(--green-dark) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        /* =========================================================
           MAIN CONTAINER
        ========================================================= */

        .home-container {
            width: min(1180px, 90%);
            margin: auto;
        }

        /* =========================================================
           HERO
        ========================================================= */

        .hero {
            min-height: 600px;

            display: grid;

            grid-template-columns:
                1.08fr
                0.92fr;

            align-items: center;

            gap: 65px;

            padding:
                75px
                0
                70px;
        }

        .hero-content {
            max-width: 680px;
        }

        .eyebrow {
            display: inline-flex;

            align-items: center;
            gap: 8px;

            padding: 8px 14px;

            border-radius: 999px;

            background: var(--green-light);

            color: var(--green);

            font-size: 13px;
            font-weight: 800;

            letter-spacing: 0.2px;
        }

        .hero h1 {
            margin: 20px 0 18px;

            font-size:
                clamp(
                    44px,
                    6vw,
                    70px
                );

            line-height: 1.03;

            letter-spacing: -2.5px;

            color: var(--text);
        }

        .hero h1 .highlight {
            color: var(--green);
        }

        .hero h1 .lime {
            color: var(--lime);
        }

        .hero-description {
            max-width: 650px;

            margin: 0;

            color: var(--muted);

            font-size: 18px;

            line-height: 1.75;
        }

        /* =========================================================
           HERO BUTTONS
        ========================================================= */

        .hero-actions {
            display: flex;

            align-items: center;

            gap: 12px;

            flex-wrap: wrap;

            margin-top: 30px;
        }

        .main-btn {
            min-height: 50px;

            padding:
                0
                22px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 9px;

            border-radius: 14px;

            font-size: 15px;
            font-weight: 800;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .main-btn-primary {
            color: #ffffff;

            background: var(--green);

            box-shadow:
                0 12px 28px
                rgba(8, 127, 91, 0.22);
        }

        .main-btn-primary:hover {
            background: var(--green-dark);

            transform: translateY(-2px);

            box-shadow:
                0 15px 32px
                rgba(8, 127, 91, 0.27);
        }

        .main-btn-secondary {
            color: var(--green);

            background: #ffffff;

            border: 1px solid var(--border);
        }

        .main-btn-secondary:hover {
            background: var(--green-light);

            border-color: #b7dacb;

            transform: translateY(-2px);
        }

        /* =========================================================
           HERO VISUAL
        ========================================================= */

        .hero-visual {
            position: relative;

            min-height: 430px;

            display: flex;

            align-items: center;
            justify-content: center;
        }

        .visual-main {
            width: 365px;
            height: 365px;

            border-radius: 50%;

            display: flex;

            align-items: center;
            justify-content: center;

            background:
                radial-gradient(
                    circle at 35% 25%,
                    #ccefdc,
                    transparent 25%
                ),
                linear-gradient(
                    145deg,
                    #087f5b,
                    #075f47
                );

            box-shadow:
                0 35px 70px
                rgba(8, 127, 91, 0.20);

            font-size: 105px;

            position: relative;

            overflow: hidden;
        }

        .visual-main::before {
            content: "";

            position: absolute;

            width: 190px;
            height: 190px;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.07);

            top: -50px;
            right: -35px;
        }

        .visual-main::after {
            content: "";

            position: absolute;

            width: 150px;
            height: 150px;

            border-radius: 50%;

            background: rgba(139, 207, 57, 0.10);

            bottom: -40px;
            left: -30px;
        }

        .floating-card {
            position: absolute;

            padding: 16px 18px;

            background: #ffffff;

            border:
                1px solid
                var(--border);

            border-radius: 17px;

            box-shadow:
                0 18px 45px
                rgba(8, 127, 91, 0.13);
        }

        .floating-card strong {
            display: block;

            color: var(--text);

            font-size: 14px;
        }

        .floating-card span {
            display: block;

            color: var(--muted);

            font-size: 12px;

            margin-top: 3px;
        }

        .floating-one {
            top: 45px;
            left: 5px;
        }

        .floating-two {
            right: 0;
            bottom: 55px;
        }

        .floating-icon {
            display: inline-flex;

            width: 34px;
            height: 34px;

            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: var(--green-light);

            margin-bottom: 7px;
        }

        /* =========================================================
           FEATURE SECTION
        ========================================================= */

        .features-section {
            padding: 40px 0 80px;
        }

        .section-heading {
            text-align: center;

            max-width: 700px;

            margin:
                0
                auto
                35px;
        }

        .section-heading h2 {
            margin: 15px 0 10px;

            font-size: 35px;

            letter-spacing: -1px;
        }

        .section-heading p {
            color: var(--muted);

            font-size: 16px;
        }

        .feature-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }

        .feature-card {
            padding: 27px;

            background: #ffffff;

            border:
                1px solid
                var(--border);

            border-radius: 23px;

            box-shadow:
                0 12px 35px
                rgba(18, 60, 50, 0.055);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                border-color 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);

            border-color: #b8daca;

            box-shadow:
                0 18px 45px
                rgba(8, 127, 91, 0.10);
        }

        .feature-icon {
            width: 52px;
            height: 52px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 16px;

            background: var(--green-light);

            font-size: 24px;

            margin-bottom: 18px;
        }

        .feature-card h3 {
            margin: 0 0 8px;

            font-size: 19px;
        }

        .feature-card p {
            margin: 0;

            color: var(--muted);

            font-size: 14px;

            line-height: 1.65;
        }

        /* =========================================================
           HOW IT WORKS
        ========================================================= */

        .how-section {
            padding: 15px 0 85px;
        }

        .steps-grid {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 16px;
        }

        .step-card {
            padding: 23px;

            background: rgba(255, 255, 255, 0.75);

            border:
                1px solid
                var(--border);

            border-radius: 20px;
        }

        .step-number {
            width: 38px;
            height: 38px;

            border-radius: 12px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: var(--green);

            color: #ffffff;

            font-weight: 850;

            margin-bottom: 16px;
        }

        .step-card h3 {
            font-size: 17px;

            margin-bottom: 6px;
        }

        .step-card p {
            margin: 0;

            color: var(--muted);

            font-size: 13px;
        }

        /* =========================================================
           CTA
        ========================================================= */

        .cta-section {
            padding: 0 0 80px;
        }

        .cta-box {
            position: relative;

            overflow: hidden;

            padding: 50px;

            border-radius: 28px;

            color: #ffffff;

            background:
                radial-gradient(
                    circle at 85% 20%,
                    rgba(139, 207, 57, 0.18),
                    transparent 25%
                ),
                linear-gradient(
                    135deg,
                    #087f5b,
                    #075d46
                );

            box-shadow:
                0 25px 60px
                rgba(8, 127, 91, 0.16);
        }

        .cta-box h2 {
            margin: 0 0 10px;

            font-size: 34px;
        }

        .cta-box p {
            margin: 0;

            max-width: 650px;

            color: rgba(255,255,255,0.82);
        }

        .cta-button {
            display: inline-flex;

            margin-top: 25px;

            min-height: 48px;

            padding:
                0
                20px;

            align-items: center;
            justify-content: center;

            border-radius: 13px;

            background: #ffffff;

            color: var(--green);

            font-weight: 800;

            transition: 0.2s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);

            background: #f5fff9;
        }

        /* =========================================================
           FOOTER
        ========================================================= */

        .footer {
            padding:
                30px
                6%;

            text-align: center;

            color: var(--muted);

            background: #ffffff;

            border-top:
                1px solid
                var(--border);

            font-size: 13px;
        }

        .footer strong {
            color: var(--green);
        }

        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 900px) {

            .hero {
                grid-template-columns: 1fr;

                text-align: center;

                padding-top: 55px;
            }

            .hero-content {
                margin: auto;
            }

            .hero-description {
                margin: auto;
            }

            .hero-actions {
                justify-content: center;
            }

            .hero-visual {
                min-height: 390px;
            }

            .feature-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .steps-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 620px) {

            .navbar {
                padding: 0 4%;
            }

            .logo {
                font-size: 21px;
            }

            .logo-icon {
                width: 42px;
                height: 42px;
                font-size: 21px;
            }

            .nav-links {
                gap: 2px;
            }

            .nav-btn {
                padding: 9px 10px;

                font-size: 12px;
            }

            .hero {
                padding:
                    45px
                    0
                    50px;
            }

            .hero h1 {
                font-size: 43px;

                letter-spacing: -1.5px;
            }

            .hero-description {
                font-size: 16px;
            }

            .visual-main {
                width: 285px;
                height: 285px;

                font-size: 78px;
            }

            .floating-one {
                left: 0;
                top: 30px;
            }

            .floating-two {
                right: 0;
                bottom: 30px;
            }

            .feature-grid,
            .steps-grid {
                grid-template-columns: 1fr;
            }

            .cta-box {
                padding: 32px 25px;
            }

            .cta-box h2 {
                font-size: 28px;
            }

        }

    </style>

</head>

<body>

<!-- =========================================================
     NAVIGATION
========================================================= -->

<header class="navbar">

    <a class="logo" href="index.php">

        <div class="logo-icon">
            🌿
        </div>

        <span>
            Stress<span>Care</span>
        </span>

    </a>


    <div class="nav-links">

        <?php if ($isLoggedIn): ?>

            <a class="nav-btn" href="dashboard.php">
                Dashboard
            </a>

            <a class="nav-btn" href="history.php">
                History
            </a>

            <a class="nav-btn nav-register" href="assessment.php">
                New Assessment
            </a>

        <?php else: ?>

            <a class="nav-btn" href="login.php">
                Login
            </a>

            <a class="nav-btn nav-register" href="register.php">
                Register
            </a>

        <?php endif; ?>

    </div>

</header>


<!-- =========================================================
     MAIN
========================================================= -->

<main>

    <!-- HERO -->

    <section class="hero home-container">

        <div class="hero-content">

            <span class="eyebrow">
                🌱 Personal Wellbeing Companion
            </span>


            <h1>

                Understand your stress.

                <span class="highlight">
                    Take your next step.
                </span>

            </h1>


            <p class="hero-description">

                StressCare helps you understand your emotions,
                identify the situation affecting you, measure
                your stress level and discover practical,
                positive recommendations designed around you.

            </p>


            <div class="hero-actions">

                <?php if ($isLoggedIn): ?>

                    <a
                        class="main-btn main-btn-primary"
                        href="assessment.php"
                    >
                        Start Assessment
                        <span>→</span>
                    </a>

                    <a
                        class="main-btn main-btn-secondary"
                        href="dashboard.php"
                    >
                        Go to Dashboard
                    </a>

                <?php else: ?>

                    <a
                        class="main-btn main-btn-primary"
                        href="register.php"
                    >
                        Get Started
                        <span>→</span>
                    </a>

                    <a
                        class="main-btn main-btn-secondary"
                        href="login.php"
                    >
                        I Already Have an Account
                    </a>

                <?php endif; ?>

            </div>

        </div>


        <!-- HERO VISUAL -->

        <div class="hero-visual">

            <div class="floating-card floating-one">

                <div class="floating-icon">
                    🧠
                </div>

                <strong>
                    Understand
                </strong>

                <span>
                    Your emotions
                </span>

            </div>


            <div class="visual-main">

                🌿

            </div>


            <div class="floating-card floating-two">

                <div class="floating-icon">
                    💚
                </div>

                <strong>
                    Positive Steps
                </strong>

                <span>
                    Personalized for you
                </span>

            </div>

        </div>

    </section>


    <!-- FEATURES -->

    <section class="features-section">

        <div class="home-container">

            <div class="section-heading">

                <span class="eyebrow">
                    Built around you
                </span>

                <h2>
                    A calmer way to understand stress
                </h2>

                <p>
                    StressCare brings assessment, analysis,
                    recommendations and history together
                    in one simple experience.
                </p>

            </div>


            <div class="feature-grid">


                <div class="feature-card">

                    <div class="feature-icon">
                        🧠
                    </div>

                    <h3>
                        Smart Assessment
                    </h3>

                    <p>
                        Answer category-based questions
                        according to the situation affecting
                        you, such as studies, relationships,
                        family, finances and more.
                    </p>

                </div>


                <div class="feature-card">

                    <div class="feature-icon">
                        🎯
                    </div>

                    <h3>
                        Personalized Recommendations
                    </h3>

                    <p>
                        Receive positive suggestions based
                        on your emotion, main concern and
                        calculated stress level.
                    </p>

                </div>


                <div class="feature-card">

                    <div class="feature-icon">
                        📈
                    </div>

                    <h3>
                        Track Your Progress
                    </h3>

                    <p>
                        Review your previous assessments,
                        stress levels, emotions and concerns
                        through your personal history.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <!-- HOW IT WORKS -->

    <section class="how-section">

        <div class="home-container">

            <div class="section-heading">

                <span class="eyebrow">
                    Simple process
                </span>

                <h2>
                    How StressCare works
                </h2>

                <p>
                    A short guided process designed to
                    turn reflection into practical action.
                </p>

            </div>


            <div class="steps-grid">


                <div class="step-card">

                    <div class="step-number">
                        1
                    </div>

                    <h3>
                        Choose Your Emotion
                    </h3>

                    <p>
                        Tell us how you are feeling right now.
                    </p>

                </div>


                <div class="step-card">

                    <div class="step-number">
                        2
                    </div>

                    <h3>
                        Identify the Concern
                    </h3>

                    <p>
                        Select the area creating the most
                        pressure in your life.
                    </p>

                </div>


                <div class="step-card">

                    <div class="step-number">
                        3
                    </div>

                    <h3>
                        Answer Questions
                    </h3>

                    <p>
                        Complete questions specifically
                        related to your selected situation.
                    </p>

                </div>


                <div class="step-card">

                    <div class="step-number">
                        4
                    </div>

                    <h3>
                        Get Your Suggestions
                    </h3>

                    <p>
                        Receive practical and positive
                        recommendations for your next step.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <!-- CTA -->

    <section class="cta-section">

        <div class="home-container">

            <div class="cta-box">

                <h2>
                    Take a moment for yourself. 🌿
                </h2>

                <p>
                    Understanding how you feel is the first
                    step toward managing stress more effectively.
                    Start a quick assessment and see what
                    StressCare recommends for you.
                </p>


                <?php if ($isLoggedIn): ?>

                    <a
                        class="cta-button"
                        href="assessment.php"
                    >
                        Start My Assessment →
                    </a>

                <?php else: ?>

                    <a
                        class="cta-button"
                        href="register.php"
                    >
                        Create My Account →
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </section>

</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="footer">

    🌿
    <strong>StressCare</strong>
    — Your wellbeing matters.

    <br>

    <span>
        Stress Management System
    </span>

</footer>


<script src="assets/js/script.js"></script>

</body>
</html>