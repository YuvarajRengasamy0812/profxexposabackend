<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'PROFX League Registration Completed' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#f3f6f1; font-family:Arial, sans-serif; color:#0b2d24;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6f1; padding:24px 0;">
    <tr>
        <td align="center" style="padding:0 12px;">
            <table width="680" cellpadding="0" cellspacing="0" style="max-width:680px; width:100%; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 16px 40px rgba(12,45,36,0.12);">
                <tr>
                    <td align="center" style="background:#062d23; padding:28px 24px;">
                        <img src="{{ $logo ?? 'https://profxexpo.com/africa/adminpanel/uploads/settings/17791964093936.png' }}" alt="PROFX Expo Africa" width="170" style="display:block; width:170px; max-width:70%; height:auto; margin:0 auto 14px;">
                        <h1 style="margin:0; color:#ffffff; font-size:25px; line-height:1.35;">Welcome to PROFX League</h1>
                        <p style="margin:8px 0 0; color:#e4c75f; font-size:15px; line-height:1.6; font-weight:bold;">Your FX Championship 2026 registration is confirmed.</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:26px 28px 14px; text-align:center;">
                        <h2 style="margin:0 0 10px; color:#082f49; font-size:22px; line-height:1.35;">Hi {{ $booking->name ?? 'Trader' }},</h2>
                        <p style="margin:0; color:#506174; font-size:15px; line-height:1.7;">
                            Thank you for registering for PROFX League. Your league profile has been created successfully.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:12px 28px 22px;">
                        <img src="{{ $heroImage }}" alt="PROFX League Registration {{ $booking->name ?? '' }}" width="624" style="display:block; width:100%; max-width:624px; height:auto; border-radius:14px; border:1px solid #e5e7eb;">
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 24px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px;">
                            <tr>
                                <td style="padding:20px;">
                                    <h3 style="margin:0 0 14px; color:#082f49; font-size:18px;">Registration Details</h3>
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:8px 0; color:#64748b; font-size:14px; width:130px;">League ID</td>
                                            <td style="padding:8px 0; color:#0b2d24; font-size:14px; font-weight:bold;">PFXL-{{ str_pad((string) ($booking->id ?? 0), 5, '0', STR_PAD_LEFT) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#64748b; font-size:14px;">Name</td>
                                            <td style="padding:8px 0; color:#0b2d24; font-size:14px; font-weight:bold;">{{ $booking->name ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#64748b; font-size:14px;">Role</td>
                                            <td style="padding:8px 0; color:#0b2d24; font-size:14px; font-weight:bold;">{{ $booking->role ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#64748b; font-size:14px;">Company</td>
                                            <td style="padding:8px 0; color:#0b2d24; font-size:14px; font-weight:bold;">{{ $booking->company ?? '' }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 30px;">
                        <p style="margin:0 0 10px; color:#506174; font-size:14px; line-height:1.7;">
                            We will share championship updates and next steps with you soon. Please keep this confirmation email for your records.
                        </p>
                        <p style="margin:0; color:#0b2d24; font-size:14px; line-height:1.7; font-weight:bold;">
                            Thanks,<br>PROFX League Team
                        </p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="background:#f8fafc; padding:18px; font-size:12px; color:#64748b;">
                        &copy; {{ date('Y') }} PROFX Expo Africa. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>