<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Plugin\DirectusBlog\Utility\DirectusBlogUtility;

/**
 * Class DirectusBlogPlugin
 * @package Grav\Plugin
 */
class DirectusBlogPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [
                ['autoload', 100000], // TODO: Remove when plugin requires Grav >=1.7
                ['onPluginsInitialized', 0]
            ]
        ];
    }

    /**
    * Composer autoload.
    *is
    * @return ClassLoader
    */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main events we are interested in
        $this->enable([
            'onPageInitialized' => ['onPageInitialized', 0]
        ]);
    }

    /**
     * run hooks
     */
    public function onPageInitialized()
    {
        $this->processWebHooks($this->grav['uri']->route());
    }

    /**
     * select hook
     *
     * @param $route
     */
    private function processWebHooks($route) {
        switch ($route) {
            case '/' . $this->config["plugins.directus"]['directus']['hookPrefix'] . '/refresh-blog':
                $this->refreshBlog();
                break;
        }
    }

    /**
     * Main action - create blog pages
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function refreshBlog() {
        $directusUtil = new DirectusBlogUtility (
            $this->config["plugins.directus"]['directus']['directusAPIUrl'],
            $this->grav,
            $this->config["plugins.directus"]['directus']['email'],
            $this->config["plugins.directus"]['directus']['password'],
            $this->config["plugins.directus"]['directus']['token'],
            isset($this->config["plugins.directus"]['disableCors']) && $this->config["plugins.directus"]['disableCors']
        );

        try {
            $url = '';

            /*
            if(isset($this->config()['additional_params']) && isset($this->config()['additional_params']['filter']) && $this->config()['additional_params']) {
                $url = $directusUtil->generateRequestUrl($this->config()['blog_table'], 0, 2, $this->config()['additional_params']['filter']);
            } else {
                $url = $directusUtil->generateRequestUrl($this->config()['blog_table']);
            }
            */

            $url = $directusUtil->generateRequestUrl($this->config()['blog_table']);

            $response = $directusUtil->get($url)->toArray();

            foreach($response['data'] as $blogPost) {
                $path = $this->config()['blog_entrypoint'] . '/' . $blogPost[$this->config()['slug_field']];
                if ($blogPost['status'] === 'published') {
                    $this->createFile($blogPost, $path, true);
                } else {
                    $this->createFile($blogPost, $path);
                }

            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Blogposts imported'
            ]);
            exit(200);
        } catch(\Exception $e) {
            $this->grav['debugger']->addException($e);
            exit(500);
        }

        echo json_encode([
            'status' => 'warning',
            'message' => 'No blogposts found'
        ]);
        exit(404);
    }

    /**
     * @param array $blogPost
     * @param string $path
     * @param bool $published
     */
    private function createFile(array $blogPost, string $path, bool $published = false) {
        $headerText = '';

        if($published) {
            $headerText = $this->setFileHeaders($blogPost);
        } else {
            $headerText = $this->setRedirectFileHeaders();
        }


        if (!is_dir($path)) {
            mkdir($path);
        }
        $fp = fopen($path . '/'. $this->config()['blog_filename'], 'w');
        fwrite($fp, $headerText);
        fclose($fp);
    }

    /**
     * @return string
     */
    private function setRedirectFileHeaders() {

        $content =  '---' . "\n" .
                    'redirect: \'' . $this->config()['redirect_route'] . '\'' . "\n" .
                    'sitemap:' . "\n" .
                    '    ignore: true' . "\n" .
                    'published: false' . "\n" .
                    '---' . "\n";
        return $content;
    }

    /**
     * create markdown header for blogpost
     *
     * @param array $blogPost
     * @return string
     */
    private function setFileHeaders(array $blogPost) {
        $timestamp = strtotime($blogPost[$this->config()['mapping']['column_date']]);
        $dateString = "'" . date('d-m-Y H:i', $timestamp) . "'";

        $postContent =  '---' . "\n" .
                        'title: ' . "'" . $blogPost[$this->config()['mapping']['column_title']] . "'\n" .
                        'date: ' . $dateString . "\n".
                        'taxonomy:' . "\n".
                        '    category:' . "\n".
                        '        - ' . $blogPost[$this->config()['mapping']['column_category']] . "\n".
                        'directus:' . "\n".
                        '    collection: ' . $this->config()['blog_table'] . "\n".
                        '    depth: 4' . "\n".
                        '    id: ' . $blogPost['id'] . "\n" .
                        '---';

        return $postContent;
    }
}

