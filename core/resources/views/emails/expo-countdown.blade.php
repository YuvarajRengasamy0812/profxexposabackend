<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Only 15 Days to Go - PROFX Expo Africa 2026' }}</title>
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
                        <h1 style="margin:0; color:#ffffff; font-size:25px; line-height:1.35; font-weight:800;">The Countdown Begins</h1>
                        <p style="margin:9px 0 0; color:#e4c75f; font-size:15px; line-height:1.6; font-weight:700;">PROFX EXPO AFRICA 2026 is almost here.</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:30px 28px 18px;">
                        <p style="margin:0 0 8px; color:#536273; font-size:15px; line-height:1.7;">Hi {{ $user->full_name ?? 'there' }},</p>
                        <h2 style="margin:0; color:#082f27; font-size:34px; line-height:1.18; font-weight:900;">Only 15 Days Remaining</h2>
                        <p style="margin:14px auto 0; max-width:540px; color:#536273; font-size:16px; line-height:1.75;">Cape Town is getting ready for the future of finance. Two powerful days of connections, ideas, and opportunities are getting closer.</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:0 28px 28px;">
                        <table cellpadding="0" cellspacing="0" role="presentation" style="margin:0 auto;">
                            <tr>
                                <td align="center" style="border-radius:999px; background:#0f8f46;">
                                    <a href="{{ $eventUrl ?? 'https://profxexpo.com/africa' }}" target="_blank" style="display:inline-block; color:#ffffff; text-decoration:none; font-size:15px; font-weight:800; padding:15px 28px; border-radius:999px;">Visit Event Website</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:0 28px 26px;">
                        <img src="{{ $countdownHeroImage ?? 'https://profxexpo.com/africa/adminpanel/assets/dashboard/images/email/profx-15-days-countdown.jpeg' }}" alt="PROFX Expo Africa 2026 countdown: 15 days to go" width="624" style="display:block; width:100%; max-width:624px; height:auto; border-radius:16px; border:1px solid #dde7e0;">
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 28px;">
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f8fbf9; border:1px solid #dfe9e4; border-left:5px solid #c6a133; border-radius:14px;">
                            <tr>
                                <td style="padding:20px 22px;">
                                    <h3 style="margin:0 0 10px; color:#082f27; font-size:18px; line-height:1.4;">Event Details</h3>
                                    <p style="margin:0; color:#536273; font-size:14px; line-height:1.8;"><strong>Date:</strong> 20 - 21 August 2026<br><strong>Venue:</strong> Cape Town, South Africa<br><strong>Website:</strong> <a href="{{ $eventUrl ?? 'https://profxexpo.com/africa' }}" target="_blank" style="color:#0f8f46; font-weight:700; text-decoration:none;">www.profxexpo.com/africa</a></p>
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