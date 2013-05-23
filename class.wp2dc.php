<?php

const WORDPRESSXML = 'wordpressxml';

class wp2dc {
    private $filename;
    private $uploadDirectory;
    private $currentError;
    private $core;
    private $layer;

    public function __construct($core) {
        $this->uploadDirectory = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;
        $this->core = $core;
        $this->layer = NULL;

        if ( ! is_dir($this->uploadDirectory)) {
            if ( ! @mkdir($this->uploadDirectory)) {
                $this->core->error->add(__("Unable to create the upload directory"));
            }
        }
        $this->currentError = __('No error');
    }

    /** Test if the uploaded file have the correct extension
     **/
    public function isPostCorrect() {
        if (isset($_FILES[WORDPRESSXML])) {
            return (pathinfo($_FILES[WORDPRESSXML]['name'], PATHINFO_EXTENSION) == 'xml');
        }
        $this->currentError = __('No uploaded file');
        return FALSE;
    }

    /** Upload the file
     * @todo Ensure it's a XML file
     * @return The absolute path
     */
    public function uploadFile() {
        $name = $this->_sanitize($_FILES[WORDPRESSXML]['name']);
        $tempname = $_FILES[WORDPRESSXML]['tmp_name'];
        $result = move_uploaded_file($tempname, $this->uploadDirectory . $name);
        if ( ! $result) {
            $this->currentError = __('Can not upload the file');
            return FALSE;
        }
        return $this->uploadDirectory . $name;
    }

    /** Replace non ascii characters with a minus (-)
     * @param string $fileName The file name
     * @return string The sanitized file name
     */
    private function _sanitize($fileName) {
        return preg_replace('/([^.a-z0-9]+)/i', '-', $fileName);
    }

    /** Extract all categoriy field form a node
     */
    private function _doCategories(DOMNodeList $categNode) {
        $name = '';
        $parent = '';
        $nice = '';

        for ($j = 0; $j < $categNode->length; $j++) {
            if ($categNode->item($j) instanceof DOMText) {
                continue;
            }

            switch ($categNode->item($j)->nodeName) {
                case 'wp:cat_name':
                    $name = $categNode->item($j)->nodeValue;
                    break;

                case 'wp:category_nicename':
                    $nice = $categNode->item($j)->nodeValue;

                case 'wp:category_parent':
                    $parent = $categNode->item($j)->nodeValue;
                    break;

                default:
                    # Ignored
                    ;
            }
        }
        if ($parent == $name)
            $parent = FALSE;
        return array('name' => $name, 'parent' => $parent, 'nicename' => $nice);

    }

    private function _doTags(DOMNodeList $tagNode) {
        $name = '';
        for($i = 0; $i < $tagNode->length; $i++) {
            if ($tagNode->item($i) instanceof DOMText) {
                continue;
            }
            switch ($tagNode->item($i)->nodeName) {
                case 'wp:tag_name':
                    $name = $tagNode->item($i)->nodeValue;
                    break;
            }
        }
        return $name;
    }

    private function _doComment(DOMNodeList $commentNode) {
        $author = '';
        $email = '';
        $url = '';
        $date = '';
        $content = '';
        $ip = '';
        $status = '';
        for($i = 0; $i < $commentNode->length; $i++) {
            if ($commentNode->item($i) instanceof DOMText) {
                continue;
            }

            switch ($commentNode->item($i)->nodeName) {
                case 'wp:comment_author':
                    $author = $commentNode->item($i)->nodeValue;
                    break;

                case 'wp:comment_author_email':
                    $email = $commentNode->item($i)->nodeValue;
                    break;

                case 'wp:comment_author_url':
                    $url = $commentNode->item($i)->nodeValue;
                    break;

                case 'wp:comment_author_IP':
                    $ip = $commentNode->item($i)->nodeValue;
                    break;

                case 'wp:comment_date':
                    $date = $commentNode->item($i)->nodeValue;
                    break;

                case 'wp:comment_content':
                    $content = $commentNode->item($i)->nodeValue;
                    break;

                case 'wp:comment_approved':
                    $status = $commentNode->item($i)->nodeValue;
                    break;

                default:
                    ;
            }
        }

        return array(
                     'author' => $author,
                     'email' => $email,
                     'url' => $url,
                     'ip' => $ip,
                     'date' => $date,
                     'content' => $content,
                     'status' => $status
                     );
    }

    private function _doArticle(DOMNodeList $articleNode) {
        $title = '';
        $link = '';
        $pubDate = '';
        $content = '';
        $excerpt = '';
        $order = '';
        $type = '';
        $password = '';
        $openComment = '';
        $status = '';
        $categorie = array();
        $tags = array();
        $comments = array();

        for ($i = 0; $i < $articleNode->length; $i++) {
            if ($articleNode->item($i) instanceof DOMText) {
                continue;
            }

            switch ($articleNode->item($i)->nodeName) {
                case 'title':
                    $title = $articleNode->item($i)->nodeValue;
                    break;

                case 'link':
                    $link = $articleNode->item($i)->nodeValue;
                    break;

                case 'wp:post_date':
                    $pubDate = $articleNode->item($i)->nodeValue;
                    break;

                case 'content:encoded':
                    $content = $articleNode->item($i)->nodeValue;
                    break;

                case 'excerpt:encoded':
                    $excerpt = $articleNode->item($i)->nodeValue;
                    break;

                case 'wp:menu_order':
                    $order = $articleNode->item($i)->nodeValue;
                    break;

                case 'wp:post_type':
                    $type = $articleNode->item($i)->nodeValue;
                    break;

                case 'wp:post_password':
                    $password = $articleNode->item($i)->nodeValue;
                    break;

                case 'wp:comment_status':
                    $openComment = $articleNode->item($i)->nodeValue;
                    break;

                case 'wp:status':
                    $status = $articleNode->item($i)->nodeValue;
                    break;

                case 'category':
                    $domain = $articleNode->item($i)->getAttribute('domain');
                    if ($domain == 'category') {
                        $categories[] = $articleNode->item($i)->nodeValue;
                    } else if ($domain == 'post_tag') {
                        $tags[] = $articleNode->item($i)->nodeValue;
                    }
                    break;

                case 'wp:comment':
                    $comments[] = $this->_doComment($articleNode->item($i)->childNodes);
                    break;


                default:
                    ; # Does nothing here for coherency

            }
        }

        # No exceprt. Try to find the <!--more--> tag
        if ($excerpt == '') {
            $result = preg_split('/<!--more-->/', $content);
            if (count($result) == 2) {
                $excerpt = $result[0];
                $content = $result[1];
            }
        }

        return array(
            'title' => $title,
            'link' => $link,
            'pubDate' => $pubDate,
            'content' => $content,
            'excerpt' => $excerpt,
            'order' => $order,
            'type' => $type,
            'password' => $password,
            'openComment' => $openComment,
            'status' => $status,
            'comments' => $comments,
            'tags' => $tags
        );
    }

    private function _findParent($parent, $name, $categories) {
        $parentPath = '';

        foreach($categories as $categ) {
            if ($categ['nicename'] == $parent) {
                if ($categ['parent']) {
                    $parentPath .= $this->_findParent($categ['parent'], $categ['name'], $categories) . "/$name";
                } else {
                    $parentPath .= $categ['name'] . "/$name";
                }
            }
        }
        if ($parentPath == '') {
            return $name;
        }
        return $parentPath;
    }

    private function _refactorCategories(&$categories) {
        foreach($categories as $key => $value) {
            if ($categories[$key]['parent']) {
                $categories[$key]['url'] = $this->_findParent($categories[$key]['parent'], $categories[$key]['name'], $categories);
            } else {
                $categories[$key]['url'] = $categories[$key]['name'];
            }
        }
    }

    private function _changeBlogInformations($title, $description) {
        $title = $this->core->con->escape($title);
        $description = $this->core->con->escape($description);
        $this->core->con->begin();
        $this->core->con->execute('UPDATE ' . $this->core->prefix . "blog SET blog_name='$title', blog_desc='$description' WHERE blog_id='" . $this->core->blog->id . '\'');
        $this->core->con->commit();
    }

    private function _addCategories($categories) {
        $cur = $this->core->con->openCursor($this->core->prefix . 'category');
        foreach($categories as $category) {
            try {
                $cur->cat_title = $category['name'];
                $cur->cat_url = $category['url'];
                $this->core->callBehavior('adminBeforeCategoryCreate', $cur);
                $id = $this->core->blog->addCategory($cur);
    			$this->core->callBehavior('adminAfterCategoryCreate',$cur,$id);
            } catch (Exception $e) {
                $this->core->error->add($e->getMessage() . ' : ' . $category['url']);
            }
        }
        unset($cur);
    }

    private function _addArticles($articles) {

    }

    /** Process the file.
     * Wordpress XML Backup file is a RSS file.
     * @param string $filePath The XML file path
     * @throws Wp2dcException When a problem occured
     */
    public function processFile($path)  {
        $dom = new DOMDocument();
        $dom->load($path);
        $channel = $dom->getElementsByTagName('channel')->item(0)->childNodes;

        $categories = array();
        $tags = array();
        $articles = array();

        for( $i=0; $i < $channel->length; $i++) {
            $child = $channel->item($i);
            if ($child instanceof DOMText) {
                # It's always a line break or a space
                continue;
            }

            switch($child->nodeName) {
                case 'title':
                    $blogtitle = $child->nodeValue;
                    break;

                case 'description':
                    $description = $child->nodeValue;
                    break;

                case 'wp:category':
                    $categories[]  = $this->_doCategories($child->childNodes);
                    break;

                case 'item':
                    $articles[] = $this->_doArticle($child->childNodes);
                    break;
            }
        }

        $this->_refactorCategories($categories);
        $this->_changeBlogInformations($blogtitle, $description);
        $this->_addCategories($categories);
        $this->_addArticles($articles);
    }

    public function removeFile($path) {

    }

    /** Get the current error.
     * @return string The error message
     */
    public function error() {
        return $this->currentError;
    }
}
