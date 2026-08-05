<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 9px;
            margin: 18px;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 18px;
        }

        .meta {
            margin-bottom: 14px;
            color: #6b7280;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background: #c19d38;
            color: #fff;
            font-weight: 700;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        tr:nth-child(even) td {
            background: #f9fafb;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Exported at {{ now()->format('Y-m-d H:i:s') }} | Total rows: {{ $rows->count() }}</div>

    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $value)
                        <td>{{ $value ?: '-' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}">No data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>