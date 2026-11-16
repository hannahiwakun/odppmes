<?php

namespace Tests\Unit;

use App\Cms\CmsNewsHtml;
use PHPUnit\Framework\TestCase;

class CmsNewsHtmlTest extends TestCase
{
    public function test_it_keeps_allowed_markup_and_safe_links(): void
    {
        $html = '<h2>Notice</h2><p>Read the <strong>full</strong> <em>update</em> at <a href="https://example.gov.ng/news">the Office site</a>.</p><ul><li>One</li></ul>';

        $safe = CmsNewsHtml::sanitize($html);

        $this->assertStringContainsString('<h2>Notice</h2>', $safe);
        $this->assertStringContainsString('<strong>full</strong>', $safe);
        $this->assertStringContainsString('<em>update</em>', $safe);
        $this->assertStringContainsString('href="https://example.gov.ng/news"', $safe);
        $this->assertStringContainsString('<ul>', $safe);
    }

    public function test_it_strips_scripts_event_handlers_and_javascript_links(): void
    {
        $html = '<p onclick="alert(1)">Clinic timetable extra text.</p><script>alert(1)</script><p><a href="javascript:alert(1)">Unsafe</a></p><iframe src="https://evil.test"></iframe>';

        $safe = CmsNewsHtml::sanitize($html);

        $this->assertStringNotContainsString('<script', strtolower($safe));
        $this->assertStringNotContainsString('onclick', strtolower($safe));
        $this->assertStringNotContainsString('javascript:', strtolower($safe));
        $this->assertStringNotContainsString('<iframe', strtolower($safe));
        $this->assertStringContainsString('Clinic timetable extra text.', $safe);
        $this->assertStringContainsString('Unsafe', $safe);
        $this->assertStringNotContainsString('<a href="javascript:alert(1)">', $safe);
    }

    public function test_plain_text_is_not_treated_as_html_and_renders_as_paragraphs(): void
    {
        $plain = "First paragraph.\n\nSecond paragraph.";

        $this->assertFalse(CmsNewsHtml::looksLikeHtml($plain));
        $this->assertSame($plain, CmsNewsHtml::normalizeIncoming($plain));

        $html = CmsNewsHtml::toSafeHtml($plain);

        $this->assertStringContainsString('<p>First paragraph.</p>', $html);
        $this->assertStringContainsString('<p>Second paragraph.</p>', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function test_b_and_i_are_normalised_to_strong_and_em(): void
    {
        $safe = CmsNewsHtml::sanitize('<p><b>Bold</b> and <i>italic</i> announcement text.</p>');

        $this->assertStringContainsString('<strong>Bold</strong>', $safe);
        $this->assertStringContainsString('<em>italic</em>', $safe);
        $this->assertStringNotContainsString('<b>', $safe);
        $this->assertStringNotContainsString('<i>', $safe);
    }
}
