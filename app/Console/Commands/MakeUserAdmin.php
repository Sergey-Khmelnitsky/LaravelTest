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
        
        $user->permissions = $permissions;
        $user->save();
        
        $this->info("User '{$user->name}' ({$email}) has been granted admin permissions.");
        
        return 0;
    }
}
