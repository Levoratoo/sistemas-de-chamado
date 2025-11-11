<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Exibir o perfil do usuário
     */
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Atualizar a foto de perfil
     */
    public function updatePhoto(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,gif',
                'max:2048', // 2MB
                'dimensions:max_width=2000,max_height=2000',
            ],
        ]);

        // Deletar foto antiga se existir
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Fazer upload da nova foto
        $file = $request->file('profile_photo');
        $filename = 'profile-photos/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs('profile-photos', basename($filename), 'public');

        // Atualizar o usuário
        $user->profile_photo = $path;
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Foto de perfil atualizada com sucesso!');
    }

    /**
     * Remover a foto de perfil
     */
    public function removePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
            $user->profile_photo = null;
            $user->save();
        }

        return redirect()->route('profile.index')->with('success', 'Foto de perfil removida com sucesso!');
    }
}
