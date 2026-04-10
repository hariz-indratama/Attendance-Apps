<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display the profile page.
     */
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Profile/Index', [
            'user' => $user,
        ]);
    }

    /**
     * Update profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Ensure we have a valid user with the update method
        if (!$user || !method_exists($user, 'update')) {
            return back()->with('error', 'User not authenticated or invalid');
        }

        // Refresh user from database to ensure we have a fresh model
        $user = $user->fresh();

        if (!$user) {
            return back()->with('error', 'User not found');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'position', 'department']));

        return back()->with('success', 'Profile updated successfully');
    }

    /**
     * Update avatar with image compression.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'], // max 5MB
        ]);

        $user = $request->user();

        if (!$user || !method_exists($user, 'update')) {
            return back()->with('error', 'User not authenticated or invalid');
        }

        $user = $user->fresh();

        try {
            // Log starting avatar upload
            Log::info('Avatar upload started', [
                'user_id' => $user->id,
                'file_size' => $request->file('avatar')->getSize(),
                'file_name' => $request->file('avatar')->getClientOriginalName(),
            ]);

            // Delete old avatar if exists
            if ($user->avatar) {
                Log::info('Deleting old avatar', ['old_avatar' => $user->avatar]);
                $this->deleteAvatarFile($user->avatar);
            }

            // Compress and save new avatar
            $avatarPath = $this->compressAndSaveImage($request->file('avatar'), $user->id);
            Log::info('Avatar saved to path', ['avatar_path' => $avatarPath]);

            $user->update(['avatar' => $avatarPath]);
            Log::info('User avatar updated in database', ['user_id' => $user->id, 'avatar' => $avatarPath]);

            // Verify the file exists
            if (Storage::disk('public')->exists($avatarPath)) {
                Log::info('Avatar upload SUCCESS', ['avatar_path' => $avatarPath]);
                return back()->with('success', 'Avatar updated successfully');
            } else {
                Log::error('Avatar file NOT FOUND after save', ['avatar_path' => $avatarPath]);
                return back()->with('error', 'Avatar file not found after save');
            }
        } catch (\Exception $e) {
            Log::error('Avatar upload FAILED', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to upload avatar: ' . $e->getMessage());
        }
    }

    /**
     * Compress and save image to storage.
     */
    private function compressAndSaveImage($image, $userId)
    {
        Log::debug('compressAndSaveImage: Starting image compression', [
            'user_id' => $userId,
            'original_name' => $image->getClientOriginalName(),
            'size' => $image->getSize(),
        ]);

        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            Log::error('compressAndSaveImage: GD extension not available');
            throw new \Exception('GD extension is not available');
        }

        // Get original extension
        $extension = $image->getClientOriginalExtension();

        // Generate unique filename
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;

        // Create temporary directory in storage/app (using proper Laravel path)
        $tempPath = storage_path('app/public/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        // Load image for compression
        $imageResource = imagecreatefromstring(file_get_contents($image));

        if (!$imageResource) {
            Log::error('compressAndSaveImage: Failed to create image resource');
            throw new \Exception('Failed to create image resource');
        }

        // Get current dimensions
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);

        // Define max dimensions (400x400 for avatar)
        $maxWidth = 400;
        $maxHeight = 400;

        // Calculate new dimensions maintaining aspect ratio
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create new image with new dimensions
        $compressedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($extension === 'png') {
            imagealphablending($compressedImage, false);
            imagesavealpha($compressedImage, true);
            $transparent = imagecolorallocatealpha($compressedImage, 0, 0, 0, 127);
            imagefilledrectangle($compressedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize image
        imagecopyresampled(
            $compressedImage,
            $imageResource,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $width, $height
        );

        // Save compressed image
        $tempFile = $tempPath . '/' . $filename;

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($compressedImage, $tempFile, 80); // 80% quality
                break;
            case 'png':
                imagepng($compressedImage, $tempFile, 6); // compression level 6
                break;
            case 'webp':
                imagewebp($compressedImage, $tempFile, 80);
                break;
            default:
                imagejpeg($compressedImage, $tempFile, 80);
        }

        // Free memory
        imagedestroy($imageResource);
        imagedestroy($compressedImage);

        // Ensure avatars directory exists in storage/app/public
        $avatarsDir = storage_path('app/public/avatars');
        if (!file_exists($avatarsDir)) {
            mkdir($avatarsDir, 0755, true);
        }

        // Move to permanent storage (avatars directory)
        $finalPath = 'avatars/' . $filename;
        Storage::disk('public')->put($finalPath, file_get_contents($tempFile));

        // Log final paths for debugging
        Log::debug('compressAndSaveImage: File saved', [
            'final_path' => $finalPath,
            'full_storage_path' => storage_path('app/public/' . $finalPath),
            'file_exists_at_storage' => file_exists(storage_path('app/public/' . $finalPath)),
            'storage_public_files' => Storage::disk('public')->files('avatars'),
        ]);

        // Delete temp file
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        return $finalPath;
    }

    /**
     * Delete avatar file.
     */
    private function deleteAvatarFile($avatarPath)
    {
        Log::debug('deleteAvatarFile: Checking avatar path', ['avatar_path' => $avatarPath]);

        if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
            Log::info('deleteAvatarFile: Deleting avatar file', ['avatar_path' => $avatarPath]);
            Storage::disk('public')->delete($avatarPath);
            Log::info('deleteAvatarFile: Avatar deleted successfully');
        } else {
            Log::warning('deleteAvatarFile: Avatar file does not exist', ['avatar_path' => $avatarPath]);
        }
    }

    /**
     * Delete avatar.
     */
    public function deleteAvatar(Request $request)
    {
        $user = $request->user() ?? Auth::user();

        if (!$user || !method_exists($user, 'update')) {
            return back()->with('error', 'User not authenticated or invalid');
        }

        $user = $user->fresh();

        if ($user->avatar) {
            $this->deleteAvatarFile($user->avatar);
            $user->update(['avatar' => null]);
            return back()->with('success', 'Avatar removed successfully');
        }

        return back()->with('error', 'No avatar to delete');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user() ?? Auth::user();

        if (!$user || !method_exists($user, 'update')) {
            return back()->with('error', 'User not authenticated or invalid');
        }

        $user = $user->fresh();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully');
    }

    /**
     * Update bank account information.
     */
    public function updateBank(Request $request)
    {
        $request->validate([
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user() ?? Auth::user();

        if (!$user || !method_exists($user, 'update')) {
            return back()->with('error', 'User not authenticated or invalid');
        }

        $user = $user->fresh();
        $user->update($request->only(['bank_name', 'bank_account', 'bank_account_name']));

        return back()->with('success', 'Bank information updated successfully');
    }
}