<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\MemberCreateRequest;
use App\Http\Requests\MemberUpdateRequest;
use Auth;
use Session;
use DB;
use App\User;
use App\Http\Controllers\Controller;

class MemberController extends Controller
{
    public function index(Request $request) {
        $query = User::query();

        if ($request->has('key')) {
            $query = $query->where('name', 'like', '%'.$request->key.'%');
        }

        $query->where('id', '<>', 1);

        $members = $query->paginate(10);

        $viewData = [
            'members' => $members,
            'key' => $request->key,
        ];

        return view("admin.member.index", $viewData);
    }

    public function create() {
        return view('admin.member.create');
    }

    public function store(MemberCreateRequest $request) {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $data['code'] = 'NV';
            $user = User::create(array_merge($data, [
                'password' => bcrypt($data['password'])
            ]));

            $user->update([
                'code' => 'NV'.str_pad($user->id, 4, '0', STR_PAD_LEFT)
            ]);
            DB::commit();
            return redirect()->route('admin.member.index')->with('alert-success','Tạo thành viên thành công!');
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with('alert-danger','Tạo thành viên thất bại!');
        }
    }

    public function edit($id) {
        $user = User::findOrFail($id);
        return view('admin.member.edit', compact('user'));
    }

    public function update(MemberUpdateRequest $request, $id) {
        try {
            $user = User::findOrFail($id);
            $input = $request->only(['name', 'email']);
            $data = $request->all();

            $data = array_merge($request->all(), [
                'password' => bcrypt($data['password'])
            ]);
            $user->update($data);
            return redirect()->route('admin.member.index')->with('alert-success','Cập nhật thông tin thành viên thành công!');
        } catch (Exception $e) {
            return redirect()->back()->with('alert-danger', 'Cập nhật thông tin thành viên thất bại!');
        }
    }

    public function destroy($id) {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.member.index')->with('alert-success', 'Xóa thành viên thành công!');
    }

    public function profile() {
        return view('admin.member.profile');
    }

    public function updateProfile(MemberUpdateRequest $request) {
        try {
            $user = User::findOrFail(auth()->user()->id);
            $input = $request->only(['name', 'email']);
            $data = $request->all();

            $data = array_merge($request->all(), [
                'password' => bcrypt($data['password'])
            ]);
            $user->update($data);
            return redirect()->back()->with('alert-success','Cập nhật thông tin cá nhân thành công!');
        } catch (Exception $e) {
            return redirect()->back()->with('alert-danger', 'Cập nhật thông tin cá nhân thất bại!');
        }
    }
}
