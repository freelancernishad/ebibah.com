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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
class ActiveUserScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('status', 'active');
    }
}


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


        // 'partner_marital_status',
        // 'partner_religion',
        // 'partner_community',
        // 'partner_mother_tongue',

        // 'partner_qualification', deleted
        // 'partner_working_with', deleted
        // 'partner_profession', deleted

        // 'partner_professional_details',
        // 'partner_country',
        // 'partner_state',
        // 'partner_city',


        'username',
        'step',
        'smoking',
        'other_lifestyle_preferences',
        'drinking',
        'diet',
        'email_verification_hash',
        'status',
    ];

    protected $appends = ['is_favorited', 'age', 'profile_picture_url', 'active_package', 'invitation_send_status','received_invitations_count','accepted_invitations_count','favorites_count','profile_completion'];




    public function partnerMaritalStatuses()
    {
        return $this->hasMany(PartnerMaritalStatus::class);
    }

    public function partnerReligions()
    {
        return $this->hasMany(PartnerReligion::class);
    }

    public function partnerCommunities()
    {
        return $this->hasMany(PartnerCommunity::class);
    }

    public function partnerMotherTongues()
    {
        return $this->hasMany(PartnerMotherTongue::class);
    }


    public function partnerQualification()
    {
        return $this->hasMany(PartnerQualification::class);
    }

    public function partnerWorkingWith()
    {
        return $this->hasMany(PartnerWorkingWith::class);
    }

    public function partnerProfessions()
    {
        return $this->hasMany(PartnerProfession::class);
    }


    public function partnerProfessionalDetails()
{
    return $this->hasMany(PartnerProfessionalDetail::class);
}

public function partnerCountries()
{
    return $this->hasMany(PartnerCountry::class);
}

public function partnerStates()
{
    return $this->hasMany(PartnerState::class);
}

public function partnerCities()
{
    return $this->hasMany(PartnerCity::class);
}




public function getProfileCompletionAttribute()
    {
        $totalFields = count($this->fillable);
        $filledFields = 0;

        // Check fillable fields
        foreach ($this->fillable as $field) {
            if (!empty($this->{$field})) {
                $filledFields++;
            }
        }

        // Check related fields
        $relatedModels = [
            'partnerMaritalStatuses',
            'partnerReligions',
            'partnerCommunities',
            'partnerMotherTongues',
            'partnerQualification',
            'partnerWorkingWith',
            'partnerProfessions',
            'partnerProfessionalDetails',
            'partnerCountries',
            'partnerStates',
            'partnerCities',
        ];

        foreach ($relatedModels as $relation) {
            $count = $this->{$relation}()->count();
            if ($count > 0) {
                $filledFields++; // Count if there's at least one related entry
            }
        }

        $number = ($filledFields / ($totalFields + count($relatedModels))) * 100; // Returns percentage

        return number_format($number, 2);
    }







    public function setGenderAttribute($value)
    {
        // Capitalize the first letter of the gender
        $this->attributes['gender'] = ucfirst(strtolower($value));
    }
    public static function updateGenderToCapitalized()
    {
        self::query()->update([
            'gender' => \DB::raw("CONCAT(UPPER(LEFT(gender, 1)), LOWER(SUBSTRING(gender, 2)))")
        ]);
    }
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

    protected $with = [
        'partnerMaritalStatuses',
        'partnerReligions',
        'partnerCommunities',
        'partnerMotherTongues',
        'partnerQualification',
        'partnerWorkingWith',
        'partnerProfessions',
        'partnerProfessionalDetails',
        'partnerCountries',
        'partnerStates',
        'partnerCities',
    ];

    public function toArrayWithRelations()
    {
        return parent::toArray(); // Call the parent's toArray without any modifications
    }

    public function toArray()
    {
        // Unset the specified relations
        $this->unsetRelation('sentInvitations');
        $this->unsetRelation('receivedInvitations');
        $this->unsetRelation('profileViews');
        $this->unsetRelation('payments');

        // Hide the specified attributes
        $this->makeHidden([
            'active_package_id',
            'active_package',
            'email',
            'email_verification_hash',
            'step',
            'email_verified_at',
            'role',
            'role_id',
            // 'created_at',
            'updated_at',
            'views',
            'likes',
            'received_invitations_count',
            'accepted_invitations_count',
            'favorites',
        ]);
        $this->where('status','active');

        // Convert the model to an array
        $array = parent::toArray();



        return $array;
    }
    protected static $applyActiveScope = true;
    protected static function boot()
    {
        parent::boot();

        // Conditionally apply the global scope based on the static property
        if (static::$applyActiveScope) {
            static::addGlobalScope(new ActiveUserScope);
        }
    }
    // Method to set the flag
    public static function setApplyActiveScope($apply)
    {
        self::$applyActiveScope = $apply;
    }

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
    private function selectUserFields($query)
    {
        $query->select('id','name','marital_status','religion','date_of_birth','profession','living_country','working_sector','height');
    }
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'sender_id')->with([
                        // 'sender' => function($query) {
                        //     $this->selectUserFields($query);
                        // },
                        'receiver' => function($query) {
                            $this->selectUserFields($query);
                        }
                    ])
                    ->where('status', 'sent') // Only include invitations with status 'sent'
                    ->latest() // Order by latest
                    ->take(10); // Limit to the latest 10 invitations
    }

    public function receivedInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'receiver_id')->with([
                        'sender' => function($query) {
                            $this->selectUserFields($query);
                        },
                        // 'receiver' => function($query) {
                        //     $this->selectUserFields($query);
                        // }
                    ])
                    ->where('status', 'sent') // Only include invitations with status 'received'
                    ->latest() // Order by latest
                    ->take(10); // Limit to the latest 10 invitations
    }


    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)
                    ->where('status', 'completed') // Only include payments with status 'completed'
                    ->latest() // Order by latest
                    ->take(10); // Limit to the latest 10 payments
    }

    public function favorites($limit = 4)
    {
        return $this->hasMany(Favorite::class)->with([
            'user' => function($query) {
                $this->selectUserFields($query);
            },

        ])

        ->latest() // Order by latest
        ->limit($limit); // Limit to 4 records
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
     // Automatically append 'is_favorited' to the user instance


     public function getReceivedInvitationsCountAttribute(): int
     {
         return $this->hasMany(Invitation::class, 'receiver_id')
                     ->where('status', 'sent') // Only include invitations with status 'sent'
                     ->count(); // Count the invitations
     }

     public function getAcceptedInvitationsCountAttribute(): int
     {
         return $this->hasMany(Invitation::class, 'receiver_id')
                     ->where('status', 'accepted') // Only include invitations with status 'accepted'
                     ->count(); // Count the invitations
     }


     public function getFavoritesCountAttribute(): int
     {
        return $this->hasMany(Favorite::class)->count();
     }

     public function getInvitationReceivedStatusAttribute()
     {
         // Assuming you want to get the invitation statuses for the authenticated user
         $authUserId = auth()->id(); // Get the authenticated user's ID

         // Fetch invitations received by the authenticated user
         $invitations = Invitation::where('receiver_id', $authUserId)
         ->where('sender_id', $this->id)
             ->first();
             if(!$invitations){
                return 'not received';
             }

             $status = $invitations->status;
             if($status=='sent'){
                $status = 'received';
             }


         return $status; // Return an array of statuses or an empty array if none found
     }

     public function getInvitationSendStatusAttribute()
     {
         // Assuming you want to get the invitation statuses for the authenticated user
         $authUserId = auth()->id(); // Get the authenticated user's ID

         // Fetch invitations sent by the authenticated user
         $invitations = Invitation::where('sender_id', $authUserId)
         ->where('receiver_id', $this->id)
             ->first();

             if(!$invitations){
                return 'not sent';
             }

         return $invitations->status; // Return an array of statuses or an empty array if none found
     }




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
        // Define the relationship between User and Package
     // Define the relationship between User and Package
     public function activePackage()
     {
         return $this->belongsTo(Package::class, 'active_package_id');
     }

     // Add accessor to include active_package details
     public function getActivePackageAttribute()
     {
        $activePackage = $this->getRelationValue('activePackage');

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
                         'name' => $service->service->name,
                         'status' => $service->status,
                     ];
                 }),
             ];
         }

         return null; // Return null if no active package found
     }


           /**
     * Get a list of similar profiles based on all key attributes.
     *
     * @param int $limit Number of similar profiles to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSimilarProfiles($limit = 10)
    {
        // Get the user's current attributes
        $gender = $this->gender;
        $ageRange = $this->getAgeRange(); // Method to get a flexible age range
        $religion = $this->religion;
        $community = $this->community;
        $subCommunity = $this->sub_community;
        $motherTongue = $this->mother_tongue;
        $maritalStatus = $this->marital_status;
        $cityLivingIn = $this->city_living_in;
        $profession = $this->profession;
        $heightRange = $this->getHeightRange(); // Method to calculate flexible height range
        $personalValues = $this->personal_values;

        // Base query to find similar profiles
        $query = self::where('id', '!=', $this->id); // Exclude the current user

        // Initialize an array to hold matched profiles
        $matchedProfiles = [];

        // Find profiles and check for matches
       return $profiles = $query->limit($limit)->get();

        foreach ($profiles as $profile) {
            $matches = [];

            if ($gender && $profile->gender == $gender) {
                $matches[] = 'gender';
            }
            if ($ageRange && $profile->date_of_birth >= $ageRange[0] && $profile->date_of_birth <= $ageRange[1]) {
                $matches[] = 'age';
            }
            if ($religion && stripos($profile->religion, $religion) !== false) {
                $matches[] = 'religion';
            }
            if ($community && stripos($profile->community, $community) !== false) {
                $matches[] = 'community';
            }
            if ($subCommunity && stripos($profile->sub_community, $subCommunity) !== false) {
                $matches[] = 'sub_community';
            }
            if ($motherTongue && stripos($profile->mother_tongue, $motherTongue) !== false) {
                $matches[] = 'mother_tongue';
            }
            if ($maritalStatus && stripos($profile->marital_status, $maritalStatus) !== false) {
                $matches[] = 'marital_status';
            }
            if ($cityLivingIn && stripos($profile->city_living_in, $cityLivingIn) !== false) {
                $matches[] = 'city_living_in';
            }
            if ($profession && stripos($profile->profession, $profession) !== false) {
                $matches[] = 'profession';
            }
            if ($heightRange && $profile->height >= $heightRange[0] && $profile->height <= $heightRange[1]) {
                $matches[] = 'height';
            }
            if ($personalValues && stripos($profile->personal_values, $personalValues) !== false) {
                $matches[] = 'personal_values';
            }

            // Add matched profile and its matching attributes to the result
            if (!empty($matches)) {
                $profile->matchedAttributes = $matches;
                $matchedProfiles[] = $profile; // Append the matched profile to the array
            }
        }

        return $matchedProfiles;
    }





    /**
     * Get an age range for searching similar profiles.
     *
     * @return array Date range (start and end)
     */
    protected function getAgeRange()
    {
        $age = $this->getAge(); // Assume a method to calculate age from date_of_birth
        $minAge = $age - 5; // Flexible range, 5 years younger
        $maxAge = $age + 5; // Flexible range, 5 years older

        $minDateOfBirth = Carbon::now()->subYears($maxAge)->toDateString();
        $maxDateOfBirth = Carbon::now()->subYears($minAge)->toDateString();

        return [$minDateOfBirth, $maxDateOfBirth];
    }

    /**
     * Get height range for searching similar profiles.
     *
     * @return array Height range (min, max)
     */




     protected function getHeightRange()
     {
         // Convert the height to a float
         $height = floatval($this->height);

         // Check if height is numeric after conversion
         if (!is_numeric($height)) {
             // Handle the case where height is not numeric
             return [null, null]; // or set default values, e.g., [0, 0]
         }

         $minHeight = $height - 10; // Flexible range, 10 cm shorter
         $maxHeight = $height + 10; // Flexible range, 10 cm taller

         return [$minHeight, $maxHeight];
     }

    /**
     * Get the user's current age from the date of birth.
     *
     * @return int Age of the user
     */
    public function getAge()
    {
        return Carbon::parse($this->date_of_birth)->age;
    }





}
