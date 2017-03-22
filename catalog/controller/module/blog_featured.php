<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ControllerModuleBlogFeatured extends Controller
{
    public function index($setting)
    {
        $this->load->language('module/blog_featured');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_more'] = $this->language->get('text_more');

        $this->load->model('blog/post');
        $this->load->model('blog/comment');

        $this->load->model('tool/image');

        $data['posts'] = array();

        if (!$setting['limit']) {
            $setting['limit'] = 5;
        }

        if (!empty($setting['post'])) {
            if (isset($setting['random_post']) && !empty($setting['random_post'])) {
                shuffle($setting['post']);
            }

            $posts = array_slice($setting['post'], 0, (int) $setting['limit']);

            foreach ($posts as $post_id) {
                $post_info = $this->model_blog_post->getPost($post_id);

                if ($post_info) {
                    if ($post_info['image']) {
                        $image = $this->model_tool_image->resize($post_info['image'], $setting['width'], $setting['height']);
                    } else {
                        $image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
                    }

                    $comment_total = $this->model_blog_comment->getTotalCommentsByPostId($post_info['post_id']);

                    $this->trigger->fire('pre.post.display', array(&$post_info, 'featured'));

                    $data['posts'][] = array(
                        'post_id'       => $post_info['post_id'],
                        'thumb'         => $image,
                        'name'          => $post_info['name'],
                        'description'   => $post_info['description'],
                        'viewed'        => $post_info['viewed'],
                        'comment_total' => $comment_total,
                        'date_added'    => date($this->language->get('date_format_short'), strtotime($post_info['date_available'])),
                        'author'        => $post_info['author'],
                        'href'          => $this->url->link('blog/post', 'post_id=' . $post_info['post_id'])
                    );
                }
            }
        }

        if ($data['posts']) {
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/blog_featured.tpl')) {
                return $this->load->view($this->config->get('config_template') . '/template/module/blog_featured.tpl', $data);
            } else {
                return $this->load->view('default/template/module/blog_featured.tpl', $data);
            }
        }
    }
}
