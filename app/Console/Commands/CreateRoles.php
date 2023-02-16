<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Orchid\Platform\Models\Role;

class CreateRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating roles: admin, support, user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Role::create([
            'slug' => 'admin',
            'name' => 'Администратор',
            'permissions' => [
                'platform.index' => '1',
                'platform.systems.roles' => '1',
                'platform.systems.users' => '1',
                'platform.systems.support' => '1',
                'platform.systems.attachment' => '1'
            ]
        ]);

        Role::create([
            'slug' => 'support',
            'name' => 'Тех. поддержка',
            'permissions' => [
                'platform.index' => '1',
                'platform.systems.roles' => '0',
                'platform.systems.users' => '0',
                'platform.systems.support' => '1',
                'platform.systems.attachment' => '1'
            ]
        ]);

        Role::create([
            'slug' => 'user',
            'name' => 'Пользователь',
            'permissions' => [
                'platform.index' => '1',
                'platform.systems.roles' => '0',
                'platform.systems.users' => '0',
                'platform.systems.support' => '0',
                'platform.systems.attachment' => '1'
            ]
        ]);

        $this->info('Roles admin, support, user created successfully!');
        return Command::SUCCESS;
    }
}
