<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PROFX Summit Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin:0;
            padding:0;
        }
        .ticket {
            width: 100%;
            text-align: center;
        }
        .ticket img {
            width: 100%;
            display: block;
            margin: 0;
            padding: 0;
        }
        .barcode {
            margin: 10px 0;
        }
        .username {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }
        .ticket-barcode img {
    max-width: 200px; /* QR size */
    height: auto;
    margin: 0 auto;
    display: block;
}
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Ticket Header -->
        <img src="{{'https://profxsummit.com/assets/images/ticket-header.png' }}" alt="Ticket Header">
        {{-- <img src="{{ $ticket_header ?? 'https://profxsummit.com/assets/images/ticket-header.png' }}" alt="Ticket Header"> --}}

        <!-- Barcode + Name -->
        <div class="barcode">
            <img src="{{ $barcodeBase64 }}" alt="Barcode">
            <div class="username">{{ $user->full_name }}</div>
        </div>

        <!-- Ticket Footer -->
        <img src="{{'https://profxsummit.com/assets/images/ticket-footer.png' }}" alt="Ticket Footer">
        {{-- <img src="{{ $ticket_footer ?? 'https://profxsummit.com/assets/images/ticket-footer.png' }}" alt="Ticket Footer"> --}}
    </div>
</body>
</html>
