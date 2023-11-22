<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function AdminDashboard(){

        return view('admin.index');

    } // End method

    public function AdminLogout(Request $request){
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    public function AdminLogin(){

        return view('admin.admin_login');

    } // End Method

    public function AdminProfile(){

        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_profile_view', compact('profileData'));

    } // End Method

    public function AdminProfileStore(Request $request){
        // Get the authenticated user's ID
        $id = Auth::user()->id;

        // Find the user record
        $data = User::find($id);

        // Update user data
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->address = $request->address;

        // Handle photo upload
        if($request->file('photo')){
            $file = $request->file('photo');
            @unlink(public_path('upload/admin_images/'.$data->photo));
            $filename = date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/admin_images'),$filename);
            $data['photo'] = $filename;
        }
        // Save the updated user data
        $data->save();

        $notification = array(
            'message' => 'Admin Profile Updated Successfully',
            'alert-type' => 'success'
        );

        // Redirect back to the previous page
        return redirect()->back()->with($notification);

    } // End Method

    public function AdminChangePassword(){

        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_change_password', compact('profileData'));

    } // End Method

    public function AdminPasswordUpdate(Request $request){

        // Validation
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed'
        ]);

        if(!Hash::check($request->old_password, auth::user()->password)){

            $notification = array(
                'message' => 'Old Password Does not Match!',
                'alert-type' => 'error'
            );

            // Redirect back to the previous page
            return back()->with($notification);

        }

        // Update the new password
        User::whereId(auth::user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        $notification = array(
            'message' => 'Password Change Successfully',
            'alert-type' => 'success'
        );

        // Redirect back to the previous page
        return back()->with($notification);

    } // End Method
}
