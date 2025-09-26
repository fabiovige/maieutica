<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Additional security configurations for the application.
    |
    */

    'security' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 15, // minutes
        'password_history' => 3, // number of previous passwords to remember
        'session_timeout' => 120, // minutes
        'password_min_length' => 8,
        'password_require_mixed_case' => true,
        'password_require_numbers' => true,
        'password_require_symbols' => false,
        'session_regenerate' => true,
        'prevent_concurrent_logins' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Settings
    |--------------------------------------------------------------------------
    |
    | Configure advanced rate limiting for login attempts to protect against
    | brute force attacks.
    |
    */

    'rate_limiting' => [
        'ip_limit_per_minute' => env('LOGIN_RATE_LIMIT_IP_PER_MINUTE', 5),
        'email_limit_per_5_minutes' => env('LOGIN_RATE_LIMIT_EMAIL_PER_5MIN', 3),
        'escalation_levels' => [1, 5, 15, 60], // minutes for escalated lockouts
        'brute_force_threshold' => env('LOGIN_BRUTE_FORCE_THRESHOLD', 10),
        'enable_user_enumeration_protection' => env('LOGIN_PREVENT_USER_ENUMERATION', true),
        'suggest_password_reset_after_attempts' => env('LOGIN_SUGGEST_PASSWORD_RESET_AFTER', 2),
    ],

];
