<?php

namespace App\Http\Controllers;

use App\News;
use Illuminate\Http\Request;
use App\Http\Requests\NewsRequest;
use App\Http\Requests\NewsUpdateRequest;

class NewController extends Controller
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
        $news = News::paginate(10);

        if($request->key) {
            $key = $request->key;
            $news = News::where('title', 'like', '%'. $request->key .'%')->paginate(10);
        }

        $data = [
            'title' => "Quản lý tin tức",
            'news' => $news,
            'request' => $request
        ];
        return view('admin.news', $data);
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
    public function store(NewsRequest $request)
    {
        $data = $request->all();
        // xử lý img
        if ($request->hasFile('img')) {
            $file1Extension = $request->file('img')
                ->getClientOriginalExtension();
            $fileName1 = uniqid() . '.' . $file1Extension;
            $request->file('img')
                ->storeAs('public', $fileName1);
            $data['img'] = $fileName1;
        }

        $news = News::create($data);

        return redirect()->route('news.index')->with('alert-success', 'Thêm thành công!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\New  $new
     * @return \Illuminate\Http\Response
     */
    public function show(News $new)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\New  $new
     * @return \Illuminate\Http\Response
     */
    public function edit(News $new)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\New  $new
     * @return \Illuminate\Http\Response
     */
    public function update(NewsUpdateRequest $request, $id)
    {
        $data = [
            'title' => $request->titleupdate,
            'summary' => $request->summaryupdate,
            'img' => $request->imgupdate,
            'content' => $request->contentupdate,
        ];

        // xử lý img
        if ($request->hasFile('imgupdate')) {
            $file1Extension = $request->file('imgupdate')
                ->getClientOriginalExtension();
            $fileName1 = uniqid() . '.' . $file1Extension;
            $request->file('imgupdate')
                ->storeAs('public', $fileName1);
            $data['img'] = $fileName1;
        }

        News::findOrFail($id)->update($data);

        return redirect()->route('news.index')->with('alert-success', 'Sửa thành công!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\New  $new
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        News::findOrFail($id)->delete();
        return redirect()->route('news.index')->with('alert-success', 'Xóa thành công!');;
    }
}
