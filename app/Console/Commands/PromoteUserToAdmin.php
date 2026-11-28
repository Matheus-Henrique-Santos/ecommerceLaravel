<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserToAdmin extends Command
{
    /**
     * @var string
     */
    protected $signature = 'user:promote {email : E-mail of an existing account}';

    /**
     * @var string
     */
    protected $description = 'Grant back-office (admin) access to an existing user';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->components->error('No user found with that e-mail.');

            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->components->info("{$user->email} is already an admin.");

            return self::SUCCESS;
        }

        $user->promoteToAdmin();

        $this->components->info("{$user->email} is now an admin.");

        return self::SUCCESS;
    }
}
