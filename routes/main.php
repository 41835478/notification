<?php

//首页,自动判断是否登录
Route::get('/', function () {
    return redirect()->route('login');
});

//登录后路由组
Route::group(['middleware' => 'auth', 'namespace' => 'Admin', 'prefix' => 'admin'], function () {

    //后台显示相关
    Route::get('/', 'IndexController@index');
    Route::get('/top', 'IndexController@top');
    Route::get('/left', 'IndexController@left');
    Route::get('/main', 'IndexController@main');
    Route::get('/sponsor', 'IndexController@sponsor');
    Route::post('/sponsor', 'IndexController@sponsor');

    //任务显示
    Route::get('/task/page', function () {
        return redirect()->route('task_page', ['page' => 1]);
    });
    Route::get('/task/page/{page}', 'TaskController@show')->name('task_page');

    //添加任务
    Route::get('/task/add', function () {
        return redirect()->route('task_add', ['category' => app('\App\Repositories\CategoryRepositories')->routeFirst()['category_id']]);
    });
    Route::get('/task/add/{category}', 'TaskController@storeOrUpdateView')->name('task_add');
    Route::post('/task/add/{category}', 'TaskController@storeORupdate')->name('task_add_post');

    //更新任务
    Route::get('/task/update/{category}/{task}', 'TaskController@storeOrUpdateView')->name('task_update');
    Route::post('/task/update/{category}/{task}', 'TaskController@storeORupdate')->name('task_update_post');

    //删除任务
    Route::get('/task/delete/{id}', 'TaskController@destroy');

    //多选删除及选择修改(任务)
    Route::post('/task/select/', 'TaskController@selectEvent');

    //管理分类
    Route::get('/category/page', function () {
        return redirect()->route('category', ['page' => 1]);
    });
    Route::get('/category/page/{page}', 'CategoryController@index')->name('category');

    //添加分类
    Route::get('/category/add', 'CategoryController@storeOrUpdateView');
    Route::post('/category/add', 'CategoryController@storeOrUpdate')->name('category_add');

    //更新分类
    Route::get('/category/update/{category_id}', 'CategoryController@storeOrUpdateView')->name('category_update');
    Route::post('/category/update/{category_id}', 'CategoryController@storeOrUpdate')->name('category_update_post');

    //删除分类
    Route::get('/category/delete/{id}', 'CategoryController@delete');

    //多选删除及选择修改(分类)
    Route::post('/category/select/', 'CategoryController@selectEvent');

    //支付宝
    Route::get('/alipay/order/{order}', 'AlipayController@alipay')->name('alipay');
    Route::get('/alipay/query/{order}', 'AlipayController@query');
    Route::post('/alipay/pay', 'AlipayController@pay');
    Route::get('/alipay/app', 'AlipayController@app');
    Route::get('/alipay/callback', 'AlipayController@callback');

});

// Authentication Routes...
$this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
$this->post('login', 'Auth\LoginController@login');
$this->post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
$this->get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
$this->post('register', 'Auth\RegisterController@register');

// Password Reset Routes...
$this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
$this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
$this->post('password/reset', 'Auth\ResetPasswordController@reset');