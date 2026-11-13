<?php

\DB::setDefaultConnection("mysql_back_office");

use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::where("name", "admin")
    ->where("guard_name", "backoffice")
    ->first();

if (!$role) {
    $role = Role::create([
        "id" => (string) Str::uuid(),
        "name" => "admin",
        "guard_name" => "backoffice"
    ]);
}

$permissions = Permission::where("guard_name", "backoffice")->get();
$role->syncPermissions($permissions);

$lpkId = (string) Str::uuid();

\DB::table("lpks")->updateOrInsert(
    ["lpk_email" => "testing@lpk.test"],
    [
        "id" => $lpkId,
        "lpk_name" => "LPK Testing",
        "lpk_phone_no" => "080000000000",
        "lpk_address" => "Alamat Testing",
        "lpk_contact_person_phone_no" => "080000000000",
        "lpk_bank_account_number" => "0000000000",
        "lpk_bank_account_name" => "LPK Testing",
        "lpk_bank_branch" => "Testing",
        "active" => 1,
        "created_at" => now(),
        "updated_at" => now()
    ]
);

$user = \App\Models\User::firstOrNew(["email" => "admin@lpk.test"]);
$user->name = "Admin Testing";
$user->lpk_id = $lpkId;
$user->active = 1;
$user->password = "LPKtest#2026!";
$user->google_two_fa_secret = null;
$user->save();
$user->syncRoles([$role]);

app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

dump([
    "email" => $user->email,
    "role" => $role->name,
    "permissions" => $permissions->count()
]);
