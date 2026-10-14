<?php

namespace Database\Seeders;

use App\Models\Bandwidth;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@shellnet.local',
        ]);

        $routers = Router::factory(3)->create([
            'status' => 'Online',
            'enabled' => true,
        ]);

        Router::factory()->offline()->create(['enabled' => true]);

        $bandwidths = collect([
            ['name' => '2Mbps/1Mbps',   'rate_down' => 2,  'rate_up' => 1],
            ['name' => '5Mbps/2Mbps',   'rate_down' => 5,  'rate_up' => 2],
            ['name' => '10Mbps/5Mbps',  'rate_down' => 10, 'rate_up' => 5],
            ['name' => '20Mbps/10Mbps', 'rate_down' => 20, 'rate_up' => 10],
        ])->map(fn ($b) => Bandwidth::create([
            ...$b,
            'rate_down_unit' => 'Mbps',
            'rate_up_unit' => 'Mbps',
        ]));

        $plans = collect([
            ['name' => 'Daily Hotspot',   'type' => 'Hotspot', 'price' => 1.50,  'validity' => 1,  'bandwidth_id' => $bandwidths[0]->id, 'router_id' => $routers[0]->id],
            ['name' => 'Weekly Hotspot',  'type' => 'Hotspot', 'price' => 7.00,  'validity' => 7,  'bandwidth_id' => $bandwidths[1]->id, 'router_id' => $routers[0]->id],
            ['name' => 'Monthly Home',    'type' => 'PPPoE',   'price' => 25.00, 'validity' => 30, 'bandwidth_id' => $bandwidths[2]->id, 'router_id' => $routers[1]->id],
            ['name' => 'Monthly Office',  'type' => 'PPPoE',   'price' => 45.00, 'validity' => 30, 'bandwidth_id' => $bandwidths[3]->id, 'router_id' => $routers[1]->id],
            ['name' => 'Quarterly Home',  'type' => 'PPPoE',   'price' => 65.00, 'validity' => 90, 'bandwidth_id' => $bandwidths[2]->id, 'router_id' => $routers[2]->id],
            ['name' => 'VPN Business',    'type' => 'VPN',     'price' => 15.00, 'validity' => 30, 'bandwidth_id' => $bandwidths[1]->id, 'router_id' => $routers[2]->id],
        ])->map(fn ($p) => Plan::create([
            ...$p,
            'validity_unit' => 'Days',
            'is_limited' => false,
            'limit_type' => 'Time_Limit',
            'shared_users' => 1,
            'is_radius' => false,
            'enabled' => true,
            'prepaid' => true,
            'plan_type' => 'Personal',
            'device' => 'mikrotik',
        ]));

        $customers = Customer::factory(60)->active()->create();

        $customers->each(function (Customer $customer) use ($plans): void {
            $plan = $plans->random();
            $rechargedAt = now()->subDays(rand(0, 25));
            $expiresAt = $rechargedAt->copy()->addDays($plan->validity);

            Subscription::create([
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'router_id' => $plan->router_id,
                'router_name' => Router::find($plan->router_id)?->name ?? 'Router',
                'type' => $plan->type,
                'method' => collect(['cash', 'card', 'paystack'])->random(),
                'status' => $expiresAt->isFuture() ? 'on' : 'expired',
                'recharged_at' => $rechargedAt,
                'expires_at' => $expiresAt,
            ]);

            Transaction::create([
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'plan_name' => $plan->name,
                'price' => $plan->price,
                'type' => $plan->type,
                'method' => collect(['cash', 'card', 'paystack'])->random(),
                'router_id' => $plan->router_id,
                'router_name' => Router::find($plan->router_id)?->name ?? 'Router',
                'recharged_at' => $rechargedAt,
                'expires_at' => $expiresAt,
                'created_at' => $rechargedAt,
                'updated_at' => $rechargedAt,
            ]);
        });

        // Backfill 11 months of historical transactions
        for ($m = 11; $m >= 1; $m--) {
            $count = rand(15, 40);
            for ($i = 0; $i < $count; $i++) {
                $customer = $customers->random();
                $plan = $plans->random();
                $date = now()->subMonths($m)->setDay(rand(1, 28));

                Transaction::create([
                    'customer_id' => $customer->id,
                    'username' => $customer->username,
                    'plan_name' => $plan->name,
                    'price' => $plan->price,
                    'type' => $plan->type,
                    'method' => collect(['cash', 'card', 'paystack'])->random(),
                    'router_id' => $plan->router_id,
                    'router_name' => Router::find($plan->router_id)?->name ?? 'Router',
                    'recharged_at' => $date,
                    'expires_at' => $date->copy()->addDays($plan->validity),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }
}
