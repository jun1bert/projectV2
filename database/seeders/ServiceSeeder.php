<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Massage Therapy',
                'price' => 800,
                'duration' => 60,
                'description' => 'Relaxing full-body massage therapy.',
                'is_active' => 1,
                'requires_consent' => 0,
            ],
            [
                'name' => 'Swedish Massage',
                'price' => 1000,
                'duration' => 90,
                'description' => 'Gentle massage to promote relaxation and circulation.',
                'is_active' => 1,
                'requires_consent' => 0,
            ],
            [
                'name' => 'Deep Tissue Massage',
                'price' => 1200,
                'duration' => 90,
                'description' => 'Targets deeper muscle layers and chronic tension.',
                'is_active' => 1,
                'requires_consent' => 0,
            ],
            [
                'name' => 'Hot Stone Therapy',
                'price' => 1500,
                'duration' => 90,
                'description' => 'Warm stones used to relax muscles and improve circulation.',
                'is_active' => 1,
                'requires_consent' => 0,
            ],
            [
                'name' => 'Aromatherapy Massage',
                'price' => 1300,
                'duration' => 75,
                'description' => 'Massage combined with essential oils for relaxation.',
                'is_active' => 1,
                'requires_consent' => 0,
            ],
            [
                'name' => 'Foot Reflexology',
                'price' => 600,
                'duration' => 45,
                'description' => 'Pressure-point foot therapy for overall wellness.',
                'is_active' => 1,
                'requires_consent' => 0,
            ],
            [
                'name' => 'Prenatal Massage',
                'price' => 1400,
                'duration' => 60,
                'description' => 'Specialized massage for expecting mothers.',
                'is_active' => 1,
                'requires_consent' => 1,
            ],
            [
                'name' => 'Sports Massage',
                'price' => 1200,
                'duration' => 60,
                'description' => 'Designed for athletes and active individuals.',
                'is_active' => 1,
                'requires_consent' => 1,
            ],
            [
                'name' => 'Back and Shoulder Massage',
                'price' => 700,
                'duration' => 30,
                'description' => 'Focused treatment for upper body tension.',
                'is_active' => 1,
                'requires_consent' => 0,
            ],
            [
                'name' => 'Couples Massage',
                'price' => 2200,
                'duration' => 60,
                'description' => 'Relaxing massage experience for two people.',
                'is_active' => 0,
                'requires_consent' => 0,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}
