<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Default password
        $password = 'evolet';

        /*
        |--------------------------------------------------------------------------
        | Global users
        |--------------------------------------------------------------------------
        */

        $managmentDepartmentID = Department::findByName(Department::MGMT_NAME)->id;
        $globalAdminRoleID = Role::findByName(Role::GLOBAL_ADMINISTRATOR_NAME);

        $globalAdmins = [
            ['name' => 'Mister developer', 'email' => 'developer@mail.com', 'photo' => 'developer.jpg'],
            ['name' => 'Global admin', 'email' => 'admin@mail.com', 'photo' => 'developer.jpg'],
        ];

        // Create global admins
        foreach ($globalAdmins as $user) {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'department_id' => $managmentDepartmentID,
                'password' => bcrypt($password),
            ]);

            $newUser->roles()->attach($globalAdminRoleID);
        }

        /*
        |--------------------------------------------------------------------------
        | MAD users
        |--------------------------------------------------------------------------
        */

        $MadID = Department::findByName(Department::MAD_NAME)->id;

        $MADAdminRoleID = Role::findByName(Role::MAD_ADMINISTRATOR_NAME);
        $MADModeratorRoleID = Role::findByName(Role::MAD_MODERATOR_NAME);
        $MADGuestRoleID = Role::findByName(Role::MAD_GUEST_NAME);
        $MADAnalystRoleID = Role::findByName(Role::MAD_ANALYST_NAME);

        $MADAdmins = [
            ['name' => 'Firdavs Kilichbekov', 'email' => 'firdavs@mail.com', 'photo' => 'developer.jpg'],
        ];

        $MADModerators = [
            ['name' => 'Nuruloev Olimjon', 'email' => 'olim@mail.com', 'photo' => 'mad.png'],
            ['name' => 'Shahriyor Pirov', 'email' => 'shahriyor@mail.com', 'photo' => 'mad.png'],
            ['name' => 'Alim Munavarov', 'email' => 'alim@mail.com', 'photo' => 'mad.png'],
        ];

        $MADGuests = [
            ['name' => 'Mad guest', 'email' => 'madguest@mail.com', 'photo' => 'mad.png'],
        ];

        // Create MAD admins
        foreach ($MADAdmins as $user) {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'department_id' => $MadID,
                'password' => bcrypt($password),
            ]);

            $newUser->roles()->attach($MADAdminRoleID);
        }

        // Create MAD moderators
        foreach ($MADModerators as $user) {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'department_id' => $MadID,
                'password' => bcrypt($password),
            ]);

            $newUser->roles()->attach([$MADModeratorRoleID, $MADAnalystRoleID]);
            $newUser->responsibleCountries()->attach(rand(1, Country::count()));
        }

        // Create MAD guests
        foreach ($MADGuests as $user) {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'department_id' => $MadID,
                'password' => bcrypt($password),
            ]);

            $newUser->roles()->attach($MADGuestRoleID);
        }

        /*
        |--------------------------------------------------------------------------
        | CMD users
        |--------------------------------------------------------------------------
        */

        $CmdID = Department::findByName(Department::CMD_NAME)->id;
        $bdmRoleID = Role::findByName(Role::CMD_BDM_NAME);

        $bdms = [
            ['name' => 'Irini Kouimtzidou', 'email' => 'irini@mail.com', 'photo' => 'bdm.png'],
            ['name' => 'Darya Rassulova', 'email' => 'darya@mail.com', 'photo' => 'bdm.png'],
            ['name' => 'Nastya Karimova', 'email' => 'nastya@mail.com', 'photo' => 'bdm.png'],
        ];

        // Create CMD BDMs
        foreach ($bdms as $user) {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'department_id' => $CmdID,
                'password' => bcrypt($password),
            ]);

            $newUser->roles()->attach($bdmRoleID);
        }

        /*
        |--------------------------------------------------------------------------
        | Reset all user settings to default
        |--------------------------------------------------------------------------
        */

        User::resetSettingsOfAllUsers();
    }
}
