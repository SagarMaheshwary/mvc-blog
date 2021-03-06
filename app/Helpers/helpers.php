<?php

/**
 * Global helpers.
 */
use App\Core\Http\Request;
use App\Core\Security\{Hash,CSRF};
use App\Core\Validation\MessageBag;
use App\Core\Support\{Session,App};

/**
 * dump the data and kill the page.
 * 
 * @param array $data
 * @return void
 */
function dd($data = [])
{
    echo "<pre>",var_dump($data),"</pre>";
    die();
}

/**
 * Create a url from a given uri.
 * 
 * @param string $uri
 * @return string
 */
function url($uri = '')
{
    $uri = sanitizeUri($uri);
    return "http://{$_SERVER['HTTP_HOST']}/{$uri}";
}

/**
 * Get the current url.
 * 
 * @return string
 */
function currentUrl()
{
    return url(Request::uri());
}

/**
 * Sanitize the given uri.
 * 
 * @param string $uri
 * @return string
 */
function sanitizeUri($uri)
{
    if(strpos($uri,'/') == 0) $uri = ltrim($uri,'/');
    
    return filter_var(
        $uri, FILTER_SANITIZE_URL
    );
}

/**
 * get the csrf token.
 * 
 * @return string
 */
function token()
{
    return CSRF::generate();
}

/**
 * get the csrf hidden field
 * 
 * @return string
 */
function csrfField()
{
    return CSRF::csrfField();
}

/**
 * Convert specialchars to html entities.
 * 
 * @param string $str
 * @return string
 */
function e($str)
{
    return htmlentities($str,ENT_QUOTES,'UTF-8');
}

/**
 * Get a session value.
 * 
 * @param string $key
 * @return string|bool
 */
function session($key)
{
    return Session::get($key);
}

/**
 * Set/Get a flash message.
 * 
 * @param string $key
 * @param string|int $value
 * @return string|bool
 */
function flash($key,$value = null)
{
    return Session::flash($key,$value);
}

/**
 * Errors from messagebag (Validation errors).
 * 
 * @return \App\Core\Validation\MessageBag
 */
function errors()
{
    return App::get('errors');
}

/**
 * Get the input value from the previous request.
 * 
 * @return mixed
 */
function old($key)
{
    return Session::getOldInput($key);
}