<!DOCTYPE html>
<html>
<head></head>
<body>
    <table cellspacing="0" cellpadding="0" style="background: #f9f9f9; width: 100%; border-top: 10px solid #f9f9f9; border-bottom: 10px solid #f9f9f9;">
        <tbody>
            <tr>
                <td valign="top" align="center">
                    <u></u>
                    <table width="100%" cellspacing="0" cellpadding="0" style="background: #ffffff; max-width: 600px; border-radius: 20px; border: 1px solid #dfe0e3;">
                        <tbody>
                            <tr>
                                <td><u></u></td>
                            </tr>
                            <tr>
                                <td valign="top" align="center" style="padding: 20px 0 17px;">
                                    <img src="https://i.ibb.co/com/qMndRP6/ebibah.png" width="150" alt="ebibah.com" title="ebibah.com" />
                                </td>
                            </tr>
                            <tr>
                                <td valign="top" align="left" style="padding: 0 10px;">
                                    <u></u>
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                        <tbody>
                                            <tr style="color: #51505d; margin-left:20px; font-family: Arial, sans-serif; padding: 20px;">
                                                <td class="content" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
                                                    <h1 style="color: #4a4a4a; text-align: center;">Card Payment Successful!</h1>
                                                    <p style="border-top: 1px solid #e0e0e0; margin: 10px 0;"></p>
                                                    <p style="font-size: 16px; line-height: 1.5;">Thank you for your payment, <strong>{{ $user->name }}</strong>. Your card transaction has been completed successfully.</p>
                                                    <p style="font-size: 16px; line-height: 1.5;"><strong>Transaction ID:</strong> <strong>{{ $payment->trxId }}</strong></p>
                                                    <p style="font-size: 16px; line-height: 1.5;"><strong>Date:</strong> <strong>{{ $payment->date }}</strong></p>
                                                    <div style="border-top: 1px solid #e0e0e0; margin: 20px 0;"></div>
                                                    <div class="features" style="margin: 20px 0;">
                                                        <h2 style="color: #4a4a4a; text-align: left;">Your Payment Details:</h2>
                                                        <ul style="list-style-type: none; padding: 0;">
                                                            <li style="margin: 5px 0;"><strong>Payment Method:</strong> {{ $payment->method }}</li>
                                                            <li style="margin: 5px 0;"><strong>Amount:</strong> ${{ $payment->amount }}</li>
                                                            <li style="margin: 5px 0;"><strong>Card Type:</strong> {{ $payment->payment_type }}</li>
                                                            <li style="margin: 5px 0;"><strong>Status:</strong> {{ $payment->status }}</li>
                                                            <li style="margin: 5px 0;"><strong>Currency:</strong> {{ $payment->currency }}</li>
                                                        </ul>
                                                    </div>
                                                    <div style="border-top: 1px solid #e0e0e0; margin: 20px 0;"></div>
                                                    <div class="package-details" style="margin: 20px 0;">


                                                        <h2 style="color: #4a4a4a; text-align: left;">Your Subscription Details:</h2>
                                                        {{-- {{ json_encode($active_services) }} --}}
                                                        <ul style="list-style-type: none; padding: 0;">
                                                            <li style="margin: 5px 0;"><strong>Plan:</strong> {{ $package->package_name }} ({{ $package->duration }} months)</li>
                                                        </ul>

                                                        <h3 style="color: #4a4a4a; text-align: left;">Features:</h3>
                                                        {{-- {{ json_encode($active_services) }} --}}
                                                        <ol style="padding: 10px;">
                                                            @foreach($active_services as $service)
                                                                <li style="margin: 5px 0;">{{ $service['name'] }}</li>
                                                            @endforeach
                                                        </ol>


                                                    </div>
                                                    <div style="border-top: 1px solid #e0e0e0; margin: 20px 0;"></div>
                                                    <p style="font-size: 16px; line-height: 1.5;">For any queries or assistance, feel free to reach out to our Customer Service team.</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" align="center" style="padding-top: 40px;">
                                                    <img src="https://ci3.googleusercontent.com/meips/ADKq_NZBsMmceGxqkZW0PCAvh6iAghn4fXK3HGBEgKtJJk7jfH7pA3wk66czQ-nvOXiSQyQP7u4M0CGGvT5r-RLt9zw3jBNzbk6re62yHQnHiDrQ-aBBPxMpA_uRdp8OrBE=s0-d-e1-ft#https://img2.shaadi.com/assests/2022/nl/offer/20220927/dashed-border.png" width="304" height="5" alt="" title="" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" align="left" style="padding: 40px 35px; font-family: 'Roboto', sans-serif; font-weight: 400; font-size: 18px; line-height: 24px; color: #51505d; text-align: center;">
                                                    At <a href="https://ebibah.com/" target="_blank">Ebibah.com</a>, we are fully dedicated to ensuring that you have a safe and secure experience on our platform. If you have any questions, suggestions, or need assistance, please feel free to reach out to our Customer Service team or <a href="https://ebibah.com/help" target="_blank">contact us</a> directly.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" align="left" style="padding: 32px 35px; font-family: 'Roboto', sans-serif; font-weight: 400; font-size: 18px; line-height: 24px; color: #51505d; text-align: center;">
                                                    All the best for your Partner Search!
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" align="center" style="padding: 20px 0; text-align: center;"></td>
                                            </tr>
                                            <tr>
                                                <td valign="top" align="center" style="padding: 8px 0 20px;">
                                                    <img src="https://i.ibb.co/com/qMndRP6/ebibah.png" width="100" alt="ebibah.com" title="ebibah.com" />
                                                    <p style="font-weight: 400; font-size: 16px; line-height: 22px; color: #51505d; margin: 5px 0;">
                                                        Phone: <strong>+1 (888) 887 5027</strong><br />
                                                        Email: <strong><a href="mailto:support@ebibah.com" style="color: #51505d; text-decoration: none;">support@ebibah.com</a></strong><br />
                                                        Office Address: <strong>74-09 37TH Avenue, Suite 203B,<br /> Jackson Heights, NY 11372</strong>
                                                    </p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td valign="top" align="left" style="text-align: left">
                                    <img src="https://ci3.googleusercontent.com/meips/ADKq_NY0YsIUTEsJM301AxtwN2OKS0LtiiVViKU2N0pIKVvUOYNoyO3lYQmTSUxr5b9hDuwvcv3KP8XN8kwdeMHeSzKviEo0rpwOShv_m0KPOjkroX5_YwY1I8iKha0dh-vMPnA=s0-d-e1-ft#https://img2.shaadi.com/assests/2020/nl/offer/20201110/bottom-design-v2.png" width="100%" alt="" title="" style="display: block; border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
