<?php

namespace MediaWiki\Extension\Z17DEV;

use OutputPage, Parser, Skin;
use Embed\{Embed, Http\CurlDispatcher};

/**
 * Class MW_EXT_Embed
 */
class MW_EXT_Embed
{
  /**
   * Register tag function.
   *
   * @param Parser $parser
   *
   * @return bool
   * @throws \MWException
   */
  public static function onParserFirstCallInit(Parser $parser)
  {
    $parser->setFunctionHook('embed', [__CLASS__, 'onRenderTag']);

    return true;
  }

  /**
   * Render tag function.
   *
   * @param Parser $parser
   * @param string $url
   *
   * @return bool|string
   */
  public static function onRenderTag(Parser $parser, $url = '')
  {
    // Argument: url.
    $getURL = MW_EXT_Kernel::outClear($url ?? '' ?: '');
    $outURL = $getURL;

    // Check URL.
    if (empty($outURL)) {
      $parser->addTrackingCategory('mw-ext-embed-error-category');

      return null;
    }

    $dispatcher = new CurlDispatcher([
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_ENCODING => '',
      CURLOPT_AUTOREFERER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
      CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    ]);

    // Get URL data.
    $getData = Embed::create($outURL, null, $dispatcher);
    $outData = $getData->code;

    // Out HTML.
    $outHTML = '<div class="mw-ext-embed navigation-not-searchable"><div class="mw-ext-embed-body"><div class="mw-ext-embed-content">' . $outData . '</div></div></div>';

    // Out parser.
    $outParser = $parser->insertStripItem($outHTML, $parser->mStripState);

    return $outParser;
  }

  /**
   * Load resource function.
   *
   * @param OutputPage $out
   * @param Skin $skin
   *
   * @return bool
   */
  public static function onBeforePageDisplay(OutputPage $out, Skin $skin)
  {
    $out->addModuleStyles(['ext.mw.embed.styles']);

    return true;
  }
}
