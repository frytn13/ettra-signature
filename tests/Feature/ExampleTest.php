<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Pengunjung yang belum login diarahkan ke halaman login Admin.
     */
    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('admin.login'));
    }
}
