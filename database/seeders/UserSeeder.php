<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $allDepartments = Department::all();
        $allRoles = Role::all();
        $countryCount = Country::count();

        // Default password
        $password = 'evolet';

        /*
        |--------------------------------------------------------------------------
        | Global users
        |--------------------------------------------------------------------------
        */

        $managmentDepartmentID = $allDepartments->firstWhere('name', Department::MGMT_NAME)->id;
        $globalAdminRoleID = $allRoles->firstWhere('name', Role::GLOBAL_ADMINISTRATOR_NAME)->id;

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

        $MadID = $allDepartments->firstWhere('name', Department::MAD_NAME)->id;

        $MADAdminRoleID = $allRoles->firstWhere('name', Role::MAD_ADMINISTRATOR_NAME)->id;
        $MADModeratorRoleID = $allRoles->firstWhere('name', Role::MAD_MODERATOR_NAME)->id;
        $MADGuestRoleID = $allRoles->firstWhere('name', Role::MAD_GUEST_NAME)->id;
        $MADAnalystRoleID = $allRoles->firstWhere('name', Role::MAD_ANALYST_NAME)->id;

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
            $newUser->responsibleCountries()->attach(rand(1, $countryCount));
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

        $CmdID = $allDepartments->firstWhere('name', Department::CMD_NAME)->id;
        $bdmRoleID = $allRoles->firstWhere('name', Role::CMD_BDM_NAME)->id;

        $bdms = [
            ['name' => 'Irini Kouimtzidou', 'email' => 'cmd_bdm@mail.com', 'photo' => 'cmd_bdm.png'],
            ['name' => 'Darya Rassulova', 'email' => 'darya@mail.com', 'photo' => 'cmd_bdm.png'],
            ['name' => 'Nastya Karimova', 'email' => 'nastya@mail.com', 'photo' => 'cmd_bdm.png'],
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
        | PLD users
        |--------------------------------------------------------------------------
        */

        $pldID = $allDepartments->firstWhere('name', Department::PLD_NAME)->id;
        $logisticianRoleID = $allRoles->firstWhere('name', Role::PLD_LOGISTICIAN_NAME)->id;

        $logisticians = [
            ['name' => 'PLD Logistic', 'email' => 'pld_logistician@mail.com', 'photo' => 'pld_logistician.png'],
        ];

        // Create PLD logisticians
        foreach ($logisticians as $user) {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'department_id' => $pldID,
                'password' => bcrypt($password),
            ]);

            $newUser->roles()->attach($logisticianRoleID);
        }

        /*
        |--------------------------------------------------------------------------
        | PRD users
        |--------------------------------------------------------------------------
        */

        $prdID = Department::findByName(Department::PRD_NAME)->id;
        $financierRoleID = Role::findByName(Role::PRD_FINANCIER_NAME);

        $financiers = [
            ['name' => 'PRD Financier', 'email' => 'prd_financier@mail.com', 'photo' => 'prd_financier.png'],
        ];

        // Create PRD financiers
        foreach ($financiers as $user) {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'department_id' => $prdID,
                'password' => bcrypt($password),
            ]);

            $newUser->roles()->attach($financierRoleID);
        }

        /*
        |--------------------------------------------------------------------------
        | DD users
        |--------------------------------------------------------------------------
        */

        $ddID = Department::findByName(Department::DD_NAME)->id;
        $designerRoleID = Role::findByName(Role::DD_DESIGNER_NAME);

        $designers = [
            ['name' => 'DD Designer', 'email' => 'dd_designer@mail.com', 'photo' => 'dd_designer.png'],
        ];

        // Create DD designers
        foreach ($designers as $user) {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'department_id' => $ddID,
                'password' => bcrypt($password),
            ]);

            $newUser->roles()->attach($designerRoleID);
        }

        /*
        |--------------------------------------------------------------------------
        | MD users
        |--------------------------------------------------------------------------
        */

        $mdID = Department::findByName(Department::MD_NAME)->id;
        $serializerRoleID = Role::findByName(Role::MD_SERIALIZER_NAME);

        $serializers = [
            ['name' => 'MD Serializer', 'email' => 'md_serializer@mail.com', 'photo' => 'md_serializer.png'],
        ];

        // Create MD Serializers
        foreach ($serializers as $user) {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'department_id' => $mdID,
                'password' => bcrypt($password),
            ]);

            $newUser->roles()->attach($serializerRoleID);
        }

        /*
        |--------------------------------------------------------------------------
        | ELD users
        |--------------------------------------------------------------------------
        */

        // $eldID = Department::findByName(Department::ELD_NAME)->id;
        // $logisticianRoleID = Role::findByName(Role::ELD_LOGISTICIAN_NAME);

        // $logisticians = [
        //     ['name' => 'Europe logistic', 'email' => 'eld_logistician@mail.com', 'photo' => 'eld_logistician.png'],
        // ];

        // // Create ELD Logisticians
        // foreach ($logisticians as $user) {
        //     $newUser = User::create([
        //         'name' => $user['name'],
        //         'email' => $user['email'],
        //         'photo' => $user['photo'],
        //         'department_id' => $eldID,
        //         'password' => bcrypt($password),
        //     ]);

        //     $newUser->roles()->attach($logisticianRoleID);
        // }

        /*
        |--------------------------------------------------------------------------
        | Reset all user settings to default
        |--------------------------------------------------------------------------
        */

        User::resetSettingsOfAllUsers();
    }
}
