<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    /*
    |-------------------------------------------------------------------------
    | Mercado Pago (Checkout Pro)
    |-------------------------------------------------------------------------
    |
    | Las credenciales de PRUEBA arrancan con TEST-. Con esas, ningun pago es
    | real: se cobra con las tarjetas de prueba de MP y no se mueve plata.
    |
    | url_retorno  adonde vuelve el cliente despues de pagar (el frontend).
    | url_webhook  donde MP avisa el resultado. En local no se usa porque MP
    |              no puede alcanzar localhost; ver README-mercadopago.md.
    |
    */
    'mercadopago' => [
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'moneda' => env('MERCADOPAGO_MONEDA', 'ARS'),
        'url_retorno' => rtrim(env('MERCADOPAGO_URL_RETORNO', env('FRONTEND_URL', 'http://localhost:3000')), '/'),
        'url_webhook' => env('MERCADOPAGO_URL_WEBHOOK'),
        'auto_return' => env('MERCADOPAGO_AUTO_RETURN', true),

        // 'real' habla con la API de Mercado Pago. 'simulado' no llama a nadie:
        // sirve para desarrollar y demostrar el flujo entero sin credenciales,
        // sin internet y sin depender de que el panel de MP esté en pie.
        'modo' => env('MERCADOPAGO_MODO', 'real'),

        // false: se paga como invitado con tarjeta de prueba (no hay que crear
        // cuentas). true: se paga con un usuario de prueba, que vive solo en el
        // sandbox y exige que el VENDEDOR tambien sea usuario de prueba.
        'usar_sandbox' => env('MERCADOPAGO_USAR_SANDBOX', false),
    ],

];
