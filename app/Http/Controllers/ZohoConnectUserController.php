<?php namespace App\Http\Controllers;

use App\Models\ZohoConnectUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ZohoConnectUserController extends Controller
{
    public function addUser()
    {
        return view('add-user');
    }

    public function storeUser(Request $request)
    {
        $this->validate($request, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:zoho_connect_users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        ZohoConnectUser::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        session()->flash('success', 'User Created Successfully');

        return redirect()->route('welcome');
    }

    public function changePassword()
    {
        return view('change-password');
    }

    public function storePassword(Request $request)
    {
        $this->validate($request, [
            'old_password'  => 'required',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (Hash::check( $request->old_password, auth()->user()->password )) {
            auth()->user()->update([
                'password' => bcrypt($request->password)
            ]);

            session()->flash('success', "Password Changed Successfully");
            return redirect()->route('welcome');
        } else {
            session()->flash('error', "Password doesn't match");
            return redirect()->back();
        }
    }
}
