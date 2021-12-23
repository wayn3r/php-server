<?php

/*******************************
 * CONFIGURACIÓN DEL PROYECTO  
 *******************************/
setlocale(
    LC_ALL,
    'Spanish_Dominican_Republic',
    'Spanish_Spain',
    'es_ES',
    'Spanish',
    'es_ES@euro',
    'es_ES',
    'esp'
);

// APP VARIABLES
define('root', $_SERVER['DOCUMENT_ROOT']);

// DATABASE VARIABLES
define('DB_ODBC', '',);
define('DB_USER', '',);
define('DB_PASS', '');
define('DB_NAME', '');

// HTTP VARIABLES
define('CONTINUE', 100);
define('SWITCHING_PROTOCOLS', 101);
define('PROCESSING', 102);
define('EARLY_HINTS', 103);
define('OK', 200);
define('CREATED', 201);
define('ACCEPTED', 202);
define('NON_AUTHORITATIVE_INFORMATION', 203);
define('NO_CONTENT', 204);
define('RESET_CONTENT', 205);
define('PARTIAL_CONTENT', 206);
define('MULTI_STATUS', 207);
define('ALREADY_REPORTED', 208);
define('IM_USED', 226);
define('MULTIPLE_CHOICES', 300);
define('MOVED_PERMANENTLY', 301);
define('FOUND', 302);
define('SEE_OTHER', 303);
define('NOT_MODIFIED', 304);
define('USE_PROXY', 305);
define('RESERVED', 306);
define('TEMPORARY_REDIRECT', 307);
define('PERMANENTLY_REDIRECT', 308);
define('BAD_REQUEST', 400);
define('UNAUTHORIZED', 401);
define('PAYMENT_REQUIRED', 402);
define('FORBIDDEN', 403);
define('NOT_FOUND', 404);
define('METHOD_NOT_ALLOWED', 405);
define('NOT_ACCEPTABLE', 406);
define('PROXY_AUTHENTICATION_REQUIRED', 407);
define('REQUEST_TIMEOUT', 408);
define('CONFLICT', 409);
define('GONE', 410);
define('LENGTH_REQUIRED', 411);
define('PRECONDITION_FAILED', 412);
define('REQUEST_ENTITY_TOO_LARGE', 413);
define('REQUEST_URI_TOO_LONG', 414);
define('UNSUPPORTED_MEDIA_TYPE', 415);
define('REQUESTED_RANGE_NOT_SATISFIABLE', 416);
define('EXPECTATION_FAILED', 417);
define('I_AM_A_TEAPOT', 418);
define('MISDIRECTED_REQUEST', 421);
define('UNPROCESSABLE_ENTITY', 422);
define('LOCKED', 423);
define('FAILED_DEPENDENCY', 424);
define('TOO_EARLY', 425);
define('UPGRADE_REQUIRED', 426);
define('PRECONDITION_REQUIRED', 428);
define('TOO_MANY_REQUESTS', 429);
define('REQUEST_HEADER_FIELDS_TOO_LARGE', 431);
define('UNAVAILABLE_FOR_LEGAL_REASONS', 451);
define('INTERNAL_SERVER_ERROR', 500);
define('NOT_IMPLEMENTED', 501);
define('BAD_GATEWAY', 502);
define('SERVICE_UNAVAILABLE', 503);
define('GATEWAY_TIMEOUT', 504);
define('VERSION_NOT_SUPPORTED', 505);
define('VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL', 506);
define('INSUFFICIENT_STORAGE', 507);
define('LOOP_DETECTED', 508);
define('NOT_EXTENDED', 510);
define('NETWORK_AUTHENTICATION_REQUIRED', 511);
