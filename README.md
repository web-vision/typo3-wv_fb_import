# web-vision Facebook Import

This extensions aims to provide an importer for different Facebook information
into TYPO3.

## Features

At the moment the feature set is very basic. We will implement more features as
we, and our customers, need them. Feature Requests are also welcome.

### Posts -> News

You can import Facebook posts from a specified Page as News entries for
EXT:News.

## Installation

Just install the extension as usual via extension manager or CLI.

In case you downloaded it from Github, or used ```git clone``` please name the
extension ```wv_fb_import```.

### Import of posts

First you need an facebook APP.

Create a scheduler task and provide the ```pageId``` and ```accessToken```.
The ```pageId` is the name of the page, e.g. for the "TYPO3 User Group
Niederrhein" with url https://www.facebook.com/tugnr it's ```tugnr``` the part
of the url.
The ```accessToken``` is in format of "{{appId}}|{{appSecret}}".

An example call on CLI would be:

    $ typo3/cli_dispatch.phpsh extbase fbimport:importpostsasnews tugnr "1027905239253564|31083e117802c6ab48e96ee17d5414ad"

Notice: The ```accessToken``` has to be wrapped on CLI call.

All other information can be taken from the scheduler form or CLI help of the
scheduler task.
