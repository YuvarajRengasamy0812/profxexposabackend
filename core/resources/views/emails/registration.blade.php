<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'PROFX Expo Africa' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f2f2f2; font-family:Arial, sans-serif;">

<!-- Container -->
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f2f2f2; padding:20px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden;">
                
                <!-- Header -->
                <tr>
                    <td align="center" style="padding:30px 20px; background-color:#0f172a;">
                        <img src="{{ $logo ?? 'https://profxsummit.com/assets/images/logo/profx-dark.png' }}" alt="PROFX Summit Logo" width="150" style="display:block; margin-bottom:10px;">
                        <h1 style="color:#ffffff; font-size:24px; margin:0;">Your Ticket is Ready!</h1>
                        <p style="color:#cbd5e1; font-size:14px; margin:5px 0 0;">We're excited to see you at the summit</p>
                    </td>
                </tr>

                <!-- Welcome Section -->
                <tr>
                    <td style="padding:30px 20px; text-align:center;">
                        <div style="background-color:#ecfeff; border-radius:12px; padding:20px; margin-bottom:20px;">
                            <h2 style="margin:0; font-size:20px; color:#0f172a;">Welcome to PROFX Expo Africa 2026</h2>
                            <p style="color:#475569; font-size:14px; margin:8px 0 0;">
                                Thank you for registering! Your visitor access ticket has been confirmed.
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:15px;">
                                <tr>
                                    <td align="center" style="padding:5px; font-size:14px; color:#0f172a;"><strong>Date:</strong> 20 - 21 Aug 2026</td>
                                    <td align="center" style="padding:5px; font-size:14px; color:#0f172a;"><strong>Time:</strong> 10:00 AM</td>
                                    <td align="center" style="padding:5px; font-size:14px; color:#0f172a;"><strong>Venue:</strong>EXHIBITION HALL 5 CTICC 2, Cape Town, South Africa</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>

                <!-- Ticket Section -->
                <tr>
                    <td align="center" style="padding:20px;">
                        <img src="{{ $ticket_header ?? 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792010449837.png' }}" alt="Ticket Header" width="100%" style="display:block; border-radius:12px; margin-bottom:20px;">
                        
                        <!-- QR Code -->
                        <div style="margin-bottom:10px;">
                            <a href="https://profxsummit.com/login" target="_blank">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=https://profxsummit.com/login" alt="Scan to Login" width="180" style="display:block; margin:0 auto; border-radius:12px;">
                            </a>
                            <p style="font-size:12px; color:#555; margin-top:5px;">Scan to Login</p>
                        </div>

                        <!-- User Name -->
                        <h3 style="color:#0f172a; font-size:18px; margin:10px 0;">{{ $user->full_name ?? '' }}</h3>
                        
                        <img src="{{ $ticket_footer ?? 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792011237810.png' }}" alt="Ticket Footer" width="100%" style="display:block; border-radius:12px; margin-top:20px;">
                    </td>
                </tr>

                <!-- Important Note -->
                <tr>
                    <td style="padding:20px;">
                        <div style="border-left:5px solid #3b82f6; background-color:#ecfeff; padding:15px; border-radius:8px; font-size:14px; color:#475569;">
                            <strong>Important Information:</strong>
                            <ul style="padding-left:20px; margin:10px 0 0;">
                                <li>Bring a digital copy</li>
                                <li>Doors open 30 minutes before</li>
                                <li>Valid ID required</li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center" style="padding:20px; font-size:12px; color:#777;">
                        &copy; {{ date('Y') }} PROFX Summit. All rights reserved.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
