<?php
namespace WebVision\WvFbImport\Utility\Format;

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

use \stdClass;

/**
 * Provides methods to format a post.
 *
 * @author Daniel Siepmann <d.siepmann@web-vision.de>
 */
class Post
{
    /**
     * For UTF-8 string operations.
     *
     * @var \TYPO3\CMS\Core\Charset\CharsetConverter
     * @inject
     */
    protected $charsetConverter;

    /**
     * Get date_time from post.
     *
     * @param stdClass $post
     *
     * @return string
     */
    public function formatDateTime(stdClass $post)
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
    public function formatTitle(stdClass $post)
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
     * @param string $pageId The od of the facebook page containing the post.
     *
     * @return string
     */
    public function getPostUrl(stdClass $post, $pageId)
    {
        $postId = substr(
            $post->id,
            strpos($post->id, '_') + 1
        );
        return 'https://www.facebook.com/' . $pageId . '/posts/' . $postId;
    }
}
