<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>{{ __('general.mail_welcome_subject') }}</title>
</head>
<body>
    <h2>{{ __('general.mail_welcome_greeting', ['name' => $parent['fullName']]) }}</h2>

    <p>{{ __('general.mail_welcome_body') }}</p>

    <ul>
        <li>{{ __('general.mail_student_name') }}: {{ $student['fullName'] }}</li>
        <li>{{ __('general.mail_student_number') }}: {{ $student['number'] }}</li>
        <li>{{ __('general.mail_student_grade') }}: {{ $student['grade'] }}</li>
    </ul>

    <p>{{ __('general.mail_welcome_footer') }}</p>
</body>
</html>
