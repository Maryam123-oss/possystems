<?php
$services = [
    [
        'slug' => 'maintenance',
        'name' => 'الصيانة',
        'description' => 'دعم فني دوري لضمان استقرار نظام نقاط البيع.',
        'items' => [
            [
                'name' => 'صيانة دورية',
                'image' => 'assets/images/service-maintenance.jpg',
                'features' => ['فحص شامل', 'تحديثات أمان', 'تقارير أداء'],
                'details' => 'خطط صيانة شهرية أو ربع سنوية تتضمن فحص الأداء والتحديثات.'
            ],
            [
                'name' => 'استجابة طارئة',
                'image' => 'assets/images/service-emergency.jpg',
                'features' => ['دعم 24/7', 'تدخل سريع', 'مهندسون متخصصون'],
                'details' => 'تدخل فوري عند الأعطال الحرجة مع فريق طوارئ متخصص.'
            ]
        ]
    ],
    [
        'slug' => 'installation',
        'name' => 'التركيب',
        'description' => 'تركيب أجهزة وبرمجيات نقاط البيع وفق أفضل الممارسات.',
        'items' => [
            [
                'name' => 'تركيب أجهزة',
                'image' => 'assets/images/service-hardware.jpg',
                'features' => ['تهيئة كاملة', 'اختبارات قبول', 'تدريب سريع'],
                'details' => 'إعداد وتجهيز الأجهزة وربطها بالشبكة مع اختبارات قبول تشغيلية.'
            ],
            [
                'name' => 'نشر البرمجيات',
                'image' => 'assets/images/service-software.jpg',
                'features' => ['تهيئة قواعد البيانات', 'إعداد صلاحيات', 'تخصيص الواجهة'],
                'details' => 'تثبيت التطبيقات وضبط الصلاحيات وتخصيص الواجهة حسب الهوية البصرية.'
            ]
        ]
    ]
];

$products = [
    [
        'slug' => 'hardware',
        'name' => 'أجهزة',
        'items' => [
            [
                'name' => 'جهاز نقاط بيع متكامل',
                'image' => 'assets/images/product-pos.jpg',
                'price' => '1200$',
                'specs' => ['شاشة لمس 15"', 'قارئ باركود', 'طابعة فواتير'],
                'details' => 'حل متكامل مناسب للمتاجر والمطاعم مع دعم كامل للتقارير.'
            ],
            [
                'name' => 'طابعة إيصالات حرارية',
                'image' => 'assets/images/product-printer.jpg',
                'price' => '180$',
                'specs' => ['عرض 80مم', 'USB/شبكة', 'سرعة عالية'],
                'details' => 'طابعة موثوقة وسريعة لفواتير نقاط البيع.'
            ]
        ]
    ],
    [
        'slug' => 'software',
        'name' => 'برمجيات',
        'items' => [
            [
                'name' => 'نظام إدارة مخزون',
                'image' => 'assets/images/product-inventory.jpg',
                'price' => 'اشتراك شهري',
                'specs' => ['تنبيهات نقص', 'تقارير لحظية', 'دعم متعدد الفروع'],
                'details' => 'إدارة المخزون والمستودعات مع تنبيهات ذكية وتكامل مع نقاط البيع.'
            ],
            [
                'name' => 'لوحة تقارير تنفيذية',
                'image' => 'assets/images/product-dashboard.jpg',
                'price' => 'اشتراك شهري',
                'specs' => ['مؤشرات الأداء', 'تقارير مالية', 'صلاحيات granular'],
                'details' => 'لوحات معلومات تفاعلية للمديرين مع مستويات صلاحيات متعددة.'
            ]
        ]
    ]
];

$posts = [
    [
        'title' => 'إطلاق إصدار جديد لنظام نقاط البيع',
        'image' => 'assets/images/blog-release.jpg',
        'excerpt' => 'ميزات جديدة في التقارير وأداء أعلى.',
        'date' => '2025-12-01'
    ],
    [
        'title' => 'أفضل ممارسات حماية بيانات المتاجر',
        'image' => 'assets/images/blog-security.jpg',
        'excerpt' => 'دليل مختصر لتطبيق إجراءات الأمان في نقاط البيع.',
        'date' => '2025-11-20'
    ]
];

// قراءة إعدادات الموقع من قاعدة البيانات
$settings = [];
try {
    if (isset($pdo)) {
        $settingsStmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settingsRows = $settingsStmt->fetchAll();
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch (Exception $e) {
    // في حالة عدم وجود الجدول، استخدم القيم الافتراضية
}

$about = [
    'headline' => $settings['about_headline'] ?? 'حلول نقاط بيع موثوقة تدعم نمو عملك',
    'description' => $settings['about_description'] ?? 'نقدم أنظمة نقاط بيع متكاملة تغطي الأجهزة والبرمجيات مع دعم فني مستمر ولوحات تحكم إدارية مرنة.',
    'phone' => $settings['contact_phone'] ?? '+966-555-123456',
    'email' => $settings['contact_email'] ?? 'info@example.com',
    'address' => $settings['contact_address'] ?? 'الرياض - المملكة العربية السعودية'
];

$footer_description = $settings['footer_description'] ?? 'حلول نقاط بيع متكاملة للأجهزة والبرمجيات والدعم الفني.';

$users = [
    [
        'name' => 'مدير النظام',
        'email' => 'admin@example.com',
        'role' => 'admin'
    ],
    [
        'name' => 'موظف محتوى',
        'email' => 'staff@example.com',
        'role' => 'staff'
    ]
];


