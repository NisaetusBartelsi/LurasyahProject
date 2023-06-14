<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{

    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function UpdateRole(Request $request)
    {
        $auth = auth('sanctum')->user();
        $userId = $auth->id;

        if (!$auth) {
            return response()->json(['Anda belum terdaftar']);
        }

        $user = User::find($userId);
        $triger = $user->triger = true;

        if ($triger == true) {
            $user->role = 'Admin';
            $user->save();

            return response()->json(['role' => $user->role]);
        } else {
            return response()->json(['error' => 'Pengguna tidak ditemukan'], 404);
        }
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function UpdateBioProfile(Request $request)
    {

        $auth = auth('sanctum')->user();
        $userId = $auth->id;

        if (!$auth) {
            return response()->json(['Anda belum terdaftar']);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|max:255|unique:users',
            'biodata' => 'nullable|string|max:255',
            'provinsi' => 'nullable|string|max:255',
            'kota' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::updateOrCreate(
            ['id' => $userId], // Kondisi untuk mencocokkan data yang ada berdasarkan ID
            [
                'name' => $request->has('name') ? $request->name : $auth->name,
                'biodata' => $request->has('biodata') ? $request->biodata : $auth->biodata,
                'provinsi' => $request->has('provinsi') ? $request->provinsi : $auth->provinsi,
                'kota' => $request->has('kota') ? $request->kota : $auth->kota,
                'kecamatan' => $request->has('kecamatan') ? $request->kecamatan : $auth->kecamatan,
                'desa' => $request->has('desa') ? $request->desa : $auth->desa,

            ]

        );

        $images = $user->images;

        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            CloudinaryStorage::delete($images);
            $result = CloudinaryStorage::upload($file->getRealPath(), $file->getClientOriginalName());
            $user->update(['profile' => $result]);
        }
        $user->save();

        return response()->json($user);
    }
}
