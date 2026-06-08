<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Validation\Rules\Password;

class PasswordHelper
{
    /**
     * Get password validation rules based on user role.
     * If user is not provided, defaults to Admin complex rules for safety
     * or can be passed a boolean $isAdmin directly.
     *
     * @param User|null $user
     * @param bool|null $isAdminOverride
     * @return array
     */
    public static function getRules(?User $user = null, ?bool $isAdminOverride = null, bool $isNullable = false, bool $isConfirmed = true): array
    {
        $baseRules = [];
        if ($isNullable) {
            $baseRules[] = 'nullable';
        } else {
            $baseRules[] = 'required';
        }
        $baseRules[] = 'string';
        if ($isConfirmed) {
            $baseRules[] = 'confirmed';
        }

        $isAdmin = true; // Default to true if user is unknown (e.g. forgot password flow, though we usually find user first)
        
        if ($isAdminOverride !== null) {
            $isAdmin = $isAdminOverride;
        } elseif ($user && $user->hasRole('karyawan')) {
            $isAdmin = false;
        }

        if ($isAdmin) {
            // Aturan kompleks untuk Admin (Huruf Besar, Kecil, Angka, Karakter, Min 8)
            $passwordRule = Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols();
            $baseRules[] = $passwordRule;
        } else {
            // Aturan biasa untuk Karyawan
            $baseRules[] = 'min:6';
        }

        return $baseRules;
    }
    
    /**
     * Return info string for the UI.
     */
    public static function getRequirementMessage(bool $isAdmin): string
    {
        if ($isAdmin) {
            return "Password khusus Admin wajib menggunakan minimal 8 karakter, kombinasi huruf besar, huruf kecil, angka, dan karakter spesial (!@#$%^&*).";
        }
        return "Password minimal 6 karakter.";
    }
}
