<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'faculty',
        'paus_id',
        'paus_username',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * CCTV yang bisa diakses oleh user ini.
     */
    public function cctvs()
    {
        return $this->belongsToMany(Cctv::class);
    }

    /**
     * Model observer hooks for activity logging
     */
    protected static function booted()
    {
        static::created(function ($user) {
            try {
                $operatorId = auth()->id() ?: User::where('role', 'superadmin')->first()?->id;
                if ($operatorId) {
                    DB::table('activity_logs')->insert([
                        'user_id'       => $operatorId,
                        'activity_type' => 'user_add',
                        'cctv_id'       => null,
                        'details'       => json_encode([
                            'name'  => $user->name,
                            'email' => $user->email,
                            'role'  => $user->role,
                        ]),
                        'ip_address'    => request()->ip(),
                        'created_at'    => now(),
                    ]);
                }
            } catch (\Exception $e) {}
        });

        static::updated(function ($user) {
            try {
                $operatorId = auth()->id() ?: User::where('role', 'superadmin')->first()?->id;
                if ($operatorId) {
                    $dirtyFields = $user->getDirty();
                    unset($dirtyFields['updated_at'], $dirtyFields['password'], $dirtyFields['remember_token']);
                    
                    if (!empty($dirtyFields)) {
                        DB::table('activity_logs')->insert([
                            'user_id'       => $operatorId,
                            'activity_type' => 'user_edit',
                            'cctv_id'       => null,
                            'details'       => json_encode([
                                'name'    => $user->name,
                                'email'   => $user->email,
                                'changes' => $dirtyFields,
                            ]),
                            'ip_address'    => request()->ip(),
                            'created_at'    => now(),
                        ]);
                    }
                }
            } catch (\Exception $e) {}
        });

        static::deleted(function ($user) {
            try {
                $operatorId = auth()->id() ?: User::where('role', 'superadmin')->first()?->id;
                if ($operatorId) {
                    DB::table('activity_logs')->insert([
                        'user_id'       => $operatorId,
                        'activity_type' => 'user_delete',
                        'cctv_id'       => null,
                        'details'       => json_encode([
                            'name'  => $user->name,
                            'email' => $user->email,
                        ]),
                        'ip_address'    => request()->ip(),
                        'created_at'    => now(),
                    ]);
                }
            } catch (\Exception $e) {}
        });
    }
}
