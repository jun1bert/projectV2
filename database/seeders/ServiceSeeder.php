<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['category' => 'Nail Services', 'name' => 'Classic Manicure', 'price' => 150],
            ['category' => 'Nail Services', 'name' => 'Classic Pedicure', 'price' => 170],
            ['category' => 'Nail Services', 'name' => 'Gel Manicure', 'price' => 380],
            ['category' => 'Nail Services', 'name' => 'Gel Pedicure', 'price' => 400],
            ['category' => 'Nail Services', 'name' => 'Russian Manicure (Dry Cuticle Care)', 'price' => 400],
            ['category' => 'Nail Services', 'name' => 'BIAB Overlay', 'price' => 500],
            ['category' => 'Nail Services', 'name' => 'Hard Gel Overlay', 'price' => 550],
            ['category' => 'Nail Services', 'name' => 'Soft Gel Extensions', 'price' => 800],
            ['category' => 'Nail Services', 'name' => 'Nail Cleaning - Hands', 'price' => 100],
            ['category' => 'Nail Services', 'name' => 'Nail Cleaning - Feet', 'price' => 120],
            ['category' => 'Nail Services', 'name' => 'Removal Charges', 'price' => 150],
            ['category' => 'Nail Services', 'name' => 'Nail Art and Charms', 'price' => 0],

            ['category' => 'Lashes Services', 'name' => 'Lash Lift', 'price' => 350],
            ['category' => 'Lashes Services', 'name' => 'Lash Tint', 'price' => 400],
            ['category' => 'Lashes Services', 'name' => 'Upper Lash and Lower Lash Perm', 'price' => 600],
            ['category' => 'Lashes Services', 'name' => 'Soft Volume', 'price' => 500],
            ['category' => 'Lashes Services', 'name' => 'Hybrid', 'price' => 600],
            ['category' => 'Lashes Services', 'name' => 'Classic', 'price' => 700],
            ['category' => 'Lashes Services', 'name' => 'Wet Look', 'price' => 800],
            ['category' => 'Lashes Services', 'name' => 'Volume', 'price' => 800],
            ['category' => 'Lashes Services', 'name' => 'Mega Volume', 'price' => 1000],
            ['category' => 'Lashes Services', 'name' => 'Lash Add-On Styles', 'price' => 200],
            ['category' => 'Lashes Services', 'name' => 'Lash Removal', 'price' => 150],
            ['category' => 'Lashes Services', 'name' => 'Lash Refill', 'price' => 200],

            ['category' => 'Pamper Collections', 'name' => 'Classic Foot Spa + Classic Manicure + Classic Pedicure', 'price' => 550],
            ['category' => 'Pamper Collections', 'name' => 'Gel Manicure + Gel Pedicure', 'price' => 700],
            ['category' => 'Pamper Collections', 'name' => 'Classic Foot Spa + Gel Manicure + Gel Pedicure', 'price' => 1000],
            ['category' => 'Pamper Collections', 'name' => 'Classic Manicure + Gel Pedicure', 'price' => 500],
            ['category' => 'Pamper Collections', 'name' => 'Manicure + Classic Foot Spa + Pedicure Cleaning Only', 'price' => 520],
            ['category' => 'Pamper Collections', 'name' => 'Classic Manicure + Gel Pedicure + Classic Foot Spa', 'price' => 850],
            ['category' => 'Pamper Collections', 'name' => 'Gel Manicure + Classic Pedicure + Classic Foot Spa', 'price' => 850],

            ['category' => 'Head and Scalp Spa', 'name' => 'Signature 45-Min Reset', 'price' => 1000],
            ['category' => 'Head and Scalp Spa', 'name' => 'Signature 60-Min Deep Release', 'price' => 1300],
            ['category' => 'Head and Scalp Spa', 'name' => 'The 90-Minute Signature Opulence', 'price' => 1500],

            ['category' => 'Paraffin Wax Therapy', 'name' => 'Hands', 'price' => 250],
            ['category' => 'Paraffin Wax Therapy', 'name' => 'Feet', 'price' => 300],

            ['category' => 'Pamper Packages', 'name' => 'Classic Pamper Package', 'price' => 550],
            ['category' => 'Pamper Packages', 'name' => 'Signature Relaxation Package', 'price' => 725],
            ['category' => 'Pamper Packages', 'name' => 'Glow & Polish Package', 'price' => 825],
            ['category' => 'Pamper Packages', 'name' => 'Premium Self-Care Package', 'price' => 825],
            ['category' => 'Pamper Packages', 'name' => 'Honey Bliss Package', 'price' => 825],
            ['category' => 'Pamper Packages', 'name' => 'Whitening Luxe Package', 'price' => 1000],
            ['category' => 'Pamper Packages', 'name' => 'Ultimate Detox Package', 'price' => 1000],
            ['category' => 'Pamper Packages', 'name' => 'VIP Spa Escape', 'price' => 1100],
            ['category' => 'Pamper Packages', 'name' => 'Royal Hands & Feet', 'price' => 900],
            ['category' => 'Pamper Packages', 'name' => 'Complete Spa Day', 'price' => 900],

            ['category' => 'Brows Services', 'name' => 'Eyebrow Lamination', 'price' => 450],
            ['category' => 'Brows Services', 'name' => 'Eyebrow Tint', 'price' => 250],
            ['category' => 'Brows Services', 'name' => 'Brow Lamination with Tint', 'price' => 550],
            ['category' => 'Brows Services', 'name' => 'Eyebrow Mapping', 'price' => 100],
            ['category' => 'Brows Services', 'name' => 'Eyebrow Shaving', 'price' => 100],
            ['category' => 'Brows Services', 'name' => 'Brow Threading', 'price' => 150],

            ['category' => 'Foot Spa Services', 'name' => "MM's Classic Foot Spa", 'price' => 350],
            ['category' => 'Foot Spa Services', 'name' => "MM's Deluxe Foot Spa with Pedicure", 'price' => 450],
            ['category' => 'Foot Spa Services', 'name' => "MM's Premium Foot Spa with Pedicure", 'price' => 550],
            ['category' => 'Foot Spa Services', 'name' => "MM's Milk & Honey Foot Ritual with Pedicure", 'price' => 650],
            ['category' => 'Foot Spa Services', 'name' => "MM's Foot Spa Detox with Pedicure", 'price' => 750],

            ['category' => 'Hand Spa Services', 'name' => "MM's Classic Hand Spa", 'price' => 300],
            ['category' => 'Hand Spa Services', 'name' => "MM's Signature Hand Spa", 'price' => 400],
            ['category' => 'Hand Spa Services', 'name' => "MM's Whitening Hand Spa", 'price' => 500],

            ['category' => 'Sauna', 'name' => 'Infrared Sauna', 'price' => 450],

            ['category' => 'Body Scrub Treatments', 'name' => "MM's Salt Glow Therapy Scrub", 'price' => 650],
            ['category' => 'Body Scrub Treatments', 'name' => 'Salt Glow Therapy + Sauna', 'price' => 850],
            ['category' => 'Body Scrub Treatments', 'name' => 'Salt Glow Therapy + Massage', 'price' => 1000],
            ['category' => 'Body Scrub Treatments', 'name' => 'Body Scrub + Sauna + Massage', 'price' => 1500],

            ['category' => 'Body Bleaching', 'name' => 'Full Body', 'price' => 3500],
            ['category' => 'Body Bleaching', 'name' => 'Full Arms & Chest', 'price' => 1500],
            ['category' => 'Body Bleaching', 'name' => 'Half Arms', 'price' => 800],
            ['category' => 'Body Bleaching', 'name' => 'Full Legs', 'price' => 2000],
            ['category' => 'Body Bleaching', 'name' => 'Half Legs', 'price' => 1200],
            ['category' => 'Body Bleaching', 'name' => 'Back & Nape', 'price' => 1500],

            ['category' => 'Waxing Services', 'name' => 'Eyebrow', 'price' => 200],
            ['category' => 'Waxing Services', 'name' => 'Upper Lip', 'price' => 150],
            ['category' => 'Waxing Services', 'name' => 'Underarm', 'price' => 250],
            ['category' => 'Waxing Services', 'name' => 'Bikini', 'price' => 500],
            ['category' => 'Waxing Services', 'name' => 'Brazilian', 'price' => 800],
            ['category' => 'Waxing Services', 'name' => 'Half Arm', 'price' => 500],
            ['category' => 'Waxing Services', 'name' => 'Full Arm', 'price' => 1000],
            ['category' => 'Waxing Services', 'name' => 'Half Leg', 'price' => 600],
            ['category' => 'Waxing Services', 'name' => 'Full Leg', 'price' => 1200],

            ['category' => 'Facial Services', 'name' => "MM's Basic Facial", 'price' => 450],
            ['category' => 'Facial Services', 'name' => 'Anti-acne Facial', 'price' => 550],
            ['category' => 'Facial Services', 'name' => 'Skin Tightening Facial', 'price' => 600],
            ['category' => 'Facial Services', 'name' => 'Skin Brightening Facial', 'price' => 700],
            ['category' => 'Facial Services', 'name' => 'Martini Royal Signature', 'price' => 850],
            ['category' => 'Facial Services', 'name' => 'Martini Mist Hydrafacial', 'price' => 1200],
            ['category' => 'Facial Services', 'name' => 'Microneedling Face', 'price' => 1500],
            ['category' => 'Facial Services', 'name' => 'Warts Removal', 'price' => 500],

            ['category' => 'Massage Services', 'name' => 'Swedish Relaxation Massage - 1 Hour', 'price' => 700],
            ['category' => 'Massage Services', 'name' => 'Swedish Relaxation Massage - 1.5 Hours', 'price' => 900],
            ['category' => 'Massage Services', 'name' => 'Swedish Relaxation Massage - 2 Hours', 'price' => 1200],
            ['category' => 'Massage Services', 'name' => 'Traditional Filipino Hilot - 1 Hour', 'price' => 800],
            ['category' => 'Massage Services', 'name' => 'Traditional Filipino Hilot - 1.5 Hours', 'price' => 1000],
            ['category' => 'Massage Services', 'name' => 'Traditional Filipino Hilot - 2 Hours', 'price' => 1200],
            ['category' => 'Massage Services', 'name' => 'Therapeutic Recovery Massage - 1 Hour', 'price' => 800],
            ['category' => 'Massage Services', 'name' => 'Therapeutic Recovery Massage - 1.5 Hours', 'price' => 1000],
            ['category' => 'Massage Services', 'name' => 'Therapeutic Recovery Massage - 2 Hours', 'price' => 1100],
            ['category' => 'Massage Services', 'name' => 'Thai Stretch Massage - 1 Hour', 'price' => 700],
            ['category' => 'Massage Services', 'name' => 'Thai Stretch Massage - 1.5 Hours', 'price' => 900],
            ['category' => 'Massage Services', 'name' => 'Thai Stretch Massage - 2 Hours', 'price' => 1100],
            ['category' => 'Massage Services', 'name' => 'Kids Massage - 1 Hour', 'price' => 350],
            ['category' => 'Massage Services', 'name' => 'Martinis Signature Massage - 1 Hour', 'price' => 1000],
            ['category' => 'Massage Services', 'name' => 'Martinis Signature Massage - 1.5 Hours', 'price' => 1300],
            ['category' => 'Massage Services', 'name' => 'Martinis Signature Massage - 2 Hours', 'price' => 1500],

            ['category' => 'Express Relief', 'name' => 'Back Massage', 'price' => 400],
            ['category' => 'Express Relief', 'name' => 'Hands & Arm Relief', 'price' => 350],
            ['category' => 'Express Relief', 'name' => 'Arms & Hand', 'price' => 350],
            ['category' => 'Express Relief', 'name' => 'Feet & Legs', 'price' => 350],
            ['category' => 'Express Relief', 'name' => 'Head Massage', 'price' => 300],
            ['category' => 'Express Relief', 'name' => 'Foot Massage', 'price' => 300],
            ['category' => 'Express Relief', 'name' => 'Foot Reflex Dagdagay', 'price' => 450],

            ['category' => 'Beauty Drip', 'name' => 'Relumins IV Push - Single Session', 'price' => 1500],
            ['category' => 'Beauty Drip', 'name' => 'Relumins IV Push - 5 Sessions', 'price' => 6900],
            ['category' => 'Beauty Drip', 'name' => 'Relumins IV Push - 10 Sessions', 'price' => 12900],
            ['category' => 'Beauty Drip', 'name' => 'Relumins Signature Glow Drip - Single Session', 'price' => 2500],
            ['category' => 'Beauty Drip', 'name' => 'Relumins Signature Glow Drip - 3 Sessions', 'price' => 6900],
            ['category' => 'Beauty Drip', 'name' => 'Relumins Signature Glow Drip - 5 Sessions', 'price' => 10500],
            ['category' => 'Beauty Drip', 'name' => 'Relumins Signature Glow Drip - 10 Sessions', 'price' => 19000],

            ['category' => 'Body Contouring', 'name' => 'Lemon Bottle Per Vial', 'price' => 5000],
            ['category' => 'Body Contouring', 'name' => 'Kabelline Per Vial', 'price' => 2500],
        ];

        foreach ($services as $service) {
            $seededService = Service::withTrashed()->updateOrCreate(
                ['name' => $service['name']],
                [
                    'category' => $service['category'],
                    'price' => $service['price'],
                    'duration' => $this->durationFromName($service['name']),
                    'session_count' => $this->sessionsFromName($service['name']),
                    'description' => $service['price'] == 0
                        ? 'Final price depends on the selected design or add-ons.'
                        : null,
                    'requires_consent' => $this->requiresConsent($service['category'], $service['name']),
                    'is_active' => true,
                ]
            );

            if ($seededService->trashed()) {
                $seededService->restore();
            }
        }
    }

    private function durationFromName(string $name): ?int
    {
        if (preg_match('/(\d+)[- ]Min(?:ute)?/i', $name, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/(\d+(?:\.\d+)?) Hours?/i', $name, $matches)) {
            return (int) round((float) $matches[1] * 60);
        }

        return null;
    }

    private function requiresConsent(string $category, string $name): bool
    {
        if (in_array($category, ['Beauty Drip', 'Body Contouring', 'Body Bleaching'], true)) {
            return true;
        }

        return in_array($name, ['Microneedling Face', 'Warts Removal'], true);
    }

    private function sessionsFromName(string $name): int
    {
        return preg_match('/(\d+) Sessions?/i', $name, $matches)
            ? max(1, (int) $matches[1])
            : 1;
    }
}
