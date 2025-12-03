<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Orchid\Platform\Models\Role;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create
                            {--name= : User name}
                            {--email= : User email}
                            {--password= : User password}
                            {--admin : Make user an administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get user data from options or ask interactively
        $name = $this->option('name') ?: $this->ask('What is the user\'s name?');
        $email = $this->option('email') ?: $this->ask('What is the user\'s email?');
        $password = $this->option('password') ?: $this->secret('What is the user\'s password?');
        $makeAdmin = $this->option('admin');
        
        // If --admin flag is not set, ask interactively
        if (!$makeAdmin && !$this->option('no-interaction')) {
            $makeAdmin = $this->confirm('Make this user an administrator?', false);
        }

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  - ' . $error);
            }
            return 1;
        }

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email '{$email}' already exists.");
            return 1;
        }

        // Create user
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->save();

        $this->info("User '{$user->name}' ({$user->email}) has been created successfully.");

        // Make admin if requested
        if ($makeAdmin) {
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

            $user->replaceRoles([$adminRole->id]);
            $this->info("Admin role assigned: {$adminRole->name} ({$adminRole->slug})");
        }

        return 0;
    }
}
