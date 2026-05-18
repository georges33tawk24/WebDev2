<?php

namespace Tests\Feature;

use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_switch_route_stores_locale_in_session(): void
    {
        $this->from(route('login'))
            ->get(route('locale.switch', 'ar'))
            ->assertRedirect(route('login'));

        $this->assertEquals('ar', session('locale'));
    }

    public function test_set_locale_middleware_applies_arabic_translations(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee(__('ui.auth.login', [], 'ar'), false);
    }

    public function test_locale_switch_to_english_clears_arabic_ui(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->from(route('login'))
            ->get(route('locale.switch', 'en'))
            ->assertRedirect(route('login'));

        $this->withSession(['locale' => 'en'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee(__('ui.auth.login', [], 'en'), false);
    }

    public function test_localized_entity_name_uses_database_arabic_column(): void
    {
        $office = Office::query()->create([
            'name' => 'Test Office EN',
            'name_ar' => 'مكتب اختبار',
        ]);

        app()->setLocale('ar');

        $this->assertSame('مكتب اختبار', $office->localized('name'));
        $this->assertSame('مكتب اختبار', localized_entity_name($office));
    }

    public function test_localized_time_option_uses_arabic_digits_when_locale_is_ar(): void
    {
        app()->setLocale('ar');

        $this->assertMatchesRegularExpression('/[٠-٩]/u', localized_time_option('09:00'));
    }

    public function test_flash_messages_translate_in_arabic(): void
    {
        app()->setLocale('ar');

        $this->assertSame(
            'تم رفع الهوية والتحقق منها بنجاح.',
            __('ui.flash.id_uploaded_verified')
        );
    }
}
