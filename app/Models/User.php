<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
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
        'username',
        'step',
        'smoking',
        'other_lifestyle_preferences',
        'drinking',
        'diet',
        'email_verification_hash',
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
        return $this->hasMany(ProfileView::class, 'profile_id')->latest()->take(10);
    }

    /**
     * Get the profile views where the user is the viewer.
     */
    public function viewedProfiles(): HasMany
    {
        return $this->hasMany(ProfileView::class, 'viewer_id')->latest()->take(10);
    }

    // Other relationships...
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'sender_id')->latest()->take(10);
    }


    public function receivedInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'receiver_id')->latest()->take(10);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest()->take(10);
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


    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

     // Automatically append 'is_favorited' to the user instance
     protected $appends = ['is_favorited','age','profile_picture_url','active_package_details'];

     // Map of favoritable types to their corresponding models
     protected $modelMap = [
         'user' => \App\Models\User::class,
         // Add more mappings here if needed
         // 'post' => \App\Models\Post::class,
     ];

     // Accessor for is_favorited attribute
     public function getIsFavoritedAttribute()
     {
         // Check if there is an authenticated user
         if (Auth::check()) {
             $authUser = Auth::user();

             // Get the favoritable type from the model map (default to 'user')
             $favoritableType = 'user';
             $modelClass = $this->modelMap[$favoritableType] ?? null;

             // If the model exists in the map, proceed with the favorite check
             if ($modelClass && $modelClass === get_class($this)) {
                 return Favorite::where('user_id', $authUser->id)
                     ->where('favoritable_id', $this->id)
                    //  ->where('favoritable_type', $favoritableType) // 'user' as default favoritable type
                     ->exists();
             }
         }

         // If no authenticated user or invalid model, return false
         return false;
     }

    // Method to calculate age
    protected function calculateAge($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return null;
        }

        $birthDate = new \DateTime($dateOfBirth);
        $today = new \DateTime();
        return $today->diff($birthDate)->y; // Return the age in years
    }

    // Accessor for age attribute
    public function getAgeAttribute()
    {
        return $this->calculateAge($this->date_of_birth);
    }


    public function getProfilePictureUrlAttribute()
    {
        // Check if there are user images and get a random one if available
        if ($this->userImages()->exists()) {
            return $this->userImages()->inRandomOrder()->first()->image_path; // Assuming the UserImage model has a 'url' field
        }

        // Default images based on gender
        if ($this->gender === 'female') {
            return 'https://cdn-icons-png.freepik.com/512/9193/9193915.png';
        } else {
            return 'https://cdn.icon-icons.com/icons2/2643/PNG/512/male_boy_person_people_avatar_white_tone_icon_159368.png';
        }
    }



        // Define the relationship between User and Package
        public function activePackage()
        {
            return $this->belongsTo(Package::class, 'active_package_id');
        }

        // Add accessor to include active_package details
        public function getActivePackageDetailsWithServicesAttribute()
        {
            $activePackage = $this->activePackage;

            // Check if the active package exists
            if ($activePackage) {
                return [
                    'id' => $activePackage->id,
                    'package_name' => $activePackage->package_name,
                    'price' => $activePackage->price,
                    'discount_type' => $activePackage->discount_type,
                    'discount' => $activePackage->discount,
                    'sub_total_price' => $activePackage->sub_total_price,
                    'currency' => $activePackage->currency,
                    'duration' => $activePackage->duration,
                    'created_at' => $activePackage->created_at,
                    'updated_at' => $activePackage->updated_at,
                    'allowed_services' => $activePackage->activeServices->map(function ($service) {
                        return [
                            'name' => $service->service->name, // Assuming you have a 'name' field in your PackageService model
                            'status' => $service->status,
                        ];
                    }),
                ];
            }

            return null; // Return null if no active package
        }





}
