<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cookie Domain
    |--------------------------------------------------------------------------
    |
    | Here you may configure your cookie domain. The domain determines where
    | the cookie will be available. Use null to make it available to the
    | entire domain.
    |
    */

    'domain' => env('COOKIE_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Cookie Path
    |--------------------------------------------------------------------------
    |
    | The cookie path determines where the cookie will be available within
    | your application. Typically this will be the root path of your
    | application, but you are free to change this when necessary.
    |
    */

    'path' => env('COOKIE_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Cookie SameSite
    |--------------------------------------------------------------------------
    |
    | This option determines how your cookies behave when cross-site requests
    | take place, and can be used to mitigate CSRF attacks. By default, we
    | will set this to "lax" to permit secure cross-site requests.
    |
    | Supported: "lax", "strict", "none", null
    |
    */

    'same_site' => env('COOKIE_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Cookie Secure
    |--------------------------------------------------------------------------
    |
    | This option determines whether the cookie should only be transmitted over
    | secure HTTPS connections. When set to true, the cookie will only be sent
    | if the application is using a secure HTTPS connection.
    |
    */

    'secure' => env('COOKIE_SECURE', null),

    /*
    |--------------------------------------------------------------------------
    | Cookie HTTP Only
    |--------------------------------------------------------------------------
    |
    | Setting this to true will prevent JavaScript from accessing the value
    | of the cookie and the cookie will only be accessible through the HTTP
    | protocol. It's unlikely you should disable this option.
    |
    */

    'http_only' => env('COOKIE_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Cookie Partitioned
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will tie the cookie to the top-level site for
    | a cross-site context. Partitioned cookies are accepted by the browser
    | when flagged "secure" and the Same-Site attribute is set to "none".
    |
    */

    'partitioned' => env('COOKIE_PARTITIONED', false),
];
