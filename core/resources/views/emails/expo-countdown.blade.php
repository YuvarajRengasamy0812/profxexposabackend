<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'TODAY IS THE DAY! - PROFX Expo Africa 2026' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#eef4f0; font-family:Arial, Helvetica, sans-serif; color:#0f172a;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#eef4f0; padding:24px 0;">
    <tr>
        <td align="center" style="padding:0 12px;">
            <table width="680" cellpadding="0" cellspacing="0" role="presentation" style="width:100%; max-width:680px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 18px 44px rgba(8,47,39,0.14);">
                <tr>
                    <td align="center" style="background:#062d23; padding:28px 24px 24px;">
                        <img src="{{ $logo ?? 'https://profxexpo.com/africa/assets/images/logo/profx-white.png?v=20260722-082125' }}" alt="PROFX Expo Africa 2026" width="178" style="display:block; width:178px; max-width:75%; height:auto; margin:0 auto 14px;">
                        <h1 style="margin:0; color:#ffffff; font-size:25px; line-height:1.35; font-weight:800;">TODAY IS THE DAY!</h1>
                        <p style="margin:9px 0 0; color:#e4c75f; font-size:15px; line-height:1.6; font-weight:700;">PROFX EXPO AFRICA 2026 is officially LIVE in Cape Town!</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:30px 28px 18px;">
                        <p style="margin:0 0 8px; color:#536273; font-size:15px; line-height:1.7;">Hi {{ $user->full_name ?? 'there' }},</p>
                        <h2 style="margin:0; color:#082f27; font-size:34px; line-height:1.18; font-weight:900;">The wait is over</h2>
                        <p style="margin:14px auto 0; max-width:540px; color:#536273; font-size:16px; line-height:1.75;">The wait is over &mdash; <strong>PROFX EXPO AFRICA 2026</strong> is officially LIVE in <strong>Cape Town!</strong> &#x1F1FF;&#x1F1E6;</p>
                        <p style="margin:12px auto 0; max-width:560px; color:#536273; font-size:16px; line-height:1.75;">Two powerful days of <strong>Forex, Trading, FinTech, Crypto, Payments, Networking &amp; Business Opportunities</strong> start today.</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:0 28px 28px;">
                        <table cellpadding="0" cellspacing="0" role="presentation" style="margin:0 auto;">
                            <tr>
                                <td align="center" style="border-radius:999px; background:#0f8f46;">
                                    <a href="{{ $eventUrl ?? 'https://profxexpo.com/africa' }}" target="_blank" style="display:inline-block; color:#ffffff; text-decoration:none; font-size:15px; font-weight:800; padding:15px 28px; border-radius:999px;">Join the Experience</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:0 28px 26px;">
                        <img src="{{ $countdownHeroImage ?? 'https://profxexpo.com/africa/adminpanel/assets/dashboard/images/email/profx-expo-day.jpeg' }}" alt="PROFX Expo Africa 2026 Expo Day" width="624" style="display:block; width:100%; max-width:624px; height:auto; border-radius:16px; border:1px solid #dde7e0;">
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 28px;">
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f8fbf9; border:1px solid #dfe9e4; border-left:5px solid #c6a133; border-radius:14px;">
                            <tr>
                                <td style="padding:20px 22px;">
                                    <h3 style="margin:0 0 10px; color:#082f27; font-size:18px; line-height:1.4;">What to Expect Today</h3>
                                    <p style="margin:0; color:#536273; font-size:14px; line-height:1.8;">&#x1F91D; Connect with industry leaders<br>&#x1F3A4; Hear from global experts<br>&#x1F4C8; Discover new opportunities<br>&#x1F30D; Build powerful business connections<br>&#x1F3C6; Experience Africa&rsquo;s financial industry under one roof</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 28px;">
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff; border:1px solid #dfe9e4; border-radius:14px;">
                            <tr>
                                <td style="padding:20px 22px;">
                                    <h3 style="margin:0 0 10px; color:#082f27; font-size:18px; line-height:1.4;">Event Details</h3>
                                    <p style="margin:0; color:#536273; font-size:14px; line-height:1.8;">&#x1F4C5; <strong>20&ndash;21 August 2026</strong><br>&#x1F4CD; <strong>Cape Town, South Africa</strong><br>&#x1F525; <strong>Cape Town, the PROFX EXPO AFRICA experience starts NOW!</strong></p>
                                    <p style="margin:14px 0 0; color:#0f8f46; font-size:13px; line-height:1.8; font-weight:700;">#PROFXExpoAfrica #ExpoDay #CapeTown #Forex #Trading #FinTech #FinancialMarkets #Africa #PROFXMedia</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 30px;">
                        <p style="margin:0; color:#082f27; font-size:14px; line-height:1.7; font-weight:700;">Thanks,<br>PROFX EXPO AFRICA 2026 Team</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="background:#062d23; padding:18px; color:#cbd5d1; font-size:12px; line-height:1.6;">
                        &copy; 2026 PROFX EXPO AFRICA 2026. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
