<?php

namespace App\Http\Controllers;

use Cart;
use App\Category;
use App\Author;
use App\News;
use App\Book;
use App\Book_Category;
use App\Contact;
use App\Order;
use App\OrderDetail;
use App\Customer;
use App\Http\Requests\ContactRequest;
use App\Http\Requests\CustomerRequest;
use DB;
use Auth;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Trang chủ";
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();

        // Sách Tiếng Việt
        // $id_categoryBookTV = Category::where('name', 'Sách Tiếng Việt')->first()->id;
        $id_categoryBookTV = Category::find(1)->id;
        $bookTVs = Category::find($id_categoryBookTV)->books()->limit(5)->latest()->get();

        // Sách Tiếng Anh
        // $id_categoryBookTA = Category::where('name', 'Sách Tiếng Anh')->first()->id;
        $id_categoryBookTA = Category::find(2)->id;
        $bookTAs = Category::find($id_categoryBookTA)->books()->limit(5)->latest()->get();

        // Sách đang khuyến mãi
        $bookSales = Book::where('sale', '>', 0)->limit(5)->latest()->get();

        // Sách nổi bật
        $bookHighlights = Book::where('is_highlight', 1)->limit(3)->latest()->get();

        // Sách bán chạy
        $book_sellings = Book::all()->sortByDesc('book_selling')->take(5);

        // Sách văn học nước ta
        // $id_categoryBookLiterature = Category::where('name', 'Sách văn học nước ta')->first()->id;
        $id_categoryBookLiterature = Category::find(3)->id;
        $bookLiteratures = Category::find($id_categoryBookLiterature)->books()->limit(5)->latest()->get();

        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");


        return view('page.index', compact(
            'title', 
            'categories', 
            'category_parents', 
            'bookTVs',
            'bookTAs',
            'bookSales',
            'bookHighlights',
            'bookLiteratures',
            'book_sellings',
            'content',
            'total',
            'id_categoryBookTV',
            'id_categoryBookTA',
            'id_categoryBookLiterature'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    // Chi tiết sách
    public function show($id)
    {
        $book = Book::findOrFail($id);
        $title = "Chi tiết cuốn sách $book->name";
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");
        return view ('page.book-detail', compact('title', 'categories', 'book', 'content', 'total', 'category_parents'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    // Danh mục sách
    public function category(Request $request, $id)
    {
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();
        $category = Category::find($id);
        $books = $category->books()->paginate(8);
        $title = "Danh mục $category->name";
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");

        if($request->key) {
            $books = $category->books()->where('name', 'like', '%'. $request->key .'%')->paginate(8);
        }

        // $books = Book::whereHas('authors', function($query) use ($keyword)
        // {
        //     $query->where('name', 'LIKE', "%$keyword%");
        // })->orWhere('name', 'LIKE', "%$keyword%")
        //   ->with('authors')
        //   ->get();

        $data = [
            'title' => $title,
            'categories' => $categories,
            'category_parents' => $category_parents,
            'category' => $category,
            'books' => $books,
            'content' => $content,
            'total' => $total,
            'request' => $request,

        ];

        return view('page.cate-book', $data);
    }

    // Danh mục sách bán chạy
    public function category_selling(Request $request)
    {
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();

        $books = Book::paginate(8);
        if($request->key) {
            $books = Book::where('name', 'like', '%'. $request->key .'%')->paginate(8);
        }

        $books->setCollection($books->sortByDesc('book_selling'));

        $title = "Danh mục Sách bán chạy";
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");
        return view('page.cate-book-selling', compact('title', 'categories', 'books', 'content', 'total', 'request', 'category_parents'));
    }

    // Danh mục sách đang khuyến mãi
    public function category_sale(Request $request)
    {
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();


        $books = Book::where('sale', '>', 0);
        if($request->key) {
            $books = $books->where('name', 'like', '%'. $request->key .'%');
        }

        $books = $books->latest()->paginate(8);

        $title = "Danh mục Sách đang khuyến mãi";
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");
        return view('page.cate-book-sale', compact('title', 'categories', 'books', 'content', 'total', 'request', 'category_parents'));
    }
    
    // Danh mục sách mới ra mắt
    public function category_new(Request $request)
    {
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();

        $books = Book::orderBy('created_at', 'desc')->paginate(8);
        if($request->key) {
            $books = Book::orderBy('created_at', 'desc')->where('name', 'like', '%'. $request->key .'%')->paginate(8);
        }

        $title = "Danh mục Sách mới ra mắt";
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");
        return view('page.cate-book-new', compact('title', 'categories', 'books', 'content', 'total', 'request', 'category_parents'));
    }

    // Top nổi bật
    public function tophighlight(){
        $title = 'Top nổi bật';
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");

        $books = Book::where('is_highlight', 1)->limit(10)->latest()->get();
        return view('page.book-hl', compact('title', 'categories', 'content', 'total', 'books', 'category_parents'));
    }

    // Diễn đàn
    public function forum(){
        $title = 'Tin tức';
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();
        $news = News::latest()->paginate(5);
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");
        $random = News::inRandomOrder()->limit(5)->get();
        return view('page.forum', compact('title', 'categories', 'news', 'content', 'total', 'random', 'category_parents'));
    }

    // Chi tiết tin tức
    public function forum_detail($id){
        $title = 'Chi tiêt tin tức';
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();
        $news = News::find($id);
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");
        $random = News::inRandomOrder()->limit(5)->get();
        return view('page.forum-detail', compact('title', 'categories', 'news', 'content', 'total', 'random', 'category_parents'));
    }

    // Thêm sản phẩm vào giỏ
    public function add_to_cart($id){
        $book = Book::find($id);

        if ($book->amount == 0) {
            return redirect()->back()->with('alert-error', 'Sản phẩm '.$book->name.' hiện tại đã hết hàng!');
        }

        Cart::add([
            'id' => $id,
            'name' => $book->name,
            'qty' => 1,
            'price' => $book->price-($book->price*$book->sale/100),
            'weight' => 0,
            'options' => [
                'img' => $book->img,
            ]
        ]);

        $content = Cart::content();

        return redirect()->back()->with('alert-success', 'Thêm sản phẩm '.$book->name.' vào giỏ hàng thành công!');
    }



    // Đặt hàng
    public function order(){
        if(Cart::count() < 1)
        {
            return redirect()->back()->with('alert-error', 'Giỏ hàng của bạn không có sản phẩm!');
        }

        $carts = Cart::content();

        DB::beginTransaction();
        try {
            // tạo đơn nhập hàng
            $order = Order::create([
                'code' => 'PD',
                // 'user_id' => null,
                'customer_id' => auth()->guard('customer')->user()->id,
                'status' => 1,
                'payment_method' => 1,
                'total_money' => 0,
                'type' => 'online',
            ]);
            $order->update([
                'code' => 'PD'.str_pad($order->id, 6, '0', STR_PAD_LEFT)
            ]);
            
            $total_money = 0;
            
            // tạo chi tiết đơn nhập hàng
            foreach ($carts as $cart) {
                $book = Book::findOrFail($cart->id);
                if ($cart->qty > $book->amount) {
                    return redirect()->back()->with('alert-error', 'Sản phẩm '.$book->name.' cửa hàng chỉ còn lại '.$book->amount.' sản phẩm!');
                }

                OrderDetail::create([
                    'order_id' => $order->id,
                    'book_id' => $cart->id,
                    'amount' => $cart->qty,
                    'price' => $book->price,
                    'sale' => $book->sale,
                    'total_money' => $cart->qty * $cart->price,
                    'discount' => $cart->qty * $book->price * $book->sale / 100,
                ]);

                $book->update([
                    'amount' => $book->amount - $cart->qty,
                ]);

                // Thành tiền 1 sản phẩm
                $total = ($cart->qty * $book->price) - ($cart->qty * $book->price * $book->sale / 100);
                $total_money += $total;
            }

            $order->update([
                'total_money' => $total_money,
            ]);

            Cart::destroy();

            DB::commit();
            return redirect()->back()->with('alert-success', 'Đặt hàng thành công! Cảm ơn quý khách hàng đã tin tưởng và sử dụng dịch vụ của chúng tôi');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
            return redirect()->back()->with('alert-error', 'Có lỗi xảy ra! Đặt hàng thất bại!');
        }
    }


    // Xóa sản phẩm trỏng giỏ
    public function del_product_cart($id){
        Cart::remove($id);
        return redirect()->back();
    }

    // Xóa tất cả sản phẩm trỏng giỏ
    public function del_cart(){
        Cart::destroy();
        return redirect()->back();
    }
    

    // Sửa sản phẩm trong giỏ
    public function update_product_cart(Request $request, $rowid, $qty){
        $id = $request->id;
        $qty = $request->qty;
        $book = Book::find($id);
        if($qty > $book->amount){
            return redirect()->back()->with('alert-error', 'Cập nhật thất bại! Sản phẩm '.$book->name.' cửa hàng chỉ còn lại '.$book->amount.' sản phẩm!');
        }
        else{
            Cart::update($rowid, $qty);
            return redirect()->back();
        }
    }

    // Liên hệ
    public function contact(){
        $title = 'Chi tiêt tin tức';
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");

        return view('page.contact', compact('title', 'categories', 'content', 'total', 'category_parents'));
    }

    // Phản hồi
    public function send_us(ContactRequest $request){
        Contact::create($request->all());
        return redirect()->route('page.contact')->with('alert-success', 'Phản hồi của bạn đã được tiếp nhận!');
    }

    // Tìm kiếm
    public function search(Request $request){
        $title = 'Tìm kiếm';
        $categories = Category::all();
        $category_parents = Category::where('parent_id', NULL)->get();
        $content = Cart::content();
        $total = Cart::subtotal(0,",",".",".");
        // $books = Book::where('name', 'like', '%'. $request->keyall .'%')->paginate(10);

        $keyword = $request->keyall;

        $books = Book::whereHas('authors', function($query) use ($keyword)
        {
            $query->where('name', 'LIKE', "%$keyword%");
        })->orWhere('name', 'LIKE', "%$keyword%")
          ->with('authors')
          ->get();
        return view('page.search', compact('title', 'categories', 'content', 'total', 'books', 'category_parents'));
    }
}
