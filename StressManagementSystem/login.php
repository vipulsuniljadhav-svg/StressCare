<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($email === '' || $password === '') {

        $error = 'Please enter your email and password.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } else {

        /* =================================================
           FIND USER
        ================================================= */

        $stmt = $conn->prepare(
            "SELECT id, name, email, password
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            's',
            $email
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();


        /* =================================================
           VERIFY PASSWORD
        ================================================= */

        if (
            $user &&
            password_verify(
                $password,
                $user['password']
            )
        ) {

            /* Prevent session fixation */

            session_regenerate_id(true);

            $_SESSION['user_id'] =
                (int)$user['id'];

            $_SESSION['user_name'] =
                $user['name'];

            $_SESSION['user_email'] =
                $user['email'];


            header(
                'Location: dashboard.php'
            );

            exit;

        } else {

            $error =
                'Invalid email or password.';

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

    <title>Login | StressCare</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        /* =====================================================
           STRESSCARE LOGIN PAGE
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


        /* =====================================================
           LOGIN LAYOUT
        ===================================================== */

        .login-page {

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

        .login-intro {

            padding: 25px;
        }


        .login-intro h1 {

            margin:
                20px 0
                15px;

            font-size:
                clamp(
                    42px,
                    5vw,
                    60px
                );

            line-height: 1.05;

            letter-spacing: -2px;
        }


        .login-intro h1 span {

            color: #087f5b;
        }


        .login-intro > p {

            max-width: 520px;

            color: #71847c;

            font-size: 17px;

            line-height: 1.75;
        }


        /* =====================================================
           BENEFITS
        ===================================================== */

        .login-benefits {

            margin-top: 30px;

            display: grid;

            gap: 13px;
        }


        .login-benefit {

            display: flex;

            align-items: center;

            gap: 13px;

            color: #45645c;

            font-size: 14px;

            font-weight: 650;
        }


        .login-benefit-icon {

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
           LOGIN CARD
        ===================================================== */

        .login-card {

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

        .login-brand {

            display: flex;

            align-items: center;

            gap: 11px;

            color: #087f5b;

            font-size: 23px;

            font-weight: 850;

            margin-bottom: 25px;
        }


        .login-brand-icon {

            width: 45px;

            height: 45px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 14px;

            background: #e9f8f2;

            font-size: 23px;
        }


        .login-brand span span {

            color: #8bcf39;
        }


        /* =====================================================
           CARD HEADER
        ===================================================== */

        .login-card h2 {

            margin-bottom: 7px;

            font-size: 30px;

            letter-spacing: -0.7px;
        }


        .login-subtitle {

            color: #71847c;

            font-size: 14px;

            margin-bottom: 25px;
        }


        /* =====================================================
           ERROR
        ===================================================== */

        .login-error {

            padding:
                13px 15px;

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
           LOGIN BUTTON
        ===================================================== */

        .login-submit {

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


        .login-submit:hover {

            background: #056044;

            transform:
                translateY(-2px);

            box-shadow:
                0 14px 30px
                rgba(8, 127, 91, 0.25);
        }


        /* =====================================================
           REGISTER LINK
        ===================================================== */

        .register-link {

            margin-top: 20px;

            text-align: center;

            color: #71847c;

            font-size: 13px;
        }


        .register-link a {

            color: #087f5b;

            font-weight: 800;
        }


        .register-link a:hover {

            color: #056044;

            text-decoration: underline;
        }


        /* =====================================================
           BACK TO HOME
        ===================================================== */

        .back-home {

            display: block;

            margin-top: 17px;

            text-align: center;

            color: #8da09a;

            font-size: 12px;
        }


        .back-home:hover {

            color: #087f5b;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 850px) {

            .login-page {

                grid-template-columns: 1fr;

                gap: 20px;

                width: min(560px, 94%);
            }


            .login-intro {

                text-align: center;

                padding: 10px;
            }


            .login-intro > p {

                margin-left: auto;

                margin-right: auto;
            }


            .login-benefits {

                text-align: left;

                max-width: 420px;

                margin-left: auto;

                margin-right: auto;
            }
        }


        @media (max-width: 500px) {

            .login-page {

                padding: 25px 0;
            }


            .login-intro h1 {

                font-size: 39px;

                letter-spacing: -1.5px;
            }


            .login-card {

                padding: 25px;

                border-radius: 22px;
            }


            .login-card h2 {

                font-size: 26px;
            }
        }

    </style>

</head>


<body>


<main class="login-page">


    <!-- =====================================================
         LEFT INFORMATION PANEL
    ===================================================== -->

    <section class="login-intro">

        <span class="eyebrow">
            🌿 Welcome Back
        </span>


        <h1>

            Your wellbeing

            <span>
                space
            </span>

            is waiting.

        </h1>


        <p>

            Continue your StressCare journey.
            Review your previous assessments,
            understand your stress patterns and
            discover practical recommendations
            for your current situation.

        </p>


        <div class="login-benefits">


            <div class="login-benefit">

                <div class="login-benefit-icon">
                    🧠
                </div>

                <span>
                    Review your emotional check-ins
                </span>

            </div>


            <div class="login-benefit">

                <div class="login-benefit-icon">
                    📊
                </div>

                <span>
                    Track your stress assessment history
                </span>

            </div>


            <div class="login-benefit">

                <div class="login-benefit-icon">
                    🎯
                </div>

                <span>
                    Get recommendations based on your situation
                </span>

            </div>


            <div class="login-benefit">

                <div class="login-benefit-icon">
                    💚
                </div>

                <span>
                    Continue building positive wellbeing habits
                </span>

            </div>


        </div>

    </section>



    <!-- =====================================================
         LOGIN CARD
    ===================================================== -->

    <section class="login-card">


        <!-- BRAND -->

        <a
            href="index.php"
            class="login-brand"
        >

            <div class="login-brand-icon">
                🌿
            </div>

            <span>
                Stress<span>Care</span>
            </span>

        </a>


        <h2>
            Welcome Back
        </h2>


        <p class="login-subtitle">

            Login to continue your StressCare journey.

        </p>


        <!-- ERROR -->

        <?php if ($error): ?>

            <div class="login-error">

                ⚠️

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>


        <!-- LOGIN FORM -->

        <form
            method="post"
            autocomplete="on"
        >


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
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                class="login-submit"
            >

                Login to StressCare
                <span>→</span>

            </button>


        </form>


        <!-- REGISTER -->

        <div class="register-link">

            Don't have an account?

            <a href="register.php">
                Create an account
            </a>

        </div>


        <!-- HOME -->

        <a
            href="index.php"
            class="back-home"
        >
            ← Back to StressCare Home
        </a>


    </section>

</main>


</body>

</html>