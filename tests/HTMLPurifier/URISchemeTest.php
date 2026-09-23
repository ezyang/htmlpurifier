<?php

// WARNING: All the URI schemes are far to relaxed, we need to tighten
// the checks.

class HTMLPurifier_URISchemeTest extends HTMLPurifier_URIHarness
{

    private $pngBase64;

    private $webpBase64;

    public function __construct()
    {
        $this->pngBase64 =
            'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABGdBTUEAALGP'.
            'C/xhBQAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9YGARc5KB0XV+IA'.
            'AAAddEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIFRoZSBHSU1Q72QlbgAAAF1J'.
            'REFUGNO9zL0NglAAxPEfdLTs4BZM4DIO4C7OwQg2JoQ9LE1exdlYvBBeZ7jq'.
            'ch9//q1uH4TLzw4d6+ErXMMcXuHWxId3KOETnnXXV6MJpcq2MLaI97CER3N0'.
            'vr4MkhoXe0rZigAAAABJRU5ErkJggg==';

        $this->webpBase64 =
            'UklGRmgEAABXRUJQVlA4IFwEAADwHgCdASrSAFwAPpE+nUglo6MhMBYYwLASCW'.
            'kA1UiodzVW9mz3k1fRnXP3Kw+iImBrW1Eu1RFN7L4yQYyffhCApE01KCCABFUjj'.
            'WhCcZP2a3kGp6Pa1O782rJGNk8D4N/mM2SJi75bNKwe5ekf5gjywl+p4wVRDm5zyC'.
            'Fk7SUnwQQzvdPbeMDerHH9+PHwm0lZtTSHS4jmvLtXW4xGcIjbpHUqEgWvnYs6JTex8i'.
            'VxKqPsWsOi4HEEDLbP7ZruIOyQBcH/drSjn/V0WhcbKPdnbuqAfhxOIYMUEBQhirYVh3z3Z'.
            '20sr/NFmMOXf8u2cOA5ZLJ9xpdu/FKj8MkP+8gAAP76osvQ/WtsL+wmS5SOeKpY23O1HJnSzpe'.
            '/rDf1QtvwG/gOt3WR0rJ7iyLpcC3jQcSdH5mf2+h4WtixXwMm1w2YTGrRDjXR3hmjH7u6fisYn/pVA'.
            'mmaAxVi+j/r242P8JXtWclQHshTyRDPW+US2ZWCh+9SxE1172aS4sYxUyuifikorBvNTaG12rsErGB/'.
            'NV1OsKJQ85jGdjoVrAw1W5Pd1q/6QykwYJsK0i6hrynp8JDHyBegRPEpI1mSxoQE39pAUYEgFB8zENUBI'.
            '0RKwcTKfd7fGb7LzEOndeIXYGi97+YRTreTn2oL6qiqJ6wFzKqN+JxQDwOgWcvZKIvA5CfV7RcEYkZ8++j'.
            'OsBlzzZtk6649UiXCYFFcmzSp4i7V0fY7s92MbpXPM5Vxa6KSOL/a+VwV8++qltXKvjUAjeC7Pt1AFgoA+'.
            'lPw+vltsTTYNWaUoe6DuD2QZeiGsyrurSZTXvaSdxRn+SkTSoQO1bL1vWfVaMK94MMOBEzNrTsNlr3JqEgL'.
            '61SbF1MdJ30/3fTIzjuhHfUv0hmU9b2ZF/T9jCFbops+MMC8Z6UCPrf6MT++LoUy0Qb1BUDJeemvzwcXIwjbB'.
            'eq5GWaCbdRucTUqiLGJbIDyFM0+IwzeyHeGCi3zrTfSCdrZ4wXLAejeE5dcK3rKuw/xu4bkzIbjdlTT6oCaaDs1'.
            'gFkgYtglQTDTNNJm+Fi84iWs7+XRE1y7+Ox1yL7xTIQHTkwh6at/MTaIFOIlS7QZCoWQd5QaSxYdMm0CGV6am27Zrr'.
            '/ubdrehqbwhl8uPo0dw8+2U+Q5e9q1mskmbs69/tjaGpkZX9RqzxzWYqq4qIpk9N0ypFm6LxrE9nvidgKtDGAel8KZr'.
            '051mITKexV2NIf37++xV0B7aUFYYsI6C8QwU64FE4N3ePgX1QZ/TZOdTqvb88twSiiGtIoClw+gLawmF56cgNYLaX/Nu'.
            'Wo4Er2y+XObYGKK3a6979iBRBFoYmEH/GLH7iFKl96vfl/tHwndp/+POAtQSsmIItU6JGQCoIMAofRQZEzkB3/PN+maHd9'.
            'efqQEBqPJKAmQvaQT52O/pTdNOgY7XN+zgyCCS/sGPU3F3dg56R2HJgXXxvkA+5jqkhCOXG+XrnpEYF/kEnoAAAA=';
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

    public function test_data_webp()
    {
        if (PHP_VERSION_ID < 70100) {
            // Webp not available below 7.1
            return;
        }

        $this->assertValidation(
            'data:image/webp;base64,'.$this->webpBase64
        );
    }

    public function test_data_webp_not_valid_below_71()
    {
        if (PHP_VERSION_ID >= 70100) {
            // Webp available above 7.1
            return;
        }

        $this->assertValidation(
            'data:image/webp;base64,'.$this->webpBase64,
            false
        );
    }


}

// vim: et sw=4 sts=4
