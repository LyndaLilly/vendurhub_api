<?php
namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function store(Request $request)
    {
        try {
            $existingProfile = Profile::where('user_id', $request->user()->id)->first();
            if ($existingProfile) {
                return response()->json([
                    'message' => 'Profile already exists. Please update your profile instead.',
                ], 400);
            }

            // Validate input
            $request->validate([
                'business_name'           => 'required|string|max:255',
                'business_description'    => 'required|string',
                'date_of_establishment'   => 'required|date',
                'country'                 => 'required|string|max:255',
                'state'                   => 'required|string|max:255',
                'city'                    => 'required|string|max:255',
                'business_location'       => 'required|string|max:255',
                'contact_number_whatsapp' => 'required|string|max:20',
                'business_account_number' => 'nullable|string|max:50',
                'business_account_name'   => 'nullable|string|max:255',
                'business_bank_name'      => 'nullable|string|max:255',
                'business_logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'signature'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'profile_image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $data = $request->except(['business_logo', 'signature', 'profile_image']);

            // Generate WhatsApp link automatically
            $phone                 = preg_replace('/\D/', '', $request->input('contact_number_whatsapp'));
            $data['whatsapp_link'] = 'https://wa.me/' . $phone;
            $data['user_id']       = $request->user()->id;

            // Handle file uploads
            $uploads = ['business_logo' => 'logos', 'signature' => 'signatures', 'profile_image' => 'profile_images'];
            foreach ($uploads as $field => $folder) {
                if ($request->hasFile($field)) {
                    $file      = $request->file($field);
                    $uploadDir = public_path("uploads/{$folder}");
                    if (! file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $data[$field] = "{$folder}/{$filename}";
                }
            }

            $profile = Profile::create($data);

            // Create shareable link
            ProfileLink::create([
                'profile_id'     => $profile->id,
                'shareable_link' => Str::uuid(),
                'slug'           => Str::slug($profile->business_name) . '-' . substr(md5(rand()), 0, 6),
            ]);

            $request->user()->update(['profile_updated' => true]);

            return response()->json([
                'message' => 'Profile created successfully',
                'profile' => $profile,
            ]);
        } catch (\Exception $e) {
            Log::error('Profile store error: ' . $e->getMessage(), [
                'stack'        => $e->getTraceAsString(),
                'user_id'      => $request->user()->id ?? null,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the profile.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $user    = $request->user();
            $profile = $user->profile;

            if (! $profile) {
                return response()->json(['message' => 'Profile not found'], 404);
            }

            // Validate input
            $request->validate([
                'firstname'               => 'sometimes|required|string|max:255',
                'lastname'                => 'sometimes|required|string|max:255',
                'email'                   => 'sometimes|required|email|unique:users,email,' . $user->id,
                'business_name'           => 'sometimes|required|string|max:255',
                'business_description'    => 'sometimes|required|string',
                'date_of_establishment'   => 'sometimes|required|date',
                'country'                 => 'sometimes|required|string|max:255',
                'state'                   => 'sometimes|required|string|max:255',
                'city'                    => 'sometimes|required|string|max:255',
                'business_location'       => 'sometimes|required|string|max:255',
                'contact_number_whatsapp' => 'sometimes|required|string|max:20',
                'business_account_number' => 'sometimes|nullable|string|max:50',
                'business_account_name'   => 'sometimes|nullable|string|max:255',
                'business_bank_name'      => 'sometimes|nullable|string|max:255',
                'business_logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'signature'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'profile_image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Update user basic info
            $user->update([
                'firstname' => $request->input('firstname', $user->firstname),
                'lastname'  => $request->input('lastname', $user->lastname),
                'email'     => $request->input('email', $user->email),
            ]);

            $data = $request->except(['business_logo', 'signature', 'profile_image']);

            // Update WhatsApp link if number is changed
            if ($request->has('contact_number_whatsapp')) {
                $phone                 = preg_replace('/\D/', '', $request->input('contact_number_whatsapp'));
                $data['whatsapp_link'] = 'https://wa.me/' . $phone;
            }

            if ($request->has('business_name')) {
                $profileLink = $profile->profileLink;
                $profileLink->update([
                    'slug' => Str::slug($request->input('business_name')) . '-' . substr(md5(rand()), 0, 6),
                ]);

            }
            // Handle file uploads
            $uploads = ['business_logo' => 'logos', 'signature' => 'signatures', 'profile_image' => 'profile_images'];
            foreach ($uploads as $field => $folder) {
                if ($request->hasFile($field)) {
                    $file      = $request->file($field);
                    $uploadDir = public_path("uploads/{$folder}");
                    if (! file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Delete old file
                    if ($profile->$field && file_exists(public_path("uploads/{$profile->$field}"))) {
                        unlink(public_path("uploads/{$profile->$field}"));
                    }

                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $data[$field] = "{$folder}/{$filename}";
                }
            }

            $profile->update($data);

            return response()->json([
                'message' => 'Profile updated successfully',
                'profile' => $profile,
                'user'    => $user,
            ]);

        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage(), [
                'stack'        => $e->getTraceAsString(),
                'user_id'      => $request->user()->id ?? null,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'An error occurred while updating the profile.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function showSharedProfile($slug)
    {
        $profileLink = ProfileLink::where('slug', $slug)->first();

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

        if (! $profile) {
            return response()->json(['message' => 'Profile not found.'], 404);
        }

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
                'business_account_name'    => $profile->business_account_name,
                'business_account_number' => $profile->business_account_number,
                'country'                 => $profile->country,
                'contact_number_whatsapp' => $profile->contact_number_whatsapp,
                'whatsapp_link'           => $profile->whatsapp_link,

                'user'                    => [
                    'firstname'          => $user->firstname,
                    'lastname'           => $user->lastname,
                    'email'              => $user->email,
                    'phone'              => $user->phone ?? $profile->contact_number_whatsapp,
                    'products'           => $user->products->map(function ($product) {
                        return [
                            'id'       => $product->id,
                            'name'     => $product->name,
                            'price'    => $product->price,
                            'category' => ['name' => optional($product->category)->name],
                            'images'   => $product->images->map(fn($img) => [
                                'id'         => $img->id,
                                'image_path' => $img->image_path,
                            ]),
                        ];
                    }),
                    'delivery_locations' => $user->deliveryLocations->map(fn($loc) => [
                        'id'             => $loc->id,
                        'country'        => $loc->country,
                        'state'          => $loc->state,
                        'city'           => $loc->city,
                        'delivery_price' => $loc->delivery_price,
                        'other_country'  => $loc->other_country,
                        'nationwide'     => $loc->nationwide,
                        'note'           => $loc->note,
                    ]),
                ],
            ],
        ]);
    }

}
