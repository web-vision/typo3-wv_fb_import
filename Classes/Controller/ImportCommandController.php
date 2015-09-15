<?php
namespace WebVision\WvOgImport\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use stdClass;

/**
 *
 * @author Daniel Siepmann <d.siepmann@web-vision.de>
 */
class ImportCommandController extends CommandController
{
    /**
     * @var string The id of the page as string or integer.
     */
    protected $pageId = '';

    /**
     * Will import feed for configured page from facebook.
     *
     * E.g. import them as tx_news entries.
     *
     * @param string $pageId The id of the page or the string of the page from url.
     * @param string $accessToken The access token used to query facebook graph API.
     *
     * @return void
     */
    public function importPageFeedCommand($pageId, $accessToken)
    {
        $this->pageId = (string) $pageId;
        $posts = $this->fetchPosts($accessToken);
        $this->addPosts($posts);
    }

    /**
     * Will fetch the posts from facebook.
     *
     * @param string $accessToken The access token used to query facebook graph API.
     *
     * @see https://developers.facebook.com/docs/graph-api/reference/v2.4/page/feed
     *
     * @return array With stdClass instances containing information about the posts.
     */
    protected function fetchPosts($accessToken)
    {
        $urlToFetch = 'https://graph.facebook.com/' . $this->pageId .
            '/posts?access_token=' . $accessToken;
        $this->outputLine('Fetch via: "' . $urlToFetch . '"');
        $posts = json_decode(\TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($urlToFetch));

        // WVTODO: Only fetch posts we didn't import before.
        // So e.g. fetch posts from DB and diff them.

        return $posts->data;
    }

    protected function addPosts(array $posts)
    {
        // Links first, as we reference them in news entries.
        $recordsToInsert = array(
            'tx_news_domain_model_link' => array(),
            'tx_news_domain_model_news' => array(),
        );
        $tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');

        foreach($posts as $post) {
            $newsRecordId = 'NEW_' . $post->id;
            $linkRecordId = $newsRecordId . '-link';

            $recordsToInsert['tx_news_domain_model_link'][$linkRecordId] = array(
                'pid' => 18000026,
                'uri' => $this->getPostUrl($post),
            );
            $recordsToInsert['tx_news_domain_model_news'][$newsRecordId] = array(
                'pid' => 18000026,

                'related_links' => $linkRecordId,
                'categories' => 9,

                'author' => 'Provinzial Rheinland',

                'datetime' => $this->formatDateTime($post),
                'title' => $this->formatTitle($post),
            );
        }

        $tce->stripslashes_values = 0;
        $tce->start(
            $recordsToInsert,
            array()
        );
        $tce->process_datamap();
    }

    protected function formatDateTime(stdClass $post)
    {
        $dateTime = new \DateTime($post->created_time);
        return $dateTime->format('U');
    }

    protected function formatTitle(stdClass $post)
    {
        $title = substr($post->message, 0, 80);

        if(strlen($post->message) > 80) {
            $title .= ' ...';
        }

        return $title;
    }

    protected function getPostUrl(stdClass $post)
    {
        $postId = substr(
            $post->id,
            strpos($post->id, '_') + 1
        );
        return 'https://www.facebook.com/' . $this->pageId . '/posts/' . $postId;
    }
}
