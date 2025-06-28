<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\TestSuite;
use App\Models\TestCase;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create default user
        $user = User::create([
            'name' => 'Test Manager',
            'email' => 'admin@testflowpro.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        // Create sample project
        $project = Project::create([
            'name' => 'E-Commerce Platform Testing',
            'description' => 'Comprehensive testing for our e-commerce platform including user authentication, product catalog, shopping cart, and payment processing.',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        // Create test suites
        $authSuite = TestSuite::create([
            'name' => 'User Authentication',
            'description' => 'Test cases for user login, registration, and password management',
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        $cartSuite = TestSuite::create([
            'name' => 'Shopping Cart',
            'description' => 'Test cases for shopping cart functionality',
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        // Create sample test cases
        TestCase::create([
            'title' => 'User Login with Valid Credentials',
            'description' => 'Verify that users can successfully log in with valid email and password',
            'preconditions' => 'User account exists in the system',
            'test_steps' => [
                ['step' => 1, 'action' => 'Navigate to login page'],
                ['step' => 2, 'action' => 'Enter valid email address'],
                ['step' => 3, 'action' => 'Enter valid password'],
                ['step' => 4, 'action' => 'Click Login button']
            ],
            'expected_result' => 'User is successfully logged in and redirected to dashboard',
            'priority' => 'high',
            'type' => 'functional',
            'test_suite_id' => $authSuite->id,
            'project_id' => $project->id,
            'created_by' => $user->id,
            'tags' => ['authentication', 'login', 'positive-test']
        ]);

        TestCase::create([
            'title' => 'Add Product to Cart',
            'description' => 'Verify that users can add products to their shopping cart',
            'preconditions' => 'User is logged in and viewing product catalog',
            'test_steps' => [
                ['step' => 1, 'action' => 'Select a product from catalog'],
                ['step' => 2, 'action' => 'Choose product options (size, color, etc.)'],
                ['step' => 3, 'action' => 'Click "Add to Cart" button'],
                ['step' => 4, 'action' => 'Verify cart icon shows updated count']
            ],
            'expected_result' => 'Product is added to cart and cart count is updated',
            'priority' => 'high',
            'type' => 'functional',
            'test_suite_id' => $cartSuite->id,
            'project_id' => $project->id,
            'created_by' => $user->id,
            'tags' => ['shopping-cart', 'add-product', 'positive-test']
        ]);

        TestCase::create([
            'title' => 'Login with Invalid Password',
            'description' => 'Verify that login fails with invalid password',
            'preconditions' => 'User account exists in the system',
            'test_steps' => [
                ['step' => 1, 'action' => 'Navigate to login page'],
                ['step' => 2, 'action' => 'Enter valid email address'],
                ['step' => 3, 'action' => 'Enter invalid password'],
                ['step' => 4, 'action' => 'Click Login button']
            ],
            'expected_result' => 'Login fails with appropriate error message displayed',
            'priority' => 'medium',
            'type' => 'functional',
            'test_suite_id' => $authSuite->id,
            'project_id' => $project->id,
            'created_by' => $user->id,
            'tags' => ['authentication', 'login', 'negative-test']
        ]);
    }
}
