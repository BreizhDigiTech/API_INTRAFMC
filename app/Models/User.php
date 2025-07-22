<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Modèle User
 * Représente les utilisateurs de l'application avec leurs attributs et comportements.
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    // Constantes pour les états administrateur et utilisateur
    const ADMIN = true; // Indique que l'utilisateur est administrateur
    const USER = false; // Indique que l'utilisateur est un utilisateur standard

    // Constantes pour les états actif et inactif
    const ACTIVE = true; // Indique que le compte est actif
    const INACTIVE = false; // Indique que le compte est inactif

    /**
     * Attributs modifiables en masse.
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_admin',
        'is_active',
        'email_verified_at',
    ];

    /**
     * Attributs masqués pour les tableaux.
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Cast des attributs pour les types spécifiques.
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Cast en objet DateTime
            'password' => 'hashed', // Cast pour le hachage du mot de passe
            'is_admin' => 'boolean', // Cast en booléen
            'is_active' => 'boolean', // Cast en booléen
        ];
    }

    /**
     * Récupère l'identifiant JWT.
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Retourne la clé primaire (id)
    }

    /**
     * Récupère les claims personnalisés JWT.
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return []; // Aucun claim personnalisé
    }

    /**
     * Télécharge et enregistre l'avatar de l'utilisateur.
     * @param Request $avatar
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAvatar(Request $avatar)
    {
        $avatar->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = $avatar->file('avatar');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('avatars', $filename, 'public');

        $this->avatar = '/storage/' . $path;
        $this->save();

        return response()->json(['message' => 'Avatar uploaded successfully!', 'avatar' => $this->avatar]);
    }

    /**
     * Récupère le chemin complet de l'avatar.
     * @param string|null $value
     * @return string|null
     */
    public function getAvatarAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    /**
     * Vérifie si l'utilisateur est administrateur.
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === self::ADMIN;
    }

    /**
     * Vérifie si l'utilisateur est actif.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active === self::ACTIVE;
    }

    /**
     * Récupère le panier de l'utilisateur.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
}