<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        // desactivar ralacion de llaves foraneas
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        //vaciar database
        User::truncate();
        //  DB::table()
        $cantidadUsuarios = 10;

        factory(User::class, $cantidadUsuarios)->create();
    }
}
