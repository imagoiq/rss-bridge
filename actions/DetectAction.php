<?php

/**
 * This file is part of RSS-Bridge, a PHP project capable of generating RSS and
 * Atom feeds for websites that don't have one.
 *
 * For the full license information, please view the UNLICENSE file distributed
 * with this source code.
 *
 * @package Core
 * @license http://unlicense.org/ UNLICENSE
 * @link    https://github.com/rss-bridge/rss-bridge
 */

class DetectAction implements ActionInterface
{
    public function execute(array $request)
    {
        $targetURL = $request['url']
            or returnClientError('You must specify a url!');

        $format = $request['format']
            or returnClientError('You must specify a format!');

        $bridgeFactory = new \BridgeFactory();

        foreach ($bridgeFactory->getBridgeClassNames() as $bridgeClassName) {
            if (!$bridgeFactory->isWhitelisted($bridgeClassName)) {
                continue;
            }

            $bridge = $bridgeFactory->create($bridgeClassName);

            if ($bridge === false) {
                continue;
            }

            $bridgeParams = $bridge->detectParameters($targetURL);

            if (is_null($bridgeParams)) {
                continue;
            }

            $bridgeParams['bridge'] = $bridgeClassName;
            $bridgeParams['format'] = $format;

            header('Location: ?action=display&' . http_build_query($bridgeParams), true, 301);
            exit;
        }

        returnClientError('No bridge found for given URL: ' . $targetURL);
    }
}
