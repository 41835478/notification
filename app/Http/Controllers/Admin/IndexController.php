<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepositories;
use App\Service\IndexService;
use App\Service\VerficationService;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    protected $index;

    public  function __construct(IndexService $index)
    {
        $this->index = $index;
    }

    /**
     * 显示后台框架
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('admin.index');
    }

    /**
     * 显示后台头部
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function top()
    {
        return view('admin.top', [
            'user_name' => Auth::user()['name'],
        ]);
    }

    /**
     * 显示后台左侧
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function left()
    {
        //验证是否管理员true,false
        $admin = $this->index->admin();

        return view('admin.left', [
            'admin' => $admin,
        ]);
    }

    /**
     * 显示后台主要界面
     * @param UserRepositories $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function main(UserRepositories $user)
    {
        //获取上次登录时间
        $next_login_time = $user->selectFirst('next_login_time', 'id', Auth::id())['next_login_time'];

        //输出模板
        return view('admin.main', [
            'user_name' => Auth::user()['name'],
            'next_login_time' => $next_login_time ? : date('Y-m-d H:i:s')
        ]);
    }
}
