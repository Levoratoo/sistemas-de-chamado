<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $category = Category::inRandomOrder()->first() ?? Category::factory()->create();
        $requester = User::inRandomOrder()->first() ?? User::factory()->create();
        
        return [
            'code' => 'CH-' . now()->year . '-' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(3, true),
            'category_id' => $category->id,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'waiting_user', 'resolved', 'closed']),
            'requester_id' => $requester->id,
            'assignee_id' => $this->faker->optional(0.7)->randomElement(User::whereHas('role', function ($q) {
                $q->whereIn('name', ['atendente', 'gestor', 'admin']);
            })->pluck('id')),
            'due_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            'respond_by' => $this->faker->dateTimeBetween('now', '+1 day'),
            'closed_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 week', 'now'),
        ];
    }
}











