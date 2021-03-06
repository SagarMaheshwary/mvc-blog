# MVC With Plain PHP
- An mvc framework built on php without using any packages.

# Getting the app up and running
- Clone or download zip from the above green button.
- We are using psr-4 autoloading standard so you need to have composer installed, after that you can create the autoloader with **dump-autoload**:
```bash
    composer dump-autoload -o
```
- public directory is the root of this app and you need to create a virtual host. It already has a .htaccess file so you can run it with apache.

# Docs
- [Config](#config)
- [Routes, Controllers, and Views](#routes-controllers-and-views)
- [Models and Database](#models-and-database)
- [Request and Response](#request-and-response)
- [Session and Cookies](#session-and-cookies)
- [Helpers](#helpers)
- [Validation](#validation)
- [Security](#security)
- [Authentication](#authentication)

## Config
Config array is registered in the app container inside the app/Core/init.php and the values are loaded from config/app.php file. This config file has all the credentials related to app, database, session, and cookies. To retrieve a certain config value you can use the Config class:

```php
    <?php

    use App\Core\Support\Config;

    Config::get('database.name');

    //you can also use this global helper.
    config('database.name');

```

You can use dots to go deep into the arrays. In above example, **database** is the name of the array and **name** is the key inside that array.

## Routes Controllers and Views
Router for this mvc also support dynamic uris. Routes can be created in the **routes/routes.php** file and you can also specify a different routes file to **Router::load()** method inside **app/Core/init.php** file if you want.

To create a route, you can do:
```php
    $router->method('/uri/with/{dynamicvalue}','Controller@method');
```
Placeholder for the dynamic value should be a combination of letters (both uppercase and lowercase allowed), digits, and underscores.
When you pass dynamic values to the route then you need to inject the $request and $response objects in the controller method first otherwise they are optional. Here's an example:
```php
    $router->get('/users/{id}','UsersController@show');
```

Controller method:
```php
    public function show(Request $request,Response $response,$id)
    {
        echo "The user id is: {$id}";
    }
```
Available router methods:
```php
    <?php

    $router->get('uri','controller@method');
    $router->post('uri','controller@method');
    $router->put('uri','controller@method');
    $router->delete('uri','controller@method');
```

Slash at the start of the route uri will be removed and is not a problem if you add it.

Html forms don't support PUT and DELETE request methods so you need to add a hidden input named **_method**:
```php
    //PUT request
    <form method="post" action="/">
        <input type="hidden" name="_method" value="PUT">
        //other form fields...
    </form>

    //DELETE request
    <form method="post" action="/">
        <input type="hidden" name="_method" value="DELETE">
        //other form fields...
    </form>
```

### Controllers
All controllers will be stored in **app/Controllers** directory. This directory already has a **Controller.php** file which has all the controller methods like view() and include(). This controller needs to be extended by every controller. Syntax for a simple controller is defined below:
```php
    <?php

    namespace App\Controllers;

    use App\Core\Http\{Request,Response};

    class SimpleController extends Controller
    {
        public function index()
        {
            //your code...
        }

        public function store(Request $request,Response $response)
        {
            //with request and response objects...
        }
    }
```

You can use **view()** method which takes in two parameters, first view name/path and second data array that needs to be passed to that view (optional). Just like laravel you can use dot sytax e.g if the **profile** view is in the **users** directory then you will do:
```php
    public function index()
    {
        return $this->view('users.profile');
    }
```
Returning view with data:
```php
    $this->view('users.profile',[
        'username' => $username,
        'email' => $email,
    ]);
```
### Views
**views** directory will be used for storing views. inside a view, you can use include() method for including a view and php syntax for loops and conditionals:

```php
    <?php $this->include('includes.header') ?>
        Username: <?= $username ?>
        Email: <?= $email ?>
    <?php $this->include('includes.footer') ?>
```
We are including header and footer view files from the includes directory. **$username** and **$email** variables are coming from the controller.

## Models and Database
**app/Models** directory will have all the models and all the models extend **Model** class from **app/Core/Database** directory:

```php
    <?php

    namespace App\Models;

    use App\Core\Database\Model;

    class Post extends Model
    {
        protected $table = "posts";

        protected $pk = "id";
    }
```
There are two protected properties **$table** and **$pk**. $table is used for specifying the table that needs to be queried and $pk is the primary key which is optional and will be used by QueryBuilder class that has all the database methods (it's extended by Model class). By default $pk has the "id" value but if you have a different primary key column then you can specify it inside your model.

With QueryBuilder, you can do quite a lot of stuff:
```php
    Post::all(); //return all the rows.

    Post::find(1); //return the row matching the primary key value.
```

To create a row, you can use **create()** method which takes in an array with column names are the keys and values for their values:
```php
    Post::create([
        'column1' => 'this is column value',
        'column2' => 'this is column value',
    ]);
```

**update()** method is used for updating a row and it's second parameter is the primary key value:
```php
    Post::update([
        'column1' => 'this is updated column value',
        'column2' => 'this is updated column value',
    ],$id);
```

You can also use where clause(s) and whenever you are not using **all()** or **find()** method then you need to call **select()** method first which will indicate that we are retrieving rows from the table:
```php
    Post::select()->where('column_name','=','column value')->get();
```

you need to call **get()** or **first()** method after all these methods except **all()** and **find()**. get() method will retreive multiple rows and first() will retrieve only first row from the results.
```php
    Post::select()->where('column_name','=','column value')->first();
```

You can use certain operators in where clause(s):

- = (equals to)
- \> (less than)
- < (less than)
- \>= (less than or equals to)
- <= (greater than or equals to)
- <> (not equals to)
- LIKE (sql like equivalent)

Certain use cases for where:

```php

    // where(column,operator,value)
    Post::select()->where('column1','=','column value one')->whereOr('column2','=','column value two')->get(); //SQL where OR

    Post::select()->where('column1','=','column value one')->whereAnd('column2','=','column value two')->first(); //SQL where AND

    Post::select()->where('column_name','LIKE','%t%')->get(); //SQL where LIKE

    //you can also use
    Post::select()->whereLike('column_name','%t%')->first();

    // whereBetween(column,['value1',value2'])
    Post::select()->whereBetween('date_column',['2019-03-01','2019-04-30'])->get(); //SQL where BETWEEN

```

> Note that you can always chain multiple **whereOr()**, **whereLike()**, and **whereAnd()** methods if you need to.

You can also specify certain columns by passing an array of column names to the **select()** method instead of retrieving all the columns from a table:
```php

    Post::select(['column1'])->get(); //all rows with only title column.

    Post::select(['column1','column2'])->where('column_name','=','column value')->get();
    
```

Sometimes you want to query the table without a model and for that you can use **table()** method:
```php
    use App\Core\Database\QueryBuilder; // import the class.

    QueryBuilder::table('table_name')->all();
```
Just like above, you should specify table() first in every method chain. If you have a different primary key then you use **primaryKey()** method:
```php
    QueryBuilder::table('table_name')->primaryKey('id')->all();
```

## Request and Response
Request class from **app/Core/Http directory** contains all the methods and properties related to the current request. Here are the available methods:
```php

    use App\Code\Http\Request;

    //methods that you can access statically and from the object instance.

    //current request method.
    Request::method();
    
    //check for the request method.
    Request::isMethod('MethodName');

    //current request URI.
    Request::uri();

    // value of the input (both get and post methods).
    Request::input('key');

    //check if the input exists (both get and post methods).
    Request::has('key');
    
    //input value from $_GET global.
    Request::get('key');

    //input value from $_POST global.
    Request::post('key');

    //check if the file was uploaded.
    Request::hasFile('key');
    
    //get the file.
    Request::file('key');

    //check if the request is an ajax request and expecting json.
    Request::isJsonRequest();

    //returns the previous request url.
    Request::previousUrl();

    //Methods that can only be accessed by the instance.

    $request = new Request;

    //validates the current request (more details in validation section).
    $request->validate();

    //get all the cookies.
    $request->cookies();
    
    //get all the uploaded files.
    $request->files();

    //get the query string values as an associative array.
    $request->query();

    //get the $_SERVER global values.
    $request->server();

    //get the request headers.
    $request->headers();

    //you can also access $_GET and $_POST values dynamically.
    $request->name; //returns the value of "name" input. Same as $request->input('name').
```

Reponse Methods:
```php
    use App\Code\Http\Response;

    $response = new Response;

    //set a header
    $response->header($key,$value,$code,$replace);
    
    /**
     * $code has a default value of 200 and $replace
     * has default of true.
     * 
     * Example:
     */
    $response->header('Content Type','application/json',200);

    //just status code.
    $response->statusCode(500);

    //redirect
    $response->redirect(url('/posts'));

    //you can also chain with() method for a flash message.
    $response->redirect(url('/posts'))->with('message',"you've been redirected!");

    /**
     * you can also pass the status code to return a
     * response error code or display the appropriate
     * view. Acceptable codes: 403,404,500,503.
     */
    $response->redirect(500);

    /**
     * you can also return json data from a controller.
     * first argument is an array of data and second is
     * status code with a default value of 200.
     */
    public function controllerMethod(Request $request, Response $response)
    {
        return $response->json([
            'message' => 'hello world!'
        ],200);
    }
```

## Session and Cookies
Session class is stored in the **app/Core/Support** directory. Session methods:
```php

    use App\Code\Support\Session;

    //get a session value.
    Session::get();

    //set a session value.
    Session::set('key');

    //check if a session value exists.
    Session::has('key');

    //Unset/remove a session value.
    Session::unset('key');

    //set a session value the will be available for the next request also
    //known as a flash value/message. Don't specify the second argument if
    //you want to retrieve the flash value.
    Session::flash('key','value');

    //Destroy the session.
    Session::destroy();
    
    //Setting the previous Uri is defined in the Session class and can be access
    //publically. Note that this is just the URI not the complete URL.
    Session::getPreviousUri(); //you should use request class for obtaining the url.
```
Cookie class is also stored in **app/Core/Support** directory. Here are the available methods:
```php

    use App\Code\Support\Cookie;

    //Get a cookie value.
    Cookie::get('cookieName');

    //Check if a cookie exists.
    Cookie::has('cookieName');

    //Unset/remove a cookie.
    Cookie::unset('cookieName');
    
    //Set a cookie value.
    Cookie::set('key','value',expiry);
```

All parameters for the set() method:
1. cookie key
2. cookie value
3. cookie expiry (default 0)
4. httponly (boolean, default false) | if you set it to true then the cookie won't be accessed with javascript.
5. path (default "/")
6. domain (default null)
7. secure (default false)

## Helpers
app/Helpers directory has helpers.php file which is loaded from composer. This file will be used for defining all the helper functions. Here's the list of available helper functions.

- **dd()** helper takes in an array. This helper function will dump the data and kill the page/execution of the script.

- **url()** helper takes in the uri string and will return the complete url. Example:
```php
    
    //Our example url is: dev.mvc.com
    url('contact'); // returns: dev.mvc.com/contact

    //you don't need to include the first slash and even if you include the slash it will be removed.
    url('/'); //returns dev.mvc.com
    url(); //same as above.

```

- **currentUrl()** returns the current url.

- **sanitizeUri('my/uri/string')** will return the sanitized uri. it with also remove the slash at the start.

- **token()** will generate a csrf token.

- **csrfField()** will return a hidden input field with a csrf token.

- **e('\<p>string\</p>')** will convert special characters from the given string to html entities.

- **session('key')** will return the session value.

- **flash('key','optional value')** will create a flash message. To retrieve a flash value you only need to pass the key e.g **flash('key')**.

- **errors()** will return the MessageBag object used for validation messages. (more info in [validation](#validation) section)

- **old('input')** will return an input value from the previous POST request (used for [validation](#validation)). All the previous input values will be stored in the session.

## Validation
Validation class **Validator** is stored in **app\Core\Validation** directory. **validate()** method is used for validation which takes in two arguments, request object and validation rules array. example:
```php
    use App\Core\Validation\Validator;

    $validator = new Validator;
    $validator->validate($request,[
        'title' => 'required|min:2|max:100',
        'body'  => 'required|min:10|max:1000',
    ]);
```

**title** and **body** keys in the above example are the names of the input submitted by a form. If the validation failed you will be redirected back to the previous url. All the validation errors will be stored in session only for the next request. Instead of directly using validator, you can use **$request->validate()** method which only needs an array of rules:
```php
    <?php

    namespace App\Controllers;

    use App\Core\Http\{Request,Response};

    class SampleController extends Controller
    {
        public function store(Request $request,Response $response)
        {
            $request->validate([
                'title' => 'required',
                'body' => 'required',
            ]);

            //code after validation....
        }
    }
```

**errors()** helper is available which will return the MessageBag instance containing all the validation errors. Available methods:
```php
    
    //Get the first error message (string) for an input.
    $firstError = errors()->first('title');

    //Get an array of error messages for an input.
    $messages = errors()->messages('title');

    //Get an array containing all the messages for all inputs.
    $all = errors()->all();

    //Check if an input field has errors.
    $hasFailed = errors()->has('title');
```

**old()** method can be used for retrieving old input values after a failed validation and it will return an empty string if a value doesn't exists.

Example for a view form with materialize-css:
```php
    <form method="post" action="<?= url('/posts/store') ?>">
        <div class="input-field">
            <input type="text" name="title" value="<?= old('title') ?>">
            <?php if(errors()->has('title')): ?>
                <span class="helper-text">
                    <?= errors()->first('title') ?>
                </span>
            <?php endif ?>
        </div>
        <button class="btn">Create</button>
    </form>
```

Available validation rules:
- **required** check if an input field is not empty or present.
- **optional** ignore the rest of the rules if an input is null/empty (this will only ignore rules for that input).
- **string** check if an input value is a string.
- **integer** check if an input value is an integer.
- **numeric** check if an input value is numeric.
- **alpha_numeric** check if an input contains alpha numeric value.
- **email** check if an input value is a valid email.
- **file** check if input is an uploaded file.

Parameter rules:
- **min:length** checks the minimum length of an integer or a string. usage: 'min:10' or 'min:50'.
- **max:length** checks the maximum length of an integer or a string. usage: 'max:1000' or 'max:50000'
- **unique:table,column,id,primarykeycolumn** check if a value is unique to the database. Usage:
```php
    //table name: users.
    //table column to search from: email
    $request->validate([
        'email' => 'required|email|unique:users,email',
    ]);

    //if you are updating the user then you need to specify the
    //row id (primary key value) of that user row.
    $request->validate([
        'email' => 'required|email|unique:users,email,1',
    ]);

    //mostly we name primary key column "id" but if you are using
    //a different column then you can specify that as the fourth
    //parameter
    $request->validate([
        'email' => 'required|email|unique:users,email,1,pk_column',
    ]);
```

**image** should be an uploaded image. Acceptable formats: png, svg, bmp, jpeg, jpg, gif, tif, tiff, ico.

**mime** should be an uploaded file matching the mime type. Available formats: png, svg, bmp, jpeg, jpg, gif, tif, tiff, ico.

Image upload validation example:
```php
    $request->validate([
        'profile_pic' => 'file|image|mime:png,jpeg,bmp',
    ]);
```

## Security
- You can add csrf protection with **csrf()** method that is available in every controller.
```php
    //controller constructor.
    public function __construct()
    {
        $this->csrf();
    }
```
Add the csrf token in your form:
```php
    <form action="<?= url('/action') ?>">
        <?= csrfField() ?>
        //other fields...
    </form>
```
**csrfField()** will generate a hidden input field named. **_token** with csrf token.
Now whenever you submit this form without csrf token you will get an exception. **csrf()** method by default checks for post request made to all the methods in that controller but you can change that by passing an array of methods or just a single method:
```php
    $this->csrf(['PUT']); // for PUT request
    
    $this->csrf(['PUT','DELETE']); // for PUT, and DELETE request
```

- You should use **e()** helper for escaping output whenever needed:
```php
    //inside a view
    <?php echo e($myVariable) ?>
```

**Please note that I wrote this mini-framework just for learning purposes and it's not good enough to be used in a production environment.**