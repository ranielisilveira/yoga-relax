<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userExists = User::first();
        $console = new ConsoleOutput();

        if (!$userExists) {
            User::factory()->count(3)->create();
            $console->writeln("<info>users seed executed</info>");
            exit();
        }
        $console->writeln("<error>users seed: table already has data!</error>");
    }
}
