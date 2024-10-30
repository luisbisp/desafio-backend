<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{

    use RefreshDatabase;

    public function test_can_create_a_user_with_access_token(){
        $data = ["name" => "Test User", "email" => time() . "@email.test", "password" => "test_password"];

        $post = $this->post("api/users", $data);

        $this->assertDatabaseHas("users", ["email" => $data["email"]]);
        $this->assertNotNull($post["access_token"]);
   }

   public function test_cannot_register_same_email(){
        $user = User::factory()->create();
        $data = ["name" => "User " . time(), "email" => $user->email, "password" => "test_password"];

        $post = $this->post("api/users", $data);

        $post->assertStatus(422);
        $this->assertDatabaseMissing("users", ["name" => $data["name"]]);
   }

   public function test_cannot_list_users_without_admin_role(){
        $users = User::factory(3)->create();

        // Anonymus
        $post = $this->get("api/users");
        $post->assertStatus(401);

        // Regular user
        $post = $this->actingAs($users[0])->get("api/users");
        $post->assertStatus(403);

        // Admin user
        $admin = User::factory()->create(["role" => "admin"]);
        $post = $this->actingAs($admin)->get("api/users");
        $post->assertStatus(200);
   }

}

