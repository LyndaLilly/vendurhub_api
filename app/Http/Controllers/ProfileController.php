<?php
namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileLink;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function store(Request $request)
    {
        $existingProfile = Profile::where('user_id', $request->user()->id)->first();
        if ($existingProfile) {
            return response()->json([
                'message' => 'Profile already exists. Please update your profile instead.',
            ], 400);
        }

        $request->validate([
            'business_name'           => 'required|string|max:255',
            'business_description' =>    'required|string',
            'date_of_establishment'   => 'required|date',
            'country'                 => 'required|string|max:255',
            'business_location'       => 'required|string|max:255',
            'contact_number_whatsapp' => 'required|string|max:20',
            'business_account_number' => 'required|string|max:50',
            'busines_account_name'    => 'required|string|max:255',
            'business_bank_name'      => 'required|string|max:255',
            'business_logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'profile_image'           => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'whatsapp_link'           => 'required|url',
         
        ]);

        $data = $request->except(['business_logo', 'signature', 'profile_image']);

        if ($request->hasFile('business_logo')) {
            $data['business_logo'] = $request->file('business_logo')->store('logos', 'public');
        }

        if ($request->hasFile('signature')) {
            $data['signature'] = $request->file('signature')->store('signatures', 'public');
        }

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        $data['user_id']       = $request->user()->id;
        $data['whatsapp_link'] = $request->input('whatsapp_link');

        $profile = Profile::create($data);

        ProfileLink::create([
            'profile_id'     => $profile->id,
            'shareable_link' => Str::uuid(),
        ]);

        $request->user()->update(['profile_updated' => true]);

        return response()->json([
            'message' => 'Profile created successfully',
            'profile' => $profile,
        ]);
    }

    public function showSharedProfile($uuid)
    {
        $profileLink = ProfileLink::where('shareable_link', $uuid)->first();

        if (! $profileLink) {
            return response()->json(['message' => 'Shared profile not found.'], 404);
        }

        $profile = $profileLink->profile()
            ->with([
                'user.products.images',
                'user.products.category',
                'user.deliveryLocations',
            ])
            ->first();

        $user = $profile->user;

        return response()->json([
            'message' => 'Shared profile retrieved successfully',
            'profile' => [
                'business_name'           => $profile->business_name,
                'business_description'    => $profile->business_description,
                'business_logo'           => $profile->business_logo,
                'signature'               => $profile->signature,
                'profile_image'           => $profile->profile_image,
                'business_location'       => $profile->business_location,
                'business_bank_name'      => $profile->business_bank_name,
                'busines_account_name'    => $profile->busines_account_name,
                'business_account_number' => $profile->business_account_number,
                'country'                 => $profile->country,
                'contact_number_whatsapp' => $profile->contact_number_whatsapp,
                'whatsapp_link'           => $profile->whatsapp_link,
                
                'user'                   => [
                    'firstname'          => $user->firstname,
                    'lastname'           => $user->lastname,
                    'email'              => $user->email,
                    'phone'              => $user->phone ?? $profile->contact_number_whatsapp,

                    'products'           => $user->products->map(function ($product) {
                        return [
                            'id'       => $product->id,
                            'name'     => $product->name,
                            'price'    => $product->price,
                            'category' => [
                                'name' => optional($product->category)->name,
                            ],
                            'images'   => $product->images->map(function ($img) {
                                return [
                                    'id'         => $img->id,
                                    'image_path' => $img->image_path,
                                ];
                            }),
                        ];
                    }),

                    'delivery_locations' => $user->deliveryLocations->map(function ($loc) {
                        return [
                            'id'             => $loc->id,
                            'country'        => $loc->country,
                            'state'          => $loc->state,
                            'city'           => $loc->city,
                            'delivery_price' => $loc->delivery_price,
                            'other_country'  => $loc->other_country,
                            'nationwide'     => $loc->nationwide,
                            'note'           => $loc->note,
                        ];
                    }),
                ],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (! $profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        // Define restricted fields
        $restrictedFields = [
            'email',
            'business_name',
            'date_of_establishment',
            'business_account_number',
            'busines_account_name',
            'business_bank_name',
            'business_logo',
            'signature',
        ];

        // Check if any restricted field is being changed
        $attemptingRestrictedUpdate = false;

        foreach ($restrictedFields as $field) {
            if ($request->hasFile($field)) {
                $attemptingRestrictedUpdate = true;
                break;
            }

            if ($field === 'email') {
                if ($request->has('email') && $request->input('email') !== $user->email) {
                    $attemptingRestrictedUpdate = true;
                    break;
                }
            } else {
                if ($request->has($field) && $request->input($field) !== $profile->$field) {
                    $attemptingRestrictedUpdate = true;
                    break;
                }
            }
        }

        if ($attemptingRestrictedUpdate) {
            // $lastUpdate = Carbon::parse($profile->last_editable_update ?? $profile->created_at);
            // if ($lastUpdate->diffInDays(now()) < 60) {
            //     return response()->json([
            //         'message' => 'You can only edit sensitive fields every 60 days.',
            //     ], 403);
            // }

            $profile->last_editable_update = now();
            $profile->save();
        }

        // Validate request
        $request->validate([
            'firstname'               => 'sometimes|required|string|max:255',
            'lastname'                => 'sometimes|required|string|max:255',
            'email'                   => 'sometimes|required|email|unique:users,email,' . $user->id,
            'business_name'           => 'sometimes|required|string|max:255',
            'business_description'    => 'sometimes|required|string',
            'date_of_establishment'   => 'sometimes|required|date',
            'country'                 => 'sometimes|required|string|max:255',
            'business_location'       => 'sometimes|required|string|max:255',
            'contact_number_whatsapp' => 'sometimes|required|string|max:20',
            'business_account_number' => 'sometimes|required|string|max:50',
            'busines_account_name'    => 'sometimes|required|string|max:255',
            'business_bank_name'      => 'sometimes|required|string|max:255',
            'business_logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'profile_image'           => 'sometimes|required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'whatsapp_link'           => 'sometimes|required|url',
        ]);

        // Update user basic info
        $user->update([
            'firstname' => $request->input('firstname', $user->firstname),
            'lastname'  => $request->input('lastname', $user->lastname),
            'email'     => $request->input('email', $user->email),
        ]);

        // Update profile data
        $data = $request->except(['business_logo', 'signature', 'profile_image']);

        if ($request->hasFile('business_logo')) {
            if ($profile->business_logo) {
                Storage::disk('public')->delete($profile->business_logo);
            }
            $data['business_logo'] = $request->file('business_logo')->store('logos', 'public');
        }

        if ($request->hasFile('signature')) {
            if ($profile->signature) {
                Storage::disk('public')->delete($profile->signature);
            }
            $data['signature'] = $request->file('signature')->store('signatures', 'public');
        }

        if ($request->hasFile('profile_image')) {
            if ($profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        if ($request->has('whatsapp_link')) {
            $data['whatsapp_link'] = $request->input('whatsapp_link');
        }

        $profile->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile,
            'user'    => $user,
        ]);
    }

}
