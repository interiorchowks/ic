<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InteriorChowk Seller Registration Completed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #1F4386;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #1F4386;
            padding: 10px 16px;
            text-align: left;
            position: relative;
        }

        .header img {
            max-height: 50px;
        }

        .header a {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            padding: 10px 20px;
            background-color: #E26525;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            white-space: nowrap;
        }

        .main-image {
            width: 95%;
            height: auto;
            display: block;
            margin: auto;
        }

        .content {
            padding: 20px;
            text-align: left;
        }

        .content h1,
        .content h2 {
            color: #1F4386;
            margin-top: 0;
        }

        .content p {
            color: #333333;
            line-height: 1.6;
            margin: 8px 0;
        }

        .content ul {
            padding-left: 20px;
            margin: 8px 0 16px;
        }

        .content li {
            margin-bottom: 6px;
            color: #333333;
            line-height: 1.5;
        }

        .content .highlight {
            color: #1F4386;
            font-weight: bold;
        }

        .content .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 20px 0;
            background-color: #E26525;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
        }

        .footer {
            background-color: #ffffff;
            color: #333333;
            text-align: center;
            padding: 20px;
            border-radius: 0 0 8px 8px;
        }

        .footer a {
            color: #1F4386;
            text-decoration: none;
        }

        .footer .social-icons {
            margin: 20px 0;
        }

        .footer .social-icons img {
            max-height: 30px;
            margin: 0 10px;
        }

        .footer .small {
            font-size: 12px;
            margin-top: 10px;
        }

        /* Mobile */
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 6px;
            }

            .header {
                padding: 10px;
                text-align: center;
            }

            .header img {
                max-height: 40px;
            }

            .header a {
                position: static;
                display: inline-block;
                /* transform: none;
                margin-top: 8px; */
                margin-left:35px;
            }

            .content {
                padding: 16px;
            }

            .content .button {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }

            .footer .social-icons {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="https://pub-3593718b2c3a49558e703e35d10e7897.r2.dev/mail/logo.png"
                alt="InteriorChowk Logo">
            <a href="https://interiorchowk.com/storage/app/public/seller_guide/IC%20Seller%E2%80%99s%20Guide.pdf" style="margin-left:345px;">
                Seller Guide
            </a>
        </div>

        <!-- Hero Image -->
        <img src="https://pub-3593718b2c3a49558e703e35d10e7897.r2.dev/mail/onboardingheader.png"
            alt="Registration Completed" class="main-image">

        <!-- Content -->
        <div class="content">
            <h1>Your registration process is complete</h1>

            <p>
                Congratulations
                <span class="highlight">
                    {{ $seller->f_name ?? '' }} {{ $seller->l_name ?? '' }}
                </span>,
            </p>

            <p>
                Your registration process has been successfully completed and your seller code is
                <span class="highlight">
                    {{ $seller->seller_code ?? $vendorCode ?? '' }}
                </span>.
            </p>

            <p>
                Welcome to InteriorChowk, where we are thrilled to have you on board as our newest seller.
                We appreciate the trust you've placed in us and are committed to making your experience with
                our services exceptional.
            </p>

            <p>
                To ensure a smooth onboarding process, we have outlined the next steps below:
            </p>

            <h2>Account Setup:</h2>
            <ul>
                <li>
                    Visit our
                    <a href="https://interiorchowk.com">InteriorChowk</a>
                    and log in with your credentials.
                </li>
                <li>Complete your profile to personalize your experience.</li>
            </ul>

            <h2>Getting Started:</h2>
            <ul>
                <li>
                    We've crafted an extensive guide to assist you in beginning your journey.
                    You can access it through this link:
                    <a
                        href="https://interiorchowk.com/storage/app/public/seller_guide/IC%20Seller%E2%80%99s%20Guide.pdf">
                        Seller’s Guide
                    </a>
                </li>
            </ul>

            <h2>Welcome Package:</h2>
            <ul>
                <li>
                    Keep an eye on your mailbox! We'll be sending you a welcome package with some exciting offers &
                    surprises.
                </li>
            </ul>

            <h2>Training Sessions:</h2>
            <ul>
                <li>
                    Enhance your experience with our complimentary training sessions.
                    Let our expert team walk you through the essential features and address any queries you might have.
                    <a href="https://interiorchowk.com/training">Click here</a> to schedule your session and get
                    connected.
                </li>
            </ul>

            <h2>Support and Resources:</h2>
            <ul>
                <li>
                    Our customer support team is ready to assist you. Don't hesitate to reach out with any queries.
                    Additionally, explore our online resources and knowledge base for helpful articles and tutorials.
                </li>
            </ul>

            <h2>Feedback Matters:</h2>
            <ul>
                <li>
                    Your feedback is invaluable to us. As you explore our offerings, we encourage you to share your
                    thoughts and suggestions. We're constantly striving to improve and tailor our services to your
                    needs.
                </li>
            </ul>

            <h2>Join Seller Community:</h2>
            <ul>
                <li>
                    Become a part of our seller community on
                    <a href="https://www.instagram.com/icsellerchowk/">Instagram</a>,
                    <a href="https://www.facebook.com/profile.php?id=61557449746068">Facebook</a>, and
                    <a href="https://www.youtube.com/channel/UCn2inp-QlGEjgtl02CG1iWg">YouTube</a>,
                    and receive daily notifications about the latest updates.
                </li>
            </ul>

            <p>
                Thank you again for choosing
                <a href="https://interiorchowk.com">InteriorChowk</a>.
                We are confident that our partnership will be mutually beneficial, and we look forward to being a part
                of your success.
            </p>

            <p>
                If you have any immediate questions or concerns, feel free to reply to this email
                or contact our support team at
                <a href="mailto:support@interiorchowk.com">support@interiorchowk.com</a>
                or call us on +91 9953 680 690.
            </p>

            <p>
                Once again, welcome aboard!<br>
                Best Regards,<br>
                Support Team<br>
                <a href="https://interiorchowk.com">InteriorChowk</a>
            </p>

            <a href="https://interiorchowk.com/seller/auth/seller-login" class="button">
                Login Now
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="social-icons">
                <a href="https://www.facebook.com/profile.php?id=61557449746068">
                    <img src="https://pub-3593718b2c3a49558e703e35d10e7897.r2.dev/mail/fblogo.png"
                        alt="Facebook">
                </a>
                <a href="https://www.instagram.com/icsellerchowk/">
                    <img src="https://pub-3593718b2c3a49558e703e35d10e7897.r2.dev/mail/instalogo.png"
                        alt="Instagram">
                </a>
                <a href="https://www.youtube.com/channel/UCn2inp-QlGEjgtl02CG1iWg">
                    <img src="https://pub-3593718b2c3a49558e703e35d10e7897.r2.dev/mail/ytlogo.png"
                        alt="YouTube">
                </a>
                <a href="https://www.linkedin.com/company/100782897/admin/feed/posts/">
                    <img src="https://pub-3593718b2c3a49558e703e35d10e7897.r2.dev/mail/LIlogo.png"
                        alt="LinkedIn">
                </a>
            </div>
            <p>&copy; {{ date('Y') }} Soham Infratech. All rights reserved.</p>
            <p class="small">
                <br>You can
                <a href="https://interiorchowk.com/preferences">update your preferences</a>
                or
                <a href="https://interiorchowk.com/unsubscribe">unsubscribe</a>.
            </p>
        </div>
    </div>
</body>

</html>