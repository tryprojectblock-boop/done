<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Assigned to You</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #6366f1;
        }
        h1 {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #6b7280;
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            margin-bottom: 25px;
        }
        .task-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .task-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
        }
        .task-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        .meta-item {
            font-size: 14px;
            color: #6b7280;
        }
        .meta-label {
            font-weight: 600;
            color: #374151;
        }
        .priority-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .priority-urgent { background-color: #fee2e2; color: #991b1b; }
        .priority-high { background-color: #fef3c7; color: #92400e; }
        .priority-medium { background-color: #dbeafe; color: #1e40af; }
        .priority-low { background-color: #d1fae5; color: #065f46; }
        .task-description {
            font-size: 14px;
            color: #4b5563;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background-color: #6366f1;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
        }
        .button:hover {
            background-color: #4f46e5;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .footer {
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #6366f1;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
        </div>

        <h1>Task Assigned to You</h1>
        <p class="subtitle">{{ $assigner->full_name }} has assigned you a task</p>

        <div class="content">
            <p>Hi {{ $assignee->first_name }},</p>
            <p>A task has been assigned to you in <strong>{{ $task->workspace->name }}</strong>.</p>
        </div>

        <div class="task-card">
            <div class="task-title">{{ $task->title }}</div>

            <div class="task-meta">
                @if($task->priority)
                <div class="meta-item">
                    <span class="meta-label">Priority:</span>
                    <span class="priority-badge priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                </div>
                @endif

                @if($task->due_date)
                <div class="meta-item">
                    <span class="meta-label">Due:</span>
                    {{ $task->due_date->format('M d, Y') }}
                </div>
                @endif

                @if($task->status)
                <div class="meta-item">
                    <span class="meta-label">Status:</span>
                    {{ $task->status->name }}
                </div>
                @endif
            </div>

            @if($task->description)
            <div class="task-description">
                {!! Str::limit(strip_tags($task->description), 200) !!}
            </div>
            @endif
        </div>

        <div class="button-container">
            <a href="{{ $taskUrl }}" class="button">View Task</a>
        </div>

        <div class="footer">
            <p>This task was assigned by {{ $assigner->full_name }} ({{ $assigner->email }})</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
