<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Company;
use App\Models\ImagesCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateController extends Controller
{
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function CreateStore(Request $request)
    {
        $auth = auth('sanctum')->user();
        $userId = $auth->id;

        if (!$auth) {
            return response()->json(['Anda belum terdaftar']);
        } elseif ($auth->role !== 'Admin') {
            return response()->json(['message' => 'kamu bukan admin']);
        }

        $validator = Validator::make($request->all(), [
            'CompanyName' => 'required|string|max:255',
            'phone_number' => 'required|string|max:13|unique:companies',
            'CompanyAddres' => 'required|string|unique:companies',
            'CompanyProvince' => 'required|string',
            'CompanyRegency' => 'required|string',
            'CompanyDistrict' => 'required|string',
            'lat' => 'required|string|unique:companies',
            'long' => 'required|string|unique:companies',
            'deskripsi' => 'required|string',
            'CompanyVillage' => 'required|string',
            'CompanyType' => [
                'required', 'string', 'max:255',
                function ($attribute, $value, $fail) {
                    $allowed_types = ['kafe', 'masjid', 'atm', 'tempat wisata', 'minimarket'];
                    if (!in_array(strtolower($value), $allowed_types)) {
                        $fail("Kolom $attribute tidak valid.");
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }


        $company = Company::create(

            [
                'user_id' => $userId,
                'phone_number' => $request->phone_number,
                'CompanyProvince' => $request->CompanyProvince,
                'CompanyRegency' => $request->CompanyRegency,
                'CompanyDistrict' => $request->CompanyDistrict,
                'CompanyName' => $request->CompanyName,
                'CompanyAddres' => $request->CompanyAddres,
                'CompanyType' => $request->CompanyType,
                'CompanyVillage' => $request->CompanyVillage,
                'lat' => $request->lat,
                'long' => $request->long,
                'deskripsi' => $request->deskripsi,
            ]

        );
        $company->save();
        $company_id = $company->id;


        $image  = $request->file('image');
        $result = CloudinaryStorage2::upload($image->getRealPath(), $image->getClientOriginalName());

        ImagesCompany::create([
            'images' => $result,
            'user_id' => $userId,
            'company_id' => $company_id
        ]);


        return response()->json(['Toko Berhasil Ditambah']);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function CreateComment(Request $request, $username, $id)
    {
        $auth = auth('sanctum')->user();
        $authId = $auth->id;
        // dd($auth);
        if (!$auth) {
            return response()->json(['Anda belum terdaftar']);
        }

        $user = User::where('username', $username)->first();
        // dd($user);
        $userId = $user->id;

        $company = Company::where('id', $id)->where('user_id', $userId)->first();
        if (!$company) {
            return response()->json(['Username bukan pemilik dari toko ini']);
        }


        DB::table('comments')
            ->insert([
                'comment' => $request->comment,
                'images_companies_id' => $id,
                'user_id' => $authId,
            ]);

        $count = DB::table('comments')->count();
        $comments = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->select('comments.update_at', 'comments.comment', 'users.name')
            ->get();


        return response()->json(['kembali kehalaman dashboard']);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function CreateLike(Request $request, $username, $id)
    {
        // dd($id);
        $auth = auth('sanctum')->user();
        $authId = $auth->id;

        if (!$auth) {
            return response()->json(['Anda belum terdaftar']);
        }


        $user = User::where('username', $username)->first();
        // dd($user);
        $userId = $user->id;

        $company = Company::where('id', $id)->where('user_id', $userId)->first();
        // dd($company);    
        if (!$company) {
            return response()->json(['Username bukan pemilik dari toko ini']);
        }

        $image = ImagesCompany::find($id);
        $image_id = $image->id;

        $existingLike = DB::table('likes')
            ->where('user_id', $authId)
            ->where('images_companies_id', $image_id)
            ->first();

        if ($existingLike) {
            if ($existingLike->like == false) {
                DB::table('likes')
                    ->where('user_id', $authId)
                    ->where('images_companies_id', $image_id)
                    ->update(['like' => true]);

                $message = 'Liked successfully.';
            } else {
                DB::table('likes')
                    ->where('user_id', $authId)
                    ->where('images_companies_id', $image_id)
                    ->update(['like' => false]);

                $message = 'Like removed successfully.';
            }
        } else {
            DB::table('likes')
                ->insert([
                    'user_id' => $authId,
                    'images_companies_id' => $image_id,
                    'like' => true,
                ]);

            $message = 'Liked successfully.';
        }

            $totalLikes = DB::table('likes')
                ->where('images_companies_id', $image_id)
                ->where('like', true)
                ->count();


        return response()->json([
            'total_likes' => $totalLikes,
        ]);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function CreateBookmark(Request $request, $username, $id)
    {

        $auth = auth('sanctum')->user();
        $authId = $auth->id;
        if (!$auth) {
            return response()->json(['Anda belum terdaftar']);
        }


        $user = User::where('username', $username)->first();
        // dd($user);
        $userId = $user->id;


        $company = Company::where('id', $id)->where('user_id', $userId)->first();
        if (!$company) {
            return response()->json(['Username bukan pemilik dari toko ini']);
        }


        $image = ImagesCompany::find($id);
        $image_id = $image->id;

        $bookmarked = DB::table('bookmarks')
            ->where('user_id', $authId)
            ->where('images_companies_id', $image_id)
            ->first();

        if ($bookmarked) {
            if ($bookmarked->bookmark == false) {
                DB::table('bookmarks')
                    ->where('user_id', $authId)
                    ->where('images_companies_id', $image_id)
                    ->update(['bookmark' => true]);

                $message = 'bookmark successfully.';
            } else {
                DB::table('bookmarks')
                    ->where('user_id', $authId)
                    ->where('images_companies_id', $image_id)
                    ->update(['bookmark' => false]);

                $message = 'bookmark removed successfully.';
            }
        } else {
            DB::table('bookmarks')
                ->insert([
                    'user_id' => $authId,
                    'images_companies_id' => $image_id,
                    'bookmark' => true,
                ]);

            $message = 'bookmarkd successfully.';
        }

        $totalbookmarks = DB::table('bookmarks')
            ->where('images_companies_id', $image_id)
            ->where('bookmark', true)
            ->count();


        return response()->json([
            'total_bookmarks' => $totalbookmarks,
        ]);
    }
}
