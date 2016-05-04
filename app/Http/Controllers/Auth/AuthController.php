<?php

namespace Castle\Http\Controllers\Auth;
use Castle\Http\Controllers\Controller;
use Castle\Permission;
use Castle\User;

use Auth;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use InvalidArgumentException;
use Socialite;
use Validator;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = 'home.index';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->permissions()->saveMany(
            Permission::byType(Permission::DEFAULT_PERMISSION_TYPE)->get()->all()
        );

        return $user;
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @return Response
     */
    public function redirectToProvider($provider = null)
    {
        switch ($provider) {
            case 'google':
                return Socialite::driver('google')->redirect();
        }

        return response('Authenticating via '.$provider.' isn\'t currently supported', 400);
    }

    /**
     * Obtain the user information from provider.
     *
     * @return Response
     */
    public function handleProviderCallback($provider = null)
    {
        $authenticated = Socialite::driver($provider)->user();
        $user = User::where('email', $authenticated->email)->first();

        if (!$user or ($user and !$user->exists())) {
            return redirect()->route('auth.login')
                ->with('alert-warning', 'Couldn\'t log you in.');
        }

        return (Auth::login($user) and Auth::check()) ?
            redirect($this->intended) :
            redirect()->route('auth.login')
                ->with('alert-warning', 'Couldn\'t log you in.');
    }
}
