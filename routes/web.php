<?php

Route::get('/', function () {
    return redirect('login');
});

Auth::routes();

Route::group(['prefix' => 'admin', 'middleware' => 'auth'],function(){
	Route::resource('books', 'BookController');
	Route::resource('authors', 'AuthorController');
	Route::resource('types', 'TypeController');
	Route::resource('categorys', 'CategoryController');
	Route::resource('orders', 'OrderController');
	Route::resource('news', 'NewController');
	Route::resource('contacts', 'ContactController');
	Route::resource('warehouses', 'WarehouseController');
	Route::post('cancel-order/{id}', 'OrderController@cancelOrder')->name('orders.cancel-order');
	Route::get('sales-orders', 'OrderController@indexSalesOrders')->name('orders.sales-orders');
	Route::get('sales-orders-detail/{id}', 'OrderController@salesOrdersDetail')->name('orders.sales-orders-detail');
	Route::post('change-status-order/{id}', 'OrderController@changeStatusOrder')->name('orders.change-status-order');
	
	Route::group(['prefix' => 'statistic'], function(){
		Route::get('book', 'StatisticController@bookStatistic')->name('book-statistic');
		Route::get('staff-revenue', 'StatisticController@staffRevenue')->name('staff-revenue');
		Route::get('book-sold', 'StatisticController@bookSoldStatistic')->name('book-sold');
	});
	// Thành viên
	Route::group(['prefix' => 'member'], function(){
		Route::get('/', 'MemberController@index')->name('admin.member.index');
		Route::get('/add', 'MemberController@create')->name('admin.member.create');
		Route::post('/add', 'MemberController@store')->name('admin.member.store');
		Route::get('/edit/{id}', 'MemberController@edit')->name('admin.member.edit');
		Route::post('/edit/{id}', 'MemberController@update')->name('admin.member.update');
		Route::get('/destroy/{id}', 'MemberController@destroy')->name('admin.member.destroy');
	});
	Route::get('/profile', 'MemberController@profile')->name('admin.profile');
	Route::post('/profile', 'MemberController@updateProfile')->name('admin.update-profile');

	 // Khách hàng
	 Route::group(['prefix' => 'customer'], function(){
		Route::get('/', 'CustomerController@index')->name('admin.customer.index');
		Route::get('/edit/{id}', 'CustomerController@edit')->name('admin.customer.edit');
		Route::post('/edit/{id}', 'CustomerController@update')->name('admin.customer.update');
		Route::get('/destroy/{id}', 'CustomerController@destroy')->name('admin.customer.destroy');
	});

	// Nhà cung cấp
	Route::resource('suppliers', 'SupplierController');
});


Route::resource('pages', 'PageController');
Route::prefix('/')->name('page.')->group(function(){
	Route::get('category/{id}', 'PageController@category')->name('category');
	
	Route::get('category_selling', 'PageController@category_selling')->name('category_selling');
	Route::get('category_sale', 'PageController@category_sale')->name('category_sale');
	Route::get('category_new', 'PageController@category_new')->name('category_new');
	Route::get('tophighlight', 'PageController@tophighlight')->name('tophighlight');
	Route::get('forum', 'PageController@forum')->name('forum');
	Route::get('forum-detail/{id}', 'PageController@forum_detail')->name('forum-detail');


	Route::get('add-to-cart/{id}/{name}', 'PageController@add_to_cart')->name('add-to-cart');
	Route::get('del-product-cart/{id}', 'PageController@del_product_cart')->name('del-product-cart');

	Route::get('del-cart', 'PageController@del_cart')->name('del-cart');

	Route::get('update-product-cart/{rowid}/{qty}', 'PageController@update_product_cart')->name('update-product-cart');
	Route::post('order', 'PageController@order')->name('order');


	Route::get('contact', 'PageController@contact')->name('contact');
	Route::post('send_us', 'PageController@send_us')->name('send_us');

	Route::get('search', 'PageController@search')->name('search');
});

Route::group(['middleware' => 'auth'], function () {
	// Khách hàng
	Route::group(['prefix' => 'customer'], function(){
		Route::get('/', 'CustomerController@index')->name('pages.customer.index');
		Route::get('/add', 'CustomerController@create')->name('pages.customer.create');
		Route::post('/add', 'CustomerController@store')->name('pages.customer.store');
		Route::get('/edit/{id}', 'CustomerController@edit')->name('pages.customer.edit');
		Route::post('/edit/{id}', 'CustomerController@update')->name('pages.customer.update');
		Route::get('/destroy/{id}', 'CustomerController@destroy')->name('pages.customer.destroy');
	});
});


Route::get('/customer/register', 'CustomerController@register')->name('user.register');
Route::post('/customer/register', 'CustomerController@postRegister')->name('user.postRegister');
Route::get('/customer/login', 'CustomerController@login')->name('user.login');
Route::post('/customer/login', 'CustomerController@postLogin')->name('user.postLogin');

Route::group(['middleware' => 'customer'], function () {
	Route::get('/customer/profile', 'CustomerController@profile')->name('user.profile');
	Route::post('/customer/profile', 'CustomerController@updateProfile')->name('user.updateProfile');
	Route::get('/customer/logout', 'CustomerController@logout')->name('user.logout');
});
