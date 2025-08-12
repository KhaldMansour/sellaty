<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        if (Page::count() > 0) {
            return;
        }

        $pages = [
            [
                'slug' => 'about-us',
                'title' => json_encode([
                    'en' => 'About Us',
                    'ar' => 'من نحن',
                ]),
                'content' => json_encode([
                    'en' => '<p>This is the About Us page.</p>',
                    'ar' => '<p>هذه صفحة من نحن.</p>',
                ]),
            ],
            [
                'slug' => 'disclaimer',
                'title' => json_encode([
                    'en' => 'Disclaimer',
                    'ar' => 'إخلاء المسؤولية',
                ]),
                'content' => json_encode([
                    'en' => '<p>This is the Disclaimer page.</p>',
                    'ar' => '<p>هذه صفحة إخلاء المسؤولية.</p>',
                ]),
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => json_encode([
                    'en' => 'Terms and Conditions',
                    'ar' => 'الشروط والأحكام',
                ]),
                'content' => json_encode([
                    'en' => '<p>This is the Terms and Conditions page.</p>',
                    'ar' => '<p>هذه صفحة الشروط والأحكام.</p>',
                ]),
            ],
            [
                'slug' => 'contact-us',
                'title' => json_encode([
                    'en' => 'Contact Us',
                    'ar' => 'اتصل بنا',
                ]),
                'content' => json_encode([
                    'en' => '<p>Contact us at support@example.com or fill out the form below.</p>',
                    'ar' => '<p>اتصل بنا على support@example.com أو املأ النموذج أدناه.</p>',
                ]),
            ],
            [
                'slug' => 'privacy-policy',
                'title' => json_encode([
                    'en' => 'Privacy Policy',
                    'ar' => 'سياسة الخصوصية',
                ]),
                'content' => json_encode([
                    'en' => '<p>Effective Date: ' . date('Y-m-d') . '</p>
                             <p>We value your privacy. This website does not collect, store, or share any personal information about you.</p>
                             <p>If you have any questions regarding this policy, please contact us at support@example.com.</p>',
                    'ar' => '<p>تاريخ السريان: ' . date('Y-m-d') . '</p>
                             <p>نحن نُقدّر خصوصيتك. هذا الموقع لا يقوم بجمع أو تخزين أو مشاركة أي معلومات شخصية عنك.</p>
                             <p>إذا كان لديك أي أسئلة بخصوص هذه السياسة، يرجى الاتصال بنا على support@example.com.</p>',
                ]),
            ]
        ];

        foreach ($pages as $pageData) {
            Page::insert($pageData);
        }
    }
}
