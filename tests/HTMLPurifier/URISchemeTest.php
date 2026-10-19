<?php

// WARNING: All the URI schemes are far to relaxed, we need to tighten
// the checks.

class HTMLPurifier_URISchemeTest extends HTMLPurifier_URIHarness
{

    private $pngBase64;

    public function __construct()
    {
        $this->pngBase64 =
            'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABGdBTUEAALGP'.
            'C/xhBQAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9YGARc5KB0XV+IA'.
            'AAAddEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIFRoZSBHSU1Q72QlbgAAAF1J'.
            'REFUGNO9zL0NglAAxPEfdLTs4BZM4DIO4C7OwQg2JoQ9LE1exdlYvBBeZ7jq'.
            'ch9//q1uH4TLzw4d6+ErXMMcXuHWxId3KOETnnXXV6MJpcq2MLaI97CER3N0'.
            'vr4MkhoXe0rZigAAAABJRU5ErkJggg==';
    }

    protected function assertValidation($uri, $expect_uri = true)
    {
        $this->prepareURI($uri, $expect_uri);
        $this->config->set('URI.AllowedSchemes', array($uri->scheme));
        // convenience hack: the scheme should be explicitly specified
        $scheme = $uri->getSchemeObj($this->config, $this->context);
        $result = $scheme->validate($uri, $this->config, $this->context);
        $this->assertEitherFailOrIdentical($result, $uri, $expect_uri);
    }

    public function test_http_regular()
    {
        $this->assertValidation(
            'http://example.com/?s=q#fragment'
        );
    }

    public function test_http_uppercase()
    {
        $this->assertValidation(
            'http://example.com/FOO'
        );
    }

    public function test_http_removeDefaultPort()
    {
        $this->assertValidation(
            'http://example.com:80',
            'http://example.com'
        );
    }

    public function test_http_removeUserInfo()
    {
        $this->assertValidation(
            'http://bob@example.com',
            'http://example.com'
        );
    }

    public function test_http_preserveNonDefaultPort()
    {
        $this->assertValidation(
            'http://example.com:8080'
        );
    }

    public function test_https_regular()
    {
        $this->assertValidation(
            'https://user@example.com:443/?s=q#frag',
            'https://example.com/?s=q#frag'
        );
    }

    public function test_ftp_regular()
    {
        $this->assertValidation(
            'ftp://user@example.com/path'
        );
    }

    public function test_ftp_removeDefaultPort()
    {
        $this->assertValidation(
            'ftp://example.com:21',
            'ftp://example.com'
        );
    }

    public function test_ftp_removeQueryString()
    {
        $this->assertValidation(
            'ftp://example.com?s=q',
            'ftp://example.com'
        );
    }

    public function test_ftp_preserveValidTypecode()
    {
        $this->assertValidation(
            'ftp://example.com/file.txt;type=a'
        );
    }

    public function test_ftp_removeInvalidTypecode()
    {
        $this->assertValidation(
            'ftp://example.com/file.txt;type=z',
            'ftp://example.com/file.txt'
        );
    }

    public function test_ftp_encodeExtraSemicolons()
    {
        $this->assertValidation(
            'ftp://example.com/too;many;semicolons=1',
            'ftp://example.com/too%3Bmany%3Bsemicolons=1'
        );
    }

    public function test_news_regular()
    {
        $this->assertValidation(
            'news:gmane.science.linguistics'
        );
    }

    public function test_news_explicit()
    {
        $this->assertValidation(
            'news:642@eagle.ATT.COM'
        );
    }

    public function test_news_removeNonPathComponents()
    {
        $this->assertValidation(
            'news://user@example.com:80/rec.music?path=foo#frag',
            'news:/rec.music#frag'
        );
    }

    public function test_nntp_regular()
    {
        $this->assertValidation(
            'nntp://news.example.com/alt.misc/42#frag'
        );
    }

    public function test_nntp_removalOfRedundantOrUselessComponents()
    {
        $this->assertValidation(
            'nntp://user@news.example.com:119/alt.misc/42?s=q#frag',
            'nntp://news.example.com/alt.misc/42#frag'
        );
    }

    public function test_mailto_regular()
    {
        $this->assertValidation(
            'mailto:bob@example.com'
        );
    }

    public function test_mailto_removalOfRedundantOrUselessComponents()
    {
        $this->assertValidation(
            'mailto://user@example.com:80/bob@example.com?subject=Foo#frag',
            'mailto:/bob@example.com?subject=Foo#frag'
        );
    }

    public function test_tel_strip_punctuation()
    {
        $this->assertValidation(
            'tel:+1 (555) 555-5555', 'tel:+15555555555'
        );
    }

    public function test_tel_with_url_encoding()
    {
        $this->assertValidation(
            'tel:+1%20(555)%20555-5555', 'tel:+15555555555'
        );
    }

    public function test_tel_regular()
    {
        $this->assertValidation(
            'tel:+15555555555'
        );
    }

    public function test_tel_with_extension()
    {
        $this->assertValidation(
            'tel:+1-555-555-5555x123', 'tel:+15555555555x123'
        );
    }

    public function test_tel_no_plus()
    {
        $this->assertValidation(
            'tel:555-555-5555', 'tel:5555555555'
        );
    }

    public function test_tel_strip_letters()
    {
        $this->assertValidation(
            'tel:abcd1234',
            'tel:1234'
        );
    }

    public function test_sms_rejects_spaces_in_recipient()
    {
        // RFC 3966: spaces MUST NOT be used as visual separators, so the
        // recipient is rejected rather than repaired
        $this->assertValidation(
            'sms:+1 (555) 555-5555', false
        );
    }

    public function test_sms_rejects_encoded_spaces_in_recipient()
    {
        $this->assertValidation(
            'sms:+1%20(555)%20555-5555', false
        );
    }

    public function test_sms_regular()
    {
        $this->assertValidation(
            'sms:+15555555555'
        );
    }

    public function test_sms_strips_visual_separators()
    {
        // "-", ".", "(" and ")" carry no meaning (RFC 3966) and are removed
        $this->assertValidation(
            'sms:+1-555.555(5555)', 'sms:+15555555555'
        );
    }

    public function test_sms_strips_dashes()
    {
        $this->assertValidation(
            'sms:555-555-5555', 'sms:5555555555'
        );
    }

    public function test_sms_rejects_letters_in_recipient()
    {
        // RFC 3966 does not support keypad letters (1-800-FLOWERS); silently
        // deleting them would manufacture a different destination
        $this->assertValidation(
            'sms:abcd1234', false
        );
    }

    public function test_sms_rejects_interior_plus()
    {
        $this->assertValidation(
            'sms:555+555', false
        );
    }

    public function test_sms_rejects_encoded_interior_plus()
    {
        $this->assertValidation(
            'sms:555%2B555', false
        );
    }

    public function test_sms_rejects_lone_plus()
    {
        $this->assertValidation(
            'sms:+', false
        );
    }

    public function test_sms_multiple_recipients()
    {
        // RFC 5724 allows comma-separated recipients; each is validated
        $this->assertValidation(
            'sms:+1555,+2666?body=hi'
        );
    }

    public function test_sms_rejects_invalid_recipient_in_list()
    {
        $this->assertValidation(
            'sms:+1555,abc?body=hi', false
        );
    }

    public function test_sms_rejects_recipient_parameters()
    {
        // RFC 3966 ";ext=" etc. are not supported: reject rather than
        // folding their digits into the number
        $this->assertValidation(
            'sms:5555;ext=123?body=hi', false
        );
    }

    public function test_sms_rejects_phone_context()
    {
        // Valid RFC 3966 for a local number, but outside the restricted
        // subset this validator accepts: reject rather than interpret
        $this->assertValidation(
            'sms:5555;phone-context=+1-555?body=hi', false
        );
    }

    public function test_sms_rejects_star_in_recipient()
    {
        $this->assertValidation(
            'sms:555*555?body=hi', false
        );
    }

    public function test_sms_rejects_encoded_comma_in_recipient()
    {
        // Only a literal comma separates recipients; "%" is no digit,
        // so a percent-encoded comma fails validation
        $this->assertValidation(
            'sms:555%2C666?body=hi', false
        );
    }

    public function test_sms_rejects_malformed_percent_escape_in_recipient()
    {
        $this->assertValidation(
            'sms:555%zz?body=hi', false
        );
    }

    public function test_sms_rejects_trailing_newline_in_recipient()
    {
        // The recipient pattern must anchor past a trailing newline,
        // hence the /D modifier
        $this->assertValidation(
            "sms:555\n", false
        );
    }

    public function test_sms_rejects_trailing_newline_in_recipient_with_body()
    {
        $this->assertValidation(
            "sms:+1555\n?body=hi", false
        );
    }

    public function test_sms_rejects_encoded_newline_in_recipient()
    {
        $this->assertValidation(
            'sms:555%0A', false
        );
    }

    public function test_sms_with_body_query()
    {
        // Undocumented iOS compatibility form (no "?"); preserved as written
        $this->assertValidation(
            'sms:5555&body=HOME',
            'sms:5555&body=HOME'
        );
    }

    public function test_sms_drops_unknown_field_in_path()
    {
        // Unknown fields are dropped by the sanitizer's allowlist policy
        $this->assertValidation(
            'sms:+15555555555&body=Hello&subject=Test',
            'sms:+15555555555&body=Hello'
        );
    }

    public function test_sms_with_url_encoded_body()
    {
        $this->assertValidation(
            'sms:5555&body=Hello%20World',
            'sms:5555&body=Hello%20World'
        );
    }

    public function test_sms_encoded_markup_in_path_body_stays_encoded()
    {
        // The body value passes through as received: already-encoded text
        // stays encoded, and characters the generic layer preserves are
        // not encoded further by this scheme
        $this->assertValidation(
            'sms:5555&body=%3Cscript%3Ealert(1)%3C/script%3E'
        );
    }

    public function test_sms_drops_unknown_query_field()
    {
        $this->assertValidation(
            'sms:5555?body=HOME&subject=Test',
            'sms:5555?body=HOME'
        );
    }

    public function test_sms_keeps_path_body_with_bodiless_query()
    {
        // A query without a body does not erase the compatibility form's body
        $this->assertValidation(
            'sms:5555&body=HOME?subject=T',
            'sms:5555&body=HOME'
        );
    }

    public function test_sms_pct_encoded_field_name_is_normalized()
    {
        // The parser decodes pct-encoded unreserved characters (RFC 3986
        // normalization), so this arrives as "body=hi"
        $this->assertValidation(
            'sms:5555?%62ody=hi',
            'sms:5555?body=hi'
        );
    }

    public function test_sms_no_body()
    {
        $this->assertValidation(
            'sms:988'
        );
    }

    public function test_sms_standard_query_format()
    {
        // RFC 5724's ?body= form is preserved as-is
        $this->assertValidation(
            'sms:741741?body=SEIZE'
        );
    }

    public function test_sms_rejects_body_in_path_and_query()
    {
        // The body must occur at most once (RFC 5724); a body in both the
        // compatibility path form and the query is a duplicate
        $this->assertValidation(
            'sms:741741&body=PATH?body=QUERY',
            false
        );
    }

    public function test_sms_encoded_markup_in_query_body_stays_encoded()
    {
        $this->assertValidation(
            'sms:5555?body=%3Cimg%20src=x%20onerror=alert(1)%3E'
        );
    }

    public function test_sms_double_encoded_body_keeps_its_level()
    {
        // One decode, one encode: a double encoded body is a fixed point
        $this->assertValidation(
            'sms:5555?body=%253Cscript%253E'
        );
    }

    public function test_sms_non_ascii_body_in_query()
    {
        $this->assertValidation(
            'sms:5555?body=%E2%9C%93%20ok'
        );
    }

    public function test_sms_non_ascii_body_in_path()
    {
        $this->assertValidation(
            'sms:5555&body=%E2%9C%93'
        );
    }

    public function test_sms_short_code()
    {
        $this->assertValidation(
            'sms:741741&body=SEIZE'
        );
    }

    public function test_sms_body_name_is_case_insensitive_in_query()
    {
        // RFC 5724 spells the field name as the ABNF literal "body", and
        // RFC 5234 makes ABNF literals case-insensitive
        $this->assertValidation(
            'sms:5555?BODY=HOME',
            'sms:5555?body=HOME'
        );
    }

    public function test_sms_body_name_is_case_insensitive_in_path()
    {
        $this->assertValidation(
            'sms:5555&Body=HOME',
            'sms:5555&body=HOME'
        );
    }

    public function test_sms_rejects_duplicate_body_in_query()
    {
        $this->assertValidation(
            'sms:5555?body=first&body=second',
            false
        );
    }

    public function test_sms_rejects_duplicate_body_in_path()
    {
        $this->assertValidation(
            'sms:5555&body=first&body=second',
            false
        );
    }

    public function test_sms_drops_path_field_without_body()
    {
        $this->assertValidation(
            'sms:5555&subject=T',
            'sms:5555'
        );
    }

    public function test_sms_drops_query_without_body()
    {
        $this->assertValidation(
            'sms:5555?subject=T',
            'sms:5555'
        );
    }

    public function test_sms_keeps_quotes_in_body_encoded()
    {
        // Quotes are percent-encoded, not deleted: the message survives and
        // still cannot terminate the href attribute
        $this->assertValidation(
            'sms:5555?body=say%20%22hi%22%20%27there%27',
            'sms:5555?body=say%20%22hi%22%20%27there%27'
        );
    }

    public function test_sms_keeps_quotes_in_path_body_encoded()
    {
        $this->assertValidation(
            'sms:5555&body=say%20%22hi%22',
            'sms:5555&body=say%20%22hi%22'
        );
    }

    public function test_sms_drops_field_without_value()
    {
        $this->assertValidation(
            'sms:5555&body',
            'sms:5555'
        );
    }

    public function test_sms_encoded_ampersand_in_body()
    {
        // A pct-encoded "&" is data and does not split fields
        $this->assertValidation(
            'sms:5555?body=a%26b',
            'sms:5555?body=a%26b'
        );
    }

    public function test_sms_empty_query()
    {
        $this->assertValidation(
            'sms:5555?',
            'sms:5555'
        );
    }

    public function test_sms_rejects_authority()
    {
        // The authority is discarded like the other non-hierarchical
        // schemes do; the remaining "/..." path is no valid recipient
        $this->assertValidation(
            'sms://user@example.com:99/5555',
            false
        );
    }

    public function test_sms_rejects_numeric_authority()
    {
        $this->assertValidation(
            'sms://5551234?body=HOME',
            false
        );
    }

    public function test_sms_rejects_hostname_authority()
    {
        $this->assertValidation(
            'sms://example.com?body=HOME',
            false
        );
    }

    public function test_sms_rejects_missing_recipient()
    {
        $this->assertValidation(
            'sms:?body=HOME',
            false
        );
    }

    public function test_sms_rejects_missing_recipient_path_form()
    {
        $this->assertValidation(
            'sms:&body=HOME',
            false
        );
    }

    public function test_sms_rejects_empty()
    {
        $this->assertValidation(
            'sms:',
            false
        );
    }

    public function test_sms_preserves_fragment()
    {
        $this->assertValidation(
            'sms:5555#frag'
        );
    }

    public function test_data_png()
    {
        $this->assertValidation(
            'data:image/png;base64,'.$this->pngBase64
        );
    }

    public function test_data_malformed()
    {
        $this->assertValidation(
            'data:image/png;base64,vr4MkhoXJRU5ErkJggg==',
            false
        );
    }

    public function test_data_implicit()
    {
        $this->assertValidation(
            'data:base64,'.$this->pngBase64,
            'data:image/png;base64,'.$this->pngBase64
        );
    }

    public function test_file_basic()
    {
        $this->assertValidation(
            'file://user@MYCOMPUTER:12/foo/bar?baz#frag',
            'file://MYCOMPUTER/foo/bar#frag'
        );
    }

    public function test_file_local()
    {
        $this->assertValidation(
            'file:///foo/bar?baz#frag',
            'file:///foo/bar#frag'
        );
    }

    public function test_ftp_empty_host()
    {
        $this->assertValidation('ftp:///example.com', false);
    }

    public function test_data_bad_base64()
    {
        $this->assertValidation('data:image/png;base64,aGVsbG90aGVyZXk|', false);
    }

    public function test_data_too_short()
    {
        $this->assertValidation('data:image/png;base64,aGVsbG90aGVyZXk=', false);
    }

}

// vim: et sw=4 sts=4
