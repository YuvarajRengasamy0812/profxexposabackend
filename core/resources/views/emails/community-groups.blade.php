<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Join PROFX Expo Africa Communities' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#eef4f0; font-family:Arial, Helvetica, sans-serif; color:#082f27;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#eef4f0; padding:24px 0;">
    <tr>
        <td align="center" style="padding:0 12px;">
            <table width="680" cellpadding="0" cellspacing="0" role="presentation" style="width:100%; max-width:680px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 18px 44px rgba(8,47,39,0.14);">
                <tr>
                    <td align="center" style="background:#062d23; padding:28px 24px 24px;">
                        <img src="{{ $logo ?? 'https://profxexpo.com/africa/assets/images/logo/profx-white.png?v=20260722-082125' }}" alt="PROFX Expo Africa 2026" width="178" style="display:block; width:178px; max-width:75%; height:auto; margin:0 auto 14px;">
                        <h1 style="margin:0; color:#ffffff; font-size:25px; line-height:1.35; font-weight:800;">Join Our Official Community Groups</h1>
                        <p style="margin:9px 0 0; color:#e4c75f; font-size:15px; line-height:1.6; font-weight:700;">PROFX EXPO AFRICA 2026 updates, announcements, and expo details.</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:26px 28px 12px; text-align:center;">
                        <h2 style="margin:0 0 8px; color:#082f49; font-size:22px; line-height:1.35;">Hi {{ $user->full_name ?? 'there' }},</h2>
                        <p style="margin:0; color:#566677; font-size:15px; line-height:1.7;">Thank you for being part of PROFX EXPO AFRICA 2026. Please join our official Telegram and WhatsApp groups to receive daily updates and important event information.</p>
                    </td>
                </tr>
                 <tr>
                    <td align="center" style="padding:0 28px 24px;">
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td align="center" style="padding:6px;">
                                    <a href="{{ $telegramUrl ?? 'https://t.me/profxexpoafrica' }}" target="_blank" style="display:inline-block; background:#229ED9; color:#ffffff; text-decoration:none; font-size:15px; font-weight:800; border-radius:999px; padding:15px 24px; min-width:220px;">
                                        <img src="https://cdn.simpleicons.org/telegram/ffffff" alt="" width="18" height="18" style="vertical-align:-3px; margin-right:8px; border:0;"> Join Telegram Group
                                    </a>
                                </td>
                                <td align="center" style="padding:6px;">
                                    <a href="{{ $whatsappUrl ?? 'https://chat.whatsapp.com/Er7vSpSmGoK68nFbGrxAQk' }}" target="_blank" style="display:inline-block; background:#25D366; color:#ffffff; text-decoration:none; font-size:15px; font-weight:800; border-radius:999px; padding:15px 24px; min-width:220px;">
                                        <img src="https://cdn.simpleicons.org/whatsapp/ffffff" alt="" width="18" height="18" style="vertical-align:-3px; margin-right:8px; border:0;"> Join WhatsApp Group
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 26px;">
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f8fbf9; border:1px solid #dfe9e4; border-left:5px solid #c6a133; border-radius:14px;">
                            <tr>
                                <td style="padding:20px 22px;">
                                    <h3 style="margin:0 0 10px; color:#082f27; font-size:18px; line-height:1.4;">Stay connected with PROFX EXPO AFRICA 2026</h3>
                                    <p style="margin:0; color:#536273; font-size:14px; line-height:1.8;">Daily expo updates, speaker announcements, schedule alerts, event reminders, and important venue information will be shared in these official groups. Telegram is our first preferred update channel, and WhatsApp is available as an optional community channel.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:16px 28px 22px;">
                        <img src="{{ $heroImage ?? 'https://profxexpo.com/africa/adminpanel/assets/dashboard/images/email/telgram.png' }}" alt="Join PROFX Expo Africa official Telegram and WhatsApp groups" width="624" style="display:block; width:100%; max-width:624px; height:auto; border-radius:16px; border:1px solid #dde7e0;">
                    </td>
                </tr>

               

                <tr>
                    <td style="padding:0 28px 30px;">
                        <p style="margin:0; color:#082f27; font-size:14px; line-height:1.7; font-weight:700;">Thanks,<br>PROFX EXPO AFRICA 2026 Team</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="background:#062d23; padding:18px; color:#cbd5d1; font-size:12px; line-height:1.6;">
                        © 2026 PROFX EXPO AFRICA 2026. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>