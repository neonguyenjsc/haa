<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\Lib;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckKeyAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    use Lib;

    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('api-key');
        $user = User::where('api_key', $key)->first();
        if (!$user) {
            $this->res['status'] = 403;
            $this->res['message'] = "Không tồn tại account này";
            return $this->setResponse($this->res);
        }
        if ($user->role != 'admin') {
            $user->status = 0;
            $user->notes = 'Tài khoản bị khóa vì cố truy cập vào trang admin';
            $user->save();
            DB::table('users_baned')->insert([
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'user_id' => $user->id,
                'username' => $user->username,
                'post_data' => json_encode($request->all()),
                'ip' => $request->ip(),
            ]);
            $this->res['status'] = 403;
            $this->res['message'] = "Không tồn tại account này!!";
            return $this->setResponse($this->res);
        }
        $request->request->add([
            'user' => $user
        ]);
        return $next($request);
    }
}
