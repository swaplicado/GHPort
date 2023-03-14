<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\Sys\SyncController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use App\Models\Adm\Delegation;
use \Carbon\Carbon;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout() {
        Auth::logout();
        return redirect('/login');
    }

    public function username(){
        return 'username';
    }

    protected function credentials(Request $request){
        return $request->only($this->username(), 'password');
    }

    protected function authenticated(Request $request, $user)
    {
        session()->put('old_user_id', \Auth::user()->id);
        session()->put('is_delegation', false);
        session()->put('user_delegated_id', null);
        // session()->put('user_delegated', ['id' => null, 'username' => null, 'full_name' => null]);
        
        $lDelegations_asigned = Delegation::where('user_delegation_id', \Auth::user()->id)
                                        ->where('is_deleted', 0)
                                        ->where('is_active', 1)
                                        ->get();

        foreach($lDelegations_asigned as $oDel){
            if(!is_null($oDel->end_date)){
                $date = Carbon::parse($oDel->end_date);
                $now = carbon::today();
                if($date->lessThan($now)){
                    $oDel->is_active = 0;
                    $oDel->update();
                }
            }
        }

        $lDelegations_asigned = \DB::table('delegations as d')
                            ->leftJoin('users as uA', 'uA.id', '=', 'd.user_delegation_id')
                            ->leftJoin('users as uB', 'uB.id', '=', 'd.user_delegated_id')
                            ->where('user_delegation_id', \Auth::user()->id)
                            ->where('d.is_deleted', 0)
                            ->where('d.is_active', 1)
                            ->select(
                                'd.*',
                                'uA.id as user_delegation_id',
                                'uB.id as user_delegated_id',
                                'uA.full_name_ui as user_delegation_name',
                                'uB.full_name_ui as user_delegated_name',
                            )
                            ->get();
                                
        session()->put('tot_delegations', count($lDelegations_asigned));
        $lDelegations = [];
        foreach ($lDelegations_asigned as $oDel) {
            $lDelegations[] = [
                                'id' => $oDel->user_delegated_id,
                                'name' => $oDel->user_delegated_name,
                                ];
        }
        session()->put('lDelegations', $lDelegations);

        // return redirect(route('home'));
        return null;
    }

    public function login(Request $request, $idRoute = null, $idApp = null){
        if (session()->has('key')) {
            return redirect()->route('home');
         }
         
        $request->validate([
            "username" => "required",
            "password" => "required"
        ]);

        $userCredentials = $request->only('username', 'password');

        if (Auth::attempt($userCredentials)) {
            $this->authenticated($request, Auth::user());
            $sync = new SyncController();
            $sync->toSynchronize(false);
            if(!is_null($idRoute) && $idRoute != ""){
                return redirect("/$idRoute/$idApp");
            }else{
                return redirect()->route('home');
            }
        }
        else {
            // return back()->with('message', 'usuario o contraseña invalido');
            return $this->sendFailedLoginResponse($request);
        }
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    public function showLoginForm($route = null, $idApp = null){
        return view('auth.login')->with('idRoute', $route)
                                ->with('idApp', $idApp);
    }
}
