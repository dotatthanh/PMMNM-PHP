<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfieRequest;
use App\Category;
use App\Customer;
use DB;
use Str;
use Hash;
use Auth;
use Cart;

class CustomerController extends Controller
{
    public function register(Request $request) {
        $content = Cart::content();
        $category_parents = Category::where('parent_id', NULL)->get();
        $categories = Category::paginate(10);
        if($request->key){
            $key = $request->key;
            $categories = Category::where('name', 'like', '%'. $request->key .'%')->paginate(10);
        }

        $data = [
            'title' => "Đăng ký tài khoản",
            'categories' => $categories,
            'content' => $content,
            'category_parents' => $category_parents,
            'total' => '',
        ];
        return view('page.user.register', $data);
    }

    public function postRegister(RegisterRequest $request) {
        $params = $request->all();
        DB::beginTransaction();

        $created = Customer::create([
            'name' => $params['name'],
            'sex' => $params['sex'],
            'birthday' => $params['birthday'],
            'phone' => $params['phone'],
            'address' => $params['address'],
            'email' => $params['email'],
            'remember_token' => Str::random(60),
            'password' => Hash::make($params['password']),
            'code' => 0
        ]);

        $created->update([
            'code' => 'KH'.$created->id
        ]);

        if ($created) {
            DB::commit();
            return redirect()->route('user.login')->with('alert-success', 'Đăng ký tài khoản thành công');
        } else {
            DB::rollback();
            return redirect()->back()->with('alert-error', 'Đăng ký tài khoản thất bại');
        }
    }

    public function profile(Request $request) {
        $category_parents = Category::where('parent_id', NULL)->get();
        $content = Cart::content();

        if (!Auth()->guard('customer')->user()) {
            return redirect(route('user.login'));
        }
        $categories = Category::paginate(10);
        if($request->key){
            $key = $request->key;
            $categories = Category::where('name', 'like', '%'. $request->key .'%')->paginate(10);
        }

        $data = [
            'title' => "Đăng nhập tài khoản",
            'categories' => $categories,
            'category_parents' => $category_parents,
            'content' => $content,
            'total' => '',
        ];
        return view('page.user.profile', $data);
    }

    public function updateProfile(UpdateProfieRequest $request) {
        $customer = Customer::findOrFail(['id' => auth()->guard('customer')->user()->id]);
        $params = $request->all();
        unset($params['_token']);
        if (isset($params['password']) && $params['password']) {
            $params = array_merge($params, [
                'password' => Hash::make($params['password'])
            ]);
        } else {
            unset($params['password']);
        }
        $updated = Customer::where(['id' => auth()->guard('customer')->user()->id])->update($params);
        if($updated){
            return redirect()->back()->with('alert-success', 'Cập nhật thông tin cá nhân thành công');
        } else{
            return redirect()->back()->with('alert-error', 'Cập nhật thông tin cá nhân thất bại');
        }
    }

    public function login(Request $request) {
        if (Auth()->guard('customer')->user()) {
            return redirect(route('pages.index'));
        }
        $categories = Category::paginate(10);
        $category_parents = Category::where('parent_id', NULL)->get();
        if($request->key){
            $key = $request->key;
            $categories = Category::where('name', 'like', '%'. $request->key .'%')->paginate(10);
        }

        $content = Cart::content();

        $data = [
            'title' => "Đăng nhập tài khoản",
            'categories' => $categories,
            'content' => $content,
            'total' => '',
            'category_parents' => $category_parents,
        ];
        return view('page.user.login', $data);
    }

    public function postLogin(LoginRequest $request) {
        $params = $request->all();

        $remember = isset($params['remember']) ? true : false;
        if(Auth::guard('customer')->attempt([
            'email'=>$params['email'], 
            'password'=>$params['password']
        ], $remember)){
            return redirect()->route('pages.index')->with('alert-success', 'Đăng nhập thành công');
        } else{
            return redirect()->back()->with('alert-error', 'Sai tài khoản hoặc mật khẩu');
        }
    }

    public function index(Request $request) {
        $query = Customer::query();

        if ($request->has('key')) {
            $query = $query->where('name', 'like', '%'.$request->key.'%');
        }

        $customers = $query->paginate(10);

        $viewData = [
            'customers' => $customers,
            'key' => $request->key,
        ];

        return view("admin.customer.index", $viewData);
    }

    public function edit($id) {
        $customer = Customer::findOrFail($id);
        return view('admin.customer.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, $id) {
        try {
            $customer = Customer::findOrFail($id);
            $data = $request->all();
            $customer->update($data);
            
            return redirect()->route('admin.customer.index')->with('alert-success','Cập nhật thông tin khách hàng thành công!');
        } catch (Exception $e) {
            return redirect()->back()->with('alert-danger', 'Cập nhật thông tin khách hàng thất bại!');
        }
    }

    public function destroy($id) {
        $customer = Customer::findOrFail($id);
        if ($customer->orders->count() == 0) {
            return redirect()->route('admin.customer.index')->with('alert-error', 'Xóa khách hàng thất bại! Khách hàng này đang có đơn hàng!');
        }
        
        $customer->delete();
        return redirect()->route('admin.customer.index')->with('alert-success', 'Xóa khách hàng thành công!');
    }

    public function logout()
    {
        auth()->guard('customer')->logout();

        return redirect()->route('pages.index');
    }
}
