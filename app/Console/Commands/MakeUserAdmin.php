<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Orchid\Platform\Models\Role;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant admin permissions to a user by assigning admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }
        
        // Create or get admin role with all platform permissions
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'permissions' => [
                    'platform.index' => true,
                    'platform.systems' => true,
                    'platform.systems.index' => true,
                    'platform.systems.users' => true,
                    'platform.systems.roles' => true,
                ],
            ]
        );
        
        // Ensure platform.index permission is always present in admin role
        $permissions = $adminRole->permissions ?? [];
        $permissions['platform.index'] = true;
        $permissions['platform.systems'] = true;
        $permissions['platform.systems.index'] = true;
        $permissions['platform.systems.users'] = true;
        $permissions['platform.systems.roles'] = true;
        $adminRole->permissions = $permissions;
        $adminRole->save();
        
        // Assign admin role to user (this is the standard Orchid way)
        $user->replaceRoles([$adminRole->id]);
        
        // Refresh the user model to ensure roles are loaded
        $user->refresh();
        
        // Verify that role was assigned and user has access
        if (!$user->hasAccess('platform.index') || !$user->hasAccess('platform.systems')) {
            $this->error("Failed to assign admin role. Please check database connection and user model configuration.");
            return 1;
        }
        
        $this->info("User '{$user->name}' ({$email}) has been granted admin permissions.");
        $this->line("Admin role assigned: {$adminRole->name} ({$adminRole->slug})");
        
        return 0;
    }
}
