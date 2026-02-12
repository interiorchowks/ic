<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1F4386;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #1F4386;
            padding: 10px;
            border-radius: 8px 8px 0 0;
        }
        .header img {
            max-height: 40px;
        }
        .header a {
            color: #ffffff;
            text-decoration: none;
            padding: 8px 16px;
            background-color: #E26525;
            border-radius: 4px;
            font-size: 14px;
        }
        .content {
            text-align: center;
            padding: 20px;
        }
        .content img {
            width: 80%;
            height: auto;
            margin: 20px 0;
        }
        .content p {
            color: #333333;
            line-height: 1.6;
            text-align: left;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 20px 0;
            text-decoration: none;
            color: #ffffff !important;
            background-color: #E26525;
            border-radius: 4px;
            font-size: 18px;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background-color: #1F4386;
            color: #ffffff;
            border-radius: 0 0 8px 8px;
        }
        .footer-icons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }
        .footer-icons img {
            max-height: 40px;
            margin: 0 8px;
        }
        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        @media (max-width: 600px) {
            .container { padding: 10px; }
            .header { flex-direction: column; align-items: flex-start; }
            .content img { width: 100%; }
            .footer-icons { flex-direction: column; }
        }
    </style>
</head>

<body>
<div class="container">

    {{-- Header --}}
    <div class="header">
        <img src="http://cdn.mcauto-images-production.sendgrid.net/4285993ee8562336/29d2cef6-e9c5-4994-a4e9-e2711fa5bc80/210x70.png"
             alt="InteriorChowk Logo">

        <a href="https://interiorchowk.com/storage/app/public/seller_guide/IC%20Seller%E2%80%99s%20Guide.pdf">
            Seller Guide
        </a>
    </div>

    {{-- Content --}}
    <div class="content">
        <img src="http://cdn.mcauto-images-production.sendgrid.net/4285993ee8562336/2b6eb056-d26b-4ae7-be67-4b4037c5416e/872x444.png"
             alt="Password Reset">

        <p><strong>Dear {{ $seller->f_name.' '.$seller->l_name ?? 'Seller' }},</strong></p>

        <p>
            We received a request to reset the password for your account associated with this email address.
            If you did not make this request, please ignore this email.
        </p>

        <p>To reset your password, click the button below:</p>

        {{-- Reset Button --}}
        <a href="{{ $url }}" class="button" target="_blank">
            Reset Password
        </a>

        <p>If the button does not work, copy and paste this link into your browser:</p>

        <p>
            <a href="{{ $url }}" target="_blank">{{ $url }}</a>
        </p>

        <p>
            For security purposes, this link will expire in <strong>24 hours</strong>.
            If you need a new reset link, please request again from our website.
        </p>

        <p>
            Need help? Contact us at
            <a href="mailto:support@interiorchowk.com">support@interiorchowk.com</a>
        </p>

        <p>
            Thank you,<br>
            <strong>InteriorChowk Support Team</strong>
        </p>

        <h3>So what are you waiting for?</h3>

        <a href="https://interiorchowk.com/seller/auth/seller-login" class="button">
            Login Now
        </a>
    </div>

    {{-- Footer Icons --}}
    <div class="footer-icons">
        <div>
            <a href="https://interiorchowk.com/">www.interiorchowk.com</a>
        </div>
        <div>
            <a href="https://www.facebook.com/profile.php?id=61557449746068">
                <img src="http://cdn.mcauto-images-production.sendgrid.net/4285993ee8562336/0510463a-7b42-4cc2-a050-35b9483853b6/71x71.png">
            </a>
            <a href="https://www.instagram.com/icsellerchowk/">
                <img src="http://cdn.mcauto-images-production.sendgrid.net/4285993ee8562336/7cc59e38-ad58-4b82-9659-d1cbd504f5eb/71x71.png">
            </a>
            <a href="https://www.youtube.com/channel/UCn2inp-QlGEjgtl02CG1iWg">
                <img src="http://cdn.mcauto-images-production.sendgrid.net/4285993ee8562336/37bf0133-d4ca-4dc1-bd8a-ebf685335690/71x71.png">
            </a>
            <a href="https://www.linkedin.com/company/100782897/admin/feed/posts/">
                <img src="http://cdn.mcauto-images-production.sendgrid.net/4285993ee8562336/c8f2837d-8463-4ad0-9534-208185fbda1d/71x71.png">
            </a>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>&copy; {{ date('Y') }} Soham Infratech. All rights reserved.</p>
    </div>

    <div class="footer-text">
        <p>You can update your preferences or <a href="#">unsubscribe</a></p>
    </div>

</div>
</body>
</html>
