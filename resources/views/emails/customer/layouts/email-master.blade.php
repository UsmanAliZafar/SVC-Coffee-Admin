{{-- Path: resources/views/emails/customer/layouts/email-master.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('email_title', config('app.name'))</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; background-color: #f4f4f4; margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

    {{-- Outer wrapper --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 10px;">

                {{-- Email container --}}
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                    style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

                    {{-- ===== HEADER ===== --}}
                    @include('emails.customer.partials.header')

                    {{-- ===== BODY CONTENT ===== --}}
                    <tr>
                        <td style="padding: 40px 30px;">
                            @yield('email_content')
                        </td>
                    </tr>

                    {{-- ===== FOOTER ===== --}}
                    @include('emails.customer.partials.footer')

                </table>
                {{-- /Email container --}}

            </td>
        </tr>
    </table>

</body>
</html>
