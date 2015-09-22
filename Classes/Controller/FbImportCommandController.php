<?php
namespace WebVision\WvFbImport\Controller;

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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use stdClass;

/**
 * Importer for Facebook (Open Graph) information.
 *
 * @author Daniel Siepmann <d.siepmann@web-vision.de>
 */
class FbImportCommandController extends CommandController
{
    /**
     * For UTF-8 string operations.
     *
     * @var \TYPO3\CMS\Core\Charset\CharsetConverter
     * @inject
     */
    protected $charsetConverter;

    /**
     * @var string The id of the page as string or integer.
     */
    protected $pageId = '';

    /**
     * Defines the database table name to use for insertions of news records.
     *
     * @return string
     */
    protected function getNewsTableName()
    {
        return 'tx_news_domain_model_news';
    }

    /**
     * Will import posts from feed for configured page from facebook as news entries.
     *
     * @param string $pageId The id of the page or the string of the page from url.
     * @param string $accessToken The access token used to query facebook graph API.
     * @param string $pid The UID if the page where the imported records should be.
     * @param string $categories Commaseparated list, with no spaces, of UIDs for categories which should be assigned.
     * @param string $author The author which should be applied to the records.
     *
     * @return void
     */
    public function postsAsNewsCommand($pageId, $accessToken, $pid, $categories = '', $author = '')
    {
        $this->pageId = (string) $pageId;
        $posts = $this->fetchPosts($accessToken);
        foreach($posts as $post) {
            $post->author = $author;
            $post->pid = $pid;
            $post->categories = $categories;
        }
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
        $fbResponse = json_decode(\TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($urlToFetch));
        $fbPosts = $fbResponse->data;

        $existingPosts = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'title',
            $this->getNewsTableName(),
            '1=1' .
            BackendUtility::BEenableFields($this->getNewsTableName()) .
            BackendUtility::deleteClause($this->getNewsTableName())
        );

        foreach($fbPosts as $key => $fbPost) {
            if(array_search(array('title' => $this->formatTitle($fbPost)), $existingPosts) !== false) {
                unset($fbPosts[$key]);
            }
        }

        return $fbPosts;
    }

    /**
     * Add the given posts to the persistence.
     *
     * @param array $posts
     *
     * @return ImportCommandController
     */
    protected function addPosts(array $posts)
    {
        // Links first, as we reference them in news entries.
        $recordsToInsert = array(
            'tx_news_domain_model_link' => array(),
            $this->getNewsTableName() => array(),
        );
        $tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');

        foreach($posts as $post) {
            $newsRecordId = 'NEW_' . $post->id;
            $linkRecordId = $newsRecordId . '-link';

            $recordsToInsert['tx_news_domain_model_link'][$linkRecordId] = array(
                'pid' => $post->pid,
                'uri' => $this->getPostUrl($post),
            );
            $recordsToInsert[$this->getNewsTableName()][$newsRecordId] = array(
                'pid' => $post->pid,

                'related_links' => $linkRecordId,
                'categories' => $post->categories,

                'author_email' => $post->author,

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

        return $this;
    }

    /**
     * Get date_time from post.
     *
     * @param stdClass $post
     *
     * @return string
     */
    protected function formatDateTime(stdClass $post)
    {
        $dateTime = new \DateTime($post->created_time);
        return $dateTime->format('U');
    }

    /**
     * Get title from post.
     *
     * @param stdClass $post
     *
     * @return string
     */
    protected function formatTitle(stdClass $post)
    {
        $title = $this->charsetConverter->utf8_substr($post->message, 0, 80);

        if($this->charsetConverter->utf8_strlen($post->message) > 80) {
            $title .= ' ...';
        }

        return $title;
    }

    /**
     * Get deeplink url to the post.
     *
     * @param stdClass $post
     *
     * @return string
     */
    protected function getPostUrl(stdClass $post)
    {
        $postId = substr(
            $post->id,
            strpos($post->id, '_') + 1
        );
        return 'https://www.facebook.com/' . $this->pageId . '/posts/' . $postId;
    }
}
