<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles;
 protected $guard_name = 'web'; // important si tu utilises le guard 'web'
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

      public function canAccessPanel(Panel $panel): bool
    {
        // 'admin' = id du panel (change si le tien est différent)
        return $this->hasRole('super_admin');
        // return $this->hasRole('super_admin') || $this->can('access_admin_panel')|| $this->can('Gestionnaire');
    }

    //   public function canAccessPanel(Panel $panel): bool
    // {
    //     // doit dépendre d'un utilisateur AUTHENTIFIÉ
    //     if (! auth()->check()) {
    //         return false;
    //     }

    //     return $this->hasRole('super_admin')
    //         || $this->can('access_'.$panel->getId()); // ex: access_admin (Shield)
    // }
}
