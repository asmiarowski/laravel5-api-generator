# laravel5-api-generator
Generates boilerplate for laravel REST API: migration, controller, model, request and route.

In app\Providers\AppServiceProvider@boot
```
if ($this->app->environment() == 'local') {
  $this->app->register('Smiarowski\Generators\GeneratorsServiceProvider');
}
```
Example command
```
php artisan make:api-resource emails --schema="email:email:unique; title:string; body:text; status:integer:default(1)" --softdeletes
```

Generates Controller:
```
<?php
namespace App\Http\Controllers;

use App\Http\Requests\EmailRequest;
use App\Http\Controllers\Controller;
use App\Email;

class EmailController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function index()
    {
        $emails = Email::all();

        return response()->json(compact('emails'));
    }

    /**
    * Store or update a newly created resource in storage.
    *
    * @param  \Discount\Http\Requests\EmailRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function store(EmailRequest $request, $id = 0)
    {
        $email = Email::updateOrCreate(['id' => $id], $request->input());

        return response()->json(compact('email'));
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\JsonResponse
    */
    public function show($id)
    {
        $email = Email::find($id);

        return response()->json(compact('email'));
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\JsonResponse
    */
    public function destroy($id)
    {
        $success = Email::destroy($id);

        return response()->json(compact('success'));
    }
}
```

Generates Request:
```
<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;

class EmailRequest extends Request
{
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'email' => 'required|email|unique:emails',
            'title' => 'required|string',
            'body' => 'required|string',
            'status' => 'required|integer',

        ];
    }
}
```
Generates Model:
```
<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model {

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

}
```
Generates Migration:
```
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('title');
            $table->text('body');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emails');
    }
}
```

Appends routes.php
```
Route::resource('email', 'EmailController');
Route::put('email/{email}', 'EmailController@store');
```
