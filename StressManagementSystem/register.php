<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm'] ?? '';

    /* ---------------- VALIDATION ---------------- */

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {

        $error = 'Please complete all fields.';

    } elseif (strlen($name) < 2) {

        $error = 'Please enter a valid name.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } elseif (strlen($password) < 6) {

        $error = 'Password must be at least 6 characters long.';

    } elseif ($password !== $confirmPassword) {

        $error = 'Passwords do not match.';

    } else {

        /* ---------------- CHECK EXISTING USER ---------------- */

        $stmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param('s', $email);

        $stmt->execute();

        $result = $stmt->get_result();

        $existingUser = $result->fetch_assoc();

        $stmt->close();


        if ($existingUser) {

            $error = 'This email address is already registered.';

        } else {

            /* ---------------- CREATE USER ---------------- */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                'sss',
                $name,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {

                $userId = $stmt->insert_id;

                $stmt->close();

                /* ---------------- LOGIN USER ---------------- */

                session_regenerate_id(true);

                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;

                header('Location: dashboard.php');
                exit;

            } else {

                $error = 'Unable to create your account. Please try again.';

                $stmt->close();
            }
        }
    }
}

?>

<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Create Account | StressCare</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        /* =====================================================
           STRESSCARE REGISTER PAGE
        ===================================================== */

        body {
            min-height: 100vh;

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
        }

        .register-page {

            min-height: 100vh;

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            align-items: center;

            gap: 70px;

            width: min(1100px, 92%);

            margin: auto;

            padding: 50px 0;
        }


        /* =====================================================
           LEFT SIDE
        ===================================================== */

        .register-intro {

            padding: 25px;
        }

        .register-intro h1 {

            margin: 20px 0 15px;

            font-size:
                clamp(
                    42px,
                    5vw,
                    60px
                );

            line-height: 1.05;

            letter-spacing: -2px;
        }

        .register-intro h1 span {

            color: #087f5b;
        }

        .register-intro > p {

            max-width: 520px;

            color: #71847c;

            font-size: 17px;

            line-height: 1.75;
        }


        /* =====================================================
           BENEFITS
        ===================================================== */

        .register-benefits {

            margin-top: 30px;

            display: grid;

            gap: 13px;
        }

        .benefit {

            display: flex;

            align-items: center;

            gap: 13px;

            color: #45645c;

            font-size: 14px;

            font-weight: 650;
        }

        .benefit-icon {

            width: 40px;

            height: 40px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background: #e9f8f2;

            font-size: 18px;
        }


        /* =====================================================
           REGISTER CARD
        ===================================================== */

        .register-card {

            background: #ffffff;

            border:
                1px solid
                #dcebe5;

            border-radius: 28px;

            padding: 35px;

            box-shadow:
                0 25px 65px
                rgba(8, 127, 91, 0.12);
        }


        /* =====================================================
           BRAND
        ===================================================== */

        .register-brand {

            display: flex;

            align-items: center;

            gap: 11px;

            color: #087f5b;

            font-size: 23px;

            font-weight: 850;

            margin-bottom: 25px;
        }

        .register-brand-icon {

            width: 45px;

            height: 45px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 14px;

            background: #e9f8f2;

            font-size: 23px;
        }

        .register-brand span span {

            color: #8bcf39;
        }


        /* =====================================================
           CARD HEADER
        ===================================================== */

        .register-card h2 {

            margin-bottom: 7px;

            font-size: 30px;

            letter-spacing: -0.7px;
        }

        .register-subtitle {

            color: #71847c;

            font-size: 14px;

            margin-bottom: 25px;
        }


        /* =====================================================
           ERROR
        ===================================================== */

        .register-error {

            padding: 13px 15px;

            margin-bottom: 18px;

            border-radius: 12px;

            color: #b63737;

            background: #fff0f0;

            border:
                1px solid
                #ffd6d6;

            font-size: 13px;

            font-weight: 650;
        }


        /* =====================================================
           PASSWORD REQUIREMENT
        ===================================================== */

        .password-hint {

            margin-top: 6px;

            color: #8da09a;

            font-size: 11px;
        }


        /* =====================================================
           CREATE BUTTON
        ===================================================== */

        .register-submit {

            width: 100%;

            min-height: 50px;

            border: none;

            border-radius: 13px;

            background: #087f5b;

            color: #ffffff;

            font-size: 15px;

            font-weight: 800;

            cursor: pointer;

            box-shadow:
                0 10px 25px
                rgba(8, 127, 91, 0.20);

            transition:
                0.2s ease;
        }

        .register-submit:hover {

            background: #056044;

            transform:
                translateY(-2px);

            box-shadow:
                0 14px 30px
                rgba(8, 127, 91, 0.25);
        }


        /* =====================================================
           LOGIN LINK
        ===================================================== */

        .login-link {

            margin-top: 20px;

            text-align: center;

            color: #71847c;

            font-size: 13px;
        }

        .login-link a {

            color: #087f5b;

            font-weight: 800;
        }

        .login-link a:hover {

            color: #056044;

            text-decoration: underline;
        }


        /* =====================================================
           TERMS
        ===================================================== */

        .terms {

            margin-top: 16px;

            color: #8da09a;

            font-size: 11px;

            line-height: 1.5;

            text-align: center;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 850px) {

            .register-page {

                grid-template-columns: 1fr;

                gap: 20px;

                width: min(560px, 94%);

            }

            .register-intro {

                text-align: center;

                padding: 10px;
            }

            .register-intro > p {

                margin-left: auto;

                margin-right: auto;
            }

            .register-benefits {

                text-align: left;

                max-width: 420px;

                margin-left: auto;

                margin-right: auto;
            }

        }


        @media (max-width: 500px) {

            .register-page {

                padding: 25px 0;
            }

            .register-intro h1 {

                font-size: 39px;

                letter-spacing: -1.5px;
            }

            .register-card {

                padding: 25px;

                border-radius: 22px;
            }

            .register-card h2 {

                font-size: 26px;
            }

        }

    </style>

</head>


<body>


<main class="register-page">


    <!-- =====================================================
         LEFT INFORMATION PANEL
    ===================================================== -->

    <section class="register-intro">

        <span class="eyebrow">
            🌱 Welcome to StressCare
        </span>


        <h1>

            Start your

            <span>
                wellbeing
            </span>

            journey.

        </h1>


        <p>

            Create your StressCare account and get a
            personalized space to understand your stress,
            reflect on your emotions and discover practical
            ways to manage everyday challenges.

        </p>


        <div class="register-benefits">


            <div class="benefit">

                <div class="benefit-icon">
                    🧠
                </div>

                <span>
                    Emotion and stress assessment
                </span>

            </div>


            <div class="benefit">

                <div class="benefit-icon">
                    🎯
                </div>

                <span>
                    Situation-based questions
                </span>

            </div>


            <div class="benefit">

                <div class="benefit-icon">
                    💚
                </div>

                <span>
                    Personalized positive recommendations
                </span>

            </div>


            <div class="benefit">

                <div class="benefit-icon">
                    📊
                </div>

                <span>
                    Assessment history and progress
                </span>

            </div>


        </div>

    </section>



    <!-- =====================================================
         REGISTER FORM
    ===================================================== -->

    <section class="register-card">


        <!-- BRAND -->

        <a
            href="index.php"
            class="register-brand"
        >

            <div class="register-brand-icon">
                🌿
            </div>

            <span>
                Stress<span>Care</span>
            </span>

        </a>


        <h2>
            Create Account
        </h2>


        <p class="register-subtitle">

            Enter your details to create your personal
            StressCare account.

        </p>


        <!-- ERROR -->

        <?php if ($error): ?>

            <div class="register-error">

                ⚠️

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form
            method="post"
            autocomplete="off"
        >


            <!-- NAME -->

            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    placeholder="Enter your full name"
                    value="<?= htmlspecialchars(
                        $name,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    autocomplete="name"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    placeholder="Enter your email address"
                    value="<?= htmlspecialchars(
                        $email,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    autocomplete="email"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Create a password"
                    minlength="6"
                    autocomplete="new-password"
                    required
                >

                <div class="password-hint">
                    Use at least 6 characters.
                </div>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label for="confirm">
                    Confirm Password
                </label>

                <input
                    id="confirm"
                    type="password"
                    name="confirm"
                    placeholder="Enter your password again"
                    minlength="6"
                    autocomplete="new-password"
                    required
                >

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="register-submit"
            >

                Create My Account
                <span>→</span>

            </button>


        </form>


        <!-- LOGIN -->

        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Login here
            </a>

        </div>


        <!-- TERMS -->

        <div class="terms">

            By creating an account, you agree to use
            StressCare responsibly as a personal
            wellbeing and stress-reflection system.

        </div>


    </section>

</main>


</body>

</html> 