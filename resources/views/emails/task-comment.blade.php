<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Comment on Task</title>
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
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .task-number {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        .comment-box {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #6366f1;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .commenter-name {
            font-weight: 600;
            color: #1f2937;
        }
        .comment-time {
            font-size: 12px;
            color: #9ca3af;
            margin-left: 10px;
        }
        .comment-content {
            font-size: 14px;
            color: #4b5563;
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
        .unwatch-note {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
        </div>

        <h1>New Comment on Task</h1>
        <p class="subtitle">{{ $commenter->full_name }} commented on a task you're involved in</p>

        <div class="content">
            <p>Hi {{ $recipient->first_name }},</p>
            <p>A new comment has been added to the following task in <strong>{{ $task->workspace->name }}</strong>:</p>
        </div>

        <div class="task-card">
            <div class="task-title">{{ $task->title }}</div>
            <div class="task-number">#{{ $task->task_number }}</div>
        </div>

        <div class="comment-box">
            <div class="comment-header">
                <span class="commenter-name">{{ $commenter->full_name }}</span>
                <span class="comment-time">{{ $comment->created_at->format('M d, Y \a\t h:i A') }}</span>
            </div>
            <div class="comment-content">
                {!! Str::limit(strip_tags($comment->content), 500) !!}
            </div>
        </div>

        <div class="button-container">
            <a href="{{ $taskUrl }}" class="button">View Task & Reply</a>
        </div>

        <div class="footer">
            <p>You're receiving this because you're involved in this task (as assignee, watcher, or mentioned).</p>
            <p class="unwatch-note">To stop receiving notifications for this task, click "Unwatch" on the task page.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
