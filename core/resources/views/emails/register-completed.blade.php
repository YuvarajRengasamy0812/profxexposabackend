<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Register Completed - PROFX Expo Africa' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, sans-serif; color:#0f172a;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:24px 0;">
    <tr>
        <td align="center" style="padding:0 12px;">
            <table width="620" cellpadding="0" cellspacing="0" style="max-width:620px; width:100%; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 10px 30px rgba(15,23,42,0.08);">
                <tr>
                    <td align="center" style="background:#101828; padding:28px 24px;">
                        <img src="{{ $logo ?? asset('assets/images/logo/profx-white.png') }}" alt="PROFX Expo Africa" width="160" style="display:block; max-width:160px; height:auto; margin:0 auto 14px;">
                        <h1 style="margin:0; color:#ffffff; font-size:24px; line-height:1.3;">Welcome to PROFX Expo Africa 2026</h1>
                       
                    </td>
                </tr>

                <tr>
                    <td style="padding:26px 26px 14px;">
                        <h2 style="margin:0 0 10px; color:#0f172a; font-size:20px; line-height:1.35;">Hi {{ $user->full_name ?? 'Guest' }},</h2>
                        <p style="margin:0; color:#475569; font-size:15px; line-height:1.7;">
                            Your registration has been completed successfully. We are happy to welcome you to PROFX Expo Africa 2026.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:12px 26px 22px;">
                        <img src="{{ $heroImage ?? asset('assets/dashboard/images/email/1.png') }}" alt="PROFX Expo Africa" width="568" style="display:block; width:100%; max-width:568px; height:auto; border-radius:12px;">
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 26px 24px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
                            <tr>
                                <td style="padding:20px;">
                                    <h3 style="margin:0 0 14px; color:#0f172a; font-size:18px;">Login Details</h3>
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:8px 0; color:#64748b; font-size:14px; width:120px;">Login URL</td>
                                            <td style="padding:8px 0; color:#0f172a; font-size:14px;"><a href="{{ $loginUrl ?? 'https://profxsummit.com/login' }}" style="color:#c19d38; text-decoration:none; font-weight:bold;">{{ $loginUrl ?? 'https://profxsummit.com/login' }}</a></td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#64748b; font-size:14px;">Email</td>
                                            <td style="padding:8px 0; color:#0f172a; font-size:14px; font-weight:bold;">{{ $loginEmail ?? ($user->email ?? '') }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0; color:#64748b; font-size:14px;">Password</td>
                                            <td style="padding:8px 0; color:#0f172a; font-size:14px; font-weight:bold;">{{ $loginPassword ?? '' }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 26px 28px;">
                        <p style="margin:0 0 10px; color:#475569; font-size:14px; line-height:1.7;">
                            Thank you for completing your registration. Please keep your login details safe and use them whenever you need to access your profile.
                        </p>
                        <p style="margin:0; color:#0f172a; font-size:14px; line-height:1.7; font-weight:bold;">
                            Thanks,<br>PROFX EXPO Team
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:20px; font-size:12px; color:#777;">
                        &copy; {{ date('Y') }} PROFX EXPO AFRICA . All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
