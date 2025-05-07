<?php
// app/Controllers/EmailDebugController.php

namespace App\Controllers;

class EmailDebugController extends BaseController
{
    public function test()
    {
        // OPSI 1: PHP mail() function
        $to = 'nhexania@gmail.com';
        $subject = 'Test Mail dari SWAT Monitoring';
        $message = 'Ini adalah test email dari sistem SWAT Monitoring pada ' . date('Y-m-d H:i:s');
        $headers = 'From: atthaadvisa@apps.ipb.ac.id' . "\r\n" .
                   'Reply-To: atthaadvisa@apps.ipb.ac.id' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        $mailResult = mail($to, $subject, $message, $headers);
        
        // OPSI 2: curl untuk SendGrid API langsung
        $curl = \curl_init();
        \curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.sendgrid.com/v3/mail/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'personalizations' => [
                    [
                        'to' => [['email' => 'nhexania@gmail.com']]
                    ]
                ],
                'from' => ['email' => 'atthaadvisa@apps.ipb.ac.id'],
                'subject' => 'Test Email dari API SendGrid',
                'content' => [
                    [
                        'type' => 'text/plain',
                        'value' => 'Ini test email via SendGrid API pada ' . date('Y-m-d H:i:s')
                    ]
                ]
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer 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',
                'Content-Type: application/json'
            ],
        ]);
        
        $curlResponse = \curl_exec($curl);
        $curlErr = \curl_error($curl);
        $curlInfo = \curl_getinfo($curl);
        
        \curl_close($curl);
        
        return $this->respond([
            'mail_function' => [
                'success' => $mailResult,
                'message' => $mailResult ? 'Email mungkin terkirim dengan mail()' : 'Gagal mengirim dengan mail()'
            ],
            'sendgrid_api' => [
                'success' => ($curlInfo['http_code'] == 202),
                'response' => $curlResponse,
                'error' => $curlErr,
                'info' => $curlInfo
            ]
        ]);
    }
}