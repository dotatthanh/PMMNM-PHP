<?php

namespace App\Http\Controllers;

use App\Book;
use App\Order;
use App\OrderDetail;
use App\Customer;
use Illuminate\Http\Request;
use DB;
use Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders = Order::paginate(10);

        if($request->code){
            $orders = Order::where('code', 'like', '%'.$request->code.'%')->paginate(10);
        }

        $data = [
            'title' => "Quản lý đặt hàng",
            'orders' => $orders,
            'request' => $request
        ];

        return view('admin.order.index', $data);
    }

    public function indexSalesOrders(Request $request)
    {
        $orders = Order::paginate(10);
        
        if($request->code){
            $orders = Order::where('code', 'like', '%'.$request->code.'%')->paginate(10);
        }

        $data = [
            'title' => "Quản lý bán hàng",
            'orders' => $orders,
            'request' => $request
        ];

        return view('admin.sales-orders.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $books = Book::all();
        $customers = Customer::all();

        $data = [
            'customers' => $customers,
            'books' => $books,
        ]; 

        return view('admin.sales-orders.order', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Kiểm tra tồn tại KH
            $customer = Customer::where('phone', $request->phone)->first();
            if ($customer) {
                $customer_id = $customer->id;
            }
            else {
                $customer = Customer::create([
                    'password' => bcrypt(12345678),
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'code' => 'KH',
                ]);
                $customer_id = $customer->id;
                $customer->update([
                    'code' => 'KH'.str_pad($customer->id, 6, '0', STR_PAD_LEFT)
                ]);
            }

            // tạo đơn nhập hàng
            $order = Order::create([
                'code' => 'PB',
                'user_id' => Auth::id(),
                'customer_id' => $customer_id,
                'status' => 4,
                'payment_method' => 2,
                'total_money' => 0,
            ]);
            $order->update([
                'code' => 'PB'.str_pad($order->id, 6, '0', STR_PAD_LEFT)
            ]);
            
            $total_money = 0;
            
            // tạo chi tiết đơn nhập hàng
            foreach ($request->book_id as $key => $book_id) {
                $book = Book::findOrFail($book_id);
                if ($request->amount[$key] > $book->amount) {
                    return redirect()->back()->with('alert-error', 'Sản phẩm '.$book->name.' cửa hàng chỉ còn lại '.$book->amount.' sản phẩm!');
                }
                OrderDetail::create([
                    'order_id' => $order->id,
                    'book_id' => $book_id,
                    'amount' => $request->amount[$key],
                    'price' => $book->price,
                    'sale' => $book->sale,
                    'total_money' => $request->amount[$key] * $book->price,
                    'discount' => $request->amount[$key] * $book->price * $book->sale / 100,
                ]);

                $book->update([
                    'amount' => $book->amount - $request->amount[$key],
                ]);

                // Thành tiền 1 sản phẩm
                $total = ($request->amount[$key] * $book->price) - ($request->amount[$key] * $book->price * $book->sale / 100);
                $total_money += $total;
            }

            $order->update([
                'total_money' => $total_money,
            ]);

            DB::commit();
            return redirect()->route('orders.sales-orders')->with('alert-success', 'Tạo đơn bán hàng thành công!');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
            return redirect()->back()->with('alert-error', 'Tạo đơn bán hàng thất bại!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer = Order::findOrFail($id)->customer;
        $order_details = OrderDetail::where('order_id', $id)->paginate(10);

        $data = [
            'order_details' => $order_details,
            'customer' => $customer,
        ];

        return view('admin.order.order_detail', $data);
    }

    public function salesOrdersDetail($id)
    {
        $customer = Order::findOrFail($id)->customer;
        $order_details = OrderDetail::where('order_id', $id)->paginate(10);

        $data = [
            'order_details' => $order_details,
            'customer' => $customer,
        ];

        return view('admin.sales-orders.order_detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $books = Book::all();
        $order = Order::findOrFail($id);
        $address = $order->customer->address;
        $order_details = OrderDetail::where('order_id', $id)->get();

        $data = [
            'order_details' => $order_details,
            'address' => $address,
            'books' => $books,
            'order' => $order,
        ];

        return view('admin.order.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Đơn đặt hàng
            $order = Order::findOrFail($id);

            // Hoàn trả sách
            foreach ($order->orderDetails as $order_detail) {
                $book = Book::findOrFail($order_detail->book_id);
                $book->update([
                    'amount' => $book->amount + $order_detail->amount,
                ]);
            }

            $order->orderDetails()->delete();

            $total_money = 0;
            // tạo chi tiết đơn nhập hàng
            foreach ($request->book_id as $key => $book_id) {
                $book = Book::findOrFail($book_id);

                if ($request->amount[$key] > $book->amount) {
                    return redirect()->back()->with('alert-error', 'Sản phẩm '.$book->name.' cửa hàng chỉ còn lại '.$book->amount.' sản phẩm!');
                }
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'book_id' => $book_id,
                    'amount' => $request->amount[$key],
                    'price' => $book->price,
                    'sale' => $book->sale,
                    'total_money' => $request->amount[$key] * $book->price,
                    'discount' => $request->amount[$key] * $book->price * $book->sale / 100,
                ]);

                $book->update([
                    'amount' => $book->amount - $request->amount[$key],
                ]);

                // Thành tiền 1 sản phẩm
                $total = ($request->amount[$key] * $book->price) - ($request->amount[$key] * $book->price * $book->sale / 100);
                $total_money += $total;
            }

            $order->update([
                'total_money' => $total_money
            ]);

            DB::commit();
            return redirect()->route('orders.index')->with('alert-success', 'Sửa đơn bán hàng thành công!');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
            return redirect()->back()->with('alert-error', 'Sửa đơn bán hàng thất bại!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function changeStatusOrder($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status == 1){
            $order->update([
                'status' => 2
            ]);
            
            return redirect()->back()->with('alert-success', 'Duyệt đơn đặt hàng thành công!');
        }
        if ($order->status == 2){
            $order->update([
                'status' => 3
            ]);
            
            return redirect()->back()->with('alert-success', 'Đơn hàng đang vận chuyển!');
        }
        if ($order->status == 3){
            $order->update([
                'status' => 4
            ]);
            
            return redirect()->back()->with('alert-success', ' Đơn hàng đã hoàn thành!');
        }
        
    }

    public function cancelOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            $order->update([
                'status' => 0
            ]);

            foreach ($order->orderDetails as $order_detail) {
                $book = Book::findOrFail($order_detail->book_id);
                $book->update([
                    'amount' => $book->amount + $order_detail->amount
                ]);

            }

            DB::commit();
            return redirect()->back()->with('alert-success', 'Hùy đơn thành công!');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
            return redirect()->back()->with('alert-error', 'Hủy đơn thất bại!');
        }
    }
}
