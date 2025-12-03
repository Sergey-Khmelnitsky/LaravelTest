<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

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
    protected $description = 'Grant admin permissions to a user';

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
        
        $permissions = $user->permissions ?? [];
        $permissions['platform.systems'] = true;
        $permissions['platform.systems.index'] = true;
        $permissions['platform.systems.users'] = true;
        $permissions['platform.systems.roles'] = true;
        
        $user->forceFill(['permissions' => $permissions])->save();
        
        // Refresh the user model to ensure permissions are loaded
        $user->refresh();
        
        // Verify that permissions were saved
        $savedPermissions = $user->permissions;
        if (empty($savedPermissions) || !isset($savedPermissions['platform.systems'])) {
            $this->error("Failed to save admin permissions. Please check database connection and user model configuration.");
            return 1;
        }
        
        $this->info("User '{$user->name}' ({$email}) has been granted admin permissions.");
        $this->line("Permissions granted: " . implode(', ', array_keys($savedPermissions)));
        
        return 0;
    }
}
