<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{

    public function index()
    {
        if(Gate::denies('users:list')){
            return response([], 403);
        }

        return User::simplePaginate();
    }

    public function store(Request $request)
    {
        $valid = $request->validate([
            "name" => "required|max:255",
            "email" => "required|email|unique:users,email|max:255",
            "password" => "required|min:8|max:255",
            "phone" => "required|digits:11",
        ]);

        $user =  User::create([
            "name" => $valid["name"],
            "email" => $valid["email"],
            "password" => $valid["password"],
            "phone" => $valid["phone"],
        ]);

        // Normalmente este token não seria retornado em produção dessa forma.
        // No entanto, para simplificar o desafio, está disponível aqui.
        $user->access_token = $user->createToken("access_token")->plainTextToken;

        return response($user, 201);
    }

    public function show(string $id){}
    public function update(Request $request, string $id){}
    public function destroy(string $id){}
}
