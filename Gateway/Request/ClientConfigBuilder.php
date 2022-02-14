<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Request;

class ClientConfigBuilder extends \SR\Gateway\Model\Request\ClientConfigBuilder
{
    /**
     * @inheritDoc
     */
    public function build(array $buildSubject): array
    {
        // NOTE: the list of Curl OPTIONS which will be processed by Adapter as _config
        // @see: \Magento\Framework\HTTP\Adapter\Curl::$_allowedParams
        // @see: \Magento\Framework\HTTP\Adapter\Curl::_applyConfig => getDefaultConfig_foreach

        // NOTE: key 'header' can be used to set CURLOPT_HEADER
        //     it is not included into $_allowedParams. it handled only here:
        //     @see: \Magento\Framework\HTTP\Adapter\Curl::write
        //     if it is not passed - a value TRUE is used

        $defaultOptions = [
            CURLINFO_HEADER_OUT => true,

            /**
             * CURLOPT_FAILONERROR Description:
             *     TRUE to fail verbosely if the HTTP code returned is greater than or equal to 400.
             *     The default behavior is to return the page normally, ignoring the code.
             *
             * This means when TRUE the BODY is not returned for such cases.
             *
             * NOTE: but we NEED to get the BODY on Error/Fail because it contains the descriptions of the Error (ex: in XML format).
             */
            CURLOPT_FAILONERROR => false,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,

            // NOTE: SSL Certificate usage is Mandatory for Bitcom service interaction
            //CURLOPT_SSLCERT => $certFilepath,// NOTE: The name of a file containing a PEM formatted certificate.
            //CURLOPT_SSLCERTPASSWD => $certPassphrase,// NOTE: The password required to use the CURLOPT_SSLCERT certificate.
            //CURLOPT_SSLCERTTYPE => 'P12',

            /**
             * The Option is actual , when 'Accept-Encoding' Http Header is used
             *
             * The contents of the "Accept-Encoding: " header.
             * This enables decoding of the response. Supported encodings are "identity", "deflate", and "gzip".
             * If an empty string, "", is set, a header containing all supported encoding types is sent.
             */
            //CURLOPT_ENCODING => '',
        ];
        $userDefinedOptions = $this->fetchUserDefinedOptions($buildSubject);

        return [
            self::KEY_CLIENT_CONFIG => [
                'timeout' => 60,
                'verifypeer' => false,
                'verifyhost' => false,

                // NOTE: TRICK to pass Options for ClientAdapter
                self::PARAM_CURL_EXTRA_OPTIONS => array_replace_recursive($defaultOptions, $userDefinedOptions),
            ],
        ];
    }
}
