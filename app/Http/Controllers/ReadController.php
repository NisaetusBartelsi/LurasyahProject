<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Comment;
use App\Models\Like;
use App\Models\ImageCompany;
use App\Models\ImagesCompany;
use Illuminate\Support\Facades\DB;

class ReadController extends Controller
{
    public function ReadSearch(Request $request)
    {
        $CompanyType = $request->input('CompanyType');
        $CompanyRegency = $request->input('CompanyRegency');

        $query = Company::query();

        if ($CompanyType) {
            $query->where('CompanyType', 'like', '%' . $CompanyType . '%');
        }

        if ($CompanyRegency) {
            $query->where('CompanyRegency', 'like', '%' . $CompanyRegency . '%');
        }

        $query->join('images_companies', 'companies.id', '=', 'images_companies.company_id');
        $query->join('users', 'companies.user_id', '=', 'users.id');

        $query->select('companies.*', 'images_companies.images', 'users.username');

        $results = $query->get();

        return response()->json($results);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    public function ReadDetail($username, $id)
    {

        $auth = auth('sanctum')->user();
        $authId = $auth->id;

        if (!$auth) {
            return response()->json(['Anda belum terdaftar']);
        }

        // dd($id);

        $images_companies = ImagesCompany::select('images', 'id')->where('id', $id)->first();
        $images_companies_id = $images_companies->id;

        $user = User::where('username', $username)->first();
        $userId = $user->id;



        $company = Company::where('id', $id)->where('user_id', $userId)->first();
        if (!$company) {
            return response()->json(['Username bukan pemilik dari toko ini']);
        }

        $user = DB::table('images_companies')
            ->join('users', 'images_companies.user_id', '=', 'users.id')
            ->join('companies', 'images_companies.company_id', '=', 'companies.id')
            ->where('images_companies.id', $images_companies_id)
            ->select('users.username', 'users.profile', 'companies.*', 'images_companies.images')
            ->get();

        $amount = Comment::where('images_companies_id', $images_companies_id)->count();

        $comments = Comment::where('images_companies_id', $images_companies_id)
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->select('comments.comment', 'comments.update_at', 'users.username', 'users.profile')
            ->orderBy('comments.update_at', 'desc')
            ->get();

        $totalLikes = DB::table('likes')
            ->where('images_companies_id', $images_companies_id)
            ->where('like', true)
            ->count();

        $like = DB::table('likes')
            ->where('images_companies_id', $images_companies_id)
            ->where('user_id', $authId)
            ->first();

        $bookmark = DB::table('bookmarks')
            ->where('images_companies_id', $images_companies_id)
            ->where('user_id', $authId)
            ->first();

        $response = [
            'user' => $user,
            'amount' => $amount,
            'comments' => $comments,
            'total' => $totalLikes,
            'like_status' => $like && $like->like ? 'true' : 'false',
            'bookmark_status' => $bookmark && $bookmark->bookmark ? 'true' : 'false'

        ];

        return response()->json($response);



        return response()->json(['error' => 'User tidak ditemukan'], 401);
    }

    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    public function ReadProfileUser($username)
    {
        $user = User::where('username', $username)->first();

        if ($user) {
            $userId = $user->id;
            $data = [];

            // FOTO USER DAN USERNAME
            $data['users'] = $user->only(['profile', 'name', 'username', 'email', 'biodata', 'provinsi', 'kota', 'kecamatan', 'desa']);

            $liked = Like::join('images_companies', 'likes.images_companies_id', '=', 'images_companies.id')
                ->join('users', 'likes.user_id', '=', 'users.id')
                ->select('images_companies.images')
                ->where('likes.user_id', $userId)
                ->where('likes.like', '>', 0)
                ->get();
            $data['liked'] = $liked;

            $marked = Bookmark::join('images_companies', 'bookmarks.images_companies_id', '=', 'images_companies.id')
                ->join('users', 'bookmarks.user_id', '=', 'users.id')
                ->where('bookmarks.user_id', $userId)
                ->where('bookmarks.bookmark', '>', 0)
                ->select('images_companies.images')
                ->get();
            $data['marked'] = $marked;

            return response()->json($data);
        }

        return response()->json(['error' => 'User not found'], 404);
    }

    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    public function ReadProfileAdmin($username)
    {
        $user = User::where('username', $username)->first();
        // dd($user);
        if ($user) {
            $userId = $user->id;
            $data = [];

            // FOTO USER DAN USERNAME
            $data['users'] = $user->only(['profile', 'username', 'email', 'biodata', 'provinsi', 'kota', 'kecamatan', 'desa', 'role', 'name']);

            $images_companies = ImagesCompany::select('images_companies.id', 'images_companies.images', 'users.username')
                ->join('users', 'users.id', '=', 'images_companies.user_id')
                ->where('images_companies.user_id', $userId)
                ->get();

            $data['content'] = $images_companies->toArray();

            // $data['image'] = $images_companies->pluck('id')->all();
            // dd($data['image']);
            $images_companies = ImagesCompany::select('images_companies.id', 'images_companies.images', 'users.username')
                ->join('users', 'users.id', '=', 'images_companies.user_id')
                ->where('images_companies.user_id', $userId)
                ->get();

            $data['content'] = $images_companies->toArray();

            $liked = Like::join('images_companies', 'likes.images_companies_id', '=', 'images_companies.id')
                // ->join('users', 'likes.user_id', '=', 'users.id')
                ->join('users', 'images_companies.user_id', '=', 'users.id')
                ->select('images_companies.images', 'images_companies.id', 'users.username')
                // ->whereIn('images_companies.id', $data['image'])
                ->where('likes.user_id', $userId)
                ->where('likes.like', '>', 0)
                ->get();
            $data['liked'] = $liked->toArray();

            $like = DB::table('likes')
                ->join('images_companies', 'likes.images_companies_id', '=', 'images_companies.id')
                ->where('likes.like', true)
                ->where('images_companies.user_id', $userId)
                ->count();
            $data['likes'] = $like;

            $marked = Bookmark::join('images_companies', 'bookmarks.images_companies_id', '=', 'images_companies.id')
                ->join('users', 'images_companies.user_id', '=', 'users.id')

                // ->whereIn('images_companies.id', $data['image'])
                ->where('bookmarks.user_id', $userId)
                ->where('bookmarks.bookmark', '>', 0)
                ->select('images_companies.images', 'users.username', 'images_companies.id')
                ->get();
            $data['marked'] = $marked->toArray();

            return response()->json($data);
        }

        return response()->json(['error' => 'User not found'], 404);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    public function ReadProfile($username)
    {
        $user = User::where('username', $username)->first();

        if ($user) {
            $role = $user->role;

            if ($role === 'Admin' || 'user') {
                return $this->ReadProfileAdmin($username);
            }
        }

        return response()->json(['error' => 'User not found'], 404);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////

    public function ReadAll()
    {

        $auth = auth('sanctum')->user();
        $userId = $auth->id;

        if (!$auth) {
            return response()->json(['Anda belum terdaftar']);
        }


        $user = DB::table('images_companies')
            ->join('users', 'images_companies.user_id', '=', 'users.id')
            ->join('companies', 'images_companies.company_id', '=', 'companies.id')
            ->leftJoin('likes', 'likes.images_companies_id', '=', 'images_companies.id')
            ->leftJoin('bookmarks', 'bookmarks.images_companies_id', '=', 'images_companies.id')
            ->select(
                'users.username',
                'companies.*',
                'images_companies.images',
                DB::raw('(SELECT COUNT(*) FROM likes WHERE images_companies_id = images_companies.id AND `like` = true) as like_count'),
                DB::raw('(SELECT COUNT(*) FROM bookmarks WHERE images_companies_id = images_companies.id AND bookmark = true) as bookmark_count')
            )
            ->groupBy('users.username', 'companies.id', 'images_companies.images', 'images_companies.id')
            ->get();



        $response = [
            'user' => $user,

        ];

        return response()->json($response);



        return response()->json(['error' => 'User tidak ditemukan'], 401);
    }
}
