<?php

namespace Tests\Browser;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use ProcessMaker\Models\ProcessRequestToken;
use ProcessMaker\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class UserCreationTest extends DuskTestCase
{
    /**
     * @throws \Throwable
     */
    public function testOverDueTasksNotification()
    {
        Artisan::call('migrate:fresh', []);

        $user = factory(User::class)->create([
            'username' => 'admin',
            'password' => 'admin',
            'email' => 'any@gmail.com',
            'firstname' => 'admin',
            'lastname' => 'admin',
            'timezone' => null,
            'datetime_format' => null,
            'status' => 'ACTIVE',
            'is_administrator' => true,
        ]);


        // We create some tokens that are overdue
        factory(ProcessRequestToken::class, 99)->create([
            'user_id' => 1,
            'due_at' => Carbon::now()->addDays(-10)
        ]);

        // Test login
        $this->browse(function (Browser $browser) {

            $browser->resize(1920, 1080);

            $browser->visit('/')
                ->assertSee('Username')
                ->type('#username', 'admin')
                ->type('#password', 'admin')
                ->press('.btn')
                ->clickLink('Tasks')
                ->pause(5000)
                ->waitFor('.vuetable-body', 5)
                ->screenshot('antes')
                ->assertSee('tasks pending');
        });

    }
}
