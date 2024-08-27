<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'active_package_id',
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'profile_for',
        'profile_created_by',
        'mobile_number',
        'whatsapp',
        'date_of_birth',
        'gender',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'marital_status',
        'religion',
        'community',
        'mother_tongue',
        'sub_community',
        'nationality',
        'highest_qualification',
        'college_name',
        'working_sector',
        'profession',
        'profession_details',
        'monthly_income',
        'father_occupation',
        'mother_occupation',
        'living_country',
        'currently_living_in',
        'city_living_in',
        'family_details',
        'family_values',
        'family_location',
        'family_type',
        'family_native_place',
        'total_siblings',
        'siblings_married',
        'siblings_not_married',
        'height',
        'birth_place',
        'personal_values',
        'disability',
        'posted_by',
        'weight',
        'bodyType',
        'race',
        'blood_group',
        'mother_status',
        'state',
        'about_myself',
        'partner_age',
        'partner_marital_status',
        'partner_religion',
        'partner_community',
        'partner_mother_tongue',
        'partner_qualification',
        'partner_working_with',
        'partner_profession',
        'partner_professional_details',
        'partner_country',
        'partner_state',
        'partner_city',
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


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org');
    }




 // Required method from JWTSubject
 public function getJWTIdentifier()
 {
     return $this->getKey();
 }

 // Required method from JWTSubject
 public function getJWTCustomClaims()
 {
     return [];
 }

 public function roles()
 {
     return $this->belongsTo(Role::class, 'role_id');
 }

public function permissions()
{
    return $this->hasManyThrough(
        Permission::class,
        'role_permission', // Pivot table name
        'user_id',         // Foreign key on the pivot table related to the User model
        'role_id',         // Foreign key on the pivot table related to the Permission model
        'id',              // Local key on the User model
        'role_id'          // Local key on the pivot table related to the Permission model
    );
}

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    // public function hasPermission($permission)
    // {
    //     foreach ($this->roles as $role) {
    //         if ($role->permissions->contains('name', $permission)) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }


    public function hasPermission($routeName)
    {
        // Get the user's roles with eager loaded permissions
        $permissions = $this->roles()->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten();




        // Check if any of the user's permissions match the provided route name and permission name
        $checkPermission =  $permissions->contains(function ($permission) use ($routeName) {

            return true;

            // Log:info($permission->name === $routeName && $permission->permission);
            // return $permission->path === $routeName && $permission->permission;
        });



        return $checkPermission;

    }
    public function images()
    {
        return $this->hasMany(UserImage::class);
    }


    /**
     * Get the profile views where the user is the viewed profile.
     */
    public function profileViews(): HasMany
    {
        return $this->hasMany(ProfileView::class, 'profile_id');
    }

    /**
     * Get the profile views where the user is the viewer.
     */
    public function viewedProfiles(): HasMany
    {
        return $this->hasMany(ProfileView::class, 'viewer_id');
    }

    // Other relationships...
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'sender_id');
    }

    public function receivedInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'receiver_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function userImages(): HasMany
    {
        return $this->hasMany(UserImage::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function popularity()
    {
        return $this->hasOne(Popularity::class);
    }

}
