<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body style="font-family: sans-serif; color: #1b1b18; margin: 0; padding: 24px; background: #f4f7f9;">
        <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border: 1px solid #d3dce3; border-radius: 8px; padding: 24px;">
            <h2 style="margin-top: 0;">New contact inquiry</h2>

            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="padding: 6px 0; color: #5a6a78; width: 120px;">Name</td>
                    <td style="padding: 6px 0;">{{ $inquiry->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #5a6a78;">Email</td>
                    <td style="padding: 6px 0;">{{ $inquiry->email }}</td>
                </tr>
                @if ($inquiry->phone)
                    <tr>
                        <td style="padding: 6px 0; color: #5a6a78;">Phone</td>
                        <td style="padding: 6px 0;">{{ $inquiry->phone }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding: 6px 0; color: #5a6a78;">Category</td>
                    <td style="padding: 6px 0;">{{ str($inquiry->category)->replace('_', ' ')->title() }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #5a6a78;">Subject</td>
                    <td style="padding: 6px 0;">{{ $inquiry->subject }}</td>
                </tr>
            </table>

            <p style="color: #5a6a78; font-size: 14px; margin-bottom: 4px;">Message</p>
            <p style="white-space: pre-wrap; font-size: 14px; line-height: 1.6;">{{ $inquiry->message }}</p>

            <p style="margin-top: 24px; font-size: 12px; color: #7d8b99;">
                View in the admin panel: {{ route('admin.contact-inquiries.show', $inquiry) }}
            </p>
        </div>
    </body>
</html>
