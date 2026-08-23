<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Message</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f5; font-family: Arial, Helvetica, sans-serif; color:#1f2a24;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="background:#165E53; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:20px; font-weight:bold;">New Contact Form Submission</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 20px; font-size:15px; line-height:1.6;">
                                You've received a new message from the {{ config('app.name') }} website contact form.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; line-height:1.6; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:6px 0; width:110px; color:#5a6a63;">Name</td>
                                    <td style="padding:6px 0; font-weight:bold;">{{ $data['name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#5a6a63;">Email</td>
                                    <td style="padding:6px 0; font-weight:bold;">{{ $data['email'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#5a6a63;">Phone</td>
                                    <td style="padding:6px 0; font-weight:bold;">{{ $data['phone'] ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#5a6a63;">Subject</td>
                                    <td style="padding:6px 0; font-weight:bold;">{{ $data['subject'] ?: '—' }}</td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; font-size:13px; color:#5a6a63; text-transform:uppercase; letter-spacing:.04em;">Message</p>
                            <p style="margin:0 0 28px; font-size:15px; line-height:1.7; white-space:pre-line; background:#f4f6f5; padding:16px; border-radius:8px;">{{ $data['message'] }}</p>

                            <a href="mailto:{{ $data['email'] }}" style="display:inline-block; background:#9FC149; color:#12271f; font-weight:bold; text-decoration:none; padding:12px 24px; border-radius:8px; font-size:14px;">
                                Reply to {{ $data['name'] }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; background:#f4f6f5; font-size:12px; color:#7c8b83;">
                            Sent automatically from the {{ config('app.name') }} contact form.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
