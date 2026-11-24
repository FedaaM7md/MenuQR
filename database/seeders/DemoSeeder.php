<?php

/*
 * MenuQR - DemoSeeder.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 *
 * DEMO DATA ONLY. Zaytouna Kitchen is a fictional Levantine restaurant in
 * Nasr City. Every name, price and order is sample content, not real data.
 */

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuOption;
use App\Models\OptionGroup;
use App\Models\Order;
use App\Models\OrderSequence;
use App\Models\RestaurantTable;
use App\Models\Setting;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'Demo1234!';

    public function run(): void
    {
        $password = (string) config('menuqr.demo.password', self::DEMO_PASSWORD);

        Setting::current()->update([
            'restaurant_name_ar' => 'زيتونة — مطبخ شامي',
            'restaurant_name_en' => 'Zaytouna Kitchen',
            'tagline_ar' => 'مطبخ شامي في مدينة نصر',
            'tagline_en' => 'Levantine kitchen in Nasr City',
            'address_ar' => 'مدينة نصر، القاهرة',
            'address_en' => 'Nasr City, Cairo',
            'currency' => 'EGP',
            'tax_percent' => 1400,
            'service_percent' => 1200,
            'whatsapp_number' => '+201000000000',
            'hours' => collect(Setting::defaultHours())
                ->map(fn () => ['open' => '00:00', 'close' => '23:59', 'closed' => false])
                ->all(),
            'is_accepting_orders' => true,
        ]);

        foreach ([
            ['name' => 'سارة عبد الرحمن', 'email' => User::demoEmail('admin'), 'role' => User::ROLE_ADMIN],
            ['name' => 'محمود فتحي', 'email' => User::demoEmail('cashier'), 'role' => User::ROLE_CASHIER],
            ['name' => 'كريم السيد', 'email' => User::demoEmail('kitchen'), 'role' => User::ROLE_KITCHEN],
        ] as $member) {
            User::updateOrCreate(
                ['email' => $member['email']],
                [
                    'name' => $member['name'],
                    'role' => $member['role'],
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'is_demo' => true,
                    'locale' => 'ar',
                ],
            );
        }

        $categories = [
            ['key' => 'cold', 'name_ar' => 'مقبلات باردة', 'name_en' => 'Cold mezze', 'sort_order' => 1],
            ['key' => 'hot', 'name_ar' => 'مقبلات ساخنة', 'name_en' => 'Hot mezze', 'sort_order' => 2],
            ['key' => 'grill', 'name_ar' => 'مشاوٍ', 'name_en' => 'Grills', 'sort_order' => 3],
            ['key' => 'sandwich', 'name_ar' => 'ساندويتشات وشاورما', 'name_en' => 'Sandwiches & shawarma', 'sort_order' => 4],
            ['key' => 'manakish', 'name_ar' => 'مناقيش', 'name_en' => 'Manakish', 'sort_order' => 5],
            ['key' => 'dessert', 'name_ar' => 'حلويات', 'name_en' => 'Desserts', 'sort_order' => 6],
            ['key' => 'drink', 'name_ar' => 'عصائر ومشروبات', 'name_en' => 'Juices & drinks', 'sort_order' => 7],
        ];

        $categoryModels = [];
        foreach ($categories as $row) {
            $categoryModels[$row['key']] = Category::updateOrCreate(
                ['name_ar' => $row['name_ar']],
                ['name_en' => $row['name_en'], 'sort_order' => $row['sort_order'], 'is_active' => true],
            );
        }

        $items = [
            ['cold', 'حمص بالطحينة', 'Hummus with tahini', 'حمص كريمي بزيت الزيتون والطحينة', 'Creamy chickpeas, olive oil and tahini', 4500, true, 'سمسم', 'Sesame'],
            ['cold', 'متبل باذنجان', 'Moutabal', 'باذنجان مشوي مع طحينة ولبن', 'Smoked aubergine with tahini and yogurt', 5000, true, 'ألبان، سمسم', 'Dairy, sesame'],
            ['cold', 'فتوش', 'Fattoush', 'خضار طازجة مع سماق وخبز محمص', 'Fresh greens with sumac and toasted bread', 5500, false, 'قمح', 'Wheat'],
            ['cold', 'تبولة', 'Tabbouleh', 'بقدونس وبرغل وليمون', 'Parsley, bulgur and lemon', 5000, false, 'قمح', 'Wheat'],
            ['cold', 'لبنة بالزيت', 'Labneh with olive oil', 'لبنة مصفاة مع زيت زيتون', 'Strained yogurt with olive oil', 5500, false, 'ألبان', 'Dairy'],
            ['cold', 'ورق عنب', 'Stuffed vine leaves', 'ورق عنب محشي أرز وليمون', 'Vine leaves stuffed with rice and lemon', 6000, false, null, null],
            ['cold', 'مخللات مشكلة', 'Mixed pickles', 'مخللات بيتية', 'House pickles', 2500, false, null, null],

            ['hot', 'فلافل (4 قطع)', 'Falafel (4 pieces)', 'فلافل مقرمشة مع طحينة', 'Crisp falafel with tahini', 3500, true, 'سمسم', 'Sesame'],
            ['hot', 'كبة مقلية (3 قطع)', 'Fried kibbeh (3 pieces)', 'كبة برغل محشية لحم', 'Bulgur shells filled with beef', 6500, false, 'قمح', 'Wheat'],
            ['hot', 'بطاطس مقلية', 'French fries', 'بطاطس مقرمشة', 'Crisp potato fries', 4000, false, null, null],
            ['hot', 'جبنة مشللة', 'Shanklish cheese', 'جبنة مشللة مع زعتر', 'Shanklish with zaatar', 5500, false, 'ألبان', 'Dairy'],
            ['hot', 'سجق حلبي', 'Aleppo sausage', 'سجق حلبي مع طماطم', 'Aleppo sausage with tomato', 7000, false, 'قمح', 'Wheat'],
            ['hot', 'رقائق عجين بالجبن', 'Cheese pastry', 'عجين رقيق محشو جبن', 'Thin pastry filled with cheese', 6000, false, 'ألبان، قمح', 'Dairy, wheat'],

            ['grill', 'شيش طاووق', 'Chicken shish', 'دجاج متبل مع صوص ثوم', 'Marinated chicken with garlic sauce', 16500, true, 'ألبان', 'Dairy'],
            ['grill', 'كباب حلبي', 'Aleppo kebab', 'لحم مفروم متبل مع أرز', 'Seasoned minced beef with rice', 18500, true, null, null],
            ['grill', 'كفتة مشوية', 'Grilled kofta', 'كفتة لحم مع خبز وأرز', 'Beef kofta with bread and rice', 17500, false, null, null],
            ['grill', 'ريش غنم', 'Lamb chops', 'ريش مشوية على الفحم', 'Charcoal-grilled lamb chops', 24500, false, null, null],
            ['grill', 'فروج مشوي', 'Grilled chicken', 'فروج متبل مع صوص ثوم', 'Marinated chicken with garlic sauce', 15500, true, 'ألبان', 'Dairy'],
            ['grill', 'سمك بلطي مشوي', 'Grilled tilapia', 'سمك بلطي طازج مع ليمون', 'Fresh tilapia with lemon', 19500, false, 'سمك', 'Fish'],
            ['grill', 'مشاوي مشكلة', 'Mixed grill', 'تشكيلة مشاوي لشخصين', 'Grill platter for two', 38000, false, 'ألبان', 'Dairy'],

            ['sandwich', 'شاورما دجاج', 'Chicken shawarma', 'شاورما دجاج مع صوص ثوم', 'Chicken shawarma with garlic sauce', 9500, true, 'قمح، ألبان', 'Wheat, dairy'],
            ['sandwich', 'شاورما لحم', 'Beef shawarma', 'شاورما لحم مع طحينة', 'Beef shawarma with tahini', 11500, true, 'قمح، ألبان، سمسم', 'Wheat, dairy, sesame'],
            ['sandwich', 'ساندويتش فلافل', 'Falafel sandwich', 'فلافل مع خضار وطحينة', 'Falafel with salad and tahini', 4500, false, 'قمح، سمسم', 'Wheat, sesame'],
            ['sandwich', 'ساندويتش كباب', 'Kebab sandwich', 'كباب مع خضار', 'Kebab with salad', 12000, false, 'قمح', 'Wheat'],
            ['sandwich', 'ساندويتش شيش طاووق', 'Shish taouk sandwich', 'شيش طاووق مع صوص ثوم', 'Chicken shish with garlic sauce', 10500, false, 'قمح، ألبان', 'Wheat, dairy'],
            ['sandwich', 'برجر زيتونة', 'Zaytouna burger', 'برجر لحم مع جبنة وخس', 'Beef burger with cheese and lettuce', 11000, false, 'قمح، ألبان، بيض', 'Wheat, dairy, egg'],

            ['manakish', 'منقوشة زعتر', 'Zaatar manakish', 'زعتر وزيت زيتون', 'Zaatar and olive oil', 4000, true, 'قمح، سمسم', 'Wheat, sesame'],
            ['manakish', 'منقوشة جبنة', 'Cheese manakish', 'جبنة عكاوي', 'Akkawi cheese', 5500, true, 'قمح، ألبان', 'Wheat, dairy'],
            ['manakish', 'منقوشة لحم بعجين', 'Meat manakish', 'لحم مفروم وطماطم', 'Minced beef and tomato', 7000, false, 'قمح', 'Wheat'],
            ['manakish', 'منقوشة سبانخ', 'Spinach manakish', 'سبانخ وبصل وسماق', 'Spinach, onion and sumac', 5000, false, 'قمح، ألبان', 'Wheat, dairy'],
            ['manakish', 'منقوشة محمرة', 'Muhammara manakish', 'فلفل أحمر وجوز', 'Red pepper and walnut', 6000, false, 'قمح، مكسرات', 'Wheat, nuts'],

            ['dessert', 'كنافة نابلسية', 'Nabulsi knafeh', 'كنافة بالجبنة والقطر', 'Knafeh with cheese and syrup', 7500, true, 'قمح، ألبان، مكسرات', 'Wheat, dairy, nuts'],
            ['dessert', 'بقلاوة (3 قطع)', 'Baklava (3 pieces)', 'بقلاوة بالفستق', 'Pistachio baklava', 6500, false, 'قمح، مكسرات', 'Wheat, nuts'],
            ['dessert', 'مهلبية بالفستق', 'Muhallebi', 'مهلبية بالحليب والفستق', 'Milk pudding with pistachio', 5000, false, 'ألبان، مكسرات', 'Dairy, nuts'],
            ['dessert', 'أم علي', 'Om Ali', 'طبق شرقي بالحليب والمكسرات', 'Egyptian bread pudding with milk and nuts', 7000, false, 'ألبان، مكسرات', 'Dairy, nuts'],

            ['drink', 'عصير ليمون بالنعناع', 'Mint lemonade', 'ليمون طازج مع نعناع', 'Fresh lemon with mint', 3500, true, null, null],
            ['drink', 'عصير برتقال طازج', 'Fresh orange juice', 'برتقال معصور', 'Freshly squeezed orange', 4000, false, null, null],
            ['drink', 'عصير مانجو', 'Mango juice', 'مانجو طازج', 'Fresh mango', 4500, true, null, null],
            ['drink', 'مشروبات غازية', 'Soft drink', 'علبة 330 مل', '330 ml can', 2500, false, null, null],
            ['drink', 'مياه معدنية', 'Mineral water', 'زجاجة 600 مل', '600 ml bottle', 1500, false, null, null],
            ['drink', 'شاي بالنعناع', 'Mint tea', 'شاي مصري مع نعناع', 'Egyptian tea with mint', 2000, false, null, null],
            ['drink', 'قهوة عربية', 'Arabic coffee', 'قهوة محوجة', 'Spiced Arabic coffee', 3000, false, null, null],
        ];

        foreach ($items as $index => [$categoryKey, $nameAr, $nameEn, $descAr, $descEn, $price, $featured, $allergensAr, $allergensEn]) {
            MenuItem::updateOrCreate(
                ['name_ar' => $nameAr],
                [
                    'category_id' => $categoryModels[$categoryKey]->id,
                    'name_en' => $nameEn,
                    'description_ar' => $descAr,
                    'description_en' => $descEn,
                    'allergens_ar' => $allergensAr,
                    'allergens_en' => $allergensEn,
                    'price_minor' => $price,
                    'is_available' => true,
                    'is_featured' => $featured,
                    'sort_order' => $index,
                ],
            );
        }

        $this->seedOptions();
        $this->seedTables();

        $this->seedDemoOrders();

        $this->command?->warn('Demo data seeded. Demo password: '.$password.' — replace before any real use.');
    }

    private function seedOptions(): void
    {
        $chicken = MenuItem::where('name_ar', 'فروج مشوي')->first();
        if ($chicken) {
            $portion = OptionGroup::updateOrCreate(
                ['menu_item_id' => $chicken->id, 'name_ar' => 'الحجم'],
                ['name_en' => 'Portion', 'min_select' => 1, 'max_select' => 1, 'sort_order' => 1],
            );
            foreach ([['نصف فروج', 'Half chicken', 0], ['فروج كامل', 'Whole chicken', 13000]] as $i => [$ar, $en, $delta]) {
                MenuOption::updateOrCreate(['option_group_id' => $portion->id, 'name_ar' => $ar], ['name_en' => $en, 'price_delta_minor' => $delta, 'is_available' => true, 'sort_order' => $i]);
            }
            $extras = OptionGroup::updateOrCreate(
                ['menu_item_id' => $chicken->id, 'name_ar' => 'إضافات'],
                ['name_en' => 'Extras', 'min_select' => 0, 'max_select' => 3, 'sort_order' => 2],
            );
            foreach ([['أرز إضافي', 'Extra rice', 2000], ['خبز إضافي', 'Extra bread', 500], ['صوص ثوم', 'Garlic sauce', 800]] as $i => [$ar, $en, $delta]) {
                MenuOption::updateOrCreate(['option_group_id' => $extras->id, 'name_ar' => $ar], ['name_en' => $en, 'price_delta_minor' => $delta, 'is_available' => true, 'sort_order' => $i]);
            }
        }

        $shawarma = MenuItem::where('name_ar', 'شاورما دجاج')->first();
        if ($shawarma) {
            $size = OptionGroup::updateOrCreate(
                ['menu_item_id' => $shawarma->id, 'name_ar' => 'الحجم'],
                ['name_en' => 'Size', 'min_select' => 1, 'max_select' => 1, 'sort_order' => 1],
            );
            foreach ([['عادي', 'Regular', 0], ['كبير', 'Large', 2500]] as $i => [$ar, $en, $delta]) {
                MenuOption::updateOrCreate(['option_group_id' => $size->id, 'name_ar' => $ar], ['name_en' => $en, 'price_delta_minor' => $delta, 'is_available' => true, 'sort_order' => $i]);
            }
            $extras = OptionGroup::updateOrCreate(
                ['menu_item_id' => $shawarma->id, 'name_ar' => 'إضافات'],
                ['name_en' => 'Extras', 'min_select' => 0, 'max_select' => 2, 'sort_order' => 2],
            );
            foreach ([['جبنة', 'Cheese', 1000], ['صوص ثوم', 'Garlic sauce', 800], ['حار', 'Spicy', 0]] as $i => [$ar, $en, $delta]) {
                MenuOption::updateOrCreate(['option_group_id' => $extras->id, 'name_ar' => $ar], ['name_en' => $en, 'price_delta_minor' => $delta, 'is_available' => true, 'sort_order' => $i]);
            }
        }

        $manakish = MenuItem::where('name_ar', 'منقوشة جبنة')->first();
        if ($manakish) {
            $extras = OptionGroup::updateOrCreate(
                ['menu_item_id' => $manakish->id, 'name_ar' => 'إضافات'],
                ['name_en' => 'Extras', 'min_select' => 0, 'max_select' => 3, 'sort_order' => 1],
            );
            foreach ([['زعتر', 'Zaatar', 500], ['زيتون', 'Olives', 700], ['طماطم', 'Tomato', 500]] as $i => [$ar, $en, $delta]) {
                MenuOption::updateOrCreate(['option_group_id' => $extras->id, 'name_ar' => $ar], ['name_en' => $en, 'price_delta_minor' => $delta, 'is_available' => true, 'sort_order' => $i]);
            }
        }

        $mango = MenuItem::where('name_ar', 'عصير مانجو')->first();
        if ($mango) {
            $size = OptionGroup::updateOrCreate(
                ['menu_item_id' => $mango->id, 'name_ar' => 'الحجم'],
                ['name_en' => 'Size', 'min_select' => 1, 'max_select' => 1, 'sort_order' => 1],
            );
            foreach ([['صغير', 'Small', 0], ['كبير', 'Large', 1000]] as $i => [$ar, $en, $delta]) {
                MenuOption::updateOrCreate(['option_group_id' => $size->id, 'name_ar' => $ar], ['name_en' => $en, 'price_delta_minor' => $delta, 'is_available' => true, 'sort_order' => $i]);
            }
        }

        $knafeh = MenuItem::where('name_ar', 'كنافة نابلسية')->first();
        if ($knafeh) {
            $extras = OptionGroup::updateOrCreate(
                ['menu_item_id' => $knafeh->id, 'name_ar' => 'إضافات'],
                ['name_en' => 'Extras', 'min_select' => 0, 'max_select' => 2, 'sort_order' => 1],
            );
            foreach ([['فستق إضافي', 'Extra pistachio', 1500], ['قطر إضافي', 'Extra syrup', 500]] as $i => [$ar, $en, $delta]) {
                MenuOption::updateOrCreate(['option_group_id' => $extras->id, 'name_ar' => $ar], ['name_en' => $en, 'price_delta_minor' => $delta, 'is_available' => true, 'sort_order' => $i]);
            }
        }
    }

    private function seedTables(): void
    {
        foreach ([
            ['T1', 2, 1], ['T2', 2, 2], ['T3', 4, 3],
            ['T4', 4, 4], ['T5', 6, 5], ['T6', 8, 6],
        ] as [$name, $seats, $sort]) {
            RestaurantTable::updateOrCreate(
                ['name' => $name],
                ['seats' => $seats, 'sort_order' => $sort, 'is_active' => true],
            );
        }
    }

    private function seedDemoOrders(): void
    {
        if (Order::query()->exists()) {
            return;
        }

        $tables = RestaurantTable::query()->get()->keyBy('name');
        $items = MenuItem::query()->with('optionGroups.options')->get()->keyBy('name_ar');
        $settings = Setting::current();
        $year = (int) now()->format('Y');
        $sequence = 0;

        $definitions = [
            ['table' => 'T2', 'type' => 'dine_in', 'status' => 'new', 'minutes' => 9, 'customer' => 'منى', 'notes' => 'بدون بصل على الحمص', 'lines' => [
                ['حمص بالطحينة', 2, []],
                ['شيش طاووق', 1, ['صوص ثوم']],
            ]],
            ['table' => 'T4', 'type' => 'dine_in', 'status' => 'preparing', 'minutes' => 18, 'customer' => null, 'notes' => null, 'lines' => [
                ['كفتة مشوية', 1, []],
                ['عصير مانجو', 2, ['كبير']],
                ['فتوش', 1, []],
            ]],
            ['table' => 'T1', 'type' => 'takeaway', 'status' => 'ready', 'minutes' => 27, 'customer' => 'أحمد', 'notes' => null, 'lines' => [
                ['شاورما دجاج', 1, ['كبير', 'جبنة']],
                ['مياه معدنية', 1, []],
            ]],
            ['table' => 'T5', 'type' => 'dine_in', 'status' => 'served', 'minutes' => 95, 'customer' => null, 'notes' => null, 'lines' => [
                ['كباب حلبي', 2, []],
                ['كنافة نابلسية', 2, []],
                ['عصير ليمون بالنعناع', 2, []],
            ]],
            ['table' => 'T3', 'type' => 'dine_in', 'status' => 'served', 'minutes' => 210, 'customer' => 'نادر', 'notes' => null, 'lines' => [
                ['فروج مشوي', 1, ['فروج كامل', 'أرز إضافي']],
                ['منقوشة جبنة', 1, ['زعتر']],
            ]],
            ['table' => 'T6', 'type' => 'takeaway', 'status' => 'cancelled', 'minutes' => 55, 'customer' => null, 'notes' => null, 'lines' => [
                ['فلافل (4 قطع)', 1, []],
                ['شاي بالنعناع', 1, []],
            ]],
        ];

        foreach ($definitions as $definition) {
            $sequence++;
            $placedAt = Carbon::now()->subMinutes($definition['minutes']);
            $lines = [];
            $subtotal = 0;

            foreach ($definition['lines'] as [$name, $quantity, $optionNames]) {
                /** @var MenuItem $item */
                $item = $items->get($name);
                $delta = 0;
                $choices = [];

                foreach ($item->optionGroups as $group) {
                    foreach ($group->options as $option) {
                        if (in_array($option->name_ar, $optionNames, true)) {
                            $delta += $option->price_delta_minor;
                            $choices[] = [$group, $option];
                        }
                    }
                }

                $unit = $item->price_minor + $delta;
                $lineTotal = $unit * $quantity;
                $subtotal += $lineTotal;
                $lines[] = [$item, $quantity, $unit, $lineTotal, $choices];
            }

            $tax = Money::percentOf($subtotal, $settings->tax_percent);
            $service = Money::percentOf($subtotal, $settings->service_percent);
            $status = $definition['status'];

            $order = Order::create([
                'number' => Order::formatNumber($year, $sequence),
                'restaurant_table_id' => $tables->get($definition['table'])?->id,
                'type' => $definition['type'],
                'status' => $status,
                'customer_name' => $definition['customer'],
                'notes' => $definition['notes'],
                'subtotal_minor' => $subtotal,
                'tax_percent' => $settings->tax_percent,
                'tax_minor' => $tax,
                'service_percent' => $settings->service_percent,
                'service_minor' => $service,
                'total_minor' => $subtotal + $tax + $service,
                'placed_at' => $placedAt,
                'preparing_at' => in_array($status, ['preparing', 'ready', 'served'], true) ? $placedAt->copy()->addMinutes(4) : null,
                'ready_at' => in_array($status, ['ready', 'served'], true) ? $placedAt->copy()->addMinutes(16) : null,
                'served_at' => $status === 'served' ? $placedAt->copy()->addMinutes(28) : null,
                'cancelled_at' => $status === 'cancelled' ? $placedAt->copy()->addMinutes(6) : null,
            ]);

            foreach ($lines as [$item, $quantity, $unit, $lineTotal, $choices]) {
                $line = $order->items()->create([
                    'menu_item_id' => $item->id,
                    'name_ar' => $item->name_ar,
                    'name_en' => $item->name_en,
                    'unit_price_minor' => $unit,
                    'quantity' => $quantity,
                    'line_total_minor' => $lineTotal,
                ]);

                foreach ($choices as [$group, $option]) {
                    $line->options()->create([
                        'group_name_ar' => $group->name_ar,
                        'group_name_en' => $group->name_en,
                        'option_name_ar' => $option->name_ar,
                        'option_name_en' => $option->name_en,
                        'price_delta_minor' => $option->price_delta_minor,
                    ]);
                }
            }
        }

        // Enough historical orders for the charts and reports to look lived-in.
        $historyNames = [
            'حمص بالطحينة', 'متبل باذنجان', 'فتوش', 'تبولة', 'فلافل (4 قطع)',
            'كبة مقلية (3 قطع)', 'شيش طاووق', 'كباب حلبي', 'كفتة مشوية',
            'شاورما دجاج', 'شاورما لحم', 'منقوشة جبنة', 'منقوشة زعتر',
            'كنافة نابلسية', 'بقلاوة (3 قطع)', 'عصير مانجو', 'عصير ليمون بالنعناع',
        ];
        $customers = ['منى', 'أحمد', 'هالة', 'يوسف', 'مريم', 'عمرو', 'سلمى', 'خالد', null, null];
        $tableNames = array_keys($tables->all());

        for ($i = 0; $i < 30; $i++) {
            $sequence++;
            $placedAt = Carbon::now()
                ->subDays(1 + ($i % 44))
                ->setTime(12 + ($i % 10), 10 * ($i % 6));
            $status = $i % 11 === 0 ? 'cancelled' : 'served';
            $subtotal = 0;
            $lines = [];
            $lineCount = 2 + ($i % 2);

            for ($j = 0; $j < $lineCount; $j++) {
                $name = $historyNames[($i * 3 + $j * 5) % count($historyNames)];
                /** @var MenuItem $item */
                $item = $items->get($name);
                $quantity = 1 + (($i + $j) % 2);
                $lineTotal = $item->price_minor * $quantity;
                $subtotal += $lineTotal;
                $lines[] = [$item, $quantity, $item->price_minor, $lineTotal];
            }

            $tax = Money::percentOf($subtotal, $settings->tax_percent);
            $service = Money::percentOf($subtotal, $settings->service_percent);

            $order = Order::create([
                'number' => Order::formatNumber($year, $sequence),
                'restaurant_table_id' => $tables->get($tableNames[$i % count($tableNames)])?->id,
                'type' => $i % 4 === 0 ? 'takeaway' : 'dine_in',
                'status' => $status,
                'customer_name' => $customers[$i % count($customers)],
                'notes' => $i % 7 === 0 ? 'بدون بصل' : null,
                'subtotal_minor' => $subtotal,
                'tax_percent' => $settings->tax_percent,
                'tax_minor' => $tax,
                'service_percent' => $settings->service_percent,
                'service_minor' => $service,
                'total_minor' => $subtotal + $tax + $service,
                'placed_at' => $placedAt,
                'preparing_at' => $placedAt->copy()->addMinutes(4),
                'ready_at' => $placedAt->copy()->addMinutes(16),
                'served_at' => $status === 'served' ? $placedAt->copy()->addMinutes(28) : null,
                'cancelled_at' => $status === 'cancelled' ? $placedAt->copy()->addMinutes(6) : null,
            ]);

            foreach ($lines as [$item, $quantity, $unit, $lineTotal]) {
                $order->items()->create([
                    'menu_item_id' => $item->id,
                    'name_ar' => $item->name_ar,
                    'name_en' => $item->name_en,
                    'unit_price_minor' => $unit,
                    'quantity' => $quantity,
                    'line_total_minor' => $lineTotal,
                ]);
            }
        }

        OrderSequence::updateOrCreate(['year' => $year], ['last_number' => $sequence]);
    }
}
