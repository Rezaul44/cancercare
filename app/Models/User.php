<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Filament /admin প্যানেলে ঢুকতে পারে এমন staff রোল — 'doctor' ইচ্ছাকৃতভাবে বাদ, কারণ
     * ডাক্তার পোর্টাল (/doctor/*) একই 'web' গার্ড ব্যবহার করে এবং canAccessPanel()
     * আগে "যেকোনো রোল থাকলেই" true দিত — তাতে ডাক্তার লগইন করলেও admin প্যানেলে ঢুকতে পারতেন।
     *
     * @var list<string>
     */
    private const PANEL_ROLES = [
        'super_admin',
        'verification_officer',
        'content_editor',
        'medical_reviewer',
        'field_agent',
        'hospital_manager',
        'support_agent',
        'finance',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

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
        return $this->hasAnyRole(self::PANEL_ROLES);
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }
}
